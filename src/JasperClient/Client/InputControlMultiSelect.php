<?php

/**
 * @author David Cramblett
 */

namespace JasperClient\Client;

class InputControlMultiSelect extends AbstractInputControl {

    private $optionList;

    function __construct($id, $label, $mandatory, $readOnly, $type, $uri, $visible, $state) {
        parent::__construct($id, $label, $mandatory, $readOnly, $type, $uri, $visible, $state);
        $this->optionList = $this->createOptionList();
    }


    public function getOptionList() {
        return $this->optionList;
    }
}