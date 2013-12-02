<?php

// Output the report with the correct format header
if ("html" == $jasperReport->getFormat()) {
    include( APP_TEMPLATE_DIR . "/jasper/reportToolBar.php");

}
elseif ("pdf" == $jasperReport->getFormat()) {
    header ( "Content-type: application/pdf" );
    print $buildResults;
}
elseif ("xls" == $jasperReport->getFormat()) {
    header ( 'Content-type: application/xls' );
    header ( 'Content-Disposition: attachment; filename="report.xls"');
    print $buildResults;
}
elseif ("xml" == $jasperReport->getFormat()) {
    header ( 'Content-type: text/xml' );
    print $buildResults;
}

?>
