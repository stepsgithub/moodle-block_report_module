<?php 

require_once('../../../config.php');
require_once('../lib/function.php');

httpsrequired();
require_login();

$loginUserid = $USER->id;

$courseID      = optional_param('courseid', 0, PARAM_INT);

if( !$DB->get_record('user', array('id'=>$loginUserid)) ) {
      echo get_string('noStudentInformation9', 'block_report_module');
      exit;
} else {
      $courseUserArray = array();
      $userPassArray = array();
      if($courseID) {
            if ( !$DB->get_record('reportmodule', array('courseid'=>$courseID)) ) {
                  echo get_string('courseNoSetup9', 'block_report_module');
                  exit;
            }
            if( !get_course_students($courseID) ) {
                  echo get_string('theCourseNowNoStudent9', 'block_report_module');
                  exit;
            } else {
                  if ( $course = $DB->get_record('course', array('id'=>$courseID)) ) {
                        $userPass = true;
                        $courseUserLogData = produceUserCourseLog($loginUserid, $courseID);
                        $courseUserArray[$courseID] = produceCourseUserArray($loginUserid, $courseID, $courseUserLogData, $userPass);
                        $userPassArray[$courseID] = $userPass;
                  } else {
                        echo get_string('pleaseSelectRightCourseName9', 'block_report_module');
                        exit;
                  }  
            }
      } else {
            $courses = reportmycoursesdata($loginUserid);
            foreach( $courses as $theCourseID => $theCourseName ) {
                  if( get_course_students($theCourseID) ) {
                        //!the course no student
                        if ( $DB->get_record('reportmodule', array('courseid'=>$theCourseID)) ) {
                              //!the course don't setup stardard
                              if ( $courseActionItem = getCourseActionItem($theCourseID) ) {
                                    //!the course no action
                                    $userPass = true;
                                    $timeDifferent = getTimeDifferent($dateform);
                                    $courseUserLogData = produceUserCourseLog($loginUserid, $theCourseID);
                                    $courseUserArray[$theCourseID] = produceCourseUserArray($loginUserid, $theCourseID, $courseUserLogData, $userPass);
                                    $userPassArray[$theCourseID] = $userPass;
                              }
                        }
                  }
            }
            if(!$courseUserArray) {
                  echo get_string('allCcourseNoSetup9', 'block_report_module');
                  exit;
            }
      }
}

echo $OUTPUT->heading(get_string('studentStudySearchResult9', 'block_report_module'));
            
/*   course data inforamtion table    */
$user = $DB->get_record('user', array('id'=>$loginUserid));
echo '<p>'.get_string('studentName9', 'block_report_module').$user->firstname.$user->lastname.'</p>';
echo '<p>'.get_string('studentBrief9', 'block_report_module').$user->description.'</p>';
echo '<p>'.get_string('email9', 'block_report_module').$user->email.'</p>';
foreach($courseUserArray as $theCourseID => $courseUserData) {
      $course = $DB->get_record('course', array('id'=>$theCourseID)); //for one row
      $category = $DB->get_record('course_categories', array('id'=>$course->category));
      echo '<table border="1" width="600">';
      echo '<tr>';
            echo '<td colspan="5">'.get_string('courseCategory9', 'block_report_module').format_string($category->name).'</td>';
      echo '</tr>';
      echo '<tr>';
            echo '<td colspan="5">'.get_string('courseName9', 'block_report_module').format_string($course->fullname).'</td>';
      echo '</tr>';
      echo '<tr>';
            echo '<td>'.get_string('actioninformation9', 'block_report_module').'</td>';
            echo '<td>'.get_string('actionCategory9', 'block_report_module').'</td>';
            echo '<td>'.get_string('actionTime9', 'block_report_module').'</td>';
            echo '<td>'.get_string('actionScore9', 'block_report_module').'</td>';
            echo '<td>'.get_string('passStandard9', 'block_report_module').'</td>';
      echo '</tr>';
      foreach ($courseUserData as $courseUserActionID => $courseUserActionContent) {

      if ( getCourseActionModName($courseUserActionID) ) {
            $actionModName = getCourseActionModName($courseUserActionID);
            $actionModCategory = getActionItemModuleName($courseUserActionID);
      } else {
            $actionModName = get_string('courseTime9', 'block_report_module');
            $actionModCategory = get_string('course9', 'block_report_module');
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
                         get_string('pass9', 'block_report_module'):
                         '<span class="obviousword1">'.
                         get_string('unpass9', 'block_report_module')).'</span></td>';
      echo '</tr>';
      
      }
      echo '<tr>';
            echo '<td colspan="5" align="right">'.get_string('passif9', 'block_report_module')
            .(($userPassArray[$theCourseID])?
              get_string('pass9', 'block_report_module'):
              '<span class="obviousword1">'.
              get_string('unpass9', 'block_report_module')).'</span></td>';
      echo '</tr>';
      echo '</table>';
      echo '<br />';
}

//print_footer();

?>
