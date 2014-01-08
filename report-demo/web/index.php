<?php

# Load bootstrap to apply configuration
include( "../config/config.php" );

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