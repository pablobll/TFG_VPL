<?php
ini_set('memory_limit', '512M');
require(__DIR__ . '/../../config.php');

$courseid = required_param('id', PARAM_INT);
require_login($courseid);
$context = context_course::instance($courseid);
require_capability('report/vpl_analytics:view', $context);

$url = new moodle_url('/report/vpl_analytics/index.php', array('id' => $courseid));
$PAGE->set_url($url);
$PAGE->set_title(get_string('dashboard_title', 'report_vpl_analytics'));

$dashboard_data = \report_vpl_analytics\data_manager::get_dashboard_data($courseid);
$dashboard_json = json_encode($dashboard_data);

$lang_strings = [
    'col_student' => get_string('col_student', 'report_vpl_analytics'),
    'col_group' => get_string('col_group', 'report_vpl_analytics'),
    'label_student' => get_string('label_student', 'report_vpl_analytics'),
    'label_group' => get_string('label_group', 'report_vpl_analytics'),
    'label_no_group' => get_string('label_no_group', 'report_vpl_analytics'),
    'label_subs' => get_string('label_subs', 'report_vpl_analytics'),
    'label_avg_grade' => get_string('label_avg_grade', 'report_vpl_analytics'),
    'label_students' => get_string('label_students', 'report_vpl_analytics'),
    'label_no_students' => get_string('label_no_students', 'report_vpl_analytics'),
    'cat_graded' => get_string('cat_graded', 'report_vpl_analytics'),
    'cat_not_graded' => get_string('cat_not_graded', 'report_vpl_analytics'),
    'label_runs' => get_string('col_runs', 'report_vpl_analytics'),
    'label_evals' => get_string('col_evals', 'report_vpl_analytics'),
    'badge_risk' => get_string('badge_risk', 'report_vpl_analytics'),
    'badge_risk_desc' => get_string('badge_risk_desc', 'report_vpl_analytics'),
    'badge_early' => get_string('badge_early', 'report_vpl_analytics'),
    'badge_early_desc' => get_string('badge_early_desc', 'report_vpl_analytics'),
    'badge_late' => get_string('badge_late', 'report_vpl_analytics'),
    'badge_late_desc' => get_string('badge_late_desc', 'report_vpl_analytics'),
    'settings_proc_init' => get_string('settings_proc_init', 'report_vpl_analytics'),
    'settings_proc_init_hours' => get_string('settings_proc_init_hours', 'report_vpl_analytics'),
    'settings_proc_final' => get_string('settings_proc_final', 'report_vpl_analytics'),
    'settings_proc_final_hours' => get_string('settings_proc_final_hours', 'report_vpl_analytics'),
    'badge_proc_init' => get_string('badge_proc_init', 'report_vpl_analytics'),
    'badge_proc_init_desc' => get_string('badge_proc_init_desc', 'report_vpl_analytics'),
    'badge_proc_final' => get_string('badge_proc_final', 'report_vpl_analytics'),
    'badge_proc_final_desc' => get_string('badge_proc_final_desc', 'report_vpl_analytics'),
    'label_no_data' => get_string('label_no_data', 'report_vpl_analytics'),
    'label_num_subs' => get_string('label_num_subs', 'report_vpl_analytics'),
    'label_qty' => get_string('label_qty', 'report_vpl_analytics'),
    'label_grade_range' => get_string('label_grade_range', 'report_vpl_analytics'),
    'label_execs' => get_string('label_execs', 'report_vpl_analytics'),
    'cat_ungraded' => get_string('cat_ungraded', 'report_vpl_analytics'),
    'cat_open' => get_string('cat_open', 'report_vpl_analytics'),
    'cat_closed' => get_string('cat_closed', 'report_vpl_analytics'),
    'cat_group' => get_string('cat_group', 'report_vpl_analytics'),
    'cat_individual' => get_string('cat_individual', 'report_vpl_analytics'),
    'col_first_sub' => get_string('col_first_sub', 'report_vpl_analytics'),
    'col_last_sub' => get_string('col_last_sub', 'report_vpl_analytics'),
    'col_runs' => get_string('col_runs', 'report_vpl_analytics'),
    'col_debugs' => get_string('col_debugs', 'report_vpl_analytics'),
    'col_evals' => get_string('col_evals', 'report_vpl_analytics'),
    'no_data' => get_string('no_data', 'report_vpl_analytics'),
    'legend_avg_grade' => get_string('legend_avg_grade', 'report_vpl_analytics'),
    'legend_subs' => get_string('legend_subs', 'report_vpl_analytics'),
    'title_performance' => get_string('title_performance', 'report_vpl_analytics'),
    'title_evolution' => get_string('title_evolution', 'report_vpl_analytics'),
    'title_effort' => get_string('title_effort', 'report_vpl_analytics'),
    'title_heatmap' => get_string('title_heatmap', 'report_vpl_analytics'),
    'axis_time' => get_string('axis_time', 'report_vpl_analytics'),
    'axis_grade' => get_string('axis_grade', 'report_vpl_analytics'),
    'axis_subs' => get_string('axis_subs', 'report_vpl_analytics'),
    'cat_global' => get_string('cat_global', 'report_vpl_analytics')
];

$lang_json = json_encode($lang_strings);

$PAGE->requires->css(new moodle_url('/report/vpl_analytics/styles.css'));

$js_version = time();
$PAGE->requires->js('/report/vpl_analytics/js/vendor/chart.min.js');
$PAGE->requires->js('/report/vpl_analytics/js/vendor/chartjs-adapter-date-fns.bundle.min.js');
$PAGE->requires->js('/report/vpl_analytics/js/vendor/hammer.min.js');
$PAGE->requires->js('/report/vpl_analytics/js/vendor/chartjs-plugin-zoom.min.js');

echo $OUTPUT->header();

echo '<script>
    var VplAnalyticsCourseId = ' . $courseid . ';
    var VplAnalyticsLang = ' . $lang_json . ';
    var VplAnalyticsData = ' . $dashboard_json . ';
</script>';

require_once(__DIR__ . '/views/dashboard.php');

echo '<script src="' . $CFG->wwwroot . '/report/vpl_analytics/js/dashboard.js?v=' . $js_version . '"></script>';

echo $OUTPUT->footer();
