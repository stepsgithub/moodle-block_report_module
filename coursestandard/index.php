<?php // $Id: index.php,v 1.19.2.3 2008/11/29 16:16:21 skodak Exp $
      // Displays different views of the logs.

require_once('../../../config.php');
require_once('../lib/function.php');

httpsrequired();
require_login();
if (isguestuser()) {
    die();
}

global $USER, $CFG, $DB;

//$context = get_context_instance(CONTEXT_SYSTEM);
//require_capability('moodle/site:viewreports', $context); // basic capability for listing of reports

//require_once('../../lib.php');
//require_once('lib.php');
//require_once($CFG->libdir.'/adminlib.php');

$context = get_context_instance(CONTEXT_SYSTEM);
$PAGE->set_context($context);

$PAGE->set_url('/blocks/report_module/coursestandard/index.php');
$PAGE->set_pagelayout('base');

$strreports = get_string('courseStandard1', 'block_report_module');

$PAGE->set_title($strreports);
$PAGE->set_heading($strreports);

$PAGE->navbar->add(get_string('reportModuleTitle', 'block_report_module'));
$PAGE->navbar->add($strreports);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('courseStandardStoreDisplay', 'block_report_module'));

$courseID      = optional_param('courseID', 0, PARAM_INT);

if ( !$course = $DB->get_record('course', array('id'=>$courseID)) ) {
      error(get_string('selectRightCourseName1', 'block_report_module'));
}

$context = get_context_instance(CONTEXT_COURSE, $course->id);
if (!has_capability('moodle/grade:viewall', $context)) {
      error(get_string('NoModifyCourseStandardAuth', 'block_report_module'));
}

$courseAction = getCourseActionItem($courseID);

$courseStandardArray = array();
$courseStandardArray['course'] = array();
$courseStandardArray['course']['timeCheck'] = optional_param('courseTimeCheck', 0, PARAM_INT);
$courseStandardArray['course']['time'] = optional_param('courseTime', 0, PARAM_NUMBER);
$courseStandardArray['course']['scoreCheck'] = optional_param('courseScoreCheck', 0, PARAM_INT);
$courseStandardArray['course']['score'] = optional_param('courseScore', 0, PARAM_NUMBER);

foreach($courseAction as $courseActionOne) {
      $courseStandardArray[$courseActionOne->id] = array();
      $courseStandardArray[$courseActionOne->id]['timeCheck'] = optional_param('actionTimeCheck'.$courseActionOne->id, 0, PARAM_INT);
      $courseStandardArray[$courseActionOne->id]['time'] = optional_param('actionTime'.$courseActionOne->id, 0, PARAM_NUMBER);
      $courseStandardArray[$courseActionOne->id]['scoreCheck'] = optional_param('actionScoreCheck'.$courseActionOne->id, 0, PARAM_INT);
      $courseStandardArray[$courseActionOne->id]['score'] = optional_param('actionScore'.$courseActionOne->id, 0, PARAM_NUMBER);
}

$courseStandardString = serialize($courseStandardArray);

if ( !$reportmodule = $DB->get_record('reportmodule', array('courseid'=>$courseID)) ) {
      $newreportmodule = new stdClass();
      $newreportmodule->courseid = $courseID;
      $newreportmodule->info = $courseStandardString;
      $newreportmodule->userid = '';
      $newreportmodule->timemodified = time();
      if (!$reportmoduleID = $DB->insert_record('reportmodule', $newreportmodule)) {
            error("Could not insert the new reportmodule");
      }   
} else {
      $updatereportmodule = new stdClass();
      $updatereportmodule->id = $reportmodule->id;
      $updatereportmodule->courseid = $courseID;
      $updatereportmodule->info = $courseStandardString;
      $updatereportmodule->userid = $USER->id;
      $updatereportmodule->timemodified = time();
      if ( !$DB->update_record('reportmodule', $updatereportmodule) ) {
            error( "Could not update the reportmodule");
      }
}

notify(get_string('updateSuccess', 'block_report_module'));

echo '<div align="center"><a href="'.$CFG->wwwroot.'/blocks/report_module/coursestandard.php'.'">'.get_string('backPage', 'block_report_module').'</a><div>';

//redirect($CFG->wwwroot . '/course/category.php?id=' . $id . '&categoryedit=on');
//$courseStandardArray = array();
//$courseStandardArray = unserialize($courseStandardString);

echo $OUTPUT->footer();
