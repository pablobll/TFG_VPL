<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * VPL Analytics Dashboard
 *
 * @package    report_vpl_analytics
 * @copyright  2024 Pablobll
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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

$pass_threshold = get_config('report_vpl_analytics', 'pass_threshold');
$exc_threshold = get_config('report_vpl_analytics', 'exc_threshold');
$dashboard_data['settings'] = [
    'pass_threshold' => ($pass_threshold !== false && $pass_threshold !== '') ? (float)$pass_threshold : 0.5,
    'exc_threshold' => ($exc_threshold !== false && $exc_threshold !== '') ? (float)$exc_threshold : 0.9
];

$dashboard_json = json_encode($dashboard_data);

/*
 * Diccionario de cadenas de texto internacionalizadas.
 * Estas claves se inyectan como JSON en el frontend (window.VplAnalyticsLang)
 * y son consumidas directamente en dashboard.js.
 * 
 * Mapeo de uso principal en dashboard.js:
 * - col_*, label_*: Usadas en updateTable() para las cabeceras de la tabla inferior y tooltips.
 * - badge_*: Inyectadas en el DOM para los tooltips de insignias (riesgo, procrastinación).
 * - chart_*, cat_*: Usadas en renderChart() para las leyendas y labels de los gráficos.
 * - js_*: Usadas para la generación dinámica de la matriz y textos de la interfaz.
 */
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

echo $OUTPUT->header();

require_once(__DIR__ . '/views/dashboard.php');

echo $OUTPUT->footer();
