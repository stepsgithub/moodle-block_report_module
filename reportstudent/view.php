<?php

require_once('../../../config.php');
require_once('../lib/function.php');

httpsrequired();
require_login();
if (isguestuser()) {
    die();
}
$context = get_context_instance(CONTEXT_SYSTEM);
require_capability('moodle/site:viewreports', $context); // basic capability for listing of reports

//get information from user select item
$categoryID    = optional_param('menucategory', 0, PARAM_INT);
$courseID      = optional_param('menucourse', 0, PARAM_INT);
$userID        = optional_param('menuuser', 0, PARAM_INT);
$dateform      = optional_param('menudate', 'all', PARAM_FILE);
$reportformat  = optional_param('menureportformat', 'showashtml', PARAM_ALPHA);

global $CFG, $DB;

if ( !$user = $DB->get_record('user', array('id'=>$userID)) ) {
    exit;
}

//ini number
$selectedcourse = $courseID;
$selecteddate = $dateform ;
$selectedformat = $reportformat;

//make function for get content
$courses = reportmycoursesdata($userID);
$dateforms = dateformsdata();
$reportformats = reportformatsdata();

echo "<form action=\"$CFG->wwwroot/blocks/report_module/reportstudent/index.php\" method=\"post\">\n";

    echo '<input type="hidden" name="userid" value="'.$userID.'" />';
    echo html_writer::select($courses, "course", $selectedcourse, get_string('selectCourseNamedot8', 'block_report_module'));   
    echo html_writer::select($dateforms, "date", $selecteddate, get_string('allDate8', 'block_report_module'));
    echo html_writer::select($reportformats, 'reportformat', $selectedformat, false);

echo '<input type="submit" value="'.get_string('downloadStudnetReportInformation8', 'block_report_module').'" />'."\n";
echo '</form>'."\n";


?>
