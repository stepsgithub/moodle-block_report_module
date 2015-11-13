<?php


//$url = at adrees of report_module document (ex.  ../../)
function printJQueryLibScrpit($url='') {
    echo '<link type="text/css" href="'.$url.'lib/jqueryui/themes/base/ui.all.css" rel="stylesheet" />'."\n";
    //echo '<script type="text/javascript" src="'.$url.'lib/jquery-1.3.2.js"></script>'."\n";
    echo '<script type="text/javascript" src="'.$url.'lib/jqueryui/ui/ui.core.js"></script>'."\n";
    echo '<script type="text/javascript" src="'.$url.'lib/jqueryui/ui/ui.draggable.js"></script>'."\n";
    echo '<script type="text/javascript" src="'.$url.'lib/jqueryui/ui/ui.resizable.js"></script>'."\n";
    echo '<script type="text/javascript" src="'.$url.'lib/jqueryui/ui/ui.dialog.js"></script>'."\n";
    echo '<script type="text/javascript" src="'.$url.'lib/jqueryui/external/bgiframe/jquery.bgiframe.js"></script>';
}



?>
