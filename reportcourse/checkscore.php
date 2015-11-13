<?php

require_once('../../../config.php');

httpsrequired();
require_login();
if (isguestuser()) {
    die();
}
//$context = get_context_instance(CONTEXT_SYSTEM);
//require_capability('moodle/site:viewreports', $context); // basic capability for listing of reports

$courseID      = optional_param('menucourse', 0, PARAM_INT);
$action        = optional_param('menuaction', 'checkscore', PARAM_FILE);

if ( !$course = $DB->get_record('course', array('id'=>$courseID)) ) {
    error(get_string('pleaseSelectrightCourseName2', 'block_report_module'));
}

if ( $reportmodule = $DB->get_record('reportmodule', array('courseid'=>$courseID)) ) {
    echo get_string('courseSetup2', 'block_report_module');
} else {
    echo get_string('courseNoSetup2', 'block_report_module');
}



?>
