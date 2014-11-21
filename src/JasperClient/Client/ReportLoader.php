<?php

namespace JasperClient\Client;

use JasperClient\Client\Report;

/**
 * Object to handle loading reports that were cached by the jasper client
 */
class ReportLoader 
{
    ///////////////
    // CONSTANTS //
    ///////////////

    const FORMAT_HTML = 'html';
    const FORMAT_PDF  = 'pdf';
    const FORMAT_XLS  = 'xls';

    const MESSAGE_REPORT_NOT_FOUND = 'The requested report could not be retrieved';
    const MESSAGE_REPORT_NOT_IN_FORMAT = 'The requested report is not available in the requested format';

    ///////////////
    // VARIABLES //
    ///////////////

    /**
     * Path to the report cache
     * @var string
     */
    private $reportCache;

    /**
     * Default Value for the page option
     * @var int
     */
    private $defaultPage;

    /**
     * Default on whether to attach an asset url upon loading
     * @var boolean
     */
    private $defaultAttachAssetUrl;

    /**
     * The default asset url to attach if attaching asset urls is set to true
     *   Needs to have placeholds !asset! and !requestId!
     * @var string
     */
    private $defaultAssetUrl;


    //////////////////
    // BASE METHODS //
    //////////////////

    /**
     * Constructor
     * 
     * @param string $reportCache Path to the report cache
     */
    public function __construct(
        $reportCache = 'report_cache/',
        $defaultAttachAssetUrl = false,
        $defaultAssetUrl = '',
        $defaultPage = 1
        ) {
        //Set stuff
        $this->reportCache = $reportCache;
        $this->defaultAttachAssetUrl = $defaultAttachAssetUrl;
        $this->defaultAssetUrl = $defaultAssetUrl;
        $this->defaultPage = $defaultPage;
    }


    ///////////////////
    // CLASS METHODS //
    ///////////////////


    /**
     * Checks if a report is saved in the report store
     *
     * @param  string  $requestId The request id of the report to check
     *
     * @return boolean            Whether the report is in the store or not
     */
    public function checkIfReportIsStored($requestId) {
        //Check if the report exists within the cache
        $cacheDir = JasperHelper::generateReportCacheFolderPath($requestId, $this->reportCache);

        //Check that the report was cached
        if (file_exists($cacheDir)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Gets the output of a report from the cache
     * 
     * @param  string $requestId Request Id of the report to retrieve
     * @param  string $format    Format to retrieve the report in
     * @param  array  $options   Options Array:
     *                             'page'     => The page to load if html
     *                             'attachAssetUrl' => Whether to attach an asset Url to the report output
     *                             'assetUrl' => An asset url to place on the reports when loading
     * 
     * @return JasperClient\Client\Report The loaded report object from the cache
     */
    public function getCachedReport($requestId, $format, $options = array()) {
        //Handle the options
        $page = (isset($options['page']) && null !== $options['page']) ? $options['page'] : $this->defaultPage;
        $attachAssetUrl = (isset($options['attachAssetUrl']) && null !== $options['attachAssetUrl']) 
            ? $options['attachAssetUrl'] : $this->defaultAttachAssetUrl;
        $assetUrl = (isset($options['assetUrl']) && null !== $options['assetUrl']) ? $options['assetUrl'] : $this->defaultAssetUrl;

        //Check if the report exists within the cache
        $cacheDir = JasperHelper::generateReportCacheFolderPath($requestId, $this->reportCache);

        //Check that the report was cached
        if (!file_exists($cacheDir)) {
            throw new \Exception(self::MESSAGE_REPORT_NOT_FOUND);
        }

        //Get the meta data
        $metaDataFile = $cacheDir . '/report_execution_details.xml';
        if (!file_exists($metaDataFile)) {
            throw new \Exception(self::MESSAGE_REPORT_NOT_FOUND);
        }

        try {
            $redOutput = file_get_contents($metaDataFile);
            $red = new \SimpleXMLElement($redOutput);
        } catch(\Exception $e) {
            throw $e;
        }

        //Create a new report object to store the information in
        $report = new Report($format, $page);
        $report->loadReportExecutionDetails($red);

        //Check that the report is in the request format
        if (!in_array($format, $report->getAvailableFormats())) {
            throw new \Exception(self::MESSAGE_REPORT_NOT_IN_FORMAT);
        }

        //Check that the requested format is available
        if (self::FORMAT_HTML === $format) {
            $cacheFile = $cacheDir . '/html_page_' . $page . '.html';
        } else {
            $cacheFile = $cacheDir . '/export.' . $format;
        }

        if (file_exists($cacheFile)) {
            //Load the cached file
            try {
                $output = file_get_contents($cacheFile);
            } catch(\Exception $e) {
                throw $e;
            }

            $report->setOutput($output);

            //If set, call the attach asset url function of the report object
            if ($attachAssetUrl && self::FORMAT_HTML === $format) {
                $report->addAssetUrl($assetUrl);
            }
        } else {
            $report->setOutput('The report is empty');
        }

        //Return the report
        return $report;
    }


    /**
     * Returns the raw output of a cached asset
     *
     * @param  string $asset     The name of the raw asset relative to the cache folder (e.g. images/img_0_0_2.png)
     * @param  string $requestId The request id of the report the asset is associated with
     *
     * @return string            The raw output of the asset
     */
    public function getCachedAsset($asset, $requestId) {
        //Get the directory to this report
        $cacheDir = JasperHelper::generateReportCacheFolderPath($requestId, $this->reportCache);

        //check that the file exists
        if (file_exists($cacheDir . '/images/' . $asset)) {
            return file_get_contents($cacheDir . '/images/' . $asset);
        } else {
            //return an empty string until better error handling can be put in place
            return '';
        }
    }


    /////////////////////////
    // GETTERS AND SETTERS //
    /////////////////////////


    /**
     * Gets the Path to the report cache.
     *
     * @return string
     */
    public function getReportCache()
    {
        return $this->reportCache;
    }

    /**
     * Sets the Path to the report cache.
     *
     * @param string $reportCache the report cache
     *
     * @return self
     */
    public function setReportCache($reportCache)
    {
        $this->reportCache = $reportCache;

        return $this;
    }

    /**
     * Gets the Default Value for the page option.
     *
     * @return int
     */
    public function getDefaultPage()
    {
        return $this->defaultPage;
    }

    /**
     * Sets the Default Value for the page option.
     *
     * @param int $defaultPage the default page
     *
     * @return self
     */
    public function setDefaultPage($defaultPage)
    {
        $this->defaultPage = $defaultPage;

        return $this;
    }

    /**
     * Gets the Default on whether to attach an asset url upon loading.
     *
     * @return boolean
     */
    public function getDefaultAttachAssetUrl()
    {
        return $this->defaultAttachAssetUrl;
    }

    /**
     * Sets the Default on whether to attach an asset url upon loading.
     *
     * @param boolean $defaultAttachAssetUrl the default attach asset url
     *
     * @return self
     */
    public function setDefaultAttachAssetUrl($defaultAttachAssetUrl)
    {
        $this->defaultAttachAssetUrl = $defaultAttachAssetUrl;

        return $this;
    }

    /**
     * Gets the The default asset url to attach if attaching asset urls is set to true
     *   Needs to have placeholds !asset! and !requestId!.
     *
     * @return string
     */
    public function getDefaultAssetUrl()
    {
        return $this->defaultAssetUrl;
    }

    /**
     * Sets the The default asset url to attach if attaching asset urls is set to true
     *    Needs to have placeholds !asset! and !requestId!.
     *
     * @param string $defaultAssetUrl the default asset url
     *
     * @return self
     */
    public function setDefaultAssetUrl($defaultAssetUrl)
    {
        $this->defaultAssetUrl = $defaultAssetUrl;

        return $this;
    }
}