<?php

/**
 * @author David Cramblett
 */

namespace JasperClient\Client;

abstract class AbstractInputControl {

    protected $id;
    protected $label;
    protected $mandatory;
    protected $readOnly;
    protected $state;
    protected $type;
    protected $uri;
    protected $visible;
    protected $getICFrom;


    function __construct($id, $label, $mandatory, $readOnly, $type, $uri, $visible, $state, $getICFrom) {
        $this->id          = $id;
        $this->label       = $label;
        $this->mandatory   = $mandatory;
        $this->readOnly    = $readOnly;
        $this->state       = $state;
        $this->type        = $type;
        $this->uri         = $uri;
        $this->visible     = $visible;
        $this->getICFrom = $getICFrom;
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

        if ("Custom" == $this->getICFrom) {
            $customInputClass = "JasperClient\Custom\InputControlOptionList".ucfirst($this->id);
            $optionListClass = new $customInputClass($this->state);
            $optionList = $optionListClass->getList();
        }
        elseif ("Fallback" == $this->getICFrom) {
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
        elseif ("Jasper" == $this->getICFrom) {

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
            throw new \Exception('Invalid APP_REPORT_GET_IC_FROM type: ' . $this->getICFrom);
        }

        return $optionList;
    }


}