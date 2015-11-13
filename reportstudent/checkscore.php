<?php

require_once('../../../config.php');

httpsrequired();
require_login();
if (isguestuser()) {
    die();
}
$context = get_context_instance(CONTEXT_SYSTEM);
require_capability('moodle/site:viewreports', $context); // basic capability for listing of reports

$courseID      = optional_param('menucourse', 0, PARAM_INT);
$action        = optional_param('menuaction', 'checkscore', PARAM_FILE);

if ( !$course = $DB->get_record('course', array('id'=>$courseID)) ) {
    echo get_string('pleaseSelectRightCourseName5', 'block_report_module');
    exit;
}

if ( $reportmodule = $DB->get_record('reportmodule', array('courseid'=>$courseID)) ) {
    echo get_string('theCourseHasSetup', 'block_report_module');
} else {
    echo get_string('theCourseHasnotSetup', 'block_report_module');
}



?>
