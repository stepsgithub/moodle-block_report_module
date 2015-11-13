<?php

require_once('../../../config.php');
require_once('../lib/function.php');

httpsrequired();
require_login();
//$context = get_context_instance(CONTEXT_SYSTEM);
//require_capability('moodle/site:viewreports', $context); // basic capability for listing of reports

//get information from user select item
$categoryID    = optional_param('menucategory', 0, PARAM_INT);
$courseID      = optional_param('menucourse', 0, PARAM_INT);
$userID        = optional_param('menuuser', 0, PARAM_INT);
$dateform      = optional_param('menudate', 'all', PARAM_FILE);
$reportformat  = optional_param('menureportformat', 'showashtml', PARAM_ALPHA);

//ini number
$selectedcategory = $categoryID;
$selectedcourse = $courseID;
$selecteduser = $userID;
$selecteddate = $dateform ;
$selectedformat = $reportformat;

//make function for get content
$categories = categoriesdata();

if($categoryID){
    $courses = coursesdata($categoryID);
} else {
    $courses = array();
}

if($courseID){
    $users = usersdata($courseID);
} else{
    $users = array();
}

$dateforms = dateformsdata();
$reportformats = reportformatsdata();

echo "<form action=\"$CFG->wwwroot/blocks/report_module/reportcourse/index.php\" method=\"post\">\n";

    echo html_writer::select($categories, "category", $selectedcategory, get_string('selectCourseCategoryDot4', 'block_report_module'));
    echo html_writer::select($courses, "course", $selectedcourse, get_string('selectCourseNameDot4', 'block_report_module'));
    echo html_writer::select($users, "user", $selecteduser, get_string('allParticular4', 'block_report_module'));    
    echo html_writer::select($dateforms, "date", $selecteddate, get_string('alldate4', 'block_report_module'));
    echo html_writer::select($reportformats, 'reportformat', $selectedformat, false);

echo '<input type="button" value="'.get_string('downloadCourseReportInformaiton4', 'block_report_module').'" id="sentCourseInfoButton" />'."\n"; //for jquery
//echo '<input type="submit" value="下載課程報表資訊" />'."\n";
echo '</form>'."\n";

?>
