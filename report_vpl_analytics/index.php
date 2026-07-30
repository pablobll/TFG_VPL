<?php
ini_set('memory_limit', '512M');
require(__DIR__ . '/../../config.php');

$courseid = required_param('id', PARAM_INT);
require_login($courseid);
$context = context_course::instance($courseid);
require_capability('report/vpl_analytics:view', $context);

$url = new moodle_url('/report/vpl_analytics/index.php', array('id' => $courseid));
$PAGE->set_url($url);
$PAGE->set_title('Dashboard Analítico VPL');
$PAGE->set_heading('Dashboard Analítico VPL');

echo $OUTPUT->header();

$dashboard_data = \report_vpl_analytics\data_manager::get_dashboard_data($courseid);
$dashboard_json = json_encode($dashboard_data);

echo '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
echo '<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>';
echo '<script src="https://cdn.jsdelivr.net/npm/hammerjs@2.0.8"></script>';
echo '<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1"></script>';

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
echo '<div class="vpl-kpi-card"><div class="vpl-kpi-title">Nota Media Global</div><div class="vpl-kpi-value" id="kpiAvgGrade">--</div></div>';
echo '<div class="vpl-kpi-card"><div class="vpl-kpi-title">Entregas Totales</div><div class="vpl-kpi-value" id="kpiTotalSubs">--</div></div>';
echo '<div class="vpl-kpi-card"><div class="vpl-kpi-title">Alumnos Activos</div><div class="vpl-kpi-value" id="kpiActiveUsers">--</div></div>';
echo '<div class="vpl-kpi-card"><div class="vpl-kpi-title">Alumnos Sin Actividad</div><div class="vpl-kpi-value danger" id="kpiInactiveUsers">--</div></div>';
echo '</div>';

echo '<div class="vpl-control-panel" style="flex-direction:column; gap:15px;">';

echo '<div style="display:flex; width:100%; gap:20px; border-bottom:1px solid #dee2e6; padding-bottom:15px;">';
echo '<div class="vpl-control-group"><label>Modo de Análisis</label><select id="analysisMode"><option value="global">Análisis Global</option><option value="compare_groups">Comparar Grupos</option><option value="compare_users">Comparar Alumnos</option></select></div>';
echo '<div class="vpl-control-group"><label>Tipo de Visualización</label><select id="chartType"><option value="rendimiento">Distribución de Notas Finales</option><option value="evolucion">Evolución de Entregas en el Tiempo</option><option value="esfuerzo">Esfuerzo (Ejecuciones vs Evaluaciones)</option><option value="dificultad">Dificultad por Actividad</option></select></div>';
echo '<div class="vpl-control-group"><label>Actividad VPL</label><select id="filterVpl"><option value="all">Todas las actividades</option></select></div>';
echo '</div>';

echo '<div id="panelGlobal" style="display:flex; gap:20px; width:100%;">';
echo '<div class="vpl-control-group"><label>Filtrar por Grupo</label><select id="filterGroup"><option value="all">Todos los grupos</option></select></div>';
echo '</div>';

echo '<div id="panelCompareGroups" style="display:none; gap:20px; width:100%;">';
echo '<div class="vpl-control-group"><label>Grupo 1 (Azul)</label><select id="compareGroup1"></select></div>';
echo '<div class="vpl-control-group"><label>Grupo 2 (Verde)</label><select id="compareGroup2"></select></div>';
echo '</div>';

echo '<div id="panelCompareUsers" style="display:none; gap:20px; width:100%;">';
echo '<div class="vpl-control-group"><label>Alumno 1 (Azul)</label><select id="compareUser1"></select></div>';
echo '<div class="vpl-control-group"><label>Alumno 2 (Verde)</label><select id="compareUser2"></select></div>';
echo '</div>';

echo '</div>';

echo '<div class="vpl-canvas-container" style="position:relative;">';
echo '<div id="zoomControls" style="position:absolute; top: 15px; right: 20px; display:flex; gap: 8px; z-index: 10; display:none;">';
echo '<button type="button" id="btnZoomIn" style="padding: 6px 12px; background: #e9ecef; color: #212529; border: 1px solid #ced4da; border-radius: 4px; cursor: pointer; font-weight:bold;">+</button>';
echo '<button type="button" id="btnZoomOut" style="padding: 6px 12px; background: #e9ecef; color: #212529; border: 1px solid #ced4da; border-radius: 4px; cursor: pointer; font-weight:bold;">-</button>';
echo '<button type="button" id="btnZoomReset" style="padding: 6px 12px; background: #e9ecef; color: #212529; border: 1px solid #ced4da; border-radius: 4px; cursor: pointer;">Reset</button>';
echo '</div>';
echo '<canvas id="mainChart"></canvas>';
echo '</div>';

echo '<div id="tableScrollIndicator" style="text-align: right; font-size: 0.85em; color: #6c757d; margin-bottom: 5px; margin-top: 20px; display: none;">';
echo '<i>↓ Desliza hacia abajo dentro de la tabla para ver más alumnos</i>';
echo '</div>';

echo '<div class="vpl-table-container" id="mainTableContainer" style="display: none; margin-top: 5px;">';
echo '<table class="vpl-table">';
echo '<thead><tr><th>Alumno (ID)</th><th>Grupo</th><th>Entregas</th><th>Nota Final</th><th>Primera Entrega</th><th>Última Entrega</th><th>Ejecuciones</th><th>Evals. Auto.</th></tr></thead>';
echo '<tbody id="dataTableBody"></tbody>';
echo '</table>';
echo '</div>';

echo '</div>';

echo "
<script>
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
        let opt1 = document.createElement('option'); opt1.value = uid; opt1.innerText = 'Alumno ' + uid;
        compareUser1El.appendChild(opt1);
        let opt2 = document.createElement('option'); opt2.value = uid; opt2.innerText = 'Alumno ' + uid;
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
        
        const diffOption = Array.from(chartTypeEl.options).find(opt => opt.value === 'dificultad');
        
        if (analysisModeEl.value === 'global') {
            panelGlobal.style.display = 'flex';
            if (diffOption) {
                diffOption.disabled = false;
                diffOption.style.display = '';
            }
        } else {
            if (analysisModeEl.value === 'compare_groups') panelCompareGroups.style.display = 'flex';
            else if (analysisModeEl.value === 'compare_users') panelCompareUsers.style.display = 'flex';
            
            if (diffOption) {
                diffOption.disabled = true;
                diffOption.style.display = 'none';
                if (chartTypeEl.value === 'dificultad') {
                    chartTypeEl.value = 'rendimiento';
                }
            }
        }
        updateDashboard();
    });

    [chartTypeEl, filterGroupEl, filterVplEl, compareGroup1El, compareGroup2El, compareUser1El, compareUser2El].forEach(el => el.addEventListener('change', updateDashboard));

    function updateDashboard() {
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
            datasetsInfo.push({ label: groupMap[gid1] || 'Grupo ' + gid1, data: d1, color: primaryColor });
            datasetsInfo.push({ label: groupMap[gid2] || 'Grupo ' + gid2, data: d2, color: secondaryColor });
            
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
            datasetsInfo.push({ label: 'Alumno ' + uid1, data: d1, color: primaryColor });
            datasetsInfo.push({ label: 'Alumno ' + uid2, data: d2, color: secondaryColor });
            
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
                if (!gNames) gNames = 'Sin Grupo';
                
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
                st.vplMaxEffort[s.vpl] = { runs: 0, evals: 0 };
            }
            if (s.run_count > st.vplMaxEffort[s.vpl].runs) st.vplMaxEffort[s.vpl].runs = s.run_count;
            if (s.nevaluations > st.vplMaxEffort[s.vpl].evals) st.vplMaxEffort[s.vpl].evals = s.nevaluations;
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
                    if (!gNames || gNames === '0') gNames = 'Sin Grupo';

                    studentStats[uid] = {
                        group: gNames, subs: 0, finalGrade: null, lastGradeDate: 0,
                        firstSub: null, lastSub: null,
                        runs: 0, evals: 0
                    };
                }
            });
        }

        let sortedUsers = Object.keys(studentStats).sort((a,b) => a - b);
        if (sortedUsers.length === 0) {
            tbody.innerHTML = '<tr><td colspan=\"8\" style=\"text-align:center\">No hay alumnos en esta selección.</td></tr>';
            return;
        }

        sortedUsers.forEach(uid => {
            let st = studentStats[uid];
            let dFirst = st.firstSub ? new Date(st.firstSub * 1000).toLocaleDateString() : '--';
            let dLast = st.lastSub ? new Date(st.lastSub * 1000).toLocaleDateString() : '--';
            let gradeStr = st.finalGrade !== null ? st.finalGrade.toFixed(2) : '--';
            
            let totalRuns = 0;
            let totalEvals = 0;
            if (st.vplMaxEffort) {
                Object.values(st.vplMaxEffort).forEach(v => {
                    totalRuns += v.runs;
                    totalEvals += v.evals;
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
                <td>\${totalEvals}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    function renderChart(type, datasetsInfo) {
        if (currentChart) currentChart.destroy();
        const ctx = document.getElementById('mainChart').getContext('2d');

        let totalSubs = datasetsInfo.reduce((acc, ds) => acc + ds.data.length, 0);
        if (totalSubs === 0) {
            currentChart = new Chart(ctx, { type: 'bar', data: { labels: ['Sin datos'], datasets: [{data:[0]}] }});
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
                    } else if (g < 4) {
                        ranges['2-4']++;
                    } else if (g < 5) {
                        ranges['4-5']++;
                    } else if (g < 7) {
                        ranges['5-7']++;
                    } else if (g < 9) {
                        ranges['7-9']++;
                    } else {
                        ranges['9-10']++;
                    }
                });
                return ranges;
            });
            commonLabels = Object.keys(rangesList[0]);
            chartDatasets = datasetsInfo.map((ds, i) => ({
                label: 'Nº Entregas (' + ds.label + ')',
                data: Object.values(rangesList[i]),
                backgroundColor: ds.color
            }));

            currentChart = new Chart(ctx, {
                type: 'bar',
                data: { labels: commonLabels, datasets: chartDatasets },
                options: {
                    responsive: true,
                    plugins: { title: { display: true, text: 'Distribución de Notas', font: {size: 16} } },
                    scales: { y: { beginAtZero: true, title: {display:true, text:'Cantidad'} }, x: {title: {display:true, text:'Rango de Notas'}} }
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
                        title: { display: true, text: 'Esfuerzo Técnico', font: {size: 16} },
                        zoom: zoomOptions,
                        tooltip: { callbacks: { label: function(ctx) { return `Alumno \${ctx.raw.userid}: \${ctx.raw.x} ejec., \${ctx.raw.y} evals.`; } } }
                    },
                    scales: { x: { title: { display: true, text: 'Nº de Ejecuciones' } }, y: { title: { display: true, text: 'Nº de Evaluaciones' } } }
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
                    plugins: { title: { display: true, text: 'Evolución de Entregas en el Tiempo', font: {size: 16} }, zoom: zoomOptions },
                    scales: { x: { type: 'time', time: {unit: 'day'} }, y: { beginAtZero: true, title: {display:true, text:'Entregas'} } }
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
            vplArray.sort((a,b) => a.avgSort - b.avgSort);
            commonLabels = vplArray.map(v => v.name);

            chartDatasets = datasetsInfo.map((ds, i) => {
                let dataArray = vplArray.map(v => {
                    let st = vplSets[i][v.id];
                    return st && st.countGrade > 0 ? parseFloat((st.sumGrade / st.countGrade).toFixed(2)) : 0;
                });
                return {
                    label: 'Nota Media (' + ds.label + ')',
                    data: dataArray,
                    backgroundColor: ds.color
                };
            });

            currentChart = new Chart(ctx, {
                type: 'bar',
                data: { labels: commonLabels, datasets: chartDatasets },
                options: {
                    responsive: true,
                    plugins: { title: { display: true, text: 'Dificultad por Actividad', font: {size: 16} }, zoom: zoomOptions },
                    scales: { y: { beginAtZero: true, max: 10, title: {display:true, text:'Nota Media'} } }
                }
            });
        }
    }

    updateDashboard();
});
</script>
";
echo $OUTPUT->footer();
