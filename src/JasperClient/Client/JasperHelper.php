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


    /**
     * Converts the inputControlList collection into
     * simple array containing only default selections
     *
     * @param  Collection $inputControlList
     * 
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

        return $inputControlArray;
    }


    /**
     * Converts the inputControlState into a simple
     * array.
     *
     * @param  Collection $inputControlState
     * 
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
     * Generates a folder path to place a cached report
     * Pattern goes ReportCacheDir/[first 2 numbers of requestId]/[next 2]/[next 2]/requestId
     *   e.g. RequestId of 123456789_1111_0 makes report_cache/12/34/56/123456789_1111_0
     * 
     * @param  string $requestId            Request Id for the report execution to create cache folder for
     * @param  string $reportCacheDirectory Directory to place cached reports
     *  
     * @return string                       Folder path for a report cache
     */
    public static function generateReportCacheFolderPath($requestId, $reportCacheDirectory = 'report_cache/') {
        return $reportCacheDirectory . substr($requestId, 0, 2) . '/' . substr($requestId, 2, 2) 
                . '/' . substr($requestId, 4, 2) . '/' . $requestId;
    }


    /**
     * Extracts the request id from the report execution details xml
     * 
     * @param  SimpleXMLElement $reportExecutionDetails Returned XML from a report execution request
     * 
     * @return string                                   The request Id
     */
    public static function getRequestIdFromDetails(\SimpleXMLElement $reportExecutionDetails) {
        //Find the request id in the details xml
        $requestId = null;
        $results = $reportExecutionDetails->xpath('//reportExecution/requestId');
        foreach($results as $result) {
            $requestId = (string)$result;
        }
        return $requestId;
    }


    /**
     * Generates the report execution request xml in the form of a string
     * 
     * @param  string $resource The uri for the report to run
     * @param  array  $options  The options accepted by JasperServer's reportExecution service
     * 
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
     * 
     * @return string         resulting string
     */
    public static function generateParameterString($params, $ignore = []) {
        //If the parameters is an array turn them into a string
        if (is_array($params) && sizeof($params) > 0) {
            $paramStr = '?';
            $pieces = array();
            foreach ($params as $param => $val) {
                //If the param is not in the ignore array
                if (!in_array($param, $ignore)) {
                    if (is_array($val)) {
                        foreach ($val as $k => $v ) {
                            $pieces[] = $param . '=' . urlencode($v);
                        }
                    } else {
                        $pieces[] = $param . '=' . urlencode($val);
                    }
                }
            }
            $paramStr .= implode('&', $pieces);
        } else {
            //else, append the paramter string to the query string character
            $paramStr = '?' . substr($params,1);
        }

        //Return the result 
        return $paramStr;
    }


    /**
     * Replace the src tags in the html report export with the ones made by the attachment cache function
     *
     * @param  string  $output         The body of the html export of the report
     * @param  array   $options        The options array:
     *                                   'assetUrl' => replace the default assets with a new url given
     *                                   'replacements' => replace the default assets with a given array
     *                                                     original asset serve as key and the replacements the value 
     *                                                     (this is generated by the assetCache )
     *                                   'removeJQuery' => true to replace the jquery tag or false to leave it in (default: true)
     *                                   'defaultSrc'   => boolean flag, when using a replacements, ignore the keys and assume the 
     *                                                     jasper server default and match by just match by the attachment name
     *                                                     For use when the attachmentCache with no attachmentsPrefix
     *                                   'jSessionId'   => The JSessionId to set when using the replacement with assetUrl
     * 
     * @return string                  The modified output
     */
    public static function replaceAttachmentLinks($output, $options = []) {
        //Handle the options array
        $assetUrl = (isset($options['assetUrl']) && null != $options['assetUrl']) ? $options['assetUrl'] : null;
        $replacements = (isset($options['replacements']) && null != $options['replacements']) ? $options['replacements'] : null;
        $removeJQuery = (isset($options['removeJQuery']) && null != $options['removeJQuery']) ? $options['removeJQuery'] : true;
        $defaultSrc = (isset($options['defaultSrc']) && null != $options['defaultSrc']) ? $options['defaultSrc'] : true;
        $jSessionId = (isset($options['JSessionID']) && null != $options['JSessionID']) ? $options['JSessionID'] : '';

        //create the default arrays
        $assets = array();
        $replacementAssets = array();

        //If the assetUrl was set instead of the replacements array
        if (null !== $assetUrl) {
            //Find all the assets in the output
            preg_match_all('/<.+?src=[\"\'](.+?)[\"\'].*?>/', $output, $matches);

            //Get the matching assets
            $assets = isset($matches[1]) ? $matches[1] : array();
            $replacementAssets = array();
            foreach($assets as $asset) {
                //If this is the jquery tag, replace it with an emtpy string
                if (false !== strpos($asset, 'jquery/js/jquery-') && $removeJQuery) {
                    $replacementAssets[] = '';
                } else {
                    $replacementAssets[] = $assetUrl . "&jsessionid=" . $jSessionId . "&uri=" . $asset;
                }
            }

        } elseif (null !== $replacements) {
            if ($defaultSrc) {
                //Find all the assets in the output
                preg_match_all('/<.+?src=[\"\'](.+?)[\"\'].*?>/', $output, $matches);

                //Get the matching assets
                $assets = isset($matches[1]) ? $matches[1] : array();
                $replacementAssets = array();
                foreach($assets as $asset) {
                    //Ignore jquery for now
                    if (false === strpos($asset, 'jquery/js/jquery-') && $removeJQuery) {
                        //Break the url into parts
                        $assetUrlArray = explode('/', $asset);

                        //Get the last index
                        $attachmentName = end($assetUrlArray);
                        
                        //If the key exists in the replacements array
                        if (array_key_exists($attachmentName, $replacements)) {
                            //Insert into the find and replace arrays
                            $assets[] = $asset;
                            $replacementAssets[] = $replacements[$attachmentName];
                        }
                    }
                }
            } else {
                $assets = array_keys($replacements);
                $replacementAssets = array_values($replacements);
            }

            if ($removeJQuery) {
                $assets[] = "<script type='text/javascript' src='/jasperserver/scripts/jquery/js/jquery-1.7.1.min.js'></script>";
                $replacementAssets[] = "";
            }
        }

        //Modify the output
        $output = str_replace($assets, $replacementAssets, $output);

        //Return the modified output
        return $output;
    }

}