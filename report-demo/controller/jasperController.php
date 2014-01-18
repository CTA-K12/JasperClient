<?php

use JasperClient\Client\Client;
use JasperClient\Client\JasperHelper;
use JasperClient\Client\Report;
use JasperClient\Client\ReportBuilder;

switch ( $_GET["a"] ) {

    // View Reports Tree
    case "home":

        // Instantiate a jasper report client
        $jasperClient = new Client(
            $host = APP_REPORT_SERVER,
            $user = APP_REPORT_USER,
            $pass = APP_REPORT_PASS
        );

        // Get list of folders
        $folderCollection = $jasperClient->getFolder(APP_REPORT_DEFAULT_FOLDER);

        // If folder is selected, get report list
        if (isset($_GET["folderUri"])) {
            // Get list of sub-folders and reports
            $folderContentsCollection = $jasperClient->getFolder(
                $resource     = $_GET["folderUri"],
                $cache        = APP_REPORT_USE_CACHE,
                $cacheDir     = APP_REPORT_CACHE_DIR,
                $cacheTimeout = APP_REPORT_CACHE_TIMEOUT
            );
        }

        include( APP_TEMPLATE_DIR . "/jasper/home.php");
        break;


    // Run a report
    case "report":

        $uri    = $_GET["uri"];
        $format = $_GET["format"];

        // Defualt to page 1 for html reports
        $page   = ("html" == $format ? 1 : null);
        $params = ("html" == $format ? '&page=' . $page : null);

        // Instantiate a jasper report client
        $jasperClient = new Client(
            $host = APP_REPORT_SERVER,
            $user = APP_REPORT_USER,
            $pass = APP_REPORT_PASS
        );

        // Instantiate a jasper report
        $jasperReport = new Report($uri, $format);

        // Create a new report builder instance
        $jasperReportBuilder = new ReportBuilder(
            $jasperClient,
            $jasperReport,
            $params,
            APP_REPORT_ASSET_URL,
            APP_REPORT_GET_IC_FROM
        );

        // Get report input control list
        $inputControlList = $jasperReportBuilder->getReportInputControl();


        // Grab user supplied input control if available
        if(isset($_POST['submit'])){
            switch ($_POST['submit']) {
                case 'submit':
                    //do nothing
                    break;
                case 'exportpdf':
                    $jasperReport->setFormat('pdf');
                    $params = '';
                    break;
                case 'exportxls':
                    $jasperReport->setFormat('xls');
                    $params = '';
                    break;
                default:
                    $page = $_POST['submit'];
                    $params = '&page=' . $page;
                    break;
            }

            // Convert user input to string
            $inputControl = $_POST;
            unset($inputControl['submit']);
            $jasperReportBuilder->setParamStr($params . JasperHelper::inputAsString($inputControl));
        }
        // Alternatively, use Jasper supplied input control if available
        else {
            // Convert jasper default inputs to string
            $inputControl = JasperHelper::convertInputCollectionToDefault($inputControlList);
            $jasperReportBuilder->setParamStr($params . JasperHelper::inputAsString($inputControl));
        }


        // Check for Mandatory fields, show error if yes and user
        // has not provided input
        if (!isset($_POST['submit']) && $jasperReportBuilder->getHasMandatoryInput()) {
            $buildResults = file_get_contents(APP_TEMPLATE_DIR. '/jasper/messages/mandatoryInput.php');
        }
        elseif ($jasperReportBuilder->getHasMandatoryInput() &&
            false === JasperHelper::verifyMandatoryInput($inputControlList, $inputControl) ) {
            $buildResults = file_get_contents(APP_TEMPLATE_DIR. '/jasper/messages/mandatoryInput.php');
        }
        else {
            // Build the report on the report server
            $buildResults = $jasperReportBuilder->build();
        }


        // Create URLs for page navigation and export formats
        // in the report toolbar
        $currentPage = $page;

        if(1 != $currentPage){
            $pageFirstNo      = 1;
            $pageBackTenNo    = (10 <= $currentPage) ? ($currentPage - 10) : 1;
            $pageBackNo       = (1 < $currentPage) ? ($currentPage - 1) : 1;
        }

        $pageLastNo       = $jasperReportBuilder->getReportLastPage();
        $pageForwardNo    = ($pageLastNo > $currentPage) ? ($currentPage + 1) : $pageLastNo;
        $pageForwardTenNo = ($pageLastNo >= $currentPage +10) ? ($currentPage + 10) : $pageLastNo;

        include( APP_TEMPLATE_DIR . "/jasper/report.php");
        break;

    case "embeddedreport":
        $uri    = $_GET["uri"];
        $format = $_GET["format"];
        $params = json_decode(rawurldecode($_GET["params"]), true);

        // Instantiate a jasper report client
        $jasperClient = new Client(
            $host = APP_REPORT_SERVER,
            $user = APP_REPORT_USER,
            $pass = APP_REPORT_PASS
        );

        // Instantiate a jasper report
        $jasperReport = new Report($uri, $format);

        // Create a new report builder instance
        $jasperReportBuilder = new ReportBuilder(
            $jasperClient,
            $jasperReport,
            $params,
            APP_REPORT_ASSET_URL,
            APP_REPORT_GET_IC_FROM
        );

        $jasperReport->setFormat($format);

        $buildResults = $jasperReportBuilder->build();

        include( APP_TEMPLATE_DIR . "/jasper/report.php");
        break;


    // Fetch a report asset from jasper server - images, etc.
    case "asset":

        // Load images or attchments for a report run,
        // you must have the jsessionid that was used
        // to create a report.
        $jasperClient = new Client(
            $host = APP_REPORT_SERVER,
            $user = APP_REPORT_USER,
            $pass = APP_REPORT_PASS,
            $_GET["jsessionid"]
        );

        echo $jasperClient->getReportAsset($_GET["uri"]);
        break;


    // View Reports Tree
    default:

        // Instantiate a jasper report client
        $jasperClient = new Client(
            $host = APP_REPORT_SERVER,
            $user = APP_REPORT_USER,
            $pass = APP_REPORT_PASS
        );

        // Get list of folders
        $folderCollection = $jasperClient->getFolder(
            $resource     = APP_REPORT_DEFAULT_FOLDER,
            $cache        = APP_REPORT_USE_CACHE,
            $cacheDir     = APP_REPORT_CACHE_DIR,
            $cacheTimeout = APP_REPORT_CACHE_TIMEOUT
        );

        // If folder is selected, get report list
        if (isset($_GET["folderUri"])) {
            // Get list of sub-folders and reports
            $folderContentsCollection = $jasperClient->getFolder(
                $resource     = $_GET["folderUri"],
                $cache        = APP_REPORT_USE_CACHE,
                $cacheDir     = APP_REPORT_CACHE_DIR,
                $cacheTimeout = APP_REPORT_CACHE_TIMEOUT
            );
        }

        include( APP_TEMPLATE_DIR . "/jasper/home.php");
        break;
}