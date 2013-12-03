<!DOCTYPE html>
<html>
  <head>
    <link rel="stylesheet" type="text/css" media="all"  href="//cdnjs.cloudflare.com/ajax/libs/normalize/2.1.3/normalize.min.css" />
    <link rel="stylesheet" type="text/css" media="all"  href="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/2.3.2/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" media="all"  href="css/style.css" />
    <link rel="stylesheet" type="text/css" media="print" href="css/print.css" />
    <!--[if gt IE 8 ]>
    <link rel="stylesheet" type="text/css" media="all" href="css/font-awesome.css" />
    <![endif]-->
    <!--[if !IE ]>
    <link rel="stylesheet" type="text/css" media="all" href="css/font-awesome.css" />
    <![endif]-->
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
                    <a class="brand" href="index.php">JasperClient</a>
                    <ul class="nav">
                        <li class="btn-group">
                            <a href="index.php?q=jasper&a=home" title="Back to reports home" class="btn"><i class="icon-home"></i> Reports Home</a>
                        </li>
                        <li class="btn-group">
                            <button type="submit" id="submit" name="submit" value="<?php echo 'exportpdf'; ?>" class="btn" title="Export PDF (Adobe Acrobat)"><i class="icon-file"></i> PDF</button>
                            <button type="submit" id="submit" name="submit" value="<?php echo 'exportxls'; ?>" class="btn" title="Export XLS (Excel Spreadsheet)"><i class="icon-th"></i> XLS</button>
                        </li>
                        <li class="btn-group">
                            <button type="submit" id="submit" name="submit" class="btn" title="First Page" <?php echo empty($pageFirstNo) ? ' disabled' : ' value="' . $pageFirstNo . '"'; ?>>
                                <i class="icon-fast-backward"></i>
                            </button>
                            <button type="submit" id="submit" name="submit" class="btn" title="Previous 10 Pages" <?php echo empty($pageBackTenNo) ? ' disabled' : ' value="' . $pageBackTenNo . '"'; ?>>
                                <i class="icon-step-backward"></i>
                            </button>
                            <button type="submit" id="submit" name="submit" class="btn" title="Previous Page" <?php echo empty($pageBackNo) ? ' disabled' : ' value="' . $pageBackNo . '"'; ?>>
                                <i class="icon-backward"></i>
                            </button>
                            <button type="submit" id="submit" name="submit" class="btn" title="Next Page" <?php echo empty($pageForwardNo) ? ' disabled' : ' value="' . $pageForwardNo . '"'; ?>>
                                <i class="icon-forward"></i>
                            </button>
                            <button type="submit" id="submit" name="submit" class="btn" title="Next 10 Pages" <?php echo empty($pageForwardTenNo) ? ' disabled' : ' value="' . $pageForwardTenNo . '"'; ?>>
                                <i class="icon-step-forward"></i>
                            </button>
                            <button type="submit" id="submit" name="submit" class="btn" title="Last Page" <?php echo empty($pageLastNo) ? ' disabled' : ' value="' . $pageLastNo . '"'; ?>>
                                <i class="icon-fast-forward"></i>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="wrapper wrapper-outer">
                <div class="wrapper-inner">
<!-- if input controls are available, use the sidebar, else don't -->
<?php if((array)$inputControlList){ ?>
                    <aside class="sidebar">
                        <?php
                            foreach($inputControlList as $key => $input){
                                include(APP_TEMPLATE_DIR . '/jasper/inputControls/' . $input->getType() . '.php');
                            }
                        ?>
                        <div class="input-control">
                            <button type="submit" id="submit" name="submit" value="submit" class="btn">Submit</button>
                        </div>
                    </aside>
<?php } ?>
                    <section class="content">
                        <?php print $buildResults; ?>
                    </section>
                </div>
            </div>
        </form>
  </body>
</html>