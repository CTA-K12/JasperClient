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


    public function createOptionList() {

        $optionList = array();

        if ("Custom" == APP_REPORT_INPUT_CONTROL) {
            $customInputClass = "JasperClient\Custom\InputControlOptionList".ucfirst($this->id);
            $optionListClass = new $customInputClass($this->state);
            $optionList = $optionListClass->getList();
        }
        elseif ("Fallback" == APP_REPORT_INPUT_CONTROL) {
            $customInputClass = "JasperClient\Custom\InputControlOptionList".ucfirst($this->id);
            if (true == class_exists($customInputClass)) {
                $optionListClass = new $customInputClass($this->state);
                $optionList = $optionListClass->getList();
            }
            else {
                 $inputControlStateArray = JasperHelper::convertInputControlState($this->state);

                foreach ($inputControlStateArray["option"] as $key => $option) {

                    $optionList[] = new InputControlOption (
                        $option["value"],
                        $option["label"],
                        $option["selected"]
                    );
                }
            }
        }
        elseif ("Jasper" == APP_REPORT_INPUT_CONTROL) {

            $inputControlStateArray = JasperHelper::convertInputControlState($this->state);

            foreach ($inputControlStateArray["option"] as $key => $option) {

                $optionList[] = new InputControlOption (
                    $option["value"],
                    $option["label"],
                    $option["selected"]
                );
            }
        }
        else {
            throw new Exception('Invalid APP_REPORT_INPUT_CONTROL type: ' . APP_REPORT_INPUT_CONTROL);
        }

        return $optionList;
    }


}