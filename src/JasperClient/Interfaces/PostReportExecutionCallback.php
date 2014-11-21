<?php

namespace JasperClient\Interfaces;

/**
 * Interface that can be used to create callbacks that will be invoked after a report has been executed
 */
interface PostReportExecutionCallback
{
    /**
     * Function that is invoked if this is given to the client once a report has been executed
     *
     * @param  string           $resource The uri of the resource on the jasper server
     * @param  array            $options  The array of options given along with the report execution
     * @param  SimpleXMLElement $response The response back from the jasper server in xml format
     */
    public function postReportExecution($resource, $options, $response);
}