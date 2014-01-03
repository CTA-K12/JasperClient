<!DOCTYPE html>
<html>
  <head>
    <link rel="stylesheet" type="text/css" media="all" href="//cdnjs.cloudflare.com/ajax/libs/normalize/2.1.3/normalize.min.css" />
    <link rel="stylesheet" type="text/css" media="all" href="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/2.3.2/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" media="all" href="css/style.css" />
    <!--[if gt IE 8 ]>
    <link rel="stylesheet" type="text/css" media="all" href="css/font-awesome.css" />
    <![endif]-->
    <!--[if !IE ]>
    <link rel="stylesheet" type="text/css" media="all" href="css/font-awesome.css" />
    <![endif]-->
    <link rel="stylesheet" type="text/css" media="print" href="css/print.css" />
    <!--[if IE 7 ]>
        <style>
            #content {
                margin:    0px;
            }
        </style>
    <![endif]-->
    <title>JasperClient</title>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/modernizr/2.7.0/modernizr.min.js"></script>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/2.3.2/js/bootstrap.min.js"></script>
  </head>
  <body style="background-color:#e5e5e5;">
        <form id="input-control-form" name="input-control-form" action="" method="POST">
            <div id="reportToolBar" class="navbar navbar-fixed-top">
                <div class="navbar-inner">
                    <a class="brand" href="index.php" style="margin: 2px 0 0 0;">JasperClient</a>
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
    $folderUrl  = 'index.php?q=jasper&a=home&folder=' . $folder->getName() . '&folderUri=' . $folder->getUriString();
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
            $reportUrl = 'index.php?q=jasper&a=report&format=html&uri=' . $content->getUriString();
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