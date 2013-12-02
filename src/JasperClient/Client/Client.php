<?php

/**
 * @author Daniel Wendler
 * @author David Cramblett
 */

namespace JasperClient\Client;

class Client {

    private $host;
    private $user;
    private $pass;
    private $rest;


    public function __construct($jSessionID = null, $host = null, $user = null, $pass = null) {

        // Use the user, pass, & host passed to the constructor unless
        // null - if null use app config.
        $this->user = ( $user == null ? APP_REPORT_USER   : $user );
        $this->pass = ( $pass == null ? APP_REPORT_PASS   : $pass );
        $this->host = ( $host == null ? APP_REPORT_SERVER : $host );

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


    public function getFolder($resource) {

        $pleaseCache = false;
        $pleaseLoad  = false;

        if (true === APP_REPORT_USE_CACHE) {
            $cacheFile = APP_REPORT_CACHE_DIR . $resource . "/cache.xml";
            if(file_exists($cacheFile)) {
                if (APP_REPORT_CACHE_TIMEOUT < ((time() - filemtime($cacheFile))/60)) {
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

        if ( true == $pleaseLoad) {
            try {
                $resp = $this->rest->get(JasperHelper::url("/jasperserver/rest/resources/{$resource}"));
                $list = new \SimpleXMLElement($resp['body']);

                if (true == $pleaseCache) {
                    $cacheFolder = APP_REPORT_CACHE_DIR . $resource;
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


    public function getReport($resource, $format, $params = null) {

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
        if ($format == 'html') {

            // Replace jquery library script tag from Jasper
            // Server - it's loaded as part of the report
            // viewer already.
            $output = str_replace(
                                    "<script type='text/javascript' src='/jasperserver/scripts/jquery/js/jquery-1.7.1.min.js'></script>",
                                    "",
                                    $output
                                 );
            // Replace report image and attchment URLs with
            // an asset loading URL route within the application.
            $output = str_replace(
                                    "/jasperserver/rest_v2/reports/",
                                    APP_SYSTEM_URL . "?q=jasper&a=asset&jsessionid=".$this->rest->getJSessionID()."&uri=",
                                    $output
                                 );
        }

        return array('output' => $output, 'error' => $error);
    }


    public function getReportInputControl($resource) {
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
                        (object)$val->state
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
            $resp = $this->rest->get(JasperHelper::url("/jasperserver/rest_v2/reports/".$resource));
        }
        catch (\Exception $e) {
            throw $e;
        }

        return $resp['body'];
    }

}