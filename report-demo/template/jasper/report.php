<?php

// Output the report with the correct format header
if ("html" == $buildResults->getFormat()) {
    include( APP_TEMPLATE_DIR . "/jasper/reportToolBar.php");

}
elseif ("pdf" == $buildResults->getFormat()) {
    header ( "Content-type: application/pdf" );
    print $buildResults->getOutput;
}
elseif ("xls" == $buildResults->getFormat()) {
    header ( 'Content-type: application/xls' );
    header ( 'Content-Disposition: attachment; filename="report.xls"');
    print $buildResults->getOutput;
}
elseif ("xml" == $buildResults->getFormat()) {
    header ( 'Content-type: text/xml' );
    print $buildResults->getOutput;
}

?>
