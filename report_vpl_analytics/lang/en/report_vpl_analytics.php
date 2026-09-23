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

$string['pluginname'] = 'VPL Analytics Dashboard';
$string['dashboard_title'] = 'VPL Analytics Dashboard';
$string['kpi_avg_grade'] = 'Global Average Grade';
$string['kpi_total_subs'] = 'Total Submissions';
$string['kpi_active_users'] = 'Active Students';
$string['kpi_inactive_users'] = 'Inactive Students';
$string['mode_analysis'] = 'Analysis Mode';
$string['mode_global'] = 'Global Analysis';
$string['mode_compare_groups'] = 'Compare Groups';
$string['mode_compare_users'] = 'Compare Students';
$string['mode_matrix'] = 'Student-Activity Matrix';
$string['search_student'] = 'Search student...';
$string['chart_type'] = 'Visualization Type';
$string['chart_rendimiento'] = 'Final Grades Distribution';
$string['chart_evolucion'] = 'Submissions Timeline';
$string['chart_esfuerzo'] = 'Practical Performance';
$string['chart_heatmap'] = 'Temporal Heatmap';
$string['filter_vpl'] = 'VPL Activity';
$string['all_vpls'] = 'All activities';
$string['cat_graded'] = 'Graded activities';
$string['cat_ungraded'] = 'Ungraded activities';
$string['cat_open'] = 'Open activities';
$string['cat_closed'] = 'Closed activities';
$string['cat_group'] = 'Group activities';
$string['cat_individual'] = 'Individual activities';
$string['filter_group'] = 'Filter by Group';
$string['all_groups'] = 'All groups';
$string['group_1'] = 'Group 1 (Blue)';
$string['group_2'] = 'Group 2 (Green)';
$string['user_1'] = 'Student 1 (Blue)';
$string['user_2'] = 'Student 2 (Green)';
$string['zoom_reset'] = 'Reset';
$string['scroll_indicator'] = '↓ Scroll down inside the table to see more students';
$string['col_activity'] = 'Activity';
$string['col_student'] = 'Student';
$string['col_group'] = 'Group';
$string['col_subs'] = 'Submissions';
$string['col_grade'] = 'Final Grade';
$string['col_first_sub'] = 'First Submission';
$string['col_last_sub'] = 'Last Submission';
$string['col_runs'] = 'Executions';
$string['col_debugs'] = 'Debugs';
$string['col_evals'] = 'Auto Evals.';
$string['filter_date_from'] = 'Date From';
$string['filter_date_to'] = 'Date To';
$string['kpi_pass_rate'] = 'Pass Rate';
$string['kpi_exc_rate'] = 'Excellence';
$string['kpi_median'] = 'Median';
$string['btn_settings'] = 'Configuration';
$string['settings_title'] = 'Dashboard Configuration';
$string['settings_desc'] = 'Configure the grade scale and thresholds for the system to detect at-risk students automatically.';
$string['settings_stagnant'] = '[Risk] Stagnation';
$string['settings_stagnant_runs'] = 'Minimum evaluations:';
$string['settings_stagnant_grade'] = 'Maximum grade:';
$string['settings_proc_init'] = '[Procrastinator] Started late';
$string['settings_proc_init_hours'] = 'Start hours before deadline:';
$string['settings_proc_final'] = '[Procrastinator] Finished late';
$string['settings_proc_final_hours'] = 'Finish hours before deadline:';
$string['settings_cancel'] = 'Cancel';
$string['btn_export_csv'] = 'Export to CSV';
$string['settings_save'] = 'Save';
$string['tooltip_chart_type'] = 'Select data visualization';
$string['tooltip_date_from'] = 'Filter submissions after this date';
$string['tooltip_date_to'] = 'Filter submissions before this date';
$string['tooltip_avg_grade'] = 'Average grade of all filtered submissions';
$string['tooltip_pass_rate'] = 'Percentage of students passing (>= 5.0)';
$string['tooltip_exc_rate'] = 'Percentage of students with excellent grades (>= 9.0)';
$string['tooltip_median'] = 'Median grade of the filtered students';
$string['tooltip_total_subs'] = 'Total submissions processed in this filter';
$string['tooltip_active_users'] = 'Filtered students with at least one submission';
$string['tooltip_inactive_users'] = 'Filtered students with no submissions';
$string['badge_risk'] = '[Risk]';
$string['badge_risk_desc'] = 'Too many evaluations with low grade';
$string['badge_proc_init'] = '[Proc. Init]';
$string['badge_proc_init_desc'] = 'First submission very close to deadline';
$string['badge_proc_final'] = '[Proc. Final]';
$string['badge_proc_final_desc'] = 'Last submission very close to deadline';
$string['label_student'] = 'Student';
$string['label_group'] = 'Group';
$string['label_no_group'] = 'No Group';
$string['label_no_students'] = 'No students in this selection.';
$string['label_no_data'] = 'No data';
$string['label_subs'] = 'Submissions';
$string['label_students'] = 'Students';
$string['label_avg_grade'] = 'Average Grade';
$string['label_qty'] = 'Quantity';
$string['label_grade_range'] = 'Grade Range';
$string['label_execs'] = 'Executions';
$string['label_evals'] = 'Evaluations';
$string['label_num_subs'] = 'No. Submissions';
$string['settings_grade_scale'] = 'Grade Scale';
$string['scale_base10'] = 'Base 10 (0-10)';
$string['scale_base100'] = 'Base 100 (0-100)';
$string['scale_letters'] = 'Letters (A-F)';
$string['none_selected'] = '< None selected >';
$string['settings_global_desc'] = 'Configure the global evaluation thresholds for the analytics dashboard here. You can access the interactive Dashboard from the "Reports" tab inside any Course.';
$string['report/vpl_analytics:view'] = 'View VPL Analytics dashboard';
$string['setting_pass_threshold'] = 'Passing Threshold';
$string['setting_pass_threshold_desc'] = 'Normalized grade (between 0.0 and 1.0) above which an activity is considered passed. Default: 0.5 (equivalent to 5 out of 10).';
$string['setting_exc_threshold'] = 'Excellence Threshold';
$string['setting_exc_threshold_desc'] = 'Normalized grade (between 0.0 and 1.0) above which an activity is considered excellent. Default: 0.9 (equivalent to 9 out of 10).';
$string['warning_scales_excluded'] = 'Warning: Some VPL activities use non-numeric grading scales (like Fail/Pass) instead of a maximum score. These activities have been temporarily excluded from the statistical calculations to maintain the integrity of the dashboard charts and averages.';
$string['privacy:metadata'] = 'The VPL Analytics Dashboard plugin does not store any personal data. All displayed information is calculated on the fly from existing Moodle data.';

