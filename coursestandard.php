<?php

require_once('../../config.php');

httpsrequired();
require_login();
if (isguestuser()) {
    die();
}

//$context = get_context_instance(CONTEXT_SYSTEM);
//require_capability('moodle/site:viewreports', $context); // basic capability for listing of reports

$context = get_context_instance(CONTEXT_SYSTEM);
$PAGE->set_context($context);

$PAGE->requires->data_for_js('courseUrl', $CFG->wwwroot . '/blocks/report_module/coursestandard/', true);
$PAGE->requires->js('/blocks/report_module/javascript/jquery-1.3.2.js', true);
$PAGE->requires->js('/blocks/report_module/javascript/coursestandard.js', true);

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

$PAGE->set_url('/blocks/report_module/coursestandard.php');
$PAGE->set_pagelayout('base');

$strreports = get_string('courseStandardSetup', 'block_report_module');

$PAGE->set_title($strreports);
$PAGE->set_heading($strreports);

$PAGE->navbar->add(get_string('reportModuleTitle', 'block_report_module'));
$PAGE->navbar->add($strreports);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('selectCourseSetupStandard', 'block_report_module') . ':');

echo "<div id=\"context_main\">";
echo "<form action=\"$CFG->wwwroot/blocks/report_module/coursestandard/index.php\" method=\"post\">\n";

      echo "<div id=\"selectcourseform\">\n";
      echo '</div>'."\n";

      echo '<p></p>';

      echo "<div id=\"setupcoursescoreform\">\n";
      echo '</div>'."\n";

echo '</form>'."\n";
echo "</div>";

echo $OUTPUT->footer();
