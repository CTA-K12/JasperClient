<?php

/**
 * @author David Cramblett
 */

namespace JasperClient\Client;

class InputControlSingleValueNumber extends AbstractInputControl {

    private $defaultValue;


    function __construct($id, $label, $mandatory, $readOnly, $type, $uri, $visible, $state, $getICFrom) {
        parent::__construct($id, $label, $mandatory, $readOnly, $type, $uri, $visible, $state, $getICFrom);
        $this->defaultValue = ($state->value && null != $state->value ? $state->value : null);
    }

    public function getDefaultValue() {
        return $this->defaultValue;
    }

}
