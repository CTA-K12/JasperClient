<?php

/**
 * @author David Cramblett
 */

namespace JasperClient\Client;


/**
 * Class to hold report objects
 */
class Report {

    ///////////////
    // VARIABLES //
    ///////////////

    /**
     * Report's format (e.g. pdf, html, xls)
     * @var string
     */
    private $format;

    /**
     * Page number if format is html
     * @var int
     */
    private $page;

    /**
     * Total Number of pages in the report
     * @var int
     */
    private $totalPages;

    /**
     * The report in the requested format, and if html report, the requested page
     * @var string
     */
    private $output;

    /**
     * The resource uri of the report
     * @var string
     */
    private $uri;

    /**
     * Request Id of the report if the reported was created via the report executions service
     * @var string
     */
    private $requestId;

    /**
     * If there was an error getting the report output, this flag will be true
     * @var boolean
     */
    private $error;


    //////////////////
    // BASE METHODS //
    //////////////////


    /**
     * Constructor
     *
     * @param $format The format of the report (default html)
     * @param $page   The page number of the report if in html format (default 1)
     */
    public function __construct($format = 'html', $page = 1) {
        //Set stuff
        $this->format = $format;
        $this->page = $page;
        $this->error = false;
    }


    ///////////////////
    // CLASS METHODS //
    ///////////////////


    /**
     * Loads report details from the report execution details xml
     * 
     * @param  SimpleXMLElement $repordExecutionDetails The report execution details in SimpleXMLElement object
     * 
     * @return boolean                                  True if all fields in the report executiond details were set, false elsewise
     */
    public function loadReportExecutionDetails(\SimpleXMLElement $reportExecutionDetails) {
        $success = true;

        //Set the report uri
        $uris = $reportExecutionDetails->xpath('//reportExecution/reportURI');
        if (count($uris) > 0) {
            $this->uri = (string)$uris[0];
        } else {
            $success = false;
        }

        //Set the request id
        $requestIds = $reportExecutionDetails->xpath('//reportExecution/requestId');
        if (count($requestIds) > 0) {
            $this->requestId = (string)$requestIds[0];
        } else {
            $success = false;
        }

        //Set the total number of pages
        $totalPagess = $reportExecutionDetails->xpath('//reportExecution/totalPages');  
        //Yeah, I seriously called the variable that... I'm not good at naming things
        if (count($totalPagess) > 0) {
            $this->totalPages = (string)$totalPagess[0];
        } else {
            $success = false;
        }

        //Return success
        return $success;
    }


    /////////////////////////
    // GETTERS AND SETTERS //
    /////////////////////////


    /**
     * Gets the Report's format (e.g. pdf, html, xls).
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Sets the Report's format (e.g. pdf, html, xls).
     *
     * @param string $format the format
     *
     * @return self
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Gets the Page number if format is html.
     *
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Sets the Page number if format is html.
     *
     * @param int $page the page
     *
     * @return self
     */
    public function setPage($page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Gets the Total Number of pages in the report.
     *
     * @return int
     */
    public function getTotalPages()
    {
        return $this->totalPages;
    }

    /**
     * Sets the Total Number of pages in the report.
     *
     * @param int $totalPages the total pages
     *
     * @return self
     */
    public function setTotalPages($totalPages)
    {
        $this->totalPages = $totalPages;

        return $this;
    }

    /**
     * Gets the The report in the requested format, and if html report, the requested page.
     *
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Sets the The report in the requested format, and if html report, the requested page.
     *
     * @param string $output the output
     *
     * @return self
     */
    public function setOutput($output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * Gets the The resource uri of the report.
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Sets the The resource uri of the report.
     *
     * @param string $uri the uri
     *
     * @return self
     */
    public function setUri($uri)
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * Gets the Request Id of the report if the reported was created via the report executions service.
     *
     * @return string
     */
    public function getRequestId()
    {
        return $this->requestId;
    }

    /**
     * Sets the Request Id of the report if the reported was created via the report executions service.
     *
     * @param string $requestId the request id
     *
     * @return self
     */
    public function setRequestId( $requestId)
    {
        $this->requestId = $requestId;

        return $this;
    }

    /**
     * Checks if the report output is error informating instead of the report
     * 
     * @return boolean Flag idicating that the report output contains an error message
     */
    public function isError() {
        return $this->error;
    }

    /**
     * Sets the error flag
     * 
     * @param boolean $error Whether the report output contains error information
     *
     * @return self
     */
    public function setError($error) {
        $this->error = $error;

        return $this;
    }
}