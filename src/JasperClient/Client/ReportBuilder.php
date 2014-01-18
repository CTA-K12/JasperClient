<?php

/**
 * @author David Cramblett
 */

namespace JasperClient\Client;

class ReportBuilder {

    private $client;
    private $report;
    private $assetUrl;
    private $getICFrom;
    private $reportInputControl;
    private $reportOutput;
    private $reportLastPage;
    private $hasMandatoryInput;
    private $paramStr;

    function __construct(Client $client, Report $report, $paramStr = null, $assetUrl = null, $getICFrom = "Jasper") {
        $this->client    = $client;
        $this->report    = $report;
        $this->paramStr = $paramStr;
        $this->assetUrl  = $assetUrl;
        $this->getICFrom = $getICFrom;

        // Load report input controls
        $this->reportInputControl =
            $this->client->getReportInputControl(
                $report->getUri(),
                $this->getICFrom
            );

        // Look for Mandatory Inputs
        $this->hasMandatoryInput = false;
        foreach ($this->reportInputControl as $key => $inputControl) {
            if ('true' == $inputControl->getMandatory()) {
                $this->hasMandatoryInput = true;
            }
        }
    }


    public function getReportInputControl() {
        return $this->reportInputControl;
    }

    public function getReportLastPage() {
        return $this->reportLastPage;
    }

    public function getReportCurrentPage() {
        //Get it from the paramStr (saved as page=[some number])
        if (preg_match('/page=(?<page>[0-9]+)/', $this->paramStr, $matches )) {
            return intval($matches['page']);
        } else {
            //If the page cannot be returned, return 1 as a default for now
            return 1;
        }
    }

    public function getHasMandatoryInput() {
        return $this->hasMandatoryInput;
    }

    public function getParamStr() {
        return $this->paramStr;
    }

    public function setParamStr($paramStr) {
        $this->paramStr = $paramStr;
    }

    public function setInputControlCssClass($inputControlId, $cssClass) {
        foreach($this->reportInputControl as $input){
            if($inputControlId == $input->getId()){
                $input->setCssClass($cssClass);
            }
        }
    }


    public function build() {

        $this->reportOutput =
            $this->client->getReport(
                $this->report->getUri(),
                $this->report->getFormat(),
                $this->paramStr,
                $this->assetUrl
            );

        // Look for report errors
        if (true == $this->reportOutput['error']) {

            $errorOuput = new \SimpleXMLElement($this->reportOutput['output']);

            if ( $errorOuput->parameters->parameter ) {
                $output  = "<div class=\"jrPage jrMessage\" >\n";
                $output .= "\t\t\t<div class=\"errorMesg\">\n";
                $output .= "\t\t\t\t<h1>Error</h1>" . $errorOuput->parameters->parameter . "\n";
                $output .= "\t\t\t</div>\n";
                $output .= "\t\t</div>\n";
            }
            else {
                $output  = "<div class=\"jrPage jrMessage\" >\n";
                $output .= "\t\t\t<div class=\"errorMesg\">\n";
                $output .= "\t\t\t\t<h1>Error</h1>" . $errorOuput->error[0]->defaultMessage . "\n";
                $output .= "\t\t\t</div>\n";
                $output .= "\t\t</div>\n";
            }
            return $output;
        }

        // If html format - Find number of pages
        //   This method is terrible, as it runs
        //   the report a second time. I don't
        //   know better way at the moment.
        if ("html" == $this->report->getFormat()) {

            $xmlOutput =
                $this->client->getReport(
                    $this->report->getUri(),
                    'xml',
                    $this->paramStr
                );

            $objectOutput = new \SimpleXMLElement($xmlOutput['output']);

            foreach ($objectOutput->property as $object) {
                if('net.sf.jasperreports.export.xml.page.count' == $object->attributes()->name) {
                    $this->reportLastPage = (string)$object->attributes()->value;
                }
            }
        }

        return $this->reportOutput['output'];
    }

}