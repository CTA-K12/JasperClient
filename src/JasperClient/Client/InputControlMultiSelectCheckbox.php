<?php

/**
 * @author David Cramblett
 */

namespace JasperClient\Client;

class InputControlMultiSelectCheckbox extends AbstractInputControl {

    private $optionList;

    function __construct($id, $label, $mandatory, $readOnly, $type, $uri, $visible, $state, $getICFrom) {
        parent::__construct($id, $label, $mandatory, $readOnly, $type, $uri, $visible, $state, $getICFrom);
        $this->optionList = $this->createOptionList();
    }


    public function getOptionList() {
        return $this->optionList;
    }

}