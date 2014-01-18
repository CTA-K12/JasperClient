<?php

/**
 * @author David Cramblett
 */

namespace JasperClient\Client;

class Report {

    private $uri;
    private $format;

    public function __construct ($uri, $format = "html") {
        $this->uri      = $uri;
        $this->format   = $format;
    }

    public function getUri() {
        return $this->uri;
    }

    public function getFormat() {
        return $this->format;
    }

    public function setUri($uri) {
        $this->uri = $uri;
    }

    public function setFormat($format) {
        $this->format = $format;
    }
