<?php

/**
 * @author David Cramblett
 */

namespace JasperClient\Client;

class InputControlMultiSelect extends AbstractInputControl {

    private $optionList;

    function __construct($id, $label, $mandatory, $readOnly, $type, $uri, $visible, $state) {

        parent::__construct($id, $label, $mandatory, $readOnly, $type, $uri, $visible);

        $customInputClass = "JasperClient\Custom\InputControlOptionList".ucfirst($this->getId());
        $optionListClass = new $customInputClass($state);
        $this->optionList = $optionListClass->getList();

    }

    public function getOptionList() {
        return $this->optionList;
    }

}