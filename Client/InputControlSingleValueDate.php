<?php

/**
 * @author David Cramblett
 */

namespace JasperClient\Client;

class InputControlSingleValueDate extends AbstractInputControl {

    private $defaultValue;


    function __construct($id, $label, $mandatory, $readOnly, $type, $uri, $visible, $state) {
        parent::__construct($id, $label, $mandatory, $readOnly, $type, $uri, $visible);
        $this->defaultValue = ($state->value && null != $state->value ? $state->value : null);
    }

    public function getDefaultValue() {
        return $this->defaultValue;
    }

}