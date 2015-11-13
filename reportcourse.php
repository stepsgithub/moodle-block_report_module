<?php

require_once('../../config.php');
require_once('lib/jqueryuifunction.php');

httpsrequired();
require_login();
if (isguestuser()) {
    die();
}
//$context = get_context_instance(CONTEXT_SYSTEM);
//require_capability('moodle/site:viewreports', $context); // basic capability for listing of reports

$context = get_context_instance(CONTEXT_SYSTEM);
$PAGE->set_context($context);

$PAGE->requires->data_for_js('reportUrl', $CFG->wwwroot . '/blocks/report_module/reportcourse/', true);
$PAGE->requires->js('/blocks/report_module/javascript/jquery-1.3.2.js', true);
$PAGE->requires->js('/blocks/report_module/javascript/reportcourse.js', true);
$PAGE->requires->css('/blocks/report_module/css/reportcourse.css', true);

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

$PAGE->set_url('/blocks/report_module/reportcourse.php');
$PAGE->set_pagelayout('base');

$strreports = get_string('courseReport', 'block_report_module');

$PAGE->set_title($strreports);
$PAGE->set_heading($strreports);

$PAGE->navbar->add(get_string('reportModuleTitle', 'block_report_module'));
$PAGE->navbar->add($strreports);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('selectViewCourseRecord', 'block_report_module') . ':');

printJQueryLibScrpit(); //include jquery libary

echo "<div id=\"selectcourseform\">\n";
echo '</div>'."\n";

echo '<p></p>';

echo "<div id=\"checkcoursescoreform\">\n";
echo '</div>'."\n";

echo '<div id="editDialog">';
echo '</div>'."\n";

echo '<div id="loading"><img src="pic/loading.gif">&nbsp;Loading&nbsp;...</div>';

echo $OUTPUT->footer();
