<?php

/**
 * @author Daniel Wendler
 * @author David Cramblett
 */

namespace JasperClient\Client;

use JasperClient\Client\ReportBuilder;
use JasperClient\Interfaces\InputControlAbstractFactory;

class Client {

    ///////////////
    // CONSTANTS //
    ///////////////

    const FORMAT_HTML = 'html';
    const FORMAT_PDF = 'pdf';
    const FORMAT_XLS = 'xls';

    private $host;
    private $user;
    private $pass;
    private $rest;


    public function __construct($host = null, $user = null, $pass = null, $jSessionID = null) {

        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;

        try {
            $this->rest = new RestHandler($this->host, $ssl = false, $jSessionID);

            // Do login
            if ($this->user !== null && $this->pass !== null) {
                $this->login();
            }
        }
        catch (\Exception $e) {
            throw $e;
        }
    }


    public function login($user = null, $pass = null) {
        // Use the user & pass passed to the method unless
        // null - if null use those from the constructor.
        $this->user = ( $user == null ? $this->user : $user );
        $this->pass = ( $pass == null ? $this->pass : $pass );

        try {
            $resp = $this->rest->post("/jasperserver/rest/login?j_username={$this->user}&j_password={$this->pass}");
        }
        catch (\Exception $e) {
            throw $e;
        }

        return true;
    }


    public function getServerInfo() {
        try {
            $resp = $this->rest->get("/jasperserver/rest_v2/serverInfo");
        }
        catch (\Exception $e) {
            throw $e;
        }

        return new \SimpleXMLElement($resp['body']);
    }


    public function getFolder($resource, $cache = false, $cacheDir = null, $cacheTimeout = 0) {

        $pleaseCache = false;
        $pleaseLoad  = false;

        if (true === $cache) {
            $cacheFile = $cacheDir . $resource . "/cache.xml";
            if(file_exists($cacheFile)) {
                if ($cacheTimeout < ((time() - filemtime($cacheFile))/60)) {
                    $pleaseCache = true;
                    $pleaseLoad  = true;
                }
                else {
                    $cacheData = file_get_contents($cacheFile);
                    $list = new \SimpleXMLElement($cacheData);
                }
            }
            else {
                $pleaseCache = true;
                $pleaseLoad  = true;
            }
        }
        else {
            $pleaseLoad  = true;
        }

        if ( true === $pleaseLoad) {
            try {
                $resp = $this->rest->get(JasperHelper::url("/jasperserver/rest/resources/{$resource}"));
                $list = new \SimpleXMLElement($resp['body']);

                if (true === $pleaseCache) {
                    $cacheFolder = $cacheDir . $resource;
                    $cacheFile   = $cacheFolder . "/cache.xml";
                    if (!file_exists($cacheFolder)) {
                        mkdir($cacheFolder, 0775, true);
                    }
                    $fh = fopen($cacheFile, "w");
                    fwrite($fh, $resp['body']);
                    fclose($fh);
                }
            }
            catch (\Exception $e) {
                throw $e;
            }
        }

        $collection  = array();
        foreach ($list->resourceDescriptor as $res) {
            $descriptor   = new ResourceDescriptor();
            $collection[] = $descriptor->fromXml($res);
            $descriptor   = null;
        }

        return $collection;
    }


    /**
     * Returns the output of a report that was executed synchronously, and not cached
     * 
     * @param  string $resource URI of the report on the Jasper Server
     * @param  string $format   Format to get the report in
     * @param  array  $params   Parameters to run the report with
     * @param  string $assetUrl Url to use to handle the assets in html reports
     * 
     * @return array            return array:
     *                            output -> output of the requested report in string form
     *                            error  -> boolean inidicating whether an error occured or not
     */
    public function getReport($resource, $format, $params = null, $assetUrl = null) {
        //Process the parameter string
        $paramStr = JasperHelper::generateParameterString($params);

        //Make the request to the Jasper Reports Server
        try {
            $resp = $this->rest->get(
                JasperHelper::url("/jasperserver/rest_v2/reports/{$resource}.{$format}{$paramStr}"),
                $returnErrors = true
            );
        }
        catch (\Exception $e) {
            throw $e;
        }

        //Make references to the various portions of the response that we want to process and return
        $output = $resp['body'];
        $error  = $resp['error'];

        // Replace static content URLs in output
        if (self::FORMAT_HTML == $format && null !== $assetUrl) {
            $output = JasperHelper::replaceAttachmentLinks($output, array('assetUrl' => $assetUrl, 'JSessionID' => $this->rest->getJSessionID()));
        }

        return array('output' => $output, 'error' => $error);
    }


    /**
     * Requests a report to be run, and returns the report execution detials in xml format
     * 
     * @param  string $resource Uri for the report to request to be ran
     * @param  array  $options  Array of options (detailed in the jasper server rest v2 api)
     * 
     * @return SimpleXMLElement The Report Execution Details in XML format
     */
    public function startReportExecution($resource, $options = []) {
        //Create the XML string with the report options
        $reportExecutionRequest = JasperHelper::generateReportExecutionRequestXML($resource, $options);

        //Send a post request to the report server
        try {
            $resp = $this->rest->post(JasperHelper::url("/jasperserver/rest_v2/reportExecutions"), 
                $reportExecutionRequest, 'application/xml', 'application/xml');
        } catch (\Exception $e) {
            throw $e;
        }

        //Return the output
        return new \SimpleXMLElement($resp['body']);
    }


    /**
     * Polls the status of a report execution
     * 
     * @param  string $requestId The request id to poll the status of
     * 
     * @return SimpleXMLElement  The xml response from the jasper server
     */
    public function pollReportExecution($requestId) {
        //Make a request to the report executions service
        try {
            $resp = $this->rest->get(JasperHelper::url("/jasperserver/rest_v2/reportExecutions/{$requestId}/status"));
        } catch(\Exception $e) {
            throw $e;
        }

        //Return the output
        return new \SimpleXMLElement($resp['body']);
    }

    /**
     * Gets the return from an executed report
     * 
     * @param  string $requestID ID from the report execution details whose output to retrieve
     * 
     * @return array             Array with key output pointing to the report output and key error pointing to boolean as to whether and error occured
     */
    public function getExecutedReport($requestId, $format) {
        //Make a request to the report executions service
        try {
            $resp = $this->rest->get(JasperHelper::url("/jasperserver/rest_v2/reportExecutions/{$requestId}/exports/{$format}/outputResource"));
        } catch(\Exception $e) {
            throw $e;
        }

        //Get the output from the response
        $output = $resp['body'];
        $error = $resp['error'];

        //Return the output
        return array('output' => $output, 'error' => $error);
    }


    /**
     * Gets the report execution details from the server for a particular request
     * 
     * @param  string $requestId Request Id of the report execution request to get the details of
     * 
     * @return SimpleXmlElement  XML response from the jasper server
     */
    public function getReportExecutionDetails($requestId) {
        //Make a request to the report executions service
        try {
            $resp = $this->rest->get(JasperHelper::url("/jasperserver/rest_v2/reportExecutions/{$requestId}"));
        } catch(\Exception $e) {
            throw $e;
        }

        //Return the output
        return new \SimpleXMLElement($resp['body']);
    }


    /**
     * Caches the report in the requested formats
     * 
     * @param  string  $requestId            Report execution request id whose output to cache
     * @param  array   $options              Options array
     *                                         'formats' => array of formats to cache (e.g. array('pdf', 'html'))
     *                                         'timeout' => if the report was run asynchronously this the max amount of time (in seconds)
     *                                                        to wait for the report to finish running
     *                                         'wait'    => the amount of time to sleep in seconds between report poll requests if async report execution not yet complete
     *                                         'reportCacheDirectory' => directory where to cache reports
     *                                         'attachmentsPrefix' => prefix given to attachments in the report execution request (for image caching)
     * @return boolean                       Boolean indicator of this methods success
     */
    public function cacheReportExecution($requestId, $options = []) {
        //Set the success flag
        $success = true;

        //Set the options
        $formats = (isset($options['formats']) && is_array($options['formats'])) ? $options['formats'] : array('pdf', 'html', 'xls');
        $timeout = (isset($options['timeout']) && null !== $options['timeout'])  ? $options['timeout'] : 60000;
        $wait    = (isset($options['wait'])    && null !== $options['wait'])     ? $options['wait']    : 5;
        $prefix  = (isset($options['attachmentsPrefix']) && null !== $options['attachmentsPrefix']) ? $options['attachmentsPrefix'] : null;
        $reportCacheDirectory = (isset($options['reportCacheDirectory']) && null !== $options['reportCacheDirectory']) 
            ? $options['reportCacheDirectory'] : 'report_cache/';

        //Set a flag to whether the prefix was set or not, then give it a default of an empty string
        $prefixSet = $prefix ? true : false;
        $prefix = $prefix ?: '';

        //Check if the report is ready to be exported
        $timer = 0;
        while ('ready' !== (string)$this->pollReportExecution($requestId)) {
            sleep($wait);
            $timer += $wait;
            if ($timer > $timeout) {
                return false;
            }
        }

        //Get the report execution details
        $execDetail = $this->getReportExecutionDetails($requestId);

        //Get the outputs for the reports 
        $output = array();
        foreach($formats as $format) {
            $output[$format] = $this->getExecutedReport($requestId, $format);
        }

        try {
            //Write the outputs to the cache
            //Get the directory
            $cacheFolder = JasperHelper::generateReportCacheFolderPath($requestId, $reportCacheDirectory);

            //Create the folder if it does not exist
            if (!file_exists($cacheFolder)) {
                mkdir($cacheFolder, 0775, true);
            }

            //Write the report execution file
            $execDetailFile = $cacheFolder . '/report_execution_details.xml';
            $edfh = fopen($execDetailFile, 'w');
            fwrite($edfh, $execDetail->asXML());
            fclose($edfh);

            //Write each export into a file in the folder
            foreach($formats as $format) {
                //Handle html differently
                if (self::FORMAT_HTML == $format) {
                    $html = $output[$format]['output'];
                    //Cache the attachments
                    $attachments = $this->cacheReportAttachments($execDetail, 
                        array('reportCacheDirectory' => $reportCacheDirectory,
                              'attachmentsPrefix' => $prefix));

                    //Update the asset links
                    $html = JasperHelper::replaceAttachmentLinks($html, 
                        array('replacements' => $attachments, 'removeJQuery' => true, 'defaultSrc' => !$prefixSet));

                    //Split the html output into pages
                    libxml_use_internal_errors(true);  //Turn off the warnings for libxml (else an exception will be thrown)
                    $htmlDoc = new \DOMDocument();
                    $htmlDoc->loadHTML($html);

                    //Get all the tables from the document (the pages are saved as tables with a class of jrPage)
                    $tables = $htmlDoc->getElementsByTagName('table');
                    $pageNumber = 1;
                    foreach($tables as $table) {
                        //If the table is a jasper report page (class="jrPage") then save it
                        if ($table->hasAttribute('class') && 'jrPage' === $table->getAttribute('class')) {
                            //write each page as a seperate file in the cache
                            $htmlPageFile = $cacheFolder . '/html_page_' . $pageNumber . '.' . $format;
                            $fh = fopen($htmlPageFile, 'w');
                            fwrite($fh, $htmlDoc->saveHTML($table));
                            fclose($fh);
                            $pageNumber++;
                        }
                    }
                } else {
                    //If not html, write the file to the cache folder as export.[format]
                    $formatFile = $cacheFolder . '/export.' . $format;
                    $fh = fopen($formatFile, 'w');
                    fwrite($fh, $output[$format]['output']);
                    fclose($fh);
                }
            }

        } catch(\Exception $e) {
            $success = false;
            throw $e;
        }

        //Return success
        return $success;
    }


    /**
     * Takes a report execution detail xml and caches the attachments
     * 
     * @param  SimpleXMLElement $reportExecutionDetails The execution detail from a ready report execution
     * @param  array            $options                Options array:
     * @param  string                                     'reportCacheDirectory' => Directory of the report cache
     * @param  string                                     'attachmentsPrefix'    => Prefix requested to be attached to images in the report request
     * 
     * @return array                                    An array of the paths of the attachments in the cache keyed by their original name 
     *                                                    prepended with the attachmentsPrefix
     */
    public function cacheReportAttachments(\SimpleXMLElement $reportExecutionDetails, $options = []) {
        //Handle the options
        $reportCacheDirectory = 'report_cache/';
        $attachmentsPrefix = '';
        extract($options, EXTR_IF_EXISTS);

        //Get the request id
        $requestId = JasperHelper::getRequestIdFromDetails($reportExecutionDetails);

        //Extract the list of attachments from the report execution request
        $attachments = array();
        $attachmentNodes = $reportExecutionDetails->xpath('//reportExecution/exports/export/attachments/attachment');
        foreach($attachmentNodes as $node) {
            //Extract the values from the attachment 
            $contentType = $attachmentFile = null;
            foreach($node->children() as $child) {
                if ('contentType' === $child->getName()) {
                    $contentType = (string)$child;
                } elseif ('fileName' === $child->getName()) {
                    $attachmentFile = (string)$child;
                }
            }

            //If the values were obtained cache the attachment
            if (null !== $contentType && null !== $attachmentFile && null !== $requestId) {
                $attachments[$attachmentsPrefix . $attachmentFile] = $this->cacheReportAttachment($requestId, $attachmentFile, $contentType, 
                    array('reportCacheDirectory' => $reportCacheDirectory));
            }
        }

        //return the attachment array
        return $attachments;
    }


    /**
     * Retrieves and caches an attachment for an executed report
     * 
     * @param  string $requestId            Request Id of the executed report
     * @param  string $attachment           The name of the attachment
     * @param  string $attachmentType       The type of file the attachment is as specified in the report execution detail (e.g. image/png)
     * @param  array  $options              Options Array:
     *                                        'reportCacheDirectory' => Directory of the report cache
     *                                        'exportId' => Id of the export type (html)
     * 
     * @return string                       The path to the cached image file
     */
    public function cacheReportAttachment($requestId, $attachment, $attachmentType, $options = []) {
        //Handle the options array
        $exportId = 'html';
        $reportCacheDirectory = 'report_cache/';
        extract($options, EXTR_IF_EXISTS);

        //Get the attachment from the jasper report server
        try {
            $resp = $this->rest->get(JasperHelper::url(
                "/jasperserver/rest_v2/reportExecutions/{$requestId}/exports/{$exportId}/attachments/{$attachment}"
            ));
        } catch (\Exception $e) {
            throw $e;
        }

        //Get the folder path for this report's image cache
        $imageCacheFolder = JasperHelper::generateReportCacheFolderPath($requestId, $reportCacheDirectory) . '/images';

        //Create the folder if it does not exist
        if (!file_exists($imageCacheFolder)) {
            mkdir($imageCacheFolder, 0775, true);
        }

        //Determine the filetype
        $format = 'out';  //Fallback
        if ('image/png' === $attachmentType) {
            $format = 'png';
        }

        //Write the attachment to the cache
        $imageFile = $imageCacheFolder . '/' . $attachment . '.' . $format;
        $fh = fopen($imageFile, 'w');
        fwrite($fh, $resp['body']);
        fclose($fh);

        //return the new path for the cached image
        return 'images/' . $attachment .  '.' . $format;
    }


    /**
     * Returns a report builder attached to this client
     * 
     * @param  string $reportUri Uri of the report on the Jasper Server
     * @param  string $getICFrom Where to get the options for input controls from
     * @param  JasperClient\Interfaces\InputControlAbstractFactory $inputControlFactory Implementation of the input factory to handle inputs
     * 
     * @return JasperClient\Client\ReportBuilder The new report builder object
     */
    public function createReportBuilder($reportUri, $getICFrom = 'Jasper', JasperClient\Interfaces\InputControlAbstractFactory $inputControlFactory = null) {
        return new ReportBuilder($this, $reportUri, $getICFrom, $inputControlFactory);
    }


    public function getReportInputControl($resource, $getICFrom = 'Fallback', InputControlAbstractFactory $icFactory = null) {
        try {
            $resp = $this->rest->get(JasperHelper::url("/jasperserver/rest_v2/reports/{$resource}/inputControls"));
        }
        catch (\Exception $e) {
            throw $e;
        }

        $collection = array();

        if ( $resp['body'] ) {
            $list = new \SimpleXMLElement($resp['body']);

            if (null !== $icFactory) {
                $collection = $icFacotry->processInputControlSpecification($list);
            } else {
                foreach($list->inputControl as $key => $val ) {
                    $inputClass = "JasperClient\Client\InputControl".ucfirst($val->type);
                    try {
                        $collection[] = new $inputClass(
                            (string)$val->id,
                            (string)$val->label,
                            (string)$val->mandatory,
                            (string)$val->readOnly,
                            (string)$val->type,
                            (string)$val->uri,
                            (string)$val->visible,
                            (object)$val->state,
                            (string)$getICFrom
                        );
                    }
                    catch (\Exception $e) {
                        throw $e;
                    }
                }
            }
        }

        return $collection;
    }


    /**
     * Gets an asset from the Jasper Server
     * 
     * @param  string $resource URI of the asset 
     * 
     * @return string           The raw data of the asset in string form
     */
    public function getReportAsset($resource) {
        try {
            $resp = $this->rest->get(JasperHelper::url($resource));
        }
        catch (\Exception $e) {
            throw $e;
        }

        return $resp['body'];
    }

}