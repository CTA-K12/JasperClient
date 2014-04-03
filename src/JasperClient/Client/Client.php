<?php

/**
 * @author Daniel Wendler
 * @author David Cramblett
 */

namespace JasperClient\Client;

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


    public function getReport($resource, $format, $params = null, $assetUrl = null) {

        if (is_array($params) && sizeof($params) > 0) {
            $paramStr = '?';
            foreach ($params as $param => $val) {
                $paramStr .= $param . '=' . urlencode($val) . '&';
            }
        }
        else {
            $paramStr = '?' . substr($params,1);
        }

        try {
            $resp = $this->rest->get(
                JasperHelper::url("/jasperserver/rest_v2/reports/{$resource}.{$format}{$paramStr}"),
                $returnErrors = true
            );
        }
        catch (\Exception $e) {
            throw $e;
        }

        $output = $resp['body'];
        $error  = $resp['error'];

        // Replace static content URLs in output
        if (self::FORMAT_HTML == $format) {
            //Find all the assets in the output
            preg_match_all('/<.+?src=[\"\'](.+?)[\"\'].*?>/', $output, $matches);

            //Get the matching assets
            $assets = isset($matches[1]) ? $matches[1] : array();
            $replacements = array();
            foreach($assets as $asset) {
                //If this is the jquery tag, replace it with an emtpy string
                if (false !== strpos($asset, 'jquery/js/jquery-')) {
                    $replacements[] = '';
                } else {
                    $replacements[] = $assetUrl . "&jsessionid=" . $this->rest->getJSessionID() . "&uri=" . $asset;
                }
            }

            $output = str_replace($assets, $replacements, $output);
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
     * @return boolean                       Boolean indicator of this methods success
     */
    public function cacheReportExecution($requestId, $options = []) {
        //Set the success flag
        $success = true;

        //Set the options
        $formats = (isset($options['formats']) && is_array($options['formats'])) ? $options['formats'] : array('pdf', 'html', 'xls');
        $timeout = (isset($options['timeout']) && null !== $options['timeout'])  ? $options['timeout'] : 60000;
        $wait    = (isset($options['wait'])    && null !== $options['wait'])     ? $options['wait']    : 5;
        $reportCacheDirectory = (isset($options['reportCacheDirectory']) && null !== $options['reportCacheDirectory']) 
            ? $options['reportCacheDirectory'] : 'report_cache/';

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
            $cacheFolder = $reportCacheDirectory . substr($requestId, 0, 2) . '/' . substr($requestId, 2, 2) 
                . '/' . substr($requestId, 4, 2) . '/' . $requestId;

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
                    //print($output[$format]['output']); die;
                } else {
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

        //If html is a requested format, cache the images

        //Return success
        return $success;
    }


    public function getReportInputControl($resource, $getICFrom) {
        try {
            $resp = $this->rest->get(JasperHelper::url("/jasperserver/rest_v2/reports/{$resource}/inputControls"));
        }
        catch (\Exception $e) {
            throw $e;
        }

        $collection = array();

        if ( $resp['body'] ) {
            $list = new \SimpleXMLElement($resp['body']);

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

        return $collection;
    }


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