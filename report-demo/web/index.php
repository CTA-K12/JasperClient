<?php

# Load bootstrap to apply configuration
include( "../../config/config.php" );

# Setup JasperCLient web demo paths
define ( "APP_CONTROLLER_DIR", dirname(dirname(__FILE__)) . "/controller" );
define ( "APP_TEMPLATE_DIR",   dirname(dirname(__FILE__)) . "/template" );


session_start();

# Route request to desired controller
switch ($_GET["q"]) {

    case "jasper":
        include( APP_CONTROLLER_DIR . "/jasperController.php");
        break;

    default:
        include( APP_CONTROLLER_DIR . "/jasperController.php");
        break;
}