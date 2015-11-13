<?php

require_once('../../../config.php');
require_once('../lib/function.php');

httpsrequired();
require_login();
if (isguestuser()) {
    die();
}
//$context = get_context_instance(CONTEXT_SYSTEM);
//require_capability('moodle/site:viewreports', $context); // basic capability for listing of reports

global $DB;

$categoryID    = optional_param('category', 0, PARAM_INT);
$courseID      = optional_param('course', 0, PARAM_INT);
$userID        = optional_param('user', 0, PARAM_INT);
$dateform      = optional_param('date', 'all', PARAM_FILE);
$reportformat  = optional_param('reportformat', 'showashtml', PARAM_ALPHA);

if( !$category = $DB->get_record('course_categories', array('id'=>$categoryID)) ) {
      echo get_string('unrightCourseCategory', 'block_report_module');
      exit;
      //error('不正確的課程類別!');
}

if( !$course = $DB->get_record('course', array('id'=>$courseID)) ) {
      echo get_string('pleaseSelectRightCourseName3', 'block_report_module');
      exit;
      //error('請選擇正確的課程名稱!');
}

if( !get_course_students($courseID) ) {
      echo get_string('theCourseNowNostudent3', 'block_report_module');
      exit;
      //error('這門課程目前沒有學生參加!');
}

if ( !$DB->get_record('reportmodule', array('courseid'=>$courseID)) ) {
      echo get_string('theCourseNoSetup3', 'block_report_module');
      exit;
      //error('課程尚未設定!');
}

$context = get_context_instance(CONTEXT_COURSE, $course->id);
if (!has_capability('moodle/grade:viewall', $context)) {
      error(get_string('NoViewCourseReportAuth3', 'block_report_module'));
}

$courseUserArray = array();
$userPassArray = array();

if( $DB->get_record('user', array('id'=>$userID)) ) {
      $userPass = true;
      $timeDifferent = getTimeDifferent($dateform);
      $courseUserLogData = produceUserCourseLog($userID, $courseID, $timeDifferent);
      $courseUserArray[$userID] = produceCourseUserArray($userID, $courseID, $courseUserLogData, $userPass);
      $userPassArray[$userID] = $userPass;
      
} else {
      $students = get_course_students($courseID);
      $timeDifferent = getTimeDifferent($dateform);
      if($students) {
            foreach( $students as $theUserID => $theStudentObj ) {
                  if( !$DB->get_record('user', array('id'=>$theUserID)) ) {
                        break;
                  } 
                  $userPass = true;
                  $courseUserLogData = produceUserCourseLog($theUserID, $courseID, $timeDifferent);
                  $courseUserArray[$theUserID] = produceCourseUserArray($theUserID, $courseID, $courseUserLogData, $userPass);
                  $userPassArray[$theUserID] = $userPass;
            } 
      } else {
            echo get_string('courseNoStudentInfomation', 'block_report_module');
            exit;
      }
      //if(!$courseUserArray) {
            //echo "課程無學員資訊!";
            //exit;
            //error('課程無學員資訊!');
      //}
}

switch ($reportformat) {
      case 'showashtml':
            $strreports = get_string('courseReportSearchResult', 'block_report_module');
            //print_header($strreports, $strreports);
            echo $OUTPUT->heading(get_string('courseAtionReport3', 'block_report_module'));
            
            $course = $DB->get_record('course', array('id'=>$courseID));
            $category = $DB->get_record('course_categories', array('id'=>$course->category));
            echo '<p>'.get_string('categoryName3', 'block_report_module').format_string($category->name).'</p>';
            echo '<p>'.get_string('courseName3', 'block_report_module').format_string($course->fullname).'</p>';
            
            /*   course data inforamtion table    */
            foreach($courseUserArray as $theUserID => $courseUserData) {
                  $user = $DB->get_record('user', array('id'=>$theUserID));
                  echo '<table border="1" width="600">';
                  echo '<tr>';
                        echo '<td colspan="5">'.get_string('stundentName3', 'block_report_module').$user->firstname.$user->lastname.'</td>';
                  echo '</tr>';
                  echo '<tr>';
                        echo '<td colspan="5">'.get_string('studentBrief3', 'block_report_module').$user->description.'</td>';
                  echo '</tr>';
                  echo '<tr>';
                        echo '<td colspan="5">'.get_string('email3', 'block_report_module').$user->email.'</td>';
                  echo '</tr>';
                  echo '<tr>';
                        echo '<td>'.get_string('aciotnInformation3', 'block_report_module').'</td>';
                        echo '<td>'.get_string('aciotnCategory3', 'block_report_module').'</td>';
                        echo '<td>'.get_string('actionTime3', 'block_report_module').'</td>';
                        echo '<td>'.get_string('actionScore3', 'block_report_module').'</td>';
                        echo '<td>'.get_string('passStandard', 'block_report_module').'</td>';
                  echo '</tr>';
                  foreach ($courseUserData as $courseUserActionID => $courseUserActionContent) {

                  if ( getCourseActionModName($courseUserActionID) ) {
                        $actionModName = getCourseActionModName($courseUserActionID);
                        $actionModCategory = getActionItemModuleName($courseUserActionID);
                  } else {
                        $actionModName = get_string('courseTime3', 'block_report_module');
                        $actionModCategory = get_string('course3', 'block_report_module');
                  }
                  echo '<tr>';
                        echo '<td>'.$actionModName.'</td>';
                        echo '<td>'.$actionModCategory.'</td>';
                        echo '<td>'.( ($courseUserActionContent['timeEnable'])
                                    ? $courseUserActionContent['time']
                                    :'不採計' )
                              .'</td>';
                        echo '<td>'.( ($courseUserActionContent['scoreEnable'])
                                    ? $courseUserActionContent['score']
                                    :'不採計' )
                              .'</td>';
                        echo '<td>'.(($courseUserActionContent['scorePass'] AND
                                      $courseUserActionContent['timePass'])?
                                     get_string('pass3', 'block_report_module'):
                                     '<span class="obviousword1">'.
                                     get_string('unpass3', 'block_report_module')).'</span></td>';
                  echo '</tr>'; 
                  }
                  echo '<tr>';
                        echo '<td colspan="5" align="right">'.
                        get_string('ifpass', 'block_report_module').
                        (($userPassArray[$theUserID])?
                        get_string('pass3', 'block_report_module'):
                        '<span class="obviousword1">'.
                        get_string('unpass3', 'block_report_module')).'</span></td>';
                  echo '</tr>';
                  echo '</table>';
                  echo '<br />';
            }
            
            break;
      case 'downloadascsv' :
            
            break;
      case 'downloadasods' :
            
            require_once('../lib/ods.php');
            
            $category = $DB->get_record('course_categories', array('id'=>$course->category));
            $categoryName = format_string($category->name);
            
            $course = $DB->get_record('course', array('id'=>$courseID));
            $courseName = format_string($course->fullname);
            
            $userIDDataArray = array();
            $actionIDNameArray = array();
            foreach($courseUserArray as $theUserID => $courseUserData) {
                  $user = $DB->get_record('user', array('id'=>$theUserID));
                  $userIDDataArray[$theUserID] = array();
                  $userIDDataArray[$theUserID]['name'] = $user->firstname.$user->lastname;
                  $userIDDataArray[$theUserID]['description'] = $user->description;
                  $userIDDataArray[$theUserID]['email'] = $user->email;
                  
                  foreach ($courseUserData as $courseUserActionID => $courseUserActionContent) {
                        $actionIDNameArray[$courseUserActionID] = 
                              ((getCourseActionModName($courseUserActionID))?
                                    getCourseActionModName($courseUserActionID):get_string('courseTime3', 'block_report_module'));
                  }
            }
            
            //colect coursereport ods data
            $coursereport_ods = new object();
            $coursereport_ods->courseID = $courseID;
            $coursereport_ods->courseName = $courseName;
            $coursereport_ods->categoryName = $categoryName;
            $coursereport_ods->userIDDataArray = $userIDDataArray;
            $coursereport_ods->actionIDNameArray = $actionIDNameArray;
            $coursereport_ods->courseUserArray = $courseUserArray;
            $coursereport_ods->userPassArray = $userPassArray;
            
            if ( !print_coursereport_ods($coursereport_ods) ) {
                  notify(get_string('noCourseRecord', 'block_report_module'));
                  print_footer();
            }
            
            break;
      case 'downloadasexcel' :
            
            break;
      default:
      
            break;
}

//print_footer();
echo '<p></p>';

?>
