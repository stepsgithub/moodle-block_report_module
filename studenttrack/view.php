<?php

require_once('../../../config.php');
require_once('../lib/function.php');

httpsrequired();
require_login();
if (isguestuser()) {
    die();
}

//get login user information from user select item
$loginUserid = $USER->id;
if ( !$user = $DB->get_record('user', array('id'=>$loginUserid)) ) {
    echo get_string('noStudentinformation10', 'block_report_module');
    exit;
}

//make function for get content
$courses = reportmycoursesdata($loginUserid);

echo "<form action=\"$CFG->wwwroot/blocks/report_module/studenttrack/index.php\" method=\"post\">\n";

    echo '<input type="hidden" name="loginuserid" value="'.$loginUserid.'" />';
    echo '<table border="1" id="courseClick" cellpadding="3">';
        echo '<tr>';
            echo '<td>'.get_string('courseCategory10', 'block_report_module').'</td>';
            echo '<td>'.get_string('courseName10', 'block_report_module').'</td>';
            echo '<td>'.get_string('startCourseDate10', 'block_report_module').'</td>';
            echo '<td>'.get_string('viewReport10', 'block_report_module').'</td>';
        echo '</tr>';
    foreach($courses as $courseID => $courseName) {
        if ( $course = $DB->get_record('course', array('id'=>$courseID)) ) {
            if ( $DB->get_record('reportmodule', array('courseid'=>$courseID)) ) {
                $category = $DB->get_record('course_categories', array('id'=>$course->category));
                echo '<tr>';
                    echo '<td>'.format_string($category->name).'</td>';
                    echo '<td>'.format_string($course->fullname).'</td>';
                    echo '<td>'.userdate($course->startdate,get_string('dateForm10', 'block_report_module')).'</td>';
                    echo '<td>&nbsp;'.'<input class="courseButtonClick" type="button" name="'.$courseID
                    .'" value="'.get_string('viewCourseInformation10', 'block_report_module').'" />'.'&nbsp;</td>';
                echo '</tr>';
            }
        }
    }
        echo '<tr>';
            echo '<td colspan="4" align="right">&nbsp;'
            .'<input class="courseButtonClick" type="button" name="0" value="'.get_string('allCourseInformation10', 'block_report_module').'" />'.'&nbsp;</td>';
        echo '</tr>';
    echo '</table>';

//echo '<input type="button" value="觀看課程資訊資訊" />'."\n";
echo '</form>'."\n";


?>
