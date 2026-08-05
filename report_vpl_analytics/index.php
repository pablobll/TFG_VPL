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
$PAGE->set_heading(get_string('dashboard_title', 'report_vpl_analytics'));

echo $OUTPUT->header();

$dashboard_data = \report_vpl_analytics\data_manager::get_dashboard_data($courseid);
$dashboard_json = json_encode($dashboard_data);

echo '<script src="' . $CFG->wwwroot . '/report/vpl_analytics/js/vendor/chart.min.js"></script>';
echo '<script src="' . $CFG->wwwroot . '/report/vpl_analytics/js/vendor/chartjs-adapter-date-fns.bundle.min.js"></script>';
echo '<script src="' . $CFG->wwwroot . '/report/vpl_analytics/js/vendor/hammer.min.js"></script>';
echo '<script src="' . $CFG->wwwroot . '/report/vpl_analytics/js/vendor/chartjs-plugin-zoom.min.js"></script>';

echo '<style>
    .vpl-dashboard-wrapper { padding: 20px; font-family: inherit; }
    .vpl-kpi-container { display: flex; gap: 20px; margin-bottom: 20px; flex-wrap: wrap; }
    .vpl-kpi-card { background: #fff; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; flex: 1; min-width: 200px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .vpl-kpi-title { font-size: 0.9em; color: #6c757d; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
    .vpl-kpi-value { font-size: 2.5em; font-weight: bold; color: #007bff; margin-top: 10px; }
    .vpl-kpi-value.danger { color: #dc3545; }
    .vpl-control-panel { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin-bottom: 20px; display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap; }
    .vpl-control-group { display: flex; flex-direction: column; flex: 1; min-width: 250px; }
    .vpl-control-group label { font-weight: bold; margin-bottom: 8px; color: #495057; font-size: 0.9em; }
    .vpl-control-group select { padding: 8px; border-radius: 4px; border: 1px solid #ced4da; background: #ffffff; color: #212529; }
    .vpl-canvas-container { background: #ffffff; border: 1px solid #dee2e6; border-radius: 8px; padding: 30px; height: 500px; display: flex; justify-content: center; align-items: center; }
    canvas { max-width: 100%; max-height: 100%; }
    .vpl-table-container { margin-top: 20px; background: #ffffff; border: 1px solid #dee2e6; border-radius: 8px; padding: 0; max-height: 500px; overflow-y: auto; }
    .vpl-table { width: 100%; border-collapse: collapse; color: #212529; font-size: 0.9em; }
    .vpl-table th { background: #f8f9fa; padding: 15px 20px; text-align: left; font-weight: bold; border-bottom: 2px solid #dee2e6; position: sticky; top: 0; z-index: 10; }
    .vpl-table td { padding: 12px 20px; border-bottom: 1px solid #e9ecef; }
    .vpl-table tbody tr:hover { background: #f1f3f5; }
    .badge { padding: 4px 8px; border-radius: 12px; color: white; font-size: 0.85em; font-weight: bold; }
</style>';

echo '<div class="vpl-dashboard-wrapper">';

echo '<div class="vpl-kpi-container">';
echo '<div class="vpl-kpi-card"><div class="vpl-kpi-title">' . get_string('kpi_avg_grade', 'report_vpl_analytics') . '</div><div class="vpl-kpi-value" id="kpiAvgGrade">--</div></div>';
echo '<div class="vpl-kpi-card"><div class="vpl-kpi-title">' . get_string('kpi_total_subs', 'report_vpl_analytics') . '</div><div class="vpl-kpi-value" id="kpiTotalSubs">--</div></div>';
echo '<div class="vpl-kpi-card"><div class="vpl-kpi-title">' . get_string('kpi_active_users', 'report_vpl_analytics') . '</div><div class="vpl-kpi-value" id="kpiActiveUsers">--</div></div>';
echo '<div class="vpl-kpi-card"><div class="vpl-kpi-title">' . get_string('kpi_inactive_users', 'report_vpl_analytics') . '</div><div class="vpl-kpi-value danger" id="kpiInactiveUsers">--</div></div>';
echo '</div>';

echo '<div class="vpl-control-panel" style="flex-direction:column; gap:15px;">';

echo '<div style="display:flex; width:100%; gap:20px; border-bottom:1px solid #dee2e6; padding-bottom:15px;">';
echo '<div class="vpl-control-group"><label>' . get_string('mode_analysis', 'report_vpl_analytics') . '</label><select id="analysisMode"><option value="global">' . get_string('mode_global', 'report_vpl_analytics') . '</option><option value="compare_groups">' . get_string('mode_compare_groups', 'report_vpl_analytics') . '</option><option value="compare_users">' . get_string('mode_compare_users', 'report_vpl_analytics') . '</option></select></div>';
echo '<div class="vpl-control-group"><label>' . get_string('chart_type', 'report_vpl_analytics') . '</label><select id="chartType"><option value="rendimiento">' . get_string('chart_rendimiento', 'report_vpl_analytics') . '</option><option value="evolucion">' . get_string('chart_evolucion', 'report_vpl_analytics') . '</option><option value="esfuerzo">' . get_string('chart_esfuerzo', 'report_vpl_analytics') . '</option><option value="dedicacion">' . get_string('chart_dedicacion', 'report_vpl_analytics') . '</option><option value="dificultad">' . get_string('chart_dificultad', 'report_vpl_analytics') . '</option></select></div>';
echo '<div class="vpl-control-group"><label>' . get_string('filter_vpl', 'report_vpl_analytics') . '</label><select id="filterVpl"><option value="all">' . get_string('all_vpls', 'report_vpl_analytics') . '</option></select></div>';
echo '</div>';

echo '<div id="panelGlobal" style="display:flex; gap:20px; width:100%;">';
echo '<div class="vpl-control-group"><label>' . get_string('filter_group', 'report_vpl_analytics') . '</label><select id="filterGroup"><option value="all">' . get_string('all_groups', 'report_vpl_analytics') . '</option></select></div>';
echo '</div>';

echo '<div id="panelCompareGroups" style="display:none; gap:20px; width:100%;">';
echo '<div class="vpl-control-group"><label>' . get_string('group_1', 'report_vpl_analytics') . '</label><select id="compareGroup1"></select></div>';
echo '<div class="vpl-control-group"><label>' . get_string('group_2', 'report_vpl_analytics') . '</label><select id="compareGroup2"></select></div>';
echo '</div>';

echo '<div id="panelCompareUsers" style="display:none; gap:20px; width:100%;">';
echo '<div class="vpl-control-group"><label>' . get_string('user_1', 'report_vpl_analytics') . '</label><select id="compareUser1"></select></div>';
echo '<div class="vpl-control-group"><label>' . get_string('user_2', 'report_vpl_analytics') . '</label><select id="compareUser2"></select></div>';
echo '</div>';

echo '</div>';

echo '<div id="chartWarning" style="text-align: center; font-style: italic; font-size: 13px; color: #6c757d; margin-bottom: 15px; display: none;"></div>';
echo '<div class="vpl-canvas-container" style="position:relative;">';
echo '<div id="zoomControls" style="position:absolute; top: 15px; right: 20px; display:flex; gap: 8px; z-index: 10; display:none;">';
echo '<button type="button" id="btnZoomIn" style="padding: 6px 12px; background: #e9ecef; color: #212529; border: 1px solid #ced4da; border-radius: 4px; cursor: pointer; font-weight:bold;">+</button>';
echo '<button type="button" id="btnZoomOut" style="padding: 6px 12px; background: #e9ecef; color: #212529; border: 1px solid #ced4da; border-radius: 4px; cursor: pointer; font-weight:bold;">-</button>';
echo '<button type="button" id="btnZoomReset" style="padding: 6px 12px; background: #e9ecef; color: #212529; border: 1px solid #ced4da; border-radius: 4px; cursor: pointer;">Reset</button>';
echo '</div>';
echo '<div id="chartScrollWrapper" style="width: 100%; height: 100%; overflow-x: auto; overflow-y: hidden;">';
echo '<div id="chartInner" style="height: 100%; min-width: 100%; position: relative;">';
echo '<canvas id="mainChart"></canvas>';
echo '</div>';
echo '</div>';
echo '</div>';

echo '<div id="tableScrollIndicator" style="text-align: right; font-size: 0.85em; color: #6c757d; margin-bottom: 5px; margin-top: 20px; display: none;">';
echo '<i>' . get_string('scroll_indicator', 'report_vpl_analytics') . '</i>';
echo '</div>';

echo '<div class="vpl-table-container" id="mainTableContainer" style="display: none; margin-top: 5px;">';
echo '<table class="vpl-table">';
echo '<thead><tr><th>' . get_string('col_student', 'report_vpl_analytics') . '</th><th>' . get_string('col_group', 'report_vpl_analytics') . '</th><th>' . get_string('col_subs', 'report_vpl_analytics') . '</th><th>' . get_string('col_grade', 'report_vpl_analytics') . '</th><th>' . get_string('col_first_sub', 'report_vpl_analytics') . '</th><th>' . get_string('col_last_sub', 'report_vpl_analytics') . '</th><th>' . get_string('col_runs', 'report_vpl_analytics') . '</th><th>' . get_string('col_debugs', 'report_vpl_analytics') . '</th><th>' . get_string('col_evals', 'report_vpl_analytics') . '</th></tr></thead>';
echo '<tbody id="dataTableBody"></tbody>';
echo '</table>';
echo '</div>';

echo '</div>';


echo "
<script>
const lang = {
    warn_dificultad: '" . get_string('warn_dificultad', 'report_vpl_analytics') . "',
    warn_dedicacion: '" . get_string('warn_dedicacion', 'report_vpl_analytics') . "',
    warn_empty_dedicacion: '" . get_string('warn_empty_dedicacion', 'report_vpl_analytics') . "',
    label_student: '" . get_string('label_student', 'report_vpl_analytics') . "',
    label_group: '" . get_string('label_group', 'report_vpl_analytics') . "',
    label_no_group: '" . get_string('label_no_group', 'report_vpl_analytics') . "',
    label_no_students: '" . get_string('label_no_students', 'report_vpl_analytics') . "',
    label_no_data: '" . get_string('label_no_data', 'report_vpl_analytics') . "',
    label_subs: '" . get_string('label_subs', 'report_vpl_analytics') . "',
    label_num_subs: '" . get_string('label_num_subs', 'report_vpl_analytics') . "',
    label_students: '" . get_string('label_students', 'report_vpl_analytics') . "',
    label_avg_grade: '" . get_string('label_avg_grade', 'report_vpl_analytics') . "',
    label_qty: '" . get_string('label_qty', 'report_vpl_analytics') . "',
    label_grade_range: '" . get_string('label_grade_range', 'report_vpl_analytics') . "',
    label_time_spent: '" . get_string('label_time_spent', 'report_vpl_analytics') . "',
    label_execs: '" . get_string('label_execs', 'report_vpl_analytics') . "',
    label_evals: '" . get_string('label_evals', 'report_vpl_analytics') . "',
    label_qty_students: '" . get_string('label_qty_students', 'report_vpl_analytics') . "'
};
document.addEventListener('DOMContentLoaded', function() {

    const rawData = {$dashboard_json};
    let currentChart = null;
    const primaryColor = '#007bff';
    const secondaryColor = '#9bca3e';

    let expandedSubmissions = [];
    if (rawData.submissions && rawData.users && rawData.user_groups_map) {
        rawData.submissions.forEach(s => {
            if (s.groupid && s.groupid > 0) {
                let groupMembers = rawData.users.filter(uid => rawData.user_groups_map[uid] && rawData.user_groups_map[uid].includes(s.groupid));
                if (groupMembers.length > 0) {
                    groupMembers.forEach(uid => {
                        expandedSubmissions.push({ ...s, userid: uid });
                    });
                } else {
                    expandedSubmissions.push(s);
                }
            } else {
                expandedSubmissions.push(s);
            }
        });
        rawData.submissions = expandedSubmissions;
    }
    
    const SESSION_THRESHOLD = 900; 
    const BASE_TIME = 300; 
    rawData.timeOnTask = {}; 
    let userVplSubs = {};
    rawData.submissions.forEach(s => {
        let key = s.userid + '_' + s.vpl;
        if (!userVplSubs[key]) userVplSubs[key] = [];
        userVplSubs[key].push(s.datesubmitted);
    });
    Object.keys(userVplSubs).forEach(key => {
        let times = userVplSubs[key].sort((a,b) => a - b);
        let totalSecs = 0;
        if (times.length > 0) {
            totalSecs += BASE_TIME;
            for (let i = 1; i < times.length; i++) {
                let diff = times[i] - times[i-1];
                if (diff <= SESSION_THRESHOLD) {
                    totalSecs += diff;
                } else {
                    totalSecs += BASE_TIME;
                }
            }
        }
        rawData.timeOnTask[key] = totalSecs;
    });

    const analysisModeEl = document.getElementById('analysisMode');
    const chartTypeEl = document.getElementById('chartType');
    const filterGroupEl = document.getElementById('filterGroup');
    const filterVplEl = document.getElementById('filterVpl');
    
    const panelGlobal = document.getElementById('panelGlobal');
    const panelCompareGroups = document.getElementById('panelCompareGroups');
    const panelCompareUsers = document.getElementById('panelCompareUsers');

    const compareGroup1El = document.getElementById('compareGroup1');
    const compareGroup2El = document.getElementById('compareGroup2');
    const compareUser1El = document.getElementById('compareUser1');
    const compareUser2El = document.getElementById('compareUser2');
    
    const zoomOptions = {
        pan: { enabled: true, mode: 'xy' },
        zoom: { wheel: { enabled: false }, pinch: { enabled: false }, mode: 'xy' }
    };

    document.getElementById('btnZoomIn').addEventListener('click', () => {
        if (currentChart && typeof currentChart.zoom === 'function') currentChart.zoom(1.2);
    });
    document.getElementById('btnZoomOut').addEventListener('click', () => {
        if (currentChart && typeof currentChart.zoom === 'function') currentChart.zoom(0.8);
    });
    document.getElementById('btnZoomReset').addEventListener('click', () => {
        if (currentChart && typeof currentChart.resetZoom === 'function') currentChart.resetZoom();
    });

    let groupMap = {};
    let groupCountMap = {};
    rawData.groups.forEach(g => {
        groupMap[g.id] = g.name;
        groupCountMap[g.id] = g.member_count;
        
        let opt1 = document.createElement('option'); opt1.value = g.id; opt1.innerText = g.name;
        filterGroupEl.appendChild(opt1);
        
        let opt2 = document.createElement('option'); opt2.value = g.id; opt2.innerText = g.name;
        compareGroup1El.appendChild(opt2);
        
        let opt3 = document.createElement('option'); opt3.value = g.id; opt3.innerText = g.name;
        compareGroup2El.appendChild(opt3);
    });
    
    rawData.users.forEach(uid => {
        let opt1 = document.createElement('option'); opt1.value = uid; opt1.innerText = lang.label_student + ' ' + uid;
        compareUser1El.appendChild(opt1);
        let opt2 = document.createElement('option'); opt2.value = uid; opt2.innerText = lang.label_student + ' ' + uid;
        compareUser2El.appendChild(opt2);
    });

    if (rawData.vpls) {
        rawData.vpls.forEach(v => {
            let opt = document.createElement('option');
            opt.value = v.id; opt.innerText = v.name;
            filterVplEl.appendChild(opt);
        });
    } else {
        let uniqueVpls = {};
        rawData.submissions.forEach(s => { uniqueVpls[s.vpl] = s.vpl_name; });
        Object.keys(uniqueVpls).forEach(vplId => {
            let opt = document.createElement('option');
            opt.value = vplId; opt.innerText = uniqueVpls[vplId];
            filterVplEl.appendChild(opt);
        });
    }

    analysisModeEl.addEventListener('change', () => {
        panelGlobal.style.display = 'none';
        panelCompareGroups.style.display = 'none';
        panelCompareUsers.style.display = 'none';
        
        if (analysisModeEl.value === 'global') {
            panelGlobal.style.display = 'flex';
        } else {
            if (analysisModeEl.value === 'compare_groups') panelCompareGroups.style.display = 'flex';
            else if (analysisModeEl.value === 'compare_users') panelCompareUsers.style.display = 'flex';
        }
        
        updateDashboard();
    });

    [chartTypeEl, filterGroupEl, filterVplEl, compareGroup1El, compareGroup2El, compareUser1El, compareUser2El].forEach(el => el.addEventListener('change', updateDashboard));

    function updateDashboard() {
        const diffOption = Array.from(chartTypeEl.options).find(opt => opt.value === 'dificultad');
        if (diffOption) { diffOption.disabled = false; diffOption.style.display = ''; }
        
        if (analysisModeEl.value !== 'global' || filterVplEl.value !== 'all') {
            if (diffOption) { diffOption.disabled = true; diffOption.style.display = 'none'; }
            if (chartTypeEl.value === 'dificultad') chartTypeEl.value = 'rendimiento';
        }

        const mode = analysisModeEl.value;
        const type = chartTypeEl.value;
        const vplId = filterVplEl.value;

        let baseFiltered = rawData.submissions;
        if (vplId !== 'all') baseFiltered = baseFiltered.filter(s => s.vpl == vplId);

        let datasetsInfo = [];

        if (mode === 'global') {
            const groupId = filterGroupEl.value;
            let finalData = baseFiltered;
            let currentTotalStudents = rawData.total_students;
            
            if (groupId !== 'all') {
                const gid = parseInt(groupId);
                finalData = finalData.filter(s => s.user_groups && s.user_groups.includes(gid));
                currentTotalStudents = groupCountMap[gid] || 0;
            }
            datasetsInfo.push({ label: 'Global', data: finalData, color: primaryColor });
            updateKPIs(finalData, currentTotalStudents);
            
            if (groupId === 'all') {
                document.getElementById('mainTableContainer').style.display = 'none';
                document.getElementById('tableScrollIndicator').style.display = 'none';
            } else {
                document.getElementById('mainTableContainer').style.display = 'block';
                document.getElementById('tableScrollIndicator').style.display = 'block';
                updateTable(finalData, [parseInt(groupId)], null);
            }
            
        } else if (mode === 'compare_groups') {
            const gid1 = parseInt(compareGroup1El.value);
            const gid2 = parseInt(compareGroup2El.value);
            let d1 = baseFiltered.filter(s => s.user_groups && s.user_groups.includes(gid1));
            let d2 = baseFiltered.filter(s => s.user_groups && s.user_groups.includes(gid2));
            datasetsInfo.push({ label: groupMap[gid1] || lang.label_group + ' ' + gid1, data: d1, color: primaryColor });
            datasetsInfo.push({ label: groupMap[gid2] || lang.label_group + ' ' + gid2, data: d2, color: secondaryColor });
            
            let combinedMap = new Map();
            [...d1, ...d2].forEach(s => combinedMap.set(s.id, s));
            let combined = Array.from(combinedMap.values());
            
            let allowedUsers = new Set();
            if (rawData.users && rawData.user_groups_map) {
                rawData.users.forEach(uid => {
                    let uGroups = rawData.user_groups_map[uid] || [0];
                    if (uGroups.includes(gid1) || uGroups.includes(gid2)) {
                        allowedUsers.add(uid);
                    }
                });
            }
            let combinedTotal = allowedUsers.size > 0 ? allowedUsers.size : (groupCountMap[gid1] || 0) + (groupCountMap[gid2] || 0);
            
            updateKPIs(combined, combinedTotal);
            
            document.getElementById('mainTableContainer').style.display = 'block';
            document.getElementById('tableScrollIndicator').style.display = 'block';
            updateTable(combined, [gid1, gid2], null);
        } else if (mode === 'compare_users') {
            const uid1 = parseInt(compareUser1El.value);
            const uid2 = parseInt(compareUser2El.value);
            let d1 = baseFiltered.filter(s => s.userid === uid1);
            let d2 = baseFiltered.filter(s => s.userid === uid2);
            datasetsInfo.push({ label: lang.label_student + ' ' + uid1, data: d1, color: primaryColor });
            datasetsInfo.push({ label: lang.label_student + ' ' + uid2, data: d2, color: secondaryColor });
            
            let combinedMap = new Map();
            [...d1, ...d2].forEach(s => combinedMap.set(s.id, s));
            let combined = Array.from(combinedMap.values());
            
            let combinedTotal = uid1 === uid2 ? 1 : 2;
            updateKPIs(combined, combinedTotal);
            
            document.getElementById('mainTableContainer').style.display = 'block';
            document.getElementById('tableScrollIndicator').style.display = 'block';
            updateTable(combined, null, [uid1, uid2]);
        }

        renderChart(type, datasetsInfo);
    }

    function updateKPIs(subs, totalStudentsFallback) {
        let totalAllowed = totalStudentsFallback !== undefined ? totalStudentsFallback : (rawData.total_students || 0);
        
        if (subs.length === 0) {
            document.getElementById('kpiAvgGrade').innerText = '0.00';
            document.getElementById('kpiTotalSubs').innerText = '0';
            document.getElementById('kpiActiveUsers').innerText = '0';
            document.getElementById('kpiInactiveUsers').innerText = totalAllowed;
            return;
        }

        let activeUsers = new Set();
        let finalGrades = {};
        
        subs.forEach(s => {
            activeUsers.add(s.userid);
            if (s.grade !== null) {
                let key = s.userid + '_' + s.vpl;
                if (finalGrades[key] === undefined || s.datesubmitted > finalGrades[key].date) {
                    finalGrades[key] = { grade: s.grade, date: s.datesubmitted };
                }
            }
        });

        let sumGrades = 0;
        let countGrades = 0;
        Object.values(finalGrades).forEach(g => {
            sumGrades += g.grade;
            countGrades++;
        });

        const avgGrade = countGrades > 0 ? (sumGrades / countGrades).toFixed(2) : '0.00';
        const totalSubs = subs.length;
        const activeCount = activeUsers.size;
        const inactiveCount = Math.max(0, totalAllowed - activeCount);

        document.getElementById('kpiAvgGrade').innerText = avgGrade;
        document.getElementById('kpiTotalSubs').innerText = totalSubs;
        document.getElementById('kpiActiveUsers').innerText = activeCount + (totalAllowed ? ' / ' + totalAllowed : '');
        document.getElementById('kpiInactiveUsers').innerText = inactiveCount;
    }

    function updateTable(subs, allowedGroupIds = null, allowedUserIds = null) {
        const tbody = document.getElementById('dataTableBody');
        tbody.innerHTML = '';

        let studentStats = {};
        subs.forEach(s => {
            if (!studentStats[s.userid]) {
                let gNames = (s.user_groups || []).map(gid => groupMap[gid] || gid).join(', ');
                if (!gNames) gNames = lang.label_no_group;
                
                studentStats[s.userid] = {
                    group: gNames, subs: 0, finalGrade: null, lastGradeDate: 0,
                    firstSub: s.datesubmitted, lastSub: s.datesubmitted,
                    vplMaxEffort: {}
                };
            }
            let st = studentStats[s.userid];
            st.subs++;
            if (s.grade !== null) {
                if (st.finalGrade === null || s.datesubmitted > st.lastGradeDate) {
                    st.finalGrade = s.grade;
                    st.lastGradeDate = s.datesubmitted;
                }
            }
            if (s.datesubmitted < st.firstSub) st.firstSub = s.datesubmitted;
            if (s.datesubmitted > st.lastSub) st.lastSub = s.datesubmitted;
            
            if (!st.vplMaxEffort[s.vpl]) {
                st.vplMaxEffort[s.vpl] = { runs: 0, evals: 0, debugs: 0 };
            }
            if (s.run_count > st.vplMaxEffort[s.vpl].runs) st.vplMaxEffort[s.vpl].runs = s.run_count;
            if (s.nevaluations > st.vplMaxEffort[s.vpl].evals) st.vplMaxEffort[s.vpl].evals = s.nevaluations;
            if (s.debug_count && s.debug_count > st.vplMaxEffort[s.vpl].debugs) st.vplMaxEffort[s.vpl].debugs = s.debug_count;
        });

        if (rawData.users && rawData.user_groups_map) {
            rawData.users.forEach(uid => {
                if (allowedUserIds !== null) {
                    if (!allowedUserIds.includes(uid)) return;
                } else {
                    let uGroups = rawData.user_groups_map[uid] || [];
                    if (uGroups.length === 0) uGroups = [0];
                    if (allowedGroupIds !== null) {
                        let hasMatch = allowedGroupIds.some(gid => uGroups.includes(gid));
                        if (!hasMatch) return;
                    }
                }

                if (!studentStats[uid]) {
                    let uGroups = rawData.user_groups_map[uid] || [];
                    if (uGroups.length === 0) uGroups = [0];
                    let gNames = uGroups.map(gid => groupMap[gid] || gid).join(', ');
                    if (!gNames || gNames === '0') gNames = lang.label_no_group;

                    studentStats[uid] = {
                        group: gNames, subs: 0, finalGrade: null, lastGradeDate: 0,
                        firstSub: null, lastSub: null,
                        vplMaxEffort: {}
                    };
                }
            });
        }

        let sortedUsers = Object.keys(studentStats).sort((a,b) => a - b);
        if (sortedUsers.length === 0) {
            tbody.innerHTML = '<tr><td colspan=\"8\" style=\"text-align:center\">' + lang.label_no_students + '</td></tr>';
            return;
        }

        sortedUsers.forEach(uid => {
            let st = studentStats[uid];
            let dFirst = st.firstSub ? new Date(st.firstSub * 1000).toLocaleDateString() : '--';
            let dLast = st.lastSub ? new Date(st.lastSub * 1000).toLocaleDateString() : '--';
            let gradeStr = st.finalGrade !== null ? st.finalGrade.toFixed(2) : '--';
            
            let totalRuns = 0;
            let totalEvals = 0;
            let totalDebugs = 0;
            if (st.vplMaxEffort) {
                Object.keys(st.vplMaxEffort).forEach(vplId => {
                    let v = st.vplMaxEffort[vplId];
                    totalRuns += v.runs || 0;
                    totalEvals += v.evals || 0;
                    totalDebugs += v.debugs || 0;
                });
            }
            
            let tr = document.createElement('tr');
            tr.innerHTML = `
                <td>\${uid}</td>
                <td>\${st.group}</td>
                <td>\${st.subs}</td>
                <td>\${gradeStr}</td>
                <td>\${dFirst}</td>
                <td>\${dLast}</td>
                <td>\${totalRuns}</td>
                <td>\${totalDebugs}</td>
                <td>\${totalEvals}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    function renderChart(type, datasetsInfo) {
        if (currentChart) currentChart.destroy();
        const ctx = document.getElementById('mainChart').getContext('2d');
        const chartInner = document.getElementById('chartInner');
        chartInner.style.minWidth = '100%';
        const chartWarning = document.getElementById('chartWarning');
        chartWarning.style.display = 'none';
        chartWarning.innerText = '';

        let totalSubs = datasetsInfo.reduce((acc, ds) => acc + ds.data.length, 0);
        if (totalSubs === 0) {
            currentChart = new Chart(ctx, { type: 'bar', data: { labels: [lang.label_no_data], datasets: [{data:[0]}] }});
            document.getElementById('zoomControls').style.display = 'none';
            return;
        }

        let zoomControls = document.getElementById('zoomControls');
        if (type === 'esfuerzo' || type === 'evolucion') zoomControls.style.display = 'flex';
        else zoomControls.style.display = 'none';

        let chartDatasets = [];
        let commonLabels = [];

        if (type === 'rendimiento') {
            let rangesList = datasetsInfo.map(ds => {
                let ranges = {'0-2':0, '2-4':0, '4-5':0, '5-7':0, '7-9':0, '9-10':0};
                
                let finalGrades = {};
                ds.data.forEach(s => {
                    if (s.grade === null) return;
                    let key = s.userid + '_' + s.vpl;
                    if (finalGrades[key] === undefined || s.datesubmitted > finalGrades[key].date) {
                        finalGrades[key] = { grade: s.grade, date: s.datesubmitted };
                    }
                });

                Object.values(finalGrades).forEach(g => {
                    let grade = g.grade;
                    if (grade < 2) {
                        ranges['0-2']++;
                    } else if (grade < 4) {
                        ranges['2-4']++;
                    } else if (grade < 5) {
                        ranges['4-5']++;
                    } else if (grade < 7) {
                        ranges['5-7']++;
                    } else if (grade < 9) {
                        ranges['7-9']++;
                    } else {
                        ranges['9-10']++;
                    }
                });
                return ranges;
            });
            commonLabels = Object.keys(rangesList[0]);
            chartDatasets = datasetsInfo.map((ds, i) => ({
                label: lang.label_num_subs + ' (' + ds.label + ')',
                data: Object.values(rangesList[i]),
                backgroundColor: ds.color
            }));

            currentChart = new Chart(ctx, {
                type: 'bar',
                data: { labels: commonLabels, datasets: chartDatasets },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false }, zoom: zoomOptions },
                    scales: { y: { beginAtZero: true, title: {display:true, text:lang.label_qty} }, x: {title: {display:true, text:lang.label_grade_range}} }
                }
            });

        } else if (type === 'esfuerzo') {
            chartDatasets = datasetsInfo.map(ds => {
                let userEffort = {};
                ds.data.forEach(s => {
                    if (!userEffort[s.userid]) {
                        userEffort[s.userid] = { vplMaxEffort: {} };
                    }
                    if (!userEffort[s.userid].vplMaxEffort[s.vpl]) {
                        userEffort[s.userid].vplMaxEffort[s.vpl] = { runs: 0, evals: 0 };
                    }
                    if (s.run_count > userEffort[s.userid].vplMaxEffort[s.vpl].runs) {
                        userEffort[s.userid].vplMaxEffort[s.vpl].runs = s.run_count;
                    }
                    if (s.nevaluations > userEffort[s.userid].vplMaxEffort[s.vpl].evals) {
                        userEffort[s.userid].vplMaxEffort[s.vpl].evals = s.nevaluations;
                    }
                });
                
                let scatterData = Object.keys(userEffort).map(uid => {
                    let totalR = 0, totalE = 0;
                    Object.values(userEffort[uid].vplMaxEffort).forEach(v => {
                        totalR += v.runs;
                        totalE += v.evals;
                    });
                    return { x: totalR, y: totalE, userid: uid };
                });

                return {
                    label: ds.label,
                    data: scatterData,
                    backgroundColor: ds.color + '99',
                    pointRadius: 5
                };
            });
            
            currentChart = new Chart(ctx, {
                type: 'scatter',
                data: { datasets: chartDatasets },
                options: {
                    responsive: true,
                    plugins: { 
                        legend: { display: false },
                        zoom: zoomOptions,
                        tooltip: { callbacks: { label: function(ctx) { return `Alumno \${ctx.raw.userid}: \${ctx.raw.x} ejec., \${ctx.raw.y} evals.`; } } }
                    },
                    scales: { x: { title: { display: true, text: lang.label_execs } }, y: { title: { display: true, text: lang.label_evals } } }
                }
            });

        } else if (type === 'evolucion') {
            let dateSets = datasetsInfo.map(ds => {
                let dateCounts = {};
                ds.data.forEach(s => {
                    let jsDate = new Date(s.datesubmitted * 1000);
                    let d = jsDate.getFullYear() + '-' + 
                            String(jsDate.getMonth() + 1).padStart(2, '0') + '-' + 
                            String(jsDate.getDate()).padStart(2, '0');
                    dateCounts[d] = (dateCounts[d] || 0) + 1;
                });
                return dateCounts;
            });
            
            let allDates = new Set();
            dateSets.forEach(dc => Object.keys(dc).forEach(d => allDates.add(d)));
            commonLabels = Array.from(allDates).sort();

            chartDatasets = datasetsInfo.map((ds, i) => {
                let dataPoints = commonLabels.map(d => ({ x: d, y: dateSets[i][d] || 0 }));
                return {
                    label: ds.label,
                    data: dataPoints,
                    borderColor: ds.color,
                    backgroundColor: ds.color + '33',
                    fill: true, tension: 0.1
                };
            });

            currentChart = new Chart(ctx, {
                type: 'line',
                data: { datasets: chartDatasets },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false }, zoom: zoomOptions },
                    scales: { x: { type: 'time', time: {unit: 'day'} }, y: { beginAtZero: true, title: {display:true, text:lang.label_subs} } }
                }
            });

        } else if (type === 'dificultad') {
            let vplSets = datasetsInfo.map(ds => {
                let finalGrades = {};
                let vplNames = {};
                
                ds.data.forEach(s => {
                    if (s.grade === null) return;
                    vplNames[s.vpl] = s.vpl_name;
                    let key = s.userid + '_' + s.vpl;
                    if (finalGrades[key] === undefined || s.datesubmitted > finalGrades[key].date) {
                        finalGrades[key] = { vpl: s.vpl, grade: s.grade, date: s.datesubmitted };
                    }
                });

                let vplStats = {};
                Object.values(finalGrades).forEach(fg => {
                    if (!vplStats[fg.vpl]) vplStats[fg.vpl] = { name: vplNames[fg.vpl], sumGrade: 0, countGrade: 0 };
                    vplStats[fg.vpl].sumGrade += fg.grade;
                    vplStats[fg.vpl].countGrade++;
                });
                return vplStats;
            });

            let allVplsMap = {};
            vplSets.forEach(vs => Object.keys(vs).forEach(vid => allVplsMap[vid] = vs[vid].name));
            
            let vplArray = Object.keys(allVplsMap).map(vid => {
                let sum = 0, count = 0;
                vplSets.forEach(vs => { if (vs[vid]) { sum += vs[vid].sumGrade; count += vs[vid].countGrade; } });
                return { id: vid, name: allVplsMap[vid], avgSort: count > 0 ? (sum/count) : 0 };
            });
            vplArray.sort((a,b) => a.id - b.id);
            commonLabels = vplArray.map(v => v.name);

            chartDatasets = datasetsInfo.map((ds, i) => {
                let dataArray = vplArray.map(v => {
                    let st = vplSets[i][v.id];
                    return st && st.countGrade > 0 ? parseFloat((st.sumGrade / st.countGrade).toFixed(2)) : 0;
                });
                return {
                    label: lang.label_avg_grade + ' (' + ds.label + ')',
                    data: dataArray,
                    backgroundColor: ds.color
                };
            });

            if (commonLabels.length > 10) {
                chartInner.style.minWidth = (commonLabels.length * 60) + 'px';
            }

            chartWarning.innerText = lang.warn_dificultad;
            chartWarning.style.display = 'block';

            currentChart = new Chart(ctx, {
                type: 'bar',
                data: { labels: commonLabels, datasets: chartDatasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: false },
                        zoom: zoomOptions 
                    },
                    scales: { y: { beginAtZero: true, max: 10, title: {display:true, text:lang.label_avg_grade} } }
                }
            });
        } else if (type === 'dedicacion') {
            if (filterVplEl.value === 'all') {
                currentChart = new Chart(ctx, {
                    type: 'bar',
                    data: { labels: [], datasets: [] },
                    options: {
                        responsive: true,
                        plugins: { 
                            title: { display: true, text: lang.warn_empty_dedicacion, font: {size: 16}, padding: {top: 50} }
                        },
                        scales: { x: { display: false }, y: { display: false } }
                    }
                });
                return;
            }

            commonLabels = ['0-1h', '1-2h', '2-3h', '3-4h', '4-5h', '5-6h', '6-7h', '7-8h', '8-9h', '9-10h', '>10h'];
            chartDatasets = datasetsInfo.map(ds => {
                let binCounts = new Array(11).fill(0);
                let userVpls = new Set();
                ds.data.forEach(s => userVpls.add(s.userid + '_' + s.vpl));
                let userTotalTime = {};
                userVpls.forEach(key => {
                    let parts = key.split('_'); let uid = parts[0];
                    if (!userTotalTime[uid]) userTotalTime[uid] = 0;
                    userTotalTime[uid] += (rawData.timeOnTask[key] || 0);
                });
                Object.values(userTotalTime).forEach(secs => {
                    let hours = secs / 3600;
                    if (hours < 1) binCounts[0]++; else if (hours < 2) binCounts[1]++; else if (hours < 3) binCounts[2]++; else if (hours < 4) binCounts[3]++; else if (hours < 5) binCounts[4]++; else if (hours < 6) binCounts[5]++; else if (hours < 7) binCounts[6]++; else if (hours < 8) binCounts[7]++; else if (hours < 9) binCounts[8]++; else if (hours < 10) binCounts[9]++; else binCounts[10]++;
                });
                return { label: lang.label_students + ' (' + ds.label + ')', data: binCounts, backgroundColor: ds.color };
            });
            chartWarning.innerText = lang.warn_dedicacion;
            chartWarning.style.display = 'block';

            currentChart = new Chart(ctx, {
                type: 'bar',
                data: { labels: commonLabels, datasets: chartDatasets },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false }, zoom: zoomOptions },
                    scales: { y: { beginAtZero: true, title: {display:true, text:lang.label_qty_students} }, x: { title: {display:true, text:lang.label_time_spent} } }
                }
            });
        }
    }

    updateDashboard();
});
</script>
";
echo $OUTPUT->footer();
