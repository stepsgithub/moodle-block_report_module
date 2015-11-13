<?php

require_once('../../../config.php');
require_once('../lib/function.php');

httpsrequired();
require_login();
$context = get_context_instance(CONTEXT_SYSTEM);
require_capability('moodle/site:viewreports', $context); // basic capability for listing of reports

//get information from user select item
$categoryID    = optional_param('menucategory', 0, PARAM_INT);
$courseID      = optional_param('menucourse', 0, PARAM_INT);

//ini number
$selectedcategory = $categoryID;
$selectedcourse = $courseID;

//make function for get content
$categories = categoriesdata();

if($categoryID){
    $courses = coursesdata($categoryID);
} else{
    $courses = array();
}

echo "<form action=\"\" method=\"post\">\n";

    echo html_writer::select($categories, "category", $selectedcategory, get_string('selectCourseCategoryDot', 'block_report_module'));
    echo html_writer::select($courses, "course", $selectedcourse, get_string('selectCourseNameDot', 'block_report_module'));

echo '<input type="button" id="caculaterate" value="'.get_string('ok', 'block_report_module').'" />'."\n";
echo '</form>'."\n";

?>
