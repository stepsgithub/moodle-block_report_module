<?php

require_once('../../config.php');

httpsrequired();
require_login();
$context = get_context_instance(CONTEXT_SYSTEM);
require_capability('moodle/site:viewreports', $context); // basic capability for listing of reports

$PAGE->set_context($context);

$PAGE->requires->data_for_js('courseUrl', $CFG->wwwroot . '/blocks/report_module/coursepassrate/', true);
$PAGE->requires->js('/blocks/report_module/javascript/jquery-1.3.2.js', true);
$PAGE->requires->js('/blocks/report_module/javascript/coursepassrate.js', true);
$PAGE->requires->css('/blocks/report_module/css/coursepassrate.css', true);

$PAGE->set_url('/blocks/report_module/coursepassrate.php');
$PAGE->set_pagelayout('base');

$strreports = get_string('coursePassrateCal', 'block_report_module');

$PAGE->set_title($strreports);
$PAGE->set_heading($strreports);

$PAGE->navbar->add(get_string('reportModuleTitle', 'block_report_module'));
$PAGE->navbar->add($strreports);

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('selectCoursePassrare', 'block_report_module') . ':');

echo "<div id=\"selectcourseform\">\n";
echo '</div>'."\n";

echo '<p></p>';

echo "<div id=\"coursepassrate\">\n";
echo '</div>'."\n";

echo '<div id="loading"><img src="pic/loading.gif">&nbsp;Loading&nbsp;...</div>';

echo $OUTPUT->footer();
