<?php

/**
 * @author David Cramblett
 */

namespace JasperClient\Client;

class InputControlSingleSelect extends AbstractInputControl {

    private $optionList;

    function __construct($id, $label, $mandatory, $readOnly, $type, $uri, $visible, $state) {

        parent::__construct($id, $label, $mandatory, $readOnly, $type, $uri, $visible, $state);

        if ("Custom" == APP_REPORT_INPUT_CONTROL) {
            $customInputClass = "JasperClient\Custom\InputControlOptionList".ucfirst($this->getId());
            $optionListClass = new $customInputClass($state);
            $this->optionList = $optionListClass->getList();
        }
        elseif ("Fallback" == APP_REPORT_INPUT_CONTROL) {
            $customInputClass = "JasperClient\Custom\InputControlOptionList".ucfirst($this->getId());
            if (true == class_exists($customInputClass)) {
                $optionListClass = new $customInputClass($state);
                $this->optionList = $optionListClass->getList();
            }
            else {
                 $inputControlStateArray = JasperHelper::convertInputControlState($state);

                foreach ($inputControlStateArray["option"] as $key => $option) {

                    $this->optionList[] = new InputControlOption (
                        $option["value"],
                        $option["label"],
                        $option["selected"]
                    );
                }
            }
        }
        elseif ("Jasper" == APP_REPORT_INPUT_CONTROL) {

            $inputControlStateArray = JasperHelper::convertInputControlState($state);

            foreach ($inputControlStateArray["option"] as $key => $option) {

                $this->optionList[] = new InputControlOption (
                    $option["value"],
                    $option["label"],
                    $option["selected"]
                );
            }
        }
    }

    public function getOptionList() {
        return $this->optionList;
    }

}