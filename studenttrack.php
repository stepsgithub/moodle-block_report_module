<?php

require_once('../../config.php');
require_once('lib/jqueryuifunction.php');

httpsrequired();
require_login();
if (isguestuser()) {
    die();
}

$context = get_context_instance(CONTEXT_SYSTEM);
$PAGE->set_context($context);

$PAGE->requires->data_for_js('userID', $USER->id, true);
$PAGE->requires->data_for_js('trackUrl', $CFG->wwwroot . '/blocks/report_module/studenttrack/', true);
$PAGE->requires->js('/blocks/report_module/javascript/jquery-1.3.2.js', true);
$PAGE->requires->js('/blocks/report_module/javascript/studenttrack.js', true);
$PAGE->requires->css('/blocks/report_module/css/studenttrack.css', true);

$PAGE->set_url('/blocks/report_module/reportcourse.php');
$PAGE->set_pagelayout('base');

$strreports = get_string('studentStudytrack', 'block_report_module');

$PAGE->set_title($strreports);
$PAGE->set_heading($strreports);

$PAGE->navbar->add(get_string('reportModuleTitle', 'block_report_module'));
$PAGE->navbar->add($strreports);

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

echo $OUTPUT->header();

printJQueryLibScrpit(); //include jquery libary

$loginUserid = $USER->id;
$user = $DB->get_record('user', array('id'=>$loginUserid));

echo '<p>'.get_string('studentName', 'block_report_module').$user->firstname.$user->lastname.'</p>';
echo '<p>'.get_string('studentBrief', 'block_report_module').$user->description.'</p>';
echo '<p>'.get_string('email', 'block_report_module').$user->email.'</p>';

echo "<div id=\"courseInformation\">\n";
echo '</div>'."\n";

echo '<div id="editDialog">';
echo '</div>'."\n";

echo '<div id="loading"><img src="pic/loading.gif">&nbsp;Loading&nbsp;...</div>';

echo '<p></p>';

echo $OUTPUT->footer();
