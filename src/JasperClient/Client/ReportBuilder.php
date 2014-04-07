<?php

/**
 * @author David Cramblett
 */

namespace JasperClient\Client;

/**
 * Report Builder
 */
class ReportBuilder {

    ///////////////
    // VARIABLES //
    ///////////////


    /**
     * Reference to the jasper client class
     * @var JasperClient\Client\Client
     */
    private $client;

    /**
     * Uri of the report this builder is for on the Jasper Server
     * @var string
     */
    private $reportUri;

    /**
     * The Url to set as the src for assets in html reports
     * @var string
     */
    private $assetUrl;

    /**
     * Where to get the options for the input controls from
     * @var string
     */
    private $getICFrom;

    /**
     * Reference to the report's collection of input controls
     * @var JasperClient\Client\AbstractInputControl[]
     */
    private $reportInputControl;

    /**
     * Flag indicating whether the report has mandatory input or not
     * @var boolean
     */
    private $hasMandatoryInput;

    /**
     * Array of parameters keyed by name
     * @var array
     */
    private $params;


    //////////////////
    // BASE METHODS //
    //////////////////


    /**
     * Constructor 
     * 
     * @param Client $client    Report client
     * @param string $reportUri Uri of the report on Jasper Server
     * @param string $getICFrom Where to get the options for the input controls
     */
    function __construct(Client $client, $reportUri, $getICFrom = 'Jasper') {
        //Set stuff
        $this->client    = $client;
        $this->reportUri = $reportUri;
        $this->getICFrom = $getICFrom;

        // Load report input controls
        $this->reportInputControl =
            $this->client->getReportInputControl(
                $this->reportUri,
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


    ///////////////////
    // CLASS METHODS //
    ///////////////////


    public function startReportExecution() {
        return 'please finish this method, stupid';
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


    /////////////////////////
    // GETTERS AND SETTERS //
    /////////////////////////


    /**
     * Gets the Reference to the jasper client class.
     *
     * @return JasperClient\Client\Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Sets the Reference to the jasper client class.
     *
     * @param JasperClient\Client\Client $client the client
     *
     * @return self
     */
    public function setClient(JasperClient\Client\Client $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Gets the Uri of the report this builder is for on the Jasper Server.
     *
     * @return string
     */
    public function getReportUri()
    {
        return $this->reportUri;
    }

    /**
     * Sets the Uri of the report this builder is for on the Jasper Server.
     *
     * @param string $reportUri the report uri
     *
     * @return self
     */
    public function setReportUri($reportUri)
    {
        $this->reportUri = $reportUri;

        return $this;
    }

    /**
     * Gets the The Url to set as the src for assets in html reports.
     *
     * @return string
     */
    public function getAssetUrl()
    {
        return $this->assetUrl;
    }

    /**
     * Sets the The Url to set as the src for assets in html reports.
     *
     * @param string $assetUrl the asset url
     *
     * @return self
     */
    public function setAssetUrl($assetUrl)
    {
        $this->assetUrl = $assetUrl;

        return $this;
    }

    /**
     * Gets the Where to get the options for the input controls from.
     *
     * @return string
     */
    public function getGetICFrom()
    {
        return $this->getICFrom;
    }

    /**
     * Sets the Where to get the options for the input controls from.
     *
     * @param string $getICFrom the get i c from
     *
     * @return self
     */
    public function setGetICFrom($getICFrom)
    {
        $this->getICFrom = $getICFrom;

        return $this;
    }

    /**
     * Gets the Reference to the report's collection of input controls.
     *
     * @return JasperClient\Client\AbstractInputControl[]
     */
    public function getReportInputControl()
    {
        return $this->reportInputControl;
    }

    /**
     * Sets the Reference to the report's collection of input controls.
     *
     * @param JasperClient\Client\AbstractInputControl[] $reportInputControl the report input control
     *
     * @return self
     */
    public function setReportInputControl($reportInputControl)
    {
        $this->reportInputControl = $reportInputControl;

        return $this;
    }

    /**
     * Gets the Flag indicating whether the report has mandatory input or not.
     *
     * @return boolean
     */
    public function getHasMandatoryInput()
    {
        return $this->hasMandatoryInput;
    }

    /**
     * Sets the Flag indicating whether the report has mandatory input or not.
     *
     * @param boolean $hasMandatoryInput the has mandatory input
     *
     * @return self
     */
    public function setHasMandatoryInput($hasMandatoryInput)
    {
        $this->hasMandatoryInput = $hasMandatoryInput;

        return $this;
    }

    /**
     * Gets the Array of parameters keyed by name.
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Sets the Array of parameters keyed by name.
     *
     * @param array $params the params
     *
     * @return self
     */
    public function setParams($params = [])
    {
        $this->params = $params;

        return $this;
    }
}