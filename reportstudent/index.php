<?php

require_once('../../../config.php');
require_once('../lib/function.php');

httpsrequired();
require_login();
$context = get_context_instance(CONTEXT_SYSTEM);
require_capability('moodle/site:viewreports', $context); // basic capability for listing of reports

$courseID      = optional_param('course', 0, PARAM_INT);
$userID        = optional_param('userid', 0, PARAM_INT);
$dateform      = optional_param('date', 'all', PARAM_FILE);
$reportformat  = optional_param('reportformat', 'showashtml', PARAM_ALPHA);

if( !$DB->get_record('user', array('id'=>$userID)) ) {
      error(get_string('unrightStudentName6', 'block_report_module'));
} else {
      $courseUserArray = array();
      $userPassArray = array();
      if($courseID) {
            if ( !$DB->get_record('reportmodule', array('courseid'=>$courseID)) ) {
                  error(get_string('courserUnsetup6', 'block_report_module'));
            }
            if( !get_course_students($courseID) ) {
                  error(get_string('theCOurseNowNoStudent6', 'block_report_module'));
            } else {
                  if ( $course = $DB->get_record('course', array('id'=>$courseID)) ) {
                        $userPass = true;
                        $timeDifferent = getTimeDifferent($dateform);
                        $courseUserLogData = produceUserCourseLog($userID, $courseID, $timeDifferent);
                        $courseUserArray[$courseID] = produceCourseUserArray($userID, $courseID, $courseUserLogData, $userPass);
                        $userPassArray[$courseID] = $userPass;
                  } else {
                        error(get_string('pleaseSelectRightCourseName6', 'block_report_module'));
                  }  
            }
      } else {
            $courses = reportmycoursesdata($userID);
            foreach( $courses as $theCourseID => $theCourseName ) {
                  if( get_course_students($theCourseID) ) {
                        //!the course no student
                        if ( $DB->get_record('reportmodule', array('courseid'=>$theCourseID)) ) {
                              //!the course don't setup stardard
                              if ( $courseActionItem = getCourseActionItem($theCourseID) ) {
                                    //!the course no action
                                    $userPass = true;
                                    $timeDifferent = getTimeDifferent($dateform);
                                    $courseUserLogData = produceUserCourseLog($userID, $theCourseID, $timeDifferent);
                                    $courseUserArray[$theCourseID] = produceCourseUserArray($userID, $theCourseID, $courseUserLogData, $userPass);
                                    $userPassArray[$theCourseID] = $userPass;
                              }
                        }
                  }
            }
            if(!$courseUserArray) {
                  error(get_string('allCourseUnsetup6', 'block_report_module'));
            }
      }
}

switch ($reportformat) {
      case 'showashtml':
            $PAGE->set_context($context);

            $PAGE->set_url('/blocks/report_module/reportcourse.php');
            $PAGE->set_pagelayout('base');

            $strreports = get_string('studentReportSearchResult6', 'block_report_module');

            $PAGE->set_title($strreports);
            $PAGE->set_heading($strreports);

            $PAGE->navbar->add(get_string('reportModuleTitle', 'block_report_module'));
            $PAGE->navbar->add($strreports);

            echo $OUTPUT->header();
            
            echo '<style type="text/css" media="all">';
            echo '.obviousword1 {';
                  echo 'color:red;';
            echo '}';
            echo '</style>';

            echo $OUTPUT->heading(get_string('selectViewCourseRecord', 'block_report_module') . ':');
            
            echo $OUTPUT->heading(get_string('studentActionReport6', 'block_report_module'));
            
            /*   course data inforamtion table    */
            $user = $DB->get_record('user', array('id'=>$userID));
            echo '<p>'.get_string('studentName6', 'block_report_module').$user->firstname.$user->lastname.'</p>';
            echo '<p>'.get_string('studentIntro6', 'block_report_module').$user->description.'</p>';
            echo '<p>'.get_string('email6', 'block_report_module').$user->email.'</p>';
            foreach($courseUserArray as $theCourseID => $courseUserData) {
                  $course = $DB->get_record('course', array('id'=>$theCourseID)); //for one row
                  $category = $DB->get_record('course_categories', array('id'=>$course->category));
                  echo '<table border="1" width="600">';
                  echo '<tr>';
                        echo '<td colspan="5">'.get_string('courseCategory6', 'block_report_module').format_string($category->name).'</td>';
                  echo '</tr>';
                  echo '<tr>';
                        echo '<td colspan="5">'.get_string('coursename6', 'block_report_module').format_string($course->fullname).'</td>';
                  echo '</tr>';
                  echo '<tr>';
                        echo '<td>'.get_string('acitonInfo6', 'block_report_module').'</td>';
                        echo '<td>'.get_string('actionCategory6', 'block_report_module').'</td>';
                        echo '<td>'.get_string('actionTime6', 'block_report_module').'</td>';
                        echo '<td>'.get_string('actionScore6', 'block_report_module').'</td>';
                        echo '<td>'.get_string('passStandard6', 'block_report_module').'</td>';
                  echo '</tr>';
                  foreach ($courseUserData as $courseUserActionID => $courseUserActionContent) {

                  if ( getCourseActionModName($courseUserActionID) ) {
                        $actionModName = getCourseActionModName($courseUserActionID);
                        $actionModCategory = getActionItemModuleName($courseUserActionID);
                  } else {
                        $actionModName = get_string('courseTime6', 'block_report_module');
                        $actionModCategory = get_string('course6', 'block_report_module');
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
                                     get_string('pass6', 'block_report_module'):
                                     '<span class="obviousword1">'.
                                     get_string('unpass6', 'block_report_module')).'</span></td>';
                  echo '</tr>'; 
                  }
                  echo '<tr>';
                        echo '<td colspan="5" align="right">'.get_string('passif6', 'block_report_module')
                        .(($userPassArray[$theCourseID])?
                          get_string('pass6', 'block_report_module'):
                          '<span class="obviousword1">'.
                          get_string('unpass6', 'block_report_module')).'</span></td>';
                  echo '</tr>';
                  echo '</table>';
                  echo '<br />';
            }
            break;
      case 'downloadascsv' :
            
            break;
      case 'downloadasods' :
            
            require_once('../lib/ods.php');
            
            $user = $DB->get_record('user', array('id'=>$userID));
            $userName = $user->firstname.$user->lastname;
            $userDescription = $user->description;
            $userEmail = $user->email;
            
            $courseIDDataArray = array();
            $actionIDNameArray = array();
            foreach($courseUserArray as $theCourseID => $courseUserData) {
                  $course = $DB->get_record('course', array('id'=>$theCourseID));
                  $category = $DB->get_record('course_categories', array('id'=>$course->category));
                  $courseIDDataArray[$theCourseID] = array();
                  $courseIDDataArray[$theCourseID]['courseName'] = format_string($course->fullname);
                  $courseIDDataArray[$theCourseID]['categoryName'] = format_string($category->name);
                  
                  foreach ($courseUserData as $courseUserActionID => $courseUserActionContent) {
                        $actionIDNameArray[$courseUserActionID] = 
                              ((getCourseActionModName($courseUserActionID))?
                                    getCourseActionModName($courseUserActionID):
                                    get_string('courseTime6', 'block_report_module'));
                  }
            }
            
            //colect studentreport ods data
            $studentreport_ods = new object();
            $studentreport_ods->userID = $userID;
            $studentreport_ods->userName = $userName;
            $studentreport_ods->userDescription = $userDescription;
            $studentreport_ods->userEmail = $userEmail;
            $studentreport_ods->courseIDDataArray = $courseIDDataArray;
            $studentreport_ods->actionIDNameArray = $actionIDNameArray;
            $studentreport_ods->courseUserArray = $courseUserArray;
            $studentreport_ods->userPassArray = $userPassArray;
            
            if ( !print_studentreport_ods($studentreport_ods) ) {
                  notify(get_string('noStudentReport6', 'block_report_module'));
                  print_footer();
            }
            
            break;
      case 'downloadasexcel' :
            
            break;
      default:
            break;
}

echo $OUTPUT->footer();

?>
