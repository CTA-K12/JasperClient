<?php

/**
 * @author Daniel Wendler
 * @author David Cramblett
 */

namespace JasperClient\Client;


class JasperHelper {


    /*
     * Corrects path strings and removes slashes
     *
     * @param String $url path to correct
     * @return String
     */
    public static function url($url) {
        $url = str_replace('///', '/', $url);
        $url = str_replace('//',  '/', $url);
        $url = str_replace('//',  '/', $url);
        $url = str_replace('//',  '/', $url);
        if (substr($url, -1, 1) == '/') {
            $url = substr($url, 0, (strlen($url) - 1));
        }
        if ($url == '') {
            $url = '/';
        }
        return $url;
    }


    /*
     * Formats the input control selection into
     * string
     *
     * @param Array $inputControl
     * @return String
     */
    public static function inputAsString($inputControl) {
        $inputControlString = "";
        foreach ($inputControl as $key => $input ) {
            if (is_array($input)) {
                foreach ($input as $k => $v ) {
                    $inputControlString .= '&' . $key . '=' . urlencode($v);
                }
            }
            else {
                $inputControlString .= '&' . $key . '=' . urlencode($input);
            }
        }

        return $inputControlString;
    }


    /*
     * Converts the inputControlList collection into
     * simple array containing only default selections
     *
     * @param Collection $inputControlList
     * @return Array
     */
    public static function convertInputCollection($inputControlList) {
        $inputControlArray = array();
        foreach ($inputControlList as $key => $inputControl) {
            if (method_exists($inputControl, 'getDefaultValue')) {
                if (null != $inputControl->getDefaultValue()) {
                    $inputControlArray[$inputControl->getId()] = (string)$inputControl->getDefaultValue();
                }
            }
            elseif (is_array($inputControl->getOptionList())) {
                foreach ($inputControl->getOptionList() as $k => $option) {
                    if (true === $option->getSelected()) {
                        $inputControlArray[$inputControl->getId()][] = $option->getId();
                    }
                }
            }
        }

        return $inputControlArray;
    }


    /*
     * Converts the inputControlState into a simple
     * array.
     *
     * @param Collection $inputControlState
     * @return Array
     */
    public static function convertInputControlState($inputControlState) {
        $inputControlStateArray = array();
        $inputControlStateArray["id"] = (string)$inputControlState->id;

        $i = 0;
        foreach ($inputControlState->options->option as $key => $value) {
            $inputControlStateArray["option"][$i]["label"]    = (string)$value->label;
            $inputControlStateArray["option"][$i]["selected"] = (string)$value->selected;
            $inputControlStateArray["option"][$i]["value"]    = (string)$value->value;
            $i++;
        }

        $inputControlStateArray["uri"] = (string)$inputControlState->uri;

        return $inputControlStateArray;
    }


    /*
     * Verify user provided input selection contains
     * input for mandatory input controls
     *
     * @param Collection $inputControlList
     * @param Array $userInput
     * @return Bool
     */
    public static function verifyMandatoryInput($inputControlList, $userInput) {
        $verify = true;
        foreach ($inputControlList as $key => $inputControl) {
            if ('true' == $inputControl->getMandatory() && null == $userInput[$inputControl->getId()]) {
                $verify = false;
            }
        }

        return $verify;
    }

}