<?php

/**
 * @author Daniel Wendler
 * @author David Cramblett
 */

namespace JasperClient\Client;


class JasperHelper {

    ////////////////
    //  CONSTANTS //
    ////////////////

    const DEFAULT_OUTPUT_FORMAT = 'html';
    const DEFAULT_FRESH_DATA = false;
    const DEFAULT_SAVE_DATA_SNAPSHOT = false;
    const DEFAULT_INTERACTIVE = true;
    const DEFAULT_IGNORE_PAGINATION = false;
    const DEFAULT_ASYNC = false;

    const NULL_RESOURCE_MESSAGE = 'Resource Uri Required';
    const NULL_OUTPUT_FORMAT_MESSAGE = 'Output Format Required';


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
    public static function convertInputCollectionToDefault($inputControlList) {
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

        return $inputControlDefaultArray;
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
            $inputControlStateArray["option"][$i]["selected"] = ( 'true' == strtolower((string)$value->selected));
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


    /**
     * Generates the report execution request xml in the form of a string
     * 
     * @param  string $resource The uri for the report to run
     * @param  array  $options  The options accepted by JasperServer's reportExecution service
     * @return string           Request as an xml string
     */
    public static function generateReportExecutionRequestXML($resource, $options = []) {
        //Set the defaults and get the information from the options array
        //Options with defaults
        $outputFormat = self::DEFAULT_OUTPUT_FORMAT;
        $freshData = self::DEFAULT_FRESH_DATA;
        $saveDataSnapshot = self::DEFAULT_SAVE_DATA_SNAPSHOT;
        $interactive = self::DEFAULT_INTERACTIVE;
        $ignorePagination = self::DEFAULT_IGNORE_PAGINATION;
        $async = self::DEFAULT_ASYNC;
        //Optional Options
        $pages = $transformerKey = $attachmentsPrefix = null;
        $parameters = array();
        //Extract the options array
        extract($options, EXTR_IF_EXISTS);

        //Enforce Required Options
        if (is_null($resource)) {
            throw new \Exception(self::NULL_RESOURCE_MESSAGE);
        }
        if (is_null($outputFormat)) {
            throw new \Exception(self::NULL_OUTPUT_FORMAT_MESSAGE);
        }

        //Create an instance of the XML Writer
        $writer = new \XMLWriter();
        $writer->openMemory();

        $writer->startElement('reportExecutionRequest');

        $writer->writeElement('reportUnitUri', $resource);
        $writer->writeElement('async', $async ? 'true' : 'false');
        $writer->writeElement('freshData', $freshData ? 'true' : 'false');
        $writer->writeElement('saveDataSnapshot', $saveDataSnapshot ? 'true' : 'false');
        $writer->writeElement('outputFormat', $outputFormat);
        $writer->writeElement('interactive', $interactive ? 'true' : 'false');
        $writer->writeElement('ignorePagination', $ignorePagination ? 'true' : 'false');
        if ($pages) { $writer->writeElement('pages', $pages); }
        if ($transformerKey) { $writer->writeElement('transformerKey', $transformerKey); }
        if ($attachmentsPrefix) { $writer->writeElement('attachmentsPrefix', $attachmentsPrefix); }
        $writer->startElement('parameters');
        $writer->text('');  //By having this when no parameters are present the tags show properly
        foreach($parameters as $name => $values) {
            $writer->startElement('reportParameter');
            $writer->writeAttribute('name', $name);
            foreach($values as $value) {
                $writer->writeElement('value', $value);
            }
            $writer->endElement();
        }
        $writer->endElement();

        $writer->endElement();

        //Return the completed XML string
        return $writer->outputMemory();
    }


    /**
     * Generates the parameter string to pass to the rest handler from an array of parameters
     * 
     * @param  array  $params array of parameters
     * @param  array  $ignore array of parameters to ignore (NOTE: ignore currently only works if the params is in an array)
     * @return string         resulting string
     */
    public static function generateParameterString($params, $ignore = []) {
        //If the parameters is an array turn them into a string
        if (is_array($params) && sizeof($params) > 0) {
            $paramStr = '?';
            foreach ($params as $param => $val) {
                //If the param is not in the ignore array
                if (!in_array($param, $ignore)) {
                    $paramStr .= $param . '=' . urlencode($val) . '&';
                }
            }
        } else {
            //else, append the paramter string to the query string character
            $paramStr = '?' . substr($params,1);
        }

        //Return the result 
        return $paramStr;
    }

    /**
     * Convert parameter array to something that can be safely used for a file name
     *
     * @param  array  $params array of parameters
     * @param  array  $ignore array of parameters to ignore (NOTE: ignore currently only works if the params is in an array)
     * @return string         resulting string
     */
    public static function generateParameterStringForFilename($params, $ignore = []) {
        //If the passed in parameters is an array, sort then concat to string
        if (is_array($params) && sizeof($params) > 0) {
            $paramStr = '';
            ksort($params); //Sort the keys, this way the cache for report1?a=1&b=2 is the same for report1?b=2&a=1
            foreach ($params as $param => $val) {
                //If the param is not in the ignore array
                if (!in_array($param, $ignore)) {
                    $paramStr .= $param . '-' . $val . '_';
                }
            }
        } else {
            //else, if its a string, just set the to paramStr to it
            $paramStr = $params;
        }

        //Sanitize the name
        $paramStr = preg_replace('/[^a-zA-Z0-9-_\.]/','_', $paramStr);

        //Return the sanitized string
        return $paramStr;
    }

}