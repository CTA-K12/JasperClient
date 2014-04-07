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


    //////////////////
    // BASE METHODS //
    //////////////////

    /**
     * Constructor
     * 
     * @param string $reportCache Path to the report cache
     */
    public function __construct($reportCache = 'report_cache/') {
        //Set the variables
        $this->reportCache = $reportCache;
    }


    ///////////////////
    // CLASS METHODS //
    ///////////////////


    /**
     * Gets the output of a report from the cache
     * 
     * @param  string $requestId Request Id of the report to retrieve
     * @param  string $format    Format to retrieve the report in
     * @param  array  $options   Options Array:
     *                             'page' => The page to load if html
     * 
     * @return JasperClient\Client\Report The loaded report object from the cache
     */
    public function getCachedReport($requestId, $format, $options = []) {
        //Handle the options
        $page = (isset($options['page']) && null !== $options['page'] && is_int($options['page'])) ? $options['page'] : 1;

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

        //Check that the requested format is available
        if (self::FORMAT_HTML === $format) {
            $cacheFile = $cacheDir . '/html_page_' . $page . '.html';
        } else {
            $cacheFile = $cacheDir . '/export' . $format;
        }

        if (file_exists($cacheFile)) {
            //Load the cached file
            try {
                $output = file_get_contents($cacheFile);
            } catch(\Exception $e) {
                throw $e;
            }

            //Create a new report object to store the information in
            $report = new Report($format, $page);
            $report->loadReportExecutionDetails($red);
            $report->setOutput($output);

            //Return the report
            return $report;
        } else {
            throw new \Exception(self::MESSAGE_REPORT_NOT_IN_FORMAT);
        }
    }

}