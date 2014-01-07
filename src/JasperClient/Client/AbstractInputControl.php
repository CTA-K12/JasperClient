<?php

/**
 * @author David Cramblett
 */

namespace JasperClient\Client;

abstract class AbstractInputControl {

    private $id;
    private $label;
    private $mandatory;
    private $readOnly;
    private $state;
    private $type;
    private $uri;
    private $visible;

    function __construct($id, $label, $mandatory, $readOnly, $type, $uri, $visible, $state) {
        $this->id        = $id;
        $this->label     = $label;
        $this->mandatory = $mandatory;
        $this->readOnly  = $readOnly;
        $this->state     = $state;
        $this->type      = $type;
        $this->uri       = $uri;
        $this->visible   = $visible;
    }

    public function getId() {
        return $this->id;
    }

    public function getLabel() {
        return $this->label;
    }

    public function getMandatory() {
        return $this->mandatory;
    }

    public function getReadOnly() {
        return $this->readOnly;
    }

    public function getState() {
        return $this->state;
    }

    public function getType() {
        return $this->type;
    }

    public function getUri() {
        return $this->uri;
    }

    public function getVisible() {
        return $this->visible;
    }

}