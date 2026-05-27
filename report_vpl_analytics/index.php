<?php
require(__DIR__ . '/../../config.php');
require_login();

$context = context_system::instance();
require_capability('report/vpl_analytics:view', $context);

$url = new moodle_url('/report/vpl_analytics/index.php');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'report_vpl_analytics'));
$PAGE->set_heading(get_string('pluginname', 'report_vpl_analytics'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'report_vpl_analytics'));

echo html_writer::tag('p', 'Welcome to the VPL Analytics plugin base structure!');

echo $OUTPUT->footer();
