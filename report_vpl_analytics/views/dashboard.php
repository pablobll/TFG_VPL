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

defined('MOODLE_INTERNAL') || die(); ?>

<?php if (!empty($dashboard_data['has_scales_excluded'])): ?>
    <div class="alert alert-warning" role="alert" style="margin-top:20px; font-weight:500;">
        <?= get_string('warning_scales_excluded', 'report_vpl_analytics') ?>
    </div>
<?php endif; ?>

<div class="vpl-dashboard-wrapper">
    <div class="vpl-kpi-container">
        <div class="vpl-kpi-card" title="<?= get_string('tooltip_avg_grade', 'report_vpl_analytics') ?>">
            <div class="vpl-kpi-title"><?= get_string('kpi_avg_grade', 'report_vpl_analytics') ?></div>
            <div class="vpl-kpi-value" id="kpiAvgGrade">--</div>
        </div>
        <div class="vpl-kpi-card" title="<?= get_string('tooltip_pass_rate', 'report_vpl_analytics') ?>">
            <div class="vpl-kpi-title"><?= get_string('kpi_pass_rate', 'report_vpl_analytics') ?></div>
            <div class="vpl-kpi-value" id="kpiPassRate">--</div>
        </div>
        <div class="vpl-kpi-card" title="<?= get_string('tooltip_exc_rate', 'report_vpl_analytics') ?>">
            <div class="vpl-kpi-title"><?= get_string('kpi_exc_rate', 'report_vpl_analytics') ?></div>
            <div class="vpl-kpi-value" id="kpiExcRate">--</div>
        </div>
        <div class="vpl-kpi-card" title="<?= get_string('tooltip_median', 'report_vpl_analytics') ?>">
            <div class="vpl-kpi-title"><?= get_string('kpi_median', 'report_vpl_analytics') ?></div>
            <div class="vpl-kpi-value" id="kpiMedian">--</div>
        </div>
        <div class="vpl-kpi-card" title="<?= get_string('tooltip_total_subs', 'report_vpl_analytics') ?>">
            <div class="vpl-kpi-title"><?= get_string('kpi_total_subs', 'report_vpl_analytics') ?></div>
            <div class="vpl-kpi-value" id="kpiTotalSubs">0</div>
        </div>
        <div class="vpl-kpi-card" title="<?= get_string('tooltip_active_users', 'report_vpl_analytics') ?>">
            <div class="vpl-kpi-title"><?= get_string('kpi_active_users', 'report_vpl_analytics') ?></div>
            <div class="vpl-kpi-value" id="kpiActiveUsers">0</div>
        </div>
        <div class="vpl-kpi-card" title="<?= get_string('tooltip_inactive_users', 'report_vpl_analytics') ?>">
            <div class="vpl-kpi-title"><?= get_string('kpi_inactive_users', 'report_vpl_analytics') ?></div>
            <div class="vpl-kpi-value danger" id="kpiInactiveUsers">0</div>
        </div>
    </div>

    <div class="vpl-control-panel" style="flex-direction:column; gap:15px;">
        <div style="display:flex; flex-wrap:wrap; align-items:flex-end; width:100%; gap:20px; border-bottom:1px solid #dee2e6; padding-bottom:15px; margin-bottom:15px;">
            <div class="vpl-control-group">
                <label><?= get_string('mode_analysis', 'report_vpl_analytics') ?></label>
                <select id="analysisMode">
                    <option value="global"><?= get_string('mode_global', 'report_vpl_analytics') ?></option>
                    <option value="compare_groups"><?= get_string('mode_compare_groups', 'report_vpl_analytics') ?></option>
                    <option value="compare_users"><?= get_string('mode_compare_users', 'report_vpl_analytics') ?></option>
                    <option value="matriz"><?= get_string('mode_matrix', 'report_vpl_analytics') ?></option>
                </select>
            </div>
            <div class="vpl-control-group">
                <label><?= get_string('chart_type', 'report_vpl_analytics') ?> <span title="<?= get_string('tooltip_chart_type', 'report_vpl_analytics') ?>">(?)</span></label>
                <select id="chartType">
                    <option value="rendimiento"><?= get_string('chart_rendimiento', 'report_vpl_analytics') ?></option>
                    <option value="evolucion"><?= get_string('chart_evolucion', 'report_vpl_analytics') ?></option>
                    <option value="esfuerzo"><?= get_string('chart_esfuerzo', 'report_vpl_analytics') ?></option>
                    <option value="heatmap_tiempo"><?= get_string('chart_heatmap', 'report_vpl_analytics') ?></option>
                </select>
            </div>
            <div class="vpl-control-group">
                <label><?= get_string('filter_date_from', 'report_vpl_analytics') ?> <span title="<?= get_string('tooltip_date_from', 'report_vpl_analytics') ?>">(?)</span></label>
                <input type="date" id="filterDateFrom" class="vpl-date-input">
            </div>
            <div class="vpl-control-group">
                <label><?= get_string('filter_date_to', 'report_vpl_analytics') ?> <span title="<?= get_string('tooltip_date_to', 'report_vpl_analytics') ?>">(?)</span></label>
                <input type="date" id="filterDateTo" class="vpl-date-input">
            </div>
            <div class="vpl-control-group" style="justify-content:flex-end;">
                <label>&nbsp;</label>
                <button type="button" id="btnSettings" class="btn btn-secondary" style="padding:6px 12px; height:auto; cursor:pointer;" title="<?= get_string('settings_title', 'report_vpl_analytics') ?>">
                    <?= get_string('btn_settings', 'report_vpl_analytics') ?>
                </button>
            </div>
        </div>

        <div id="panelGlobal" style="display:flex; flex-wrap:wrap; align-items:flex-end; gap:20px; width:100%;">
            <div class="vpl-control-group">
                <label><?= get_string('filter_group', 'report_vpl_analytics') ?></label>
                <select id="filterGroup">
                    <option value="all"><?= get_string('all_groups', 'report_vpl_analytics') ?></option>
                </select>
            </div>
            <div class="vpl-control-group">
                <label><?= get_string('filter_vpl', 'report_vpl_analytics') ?></label>
                <select id="filterVpl">
                    <option value="all"><?= get_string('all_vpls', 'report_vpl_analytics') ?></option>
                </select>
            </div>
        </div>

        <div id="panelCompareGroups" style="display:none; gap:20px; width:100%;">
            <div class="vpl-control-group">
                <label><?= get_string('group_1', 'report_vpl_analytics') ?></label>
                <select id="compareGroup1">
                    <option value="none"><?= get_string('none_selected', 'report_vpl_analytics') ?></option>
                </select>
            </div>
            <div class="vpl-control-group">
                <label><?= get_string('group_2', 'report_vpl_analytics') ?></label>
                <select id="compareGroup2">
                    <option value="none"><?= get_string('none_selected', 'report_vpl_analytics') ?></option>
                </select>
            </div>
        </div>

        <div id="panelCompareUsers" style="display:none; gap:20px; width:100%;">
            <div class="vpl-control-group">
                <label><?= get_string('user_1', 'report_vpl_analytics') ?></label>
                <select id="compareUser1">
                    <option value="none"><?= get_string('none_selected', 'report_vpl_analytics') ?></option>
                </select>
            </div>
            <div class="vpl-control-group">
                <label><?= get_string('user_2', 'report_vpl_analytics') ?></label>
                <select id="compareUser2">
                    <option value="none"><?= get_string('none_selected', 'report_vpl_analytics') ?></option>
                </select>
            </div>
        </div>
    </div>

    <div class="vpl-canvas-container" style="position:relative;">
        <div id="zoomControls" style="position:absolute; top: 15px; right: 20px; display:flex; gap: 8px; z-index: 10; display:none;">
            <button type="button" id="btnZoomIn" style="padding: 6px 12px; background: #e9ecef; color: #212529; border: 1px solid #ced4da; border-radius: 4px; cursor: pointer; font-weight:bold;">+</button>
            <button type="button" id="btnZoomOut" style="padding: 6px 12px; background: #e9ecef; color: #212529; border: 1px solid #ced4da; border-radius: 4px; cursor: pointer; font-weight:bold;">-</button>
            <button type="button" id="btnZoomReset" style="padding: 6px 12px; background: #e9ecef; color: #212529; border: 1px solid #ced4da; border-radius: 4px; cursor: pointer;">Reset</button>
        </div>
        <div id="chartScrollWrapper" style="width: 100%; height: 100%; overflow-x: auto; overflow-y: hidden;">
            <div id="chartInner" style="height: 100%; min-width: 100%; position: relative;">
                <canvas id="mainChart"></canvas>
                <div id="customHtmlChart" style="display:none; width: 100%; height: 100%; box-sizing: border-box; overflow-y: auto;"></div>
            </div>
        </div>
    </div>

    <div id="tableTopControls" style="display: none; justify-content: space-between; align-items: flex-end; margin-top: 20px; margin-bottom: 10px;">
        <div style="flex:1; display:flex; gap:10px;">
            <input type="text" id="tableSearch" placeholder="<?= get_string('search_student', 'report_vpl_analytics') ?>" style="width:100%; max-width:300px; padding:6px 10px; border:1px solid #ced4da; border-radius:4px; font-size:14px;" autocomplete="off">
            <button id="btnExportCSV" class="btn btn-outline-secondary btn-sm"><?= get_string('btn_export_csv', 'report_vpl_analytics') ?></button>
        </div>
        <div style="text-align: right; font-size: 0.85em; color: #6c757d;">
            <i><?= get_string('scroll_indicator', 'report_vpl_analytics') ?></i>
        </div>
    </div>

    <div class="vpl-table-container" id="mainTableContainer" style="display: none; margin-top: 5px;">
        <table class="vpl-table">
            <thead>
                <tr>
                    <th><?= get_string('col_student', 'report_vpl_analytics') ?></th>
                    <th><?= get_string('col_group', 'report_vpl_analytics') ?></th>
                    <th><?= get_string('col_subs', 'report_vpl_analytics') ?></th>
                    <th><?= get_string('col_grade', 'report_vpl_analytics') ?></th>
                    <th><?= get_string('col_first_sub', 'report_vpl_analytics') ?></th>
                    <th><?= get_string('col_last_sub', 'report_vpl_analytics') ?></th>
                    <th><?= get_string('col_runs', 'report_vpl_analytics') ?></th>
                    <th><?= get_string('col_debugs', 'report_vpl_analytics') ?></th>
                    <th><?= get_string('col_evals', 'report_vpl_analytics') ?></th>
                </tr>
            </thead>
            <tbody id="dataTableBody"></tbody>
        </table>
    </div>
</div>

<div id="settingsModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center;">
    <div style="background:white; padding:20px; border-radius:5px; width:400px; box-shadow:0 4px 12px rgba(0,0,0,0.15);">
        <h3 style="margin-top:0;"><?= get_string('settings_title', 'report_vpl_analytics') ?></h3>
        <p style="font-size:0.9em; color:#666;"><?= get_string('settings_desc', 'report_vpl_analytics') ?></p>
        <div style="margin-bottom:20px;">
            <label style="display:block; margin-bottom:5px; font-weight:bold;"><?= get_string('settings_stagnant', 'report_vpl_analytics') ?></label>
            <div style="display:flex; gap:10px;">
                <div style="flex:1;">
                    <small><?= get_string('settings_stagnant_runs', 'report_vpl_analytics') ?></small><br>
                    <input type="number" id="settingStagnantEvals" value="15" style="width:100%;">
                </div>
                <div style="flex:1;">
                    <small><?= get_string('settings_stagnant_grade', 'report_vpl_analytics') ?></small><br>
                    <input type="number" id="settingStagnantGrade" value="5.0" step="0.1" style="width:100%;">
                </div>
            </div>
        </div>
        <div style="margin-bottom:20px;">
            <label style="display:block; margin-bottom:5px; font-weight:bold;"><?= get_string('settings_proc_init', 'report_vpl_analytics') ?></label>
            <div>
                <small><?= get_string('settings_proc_init_hours', 'report_vpl_analytics') ?></small><br>
                <input type="number" id="settingProcInitHours" value="48" style="width:100%;">
            </div>
        </div>
        <div style="margin-bottom:20px;">
            <label style="display:block; margin-bottom:5px; font-weight:bold;"><?= get_string('settings_proc_final', 'report_vpl_analytics') ?></label>
            <div>
                <small><?= get_string('settings_proc_final_hours', 'report_vpl_analytics') ?></small><br>
                <input type="number" id="settingProcFinalHours" value="2" style="width:100%;">
            </div>
        </div>
        <div style="margin-bottom:20px;">
            <label style="display:block; margin-bottom:5px; font-weight:bold;"><?= get_string('settings_grade_scale', 'report_vpl_analytics') ?></label>
            <select id="settingGradeScale" style="width:100%; padding:6px; border:1px solid #ced4da; border-radius:4px;">
                <option value="10"><?= get_string('scale_base10', 'report_vpl_analytics') ?></option>
                <option value="100"><?= get_string('scale_base100', 'report_vpl_analytics') ?></option>
                <option value="letters"><?= get_string('scale_letters', 'report_vpl_analytics') ?></option>
            </select>
        </div>
        <div style="text-align:right;">
            <button type="button" id="btnSettingsCancel" style="padding:6px 12px; margin-right:10px; cursor:pointer;"><?= get_string('settings_cancel', 'report_vpl_analytics') ?></button>
            <button type="button" id="btnSettingsSave" style="padding:6px 12px; background:#007bff; color:white; border:none; border-radius:4px; cursor:pointer;"><?= get_string('settings_save', 'report_vpl_analytics') ?></button>
        </div>
    </div>
</div>

<script>
    var VplAnalyticsCourseId = <?= json_encode($courseid) ?>;
    var VplAnalyticsLang = <?= $lang_json ?>;
    var VplAnalyticsData = <?= $dashboard_json ?>;

    var define_backup = window.define;
    window.define = undefined;
</script>

<script src="<?= $CFG->wwwroot ?>/report/vpl_analytics/js/vendor/chart.min.js"></script>
<script src="<?= $CFG->wwwroot ?>/report/vpl_analytics/js/vendor/chartjs-adapter-date-fns.bundle.min.js"></script>
<script src="<?= $CFG->wwwroot ?>/report/vpl_analytics/js/vendor/hammer.min.js"></script>
<script src="<?= $CFG->wwwroot ?>/report/vpl_analytics/js/vendor/chartjs-plugin-zoom.min.js"></script>

<script>
    window.define = define_backup;
</script>

<script src="<?= $CFG->wwwroot ?>/report/vpl_analytics/js/dashboard.js?v=<?= $js_version ?>"></script>
