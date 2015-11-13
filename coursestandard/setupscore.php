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

$courseID      = optional_param('menucourse', 0, PARAM_INT);
$action        = optional_param('menuaction', 'setupscore', PARAM_FILE);

$courseActionItem = getCourseActionItem($courseID);

if ($courseActionItem) {

    if ( $reportmodule = $DB->get_record('reportmodule', array('courseid'=>$courseID)) ) {
        $courseStandardArray = unserialize($reportmodule->info);
        echo $courseStandardArray['modid']['time'];
        echo '<input type="hidden" name="courseID" id="courseID" value="'.$courseID.'">';
        echo '<input type="hidden" name="action" id="action" value="edit">';
        echo '<table border="1">';
            echo '<tr align="center">';
                echo '<td>'.get_string('actionInformation', 'block_report_module').'</td>';
                echo '<td>'.get_string('actionCategory', 'block_report_module').'</td>';
                echo '<td>'.get_string('actionTimeSetupAndStandard', 'block_report_module').'</td>';
                echo '<td>'.get_string('actionScoreSetupAndStandard', 'block_report_module').'</td>';
            echo '</tr>';
            echo '<tr align="center">';
                echo '<td>'.get_string('viewCourseTime', 'block_report_module').'</td>';
                echo '<td>'.get_string('course', 'block_report_module').'</td>';
                echo '<td>'.get_string('setup', 'block_report_module').'<input type="checkbox" name="courseTimeCheck" '.
                (($courseStandardArray['course']['timeCheck']=="1")?' checked="checked"':'').' value="1"/>
                           '.get_string('standard', 'block_report_module').'<input type="text" name="courseTime" size="3" value="'.
                          (($courseStandardArray['course']['time']=="0")?'':$courseStandardArray['course']['time']).'" />
                            '.get_string('hours', 'block_report_module').'</td>';
                echo '<td>&nbsp;</td>';
            echo '</tr>';
            foreach($courseActionItem as $courseActionItemOne) {
                
                //insert switch action item
                if ( !$courseActionItemOne = courseActionItemCheck($courseActionItemOne) ) {
                    //if not need action item break the count
                    break;
                }
                
            echo '<tr align="center">';
                echo '<td>'.$courseActionItemOne->itemname.'</td>';
                echo '<td>'.$courseActionItemOne->itemmodule.'</td>';
                echo '<td>'.get_string('setup', 'block_report_module').'<input type="checkbox" name="actionTimeCheck'.$courseActionItemOne->id.'" '.
                (($courseStandardArray[$courseActionItemOne->id]['timeCheck']=="1")?' checked="checked"':'').' value="1"/>
                          '.get_string('standard', 'block_report_module').'<input type="text" name="actionTime'.$courseActionItemOne->id.'" size="3" value="'.
                          (($courseStandardArray[$courseActionItemOne->id]['time']=="0")?'':$courseStandardArray[$courseActionItemOne->id]['time']).'" />
                            '.get_string('hours', 'block_report_module').'</td>';
                if ( $courseActionItemOne->itemmodule == 'scorm' )
                echo '<td><span style="visibility: hidden;">'.get_string('setup', 'block_report_module').'<input type="checkbox" name="actionScoreCheck'.$courseActionItemOne->id.'" '.
                (($courseStandardArray[$courseActionItemOne->id]['scoreCheck']=="1")?' checked="checked"':'').' value="1"/>
                          '.get_string('standard', 'block_report_module').'<input type="text" name="actionScore'.$courseActionItemOne->id.'" size="3" value="'.
                          (($courseStandardArray[$courseActionItemOne->id]['score']=="0")?'':$courseStandardArray[$courseActionItemOne->id]['score']).'" />
                            '.get_string('score', 'block_report_module').'</span></td>';
                else
                echo '<td>'.get_string('setup', 'block_report_module').'<input type="checkbox" name="actionScoreCheck'.$courseActionItemOne->id.'" '.
                (($courseStandardArray[$courseActionItemOne->id]['scoreCheck']=="1")?' checked="checked"':'').' value="1"/>
                          '.get_string('standard', 'block_report_module').'<input type="text" name="actionScore'.$courseActionItemOne->id.'" size="3" value="'.
                          (($courseStandardArray[$courseActionItemOne->id]['score']=="0")?'':$courseStandardArray[$courseActionItemOne->id]['score']).'" />
                            '.get_string('score', 'block_report_module').'</td>';
            echo '</tr>';
            }
        echo '</table>';
        echo '<p></p>';
        echo '<input type="submit" value="'.get_string('addAndUpdateCourseStandardInformation', 'block_report_module').'" />'."\n";
    } else {
        echo '<input type="hidden" name="courseID" id="courseID" value="'.$courseID.'">';
        echo '<input type="hidden" name="action" id="action" value="add">';
        echo '<table border="1">';
            echo '<tr align="center">';
                echo '<td>'.get_string('actionInformation', 'block_report_module').'</td>';
                echo '<td>'.get_string('actionCategory', 'block_report_module').'</td>';
                echo '<td>'.get_string('actionTimeSetupAndStandard', 'block_report_module').'</td>';
                echo '<td>'.get_string('actionScoreSetupAndStandard', 'block_report_module').'</td>';
            echo '</tr>';
            echo '<tr align="center">';
                echo '<td>'.get_string('viewCourseTime', 'block_report_module').'</td>';
                echo '<td>'.get_string('course', 'block_report_module').'</td>';
                echo '<td>'.get_string('setup', 'block_report_module').'<input type="checkbox" name="courseTimeCheck" value="1"/>
                          '.get_string('standard', 'block_report_module').'<input type="text" name="courseTime" size="3" />
                            '.get_string('hours', 'block_report_module').'</td>';
                echo '<td>&nbsp;</td>';
            echo '</tr>';
            foreach($courseActionItem as $courseActionItemOne) {
                
                //insert switch action item
                if ( !$courseActionItemOne = courseActionItemCheck($courseActionItemOne) ) {
                    //if not need action item break the count
                    break;
                }
                
            echo '<tr align="center">';
                echo '<td>'.$courseActionItemOne->itemname.'</td>';
                echo '<td>'.$courseActionItemOne->itemmodule.'</td>';
                echo '<td>'.get_string('setup', 'block_report_module').'<input type="checkbox" name="actionTimeCheck'.$courseActionItemOne->id.'" value="1"/>
                          '.get_string('standard', 'block_report_module').'<input type="text" name="actionTime'.$courseActionItemOne->id.'" size="3" />
                            '.get_string('score', 'block_report_module').'</td>';
                if ( $courseActionItemOne->itemmodule == 'scorm' )
                echo '<td><span style="visibility: hidden;">'.get_string('setup', 'block_report_module').'<input type="checkbox" name="actionScoreCheck'.$courseActionItemOne->id.'" value="1"/>
                          '.get_string('standard', 'block_report_module').'<input type="text" name="actionScore'.$courseActionItemOne->id.'" size="3" />
                            '.get_string('hours', 'block_report_module').'</span></td>';
                else
                echo '<td>'.get_string('setup', 'block_report_module').'<input type="checkbox" name="actionScoreCheck'.$courseActionItemOne->id.'" value="1"/>
                          '.get_string('standard', 'block_report_module').'<input type="text" name="actionScore'.$courseActionItemOne->id.'" size="3" />
                            '.get_string('hours', 'block_report_module').'</td>';
            echo '</tr>';
            }
        echo '</table>';
        echo '<p></p>';
        echo '<input type="submit" value="'.get_string('addAndUpdateCourseStandardInformation', 'block_report_module').'" />'."\n";
    }
    
} else {
    echo get_string('theCourseNowNoAction1', 'block_report_module');
}





?>
