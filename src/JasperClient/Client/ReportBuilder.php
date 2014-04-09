<?php

/**
 * @author David Cramblett
 */

namespace JasperClient\Client;

use JasperClient\Client\Report;

/**
 * Report Builder
 */
class ReportBuilder {

    ///////////////
    // CONSTANTS //
    ///////////////

    const FORMAT_HTML = 'html';
    const FORMAT_PDF = 'pdf';
    const FORMAT_XLS = 'xls';

    ///////////////
    // VARIABLES //
    ///////////////


    /**
     * Reference to the jasper client class
     * @var JasperClient\Client\Client
     */
    private $client;

    /**
     * Uri of the report this builder is for on the Jasper Server
     * @var string
     */
    private $reportUri;

    /**
     * Where to get the options for the input controls from
     * @var string
     */
    private $getICFrom;

    /**
     * Reference to the report's collection of input controls
     * @var array
     */
    private $reportInputControl;

    /**
     * Flag indicating whether the report has mandatory input or not
     * @var boolean
     */
    private $hasMandatoryInput;

    /**
     * Array of parameters keyed by name
     * @var array
     */
    private $params;

    /**
     * The format of the report (for when getting report output without caching)
     * @var string
     */
    private $format;

    /**
     * The url to append to assets
     * @var string
     */
    private $assetUrl;

    /**
     * The page number of the report (for when getting report output without caching and in html format)
     * @var int
     */
    private $page;

    /**
     * The range of pages to get in a cached or asynchronous report
     * @var string
     */
    private $pageRange;


    //////////////////
    // BASE METHODS //
    //////////////////


    /**
     * Constructor 
     * 
     * @param Client $client    Report client
     * @param string $reportUri Uri of the report on Jasper Server
     * @param string $getICFrom Where to get the options for the input controls
     */
    function __construct(Client $client, $reportUri, $getICFrom = 'Jasper') {
        //Set stuff
        $this->client    = $client;
        $this->reportUri = $reportUri;
        $this->getICFrom = $getICFrom;

        //Init the params array
        $params = array();

        //Load the report input controls
        $this->loadInputControls();
    }


    ///////////////////
    // CLASS METHODS //
    ///////////////////


    /**
     * Loads the input controls for the requested report
     * 
     * @param  string $getICFrom Optional override to the location of the input control options
     * 
     * @return JasperClient\Client\AbstractInputControl The input contols for the requested report
     */
    public function loadInputControls($getICFrom = null) {
        //Set where to get the input controls options from
        $getICFrom = $getICFrom ?: $this->getICFrom;

        // Load report input controls
        $this->reportInputControl =
            $this->client->getReportInputControl(
                $this->reportUri,
                $getICFrom
            );

        // Look for Mandatory Inputs
        $this->hasMandatoryInput = false;
        foreach ($this->reportInputControl as $key => $inputControl) {
            if ('true' == $inputControl->getMandatory()) {
                $this->hasMandatoryInput = true;
            }
        }

        //Return the loaded input controls
        return $this->reportInputControl;
    }


    /**
     * Sets the page range to get for a cached or asynchronous report
     * 
     * @param int $min First page to get
     * @param int $max Last page to get
     */
    public function setPageRange($min, $max) {
        $this->pageRange = $min . '-' . $max;
    }


    /**
     * Sets the input parameters array
     * 
     * @param array $params Parameters array keyed by the input parameter's label
     */
    public function setInputParametersArray($params = []) {
        //Foreach value in the given array, set it
        foreach($params as $label => $values) {
            $this->setInputParameter($label, $values);
        }
    }


    /**
     * Sets an input parameter
     * 
     * @param string $label  The label of the input parameter
     * @param array  $values The array of values the parameter has
     *                       OR a single value 
     */
    public function setInputParameter($label, $values) {
        //Check if the values input is an array or not
        if (!is_array($values)) {
            //If not, make it an array
            $values = array($values);
        }

        //Set the params array
        $this->params[$label] = $values;
    }


    /**
     * Executes the report with the information given to the report builder
     *    If not aysnc, this will cache the report
     * 
     * @param  boolean $async   Whether to execute the report synchronously or aysnchronously
     * @param  array   $options Any additional options permitted by the Jasper Server rest v2 API
     * 
     * @return string           The request id of the report to load with the report loader (sync) or poll and export (async)
     */
    public function executeReport($async = false, $options = []) {

    }


    /**
     * Alias for executeReport(true, $options)
     * 
     * @param  array  $options Any additional options permitted by the Jaser Server rest v2 API
     * 
     * @return string          Request id from the requested report execution
     */
    public function executeAysncReport($options = []) {
        return $this->executeReport(true, $options);
    }


    /**
     * Returns the requested report synchronously, without caching it
     * 
     * @return JasperClient\Client\Report The ouput
     */
    public function build() {
        //If format is html, add page to the params
        if (self::FORMAT_HTML == $format) {
            //Set page to 1 if its not set
            $this->page = $this->page ?: 1;

            //Add it to the params
            $this->params['page'] = $this->page;
        }

        //Get the report body from the client
        $this->reportOutput =
            $this->client->getReport(
                $this->uri,
                $this->format,
                $this->params,
                $this->assetUrl
            );

        //Construct a new report object
        $report = new Report($this->format, $this->page);

        // Look for report errors
        if (true == $this->reportOutput['error']) {

            //Get the error information and put it into a format that will print all pretty like
            $errorOuput = new \SimpleXMLElement($this->reportOutput['output']);

            if ( $errorOuput->parameters->parameter ) {
                $output  = "<div class=\"jrPage jrMessage\" >\n";
                $output .= "\t\t\t<div class=\"errorMesg\">\n";
                $output .= "\t\t\t\t<h1>Error</h1>" . $errorOuput->parameters->parameter . "\n";
                $output .= "\t\t\t</div>\n";
                $output .= "\t\t</div>\n";
            }
            else {
                $output  = "<div class=\"jrPage jrMessage\" >\n";
                $output .= "\t\t\t<div class=\"errorMesg\">\n";
                $output .= "\t\t\t\t<h1>Error</h1>" . $errorOuput->error[0]->defaultMessage . "\n";
                $output .= "\t\t\t</div>\n";
                $output .= "\t\t</div>\n";
            }
            
            //Set the report to be an error message container
            $report->setOutput($output);
            $report->setError(true);

            //Return the error in the report object
            return $report;
        }

        // If html format - Find number of pages
        //   This method is terrible, as it runs
        //   the report a second time. I don't
        //   know better way at the moment.
        if ("html" == $this->report->getFormat()) {

            $xmlOutput =
                $this->client->getReport(
                    $this->report->getUri(),
                    'xml',
                    $this->paramStr
                );

            $objectOutput = new \SimpleXMLElement($xmlOutput['output']);

            foreach ($objectOutput->property as $object) {
                if('net.sf.jasperreports.export.xml.page.count' == $object->attributes()->name) {
                    $report->setTotalPages((string)$object->attributes()->value);
                }
            }
        }

        return $report;
    }


    /////////////////////////
    // GETTERS AND SETTERS //
    /////////////////////////


    /**
     * Gets the Reference to the jasper client class.
     *
     * @return JasperClient\Client\Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Sets the Reference to the jasper client class.
     *
     * @param JasperClient\Client\Client $client the client
     *
     * @return self
     */
    public function setClient(JasperClient\Client\Client $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Gets the Uri of the report this builder is for on the Jasper Server.
     *
     * @return string
     */
    public function getReportUri()
    {
        return $this->reportUri;
    }

    /**
     * Sets the Uri of the report this builder is for on the Jasper Server.
     *
     * @param string $reportUri the report uri
     *
     * @return self
     */
    public function setReportUri($reportUri)
    {
        $this->reportUri = $reportUri;

        return $this;
    }


    /**
     * Gets the Where to get the options for the input controls from.
     *
     * @return string
     */
    public function getGetICFrom()
    {
        return $this->getICFrom;
    }

    /**
     * Sets the Where to get the options for the input controls from.
     *
     * @param string  $getICFrom The location to the input controls from
     * @param boolean $reload    Whether to reload the input controls
     *
     * @return self
     */
    public function setGetICFrom($getICFrom, $reload = false)
    {
        $this->getICFrom = $getICFrom;

        if ($reload) {
            $this->loadInputControls();
        }

        return $this;
    }

    /**
     * Gets the Reference to the report's collection of input controls.
     *
     * @return array
     */
    public function getReportInputControl()
    {
        return $this->reportInputControl;
    }

    /**
     * Sets the Reference to the report's collection of input controls.
     *
     * @param array $reportInputControl the report input control
     *
     * @return self
     */
    public function setReportInputControl($reportInputControl)
    {
        $this->reportInputControl = $reportInputControl;

        return $this;
    }

    /**
     * Gets the Flag indicating whether the report has mandatory input or not.
     *
     * @return boolean
     */
    public function getHasMandatoryInput()
    {
        return $this->hasMandatoryInput;
    }

    /**
     * Sets the Flag indicating whether the report has mandatory input or not.
     *
     * @param boolean $hasMandatoryInput the has mandatory input
     *
     * @return self
     */
    public function setHasMandatoryInput($hasMandatoryInput)
    {
        $this->hasMandatoryInput = $hasMandatoryInput;

        return $this;
    }

    /**
     * Gets the Array of parameters keyed by name.
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Returns the format
     * 
     * @return string Format to get a report in if not caching
     */
    public function getFormat() {
        return $this->format;
    }

    /**
     * Set the format to get a report in if not caching
     * 
     * @param string $format Format to get a non-cached report in
     *
     * @return self
     */
    public function setFormat($format) {
        $this->format = $format;

        return $this;
    }

    /**
     * Get the page to return a non-cached html report's ouput on
     *  
     * @return int Page number
     */
    public function getPage() {
        return $this->page;
    }

    /**
     * Set the page number to return if getting a non-cached report in html format
     * 
     * @param int $page Page number
     *
     * @return self
     */
    public function setPage($page) {
        $this->page = $page;

        return self;
    }

    /**
     * Get the url to append to assets in html reports
     *  
     * @return string assetUrl The url to append in string form
     */
    public function getAssetUrl() {
        return $this->assetUrl;
    }

    /**
     * Set the url to append to assets in html reports
     * 
     * @param string $assetUrl The url to append in string form
     *
     * @return self
     */
    public function setAssetUrl($assetUrl) {
        $this->assetUrl = $assetUrl;

        return self;
    }
}