<?php

/**
 * @author David Cramblett
 */

namespace JasperClient\Client;

class InputControlOption {

    private $id;
    private $label;
    private $selected;


    function __construct($id, $label, $selected = false) {
        $this->id        = $id;
        $this->label     = $label;
        $this->selected  = $selected;
    }


    public function getId() {
        return $this->id;
    }

    public function getLabel() {
        return $this->label;
    }

    public function getSelected() {
        return $this->selected;
    }


}