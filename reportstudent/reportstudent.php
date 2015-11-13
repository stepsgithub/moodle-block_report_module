<?php

require_once('../../../config.php');

httpsrequired();
require_login();
$context = get_context_instance(CONTEXT_SYSTEM);
require_capability('moodle/site:viewreports', $context); // basic capability for listing of reports

$userID        = optional_param('userid', 0, PARAM_INT);

if ( !$user = $DB->get_record('user', array('id'=>$userID)) ) {
    error(get_string('pleaseSelectRightStudnetName7', 'block_report_module'));
}

$PAGE->set_context($context);

$PAGE->requires->data_for_js('studentUrl', $CFG->wwwroot . '/blocks/report_module/reportstudent/', true);
$PAGE->requires->data_for_js('userID', $userID, true);
$PAGE->requires->js('/blocks/report_module/javascript/jquery-1.3.2.js', true);
$PAGE->requires->js('/blocks/report_module/javascript/reportstudent.js', true);
$PAGE->requires->css('/blocks/report_module/css/reportstudent.css', true);

$PAGE->set_url('/blocks/report_module/reportstudent/reportstudent.php');
$PAGE->set_pagelayout('base');

$strreports = get_string('studentReport7', 'block_report_module');

$PAGE->set_title($strreports);
$PAGE->set_heading($strreports);

$PAGE->navbar->add(get_string('reportModuleTitle', 'block_report_module'));
$PAGE->navbar->add($strreports);

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('selectYouwantViewStudnetCourseRecord7', 'block_report_module'));

$user = $DB->get_record('user', array('id'=>$userID));

echo '<p>'.get_string('studentName7', 'block_report_module').$user->firstname.$user->lastname.'</p>';

echo '<p>'.get_string('studentintro7', 'block_report_module').$user->description.'</p>';

echo '<p>'.get_string('email7', 'block_report_module').$user->email.'</p>';

echo "<div id=\"selectcourseform\">\n";

echo '</div>'."\n";

echo '<p></p>';

echo "<div id=\"checkcoursescoreform\">\n";

echo '</div>'."\n";

echo '<div id="loading"><img src="../pic/loading.gif">&nbsp;Loading&nbsp;...</div>';

echo $OUTPUT->footer();
