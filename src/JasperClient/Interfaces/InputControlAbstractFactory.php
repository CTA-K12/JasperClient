<?php

namespace JasperClient\Interfaces;

interface InputControlAbstractFactory
{
    /**
     * Takes the xml return from the jasper servers get report input controls and
     * builds a collection of report in put controls
     * 
     * @param  SimpleXMLElement $specification The input controls to build in xml format
     * 
     * @return array                           Array of input controls
     */
    public function processInputControlSpecification(\SimpleXMLElement $specification);
}