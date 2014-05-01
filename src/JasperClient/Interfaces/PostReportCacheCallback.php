<?php

namespace JasperClient\Interfaces;

/**
 * Interface that can be used to create callbacks that will be invoked after a report has been executed
 */
interface PostReportCacheCallback
{
    /**
     * Function that is invoked if given to the client once a report has been cached
     *
     * @param string           $requestId        The request Id of the report being cached
     * @param array            $options          The options 
     * @param SimpleXMLElement $executionDetails The report execution request details XML
     */
    public function postReportCache($requestId, $options, $executionDetails);
}