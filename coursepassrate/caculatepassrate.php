<?php // $Id: index.php,v 1.19.2.3 2008/11/29 16:16:21 skodak Exp $
      // Displays different views of the logs.

require_once('../../../config.php');
require_once('../lib/function.php');

httpsrequired();
require_login();
$context = get_context_instance(CONTEXT_SYSTEM);
require_capability('moodle/site:viewreports', $context); // basic capability for listing of reports

$categoryID    = optional_param('menucategory', 0, PARAM_INT);
$courseID      = optional_param('menucourse', 0, PARAM_INT);

//$reportLog = get_record('log', 'course', $courseID); //for one row

if( $DB->get_record('course_categories', array('id'=>$categoryID)) ){
    if($courseID) {
        if ( $course = $DB->get_record('course', array('id'=>$courseID)) ) {
            if( !$courseusers = get_course_students($courseID) ) {
                  echo get_string('theCourseNowNoStudent', 'reportmodule');
                  exit;
            }
            if ( !$courseActionItem = getCourseActionItem($courseID) ) {
                echo get_string('theCourseNoAction', 'reportmodule');
                exit;
            }
            if ( !$courseStandardArray = getReportModuleConfig($courseID) ) {
                echo get_string('theCourseNoSetup', 'reportmodule');
                exit;
            }
                
            $userPassArray = array();

            foreach( $courseusers as $theUserID => $theStudentObj ) {
                  $courseUserLogData = produceUserCourseLog($theUserID, $courseID);
                  $userPassArray[$theUserID] = produceSimpleCourseUserArray($theUserID, $courseID, $courseUserLogData, $userPass);
            }
            $allCount = 0;
            $passCount = 0;
            $noPassCount = 0;
            foreach($userPassArray as $userPass) {
                  $allCount ++;
                  if($userPass) {
                        $passCount ++;
                  } else {
                        $noPassCount ++;
                  }
            }
            if ($allCount) {
                  $coursePassRate = round($passCount/$allCount, 4)*100;
            } else {
                  $coursePassRate = 0;
            }
            
            //$coursePassRate = cacualateOneCourseRate($courseID);
            echo $coursePassRate."%";
            
        } else {
            echo get_string('pleaseSelectRightCourseName', 'block_report_module');
        }
    } else {
        $courses = coursesdata($categoryID);
        $courseUserPassArray = array();
        foreach($courses as $courseID => $courseName) {
            if ( $course = $DB->get_record('course', array('id'=>$courseID)) ) {
                  $tempErrorMessage = false;
                  if( !$courseusers = get_course_students($courseID) ) {
                        //echo '這門課程目前沒有學生參加!';
                        //exit;
                        $tempErrorMessage = true;
                  }
                  if ( !$courseActionItem = getCourseActionItem($courseID) ) {
                        //echo '課程未有活動!';
                        //exit;
                        $tempErrorMessage = true;
                  }
                  if ( !$courseStandardArray = getReportModuleConfig($courseID) ) {
                        //echo '課程尚未設定!';
                        //exit;
                        $tempErrorMessage = true;
                  }
                  if(!$tempErrorMessage) {
                        $userPassArray = array();
                        foreach( $courseusers as $theUserID => $theStudentObj ) {
                              $courseUserLogData = produceUserCourseLog($theUserID, $courseID);
                              $userPassArray[$theUserID] = produceSimpleCourseUserArray($theUserID, $courseID, $courseUserLogData, $userPass);
                        }
                        //$courseUserPassArray[$courseID][$userID] = true or false
                        $courseUserPassArray[$courseID] = $userPassArray;
                  }
            }
        }
        $coursePassRateTotal = 0;
        $courseCount = 0;
        foreach($courseUserPassArray as $courseID => $userPassArray) {
            $courseCount ++;
            $allCount = 0;
            $passCount = 0;
            $noPassCount = 0;
            foreach($userPassArray as $userID => $userPass) {
                  $allCount ++;
                  if($userPass) {
                        $passCount ++;
                  } else {
                        $noPassCount ++;
                  }
            }
            if ($allCount) {
                  $coursePassRate = round($passCount/$allCount, 4)*100;
                  $coursePassRateTotal += $coursePassRate;
            } else {
                  $coursePassRate = 0;
            }
        }
        if ($courseCount) {
            $categoryPassRate = round($coursePassRateTotal/$courseCount, 2);
            echo $categoryPassRate."%";
        } else {
            echo get_string('theCourseCategoryNoEndCourse', 'block_report_module');
        } 
    }
} else{
    echo get_string('pleaseSelectRightCategoryName', 'block_report_module');
}

?>
