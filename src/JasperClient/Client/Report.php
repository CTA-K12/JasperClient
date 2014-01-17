<?php

/**
 * @author David Cramblett
 */

namespace JasperClient\Client;

class Report {

    private $uri;
    private $format;
    private $paramStr;


    public function __construct ($uri, $format = "html", $paramStr) {
        $this->uri      = $uri;
        $this->format   = $format;
        $this->paramStr = $paramStr;
    }


    public function getUri() {
        return $this->uri;
    }

    public function getFormat() {
        return $this->format;
    }

    public function getParamStr() {
        return $this->paramStr;
    }


    public function setUri($uri) {
        $this->uri = $uri;
    }

    public function setFormat($format) {
        $this->format = $format;
    }

    public function setParamStr($paramStr) {
        $this->paramStr = $paramStr;
    }

}
