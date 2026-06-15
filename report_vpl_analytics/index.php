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

$metrics = \report_vpl_analytics\data_manager::get_student_metrics();

$table = new html_table();
$table->head = ['User ID', 'Submissions', 'Runs', 'Debugs', 'Avg Grade'];
$table->data = [];

foreach ($metrics as $m) {
    $table->data[] = [
        $m['userid'],
        $m['total_submissions'],
        $m['total_runs'],
        $m['total_debugs'],
        $m['average_grade']
    ];
}

echo html_writer::table($table);

echo $OUTPUT->footer();
