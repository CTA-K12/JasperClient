<!DOCTYPE html>
<html>
  <head>
    <link rel="stylesheet" type="text/css" media="all" href="css/normalize.css" />
    <link rel="stylesheet" type="text/css" media="all" href="css/main.css" />
    <link rel="stylesheet" type="text/css" media="all" href="css/jquery-ui-1.8.13.custom.css" />
    <link rel="stylesheet" type="text/css" media="all" href="css/bootstrap.css" />
    <!--[if gt IE 8 ]>
    <link rel="stylesheet" type="text/css" media="all" href="css/font-awesome.css" />
    <![endif]-->
    <!--[if !IE ]>
    <link rel="stylesheet" type="text/css" media="all" href="css/font-awesome.css" />
    <![endif]-->
    <link rel="stylesheet" type="text/css" media="all" href="css/grid_816.css" />
    <link rel="stylesheet" type="text/css" media="all" href="css/timepicker.css" />
    <link rel="stylesheet" type="text/css" media="all" href="css/style.css" />
    <link rel="stylesheet" type="text/css" media="all" href="css/form.css" />
    <link rel="stylesheet" type="text/css" media="all" href="css/select2.css" />
    <link rel="stylesheet" type="text/css" media="print" href="css/print.css" />
    <!--[if IE 7 ]>
        <style>
            #content {
                margin:    0px;
            }
        </style>
    <![endif]-->
    <title>ORCase</title>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"></script>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
    <script type="text/javascript" src="<?php echo APP_JS_DIR . "/modernizr.min.js"; ?>"></script>
    <script type="text/javascript" src="<?php echo APP_JS_DIR . "/bootstrap.min.js"; ?>"></script>
    <script type="text/javascript" src="<?php echo APP_JS_DIR . "/select2.min.js"; ?>"></script>
    <script type="text/javascript" src="<?php echo APP_JS_DIR . "/moment.min.js"; ?>"></script>
    <script type="text/javascript" src="<?php echo APP_JS_DIR . "/application.js"; ?>"></script>
    <script type="text/javascript" src="<?php echo APP_JS_DIR . "/timepicker.js"; ?>"></script>
  </head>
  <body style="background-color:#e5e5e5;">
        <form id="input-control-form" name="input-control-form" action="" method="POST">
            <div id="reportToolBar" class="navbar navbar-fixed-top">
                <div class="navbar-inner">
                    <a class="brand" href="index.php"><img src="images/orcaseLogo.png" /></a>
                    <ul class="nav">
                        <li><span title="Reports Home"><i class="icon-home"></i> Reports Home</span></li>
                    </ul>
                </div>
            </div>
            <div class="wrapper wrapper-outer">
                <div class="wrapper-inner">
                    <aside class="sidebar">
                        <ul class="nav nav-pills nav-stacked">
                            <li <?php echo !isset($_GET["folder"]) ? 'class="active"' : ''; ?> >
                                <a href="index.php?q=jasper&a=home"><i class="icon-home"></i> Home</a></li>

<?php
$title = '';
foreach ($folderCollection as $key => $folder) {
    $folderUrl  = APP_DOC_ROOT . '/index.php?q=jasper&a=home&folder=' . $folder->getName() . '&folderUri=' . $folder->getUriString();
    $folderIcon = ($folder->getName() == $_GET["folder"] ? 'icon-folder-open' : 'icon-folder-close');
    $active     = $folder->getName() == $_GET["folder"] ? 'class="active"' : '';
    $title      .= $folder->getName() == $_GET["folder"] ? $folder->getLabel() : '';
 ?>

                            <li <?php echo $active; ?> >
                                <a href="<?php echo $folderUrl; ?>" >
                                    <i class="<?php echo $folderIcon; ?>"></i>
                                    <?php echo $folder->getLabel(); ?>
                                </a>
                            </li>

<?php } ?>

                        </ul>
                    </aside>
                    <section class="content">
                        <div class="content-panel">
                            <h2 class="blue"><?php echo '' != $title ? $title : 'Home'; ?></h2>
                            <hr style="clear:none;" />
                            <ul class="unstyled">
<?php
if (is_array($folderContentsCollection)) {
    foreach ($folderContentsCollection as $key => $content) {
        if ('reportUnit' == $content->getWsType()) {
            $reportUrl = APP_DOC_ROOT . '/index.php?q=jasper&a=report&format=html&uri=' . $content->getUriString();
?>
                            <li>
                                <a href="<?php echo $reportUrl; ?>" >
                                    <i class="icon-file"></i>
                                    <?php echo $content->getLabel(); ?>
                                </a>
                            </li>
<?php
        }
    }
}
?>
                            </ul>
                        </div>
                    </section>
                </div>
            </div>
        </form>
  </body>
</html>