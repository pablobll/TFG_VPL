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
    .vpl-table-container { margin-top: 20px; background: #ffffff; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; max-height: 500px; overflow-y: auto; }
    .vpl-table { width: 100%; border-collapse: collapse; color: #212529; font-size: 0.9em; }
    .vpl-table th { background: #f8f9fa; padding: 12px; text-align: left; font-weight: bold; border-bottom: 2px solid #dee2e6; position: sticky; top: 0; }
    .vpl-table td { padding: 10px 12px; border-bottom: 1px solid #e9ecef; }
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

echo '<div class="vpl-control-panel">';

echo '<div class="vpl-control-group">';
echo '<label>Tipo de Visualización</label>';
echo '<select id="chartType">';
echo '<option value="rendimiento">Distribución de Notas Finales</option>';
echo '<option value="evolucion">Evolución de Entregas en el Tiempo</option>';
echo '<option value="esfuerzo">Esfuerzo (Ejecuciones vs Evaluaciones)</option>';
echo '<option value="dificultad">Dificultad por Actividad</option>';
echo '</select>';
echo '</div>';

echo '<div class="vpl-control-group">';
echo '<label>Filtrar por Grupo</label>';
echo '<select id="filterGroup">';
echo '<option value="all">Todos los grupos</option>';
echo '</select>';
echo '</div>';

echo '<div class="vpl-control-group">';
echo '<label>Filtrar por Actividad VPL</label>';
echo '<select id="filterVpl">';
echo '<option value="all">Todas las actividades</option>';
echo '</select>';
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

echo '<div class="vpl-table-container">';
echo '<table class="vpl-table">';
echo '<thead><tr><th>Alumno (ID)</th><th>Grupo</th><th>Entregas</th><th>Nota Máx.</th><th>Primera Entrega</th><th>Última Entrega</th><th>Ejecuciones</th><th>Evals. Auto.</th></tr></thead>';
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

    const chartTypeEl = document.getElementById('chartType');
    const filterGroupEl = document.getElementById('filterGroup');
    const filterVplEl = document.getElementById('filterVpl');
    
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
    rawData.groups.forEach(g => {
        groupMap[g.id] = g.name;
        let opt = document.createElement('option');
        opt.value = g.id; opt.innerText = g.name;
        filterGroupEl.appendChild(opt);
    });
    
    let uniqueVpls = {};
    rawData.submissions.forEach(s => { uniqueVpls[s.vpl] = s.vpl_name; });
    Object.keys(uniqueVpls).forEach(vplId => {
        let opt = document.createElement('option');
        opt.value = vplId; opt.innerText = uniqueVpls[vplId];
        filterVplEl.appendChild(opt);
    });

    chartTypeEl.addEventListener('change', updateDashboard);
    filterGroupEl.addEventListener('change', updateDashboard);
    filterVplEl.addEventListener('change', updateDashboard);

    function updateDashboard() {
        const type = chartTypeEl.value;
        const groupId = filterGroupEl.value;
        const vplId = filterVplEl.value;

        let filtered = rawData.submissions;
        if (groupId !== 'all') {
            const gid = parseInt(groupId);
            filtered = filtered.filter(s => s.user_groups && s.user_groups.includes(gid));
        }
        if (vplId !== 'all') filtered = filtered.filter(s => s.vpl == vplId);

        updateKPIs(filtered);
        updateTable(filtered);
        renderChart(type, filtered);
    }

    function updateKPIs(subs) {
        if (subs.length === 0) {
            document.getElementById('kpiAvgGrade').innerText = '0.00';
            document.getElementById('kpiTotalSubs').innerText = '0';
            document.getElementById('kpiActiveUsers').innerText = '0';
            document.getElementById('kpiInactiveUsers').innerText = rawData.total_students || 0;
            return;
        }

        let sumGrades = 0, countGrades = 0;
        let activeUsers = new Set();
        subs.forEach(s => {
            if (s.grade !== null) { sumGrades += s.grade; countGrades++; }
            activeUsers.add(s.userid);
        });

        const avgGrade = countGrades > 0 ? (sumGrades / countGrades).toFixed(2) : '0.00';
        const totalSubs = subs.length;
        const activeCount = activeUsers.size;
        const inactiveCount = Math.max(0, (rawData.total_students || 0) - activeCount);

        document.getElementById('kpiAvgGrade').innerText = avgGrade;
        document.getElementById('kpiTotalSubs').innerText = totalSubs;
        document.getElementById('kpiActiveUsers').innerText = activeCount + (rawData.total_students ? ' / ' + rawData.total_students : '');
        document.getElementById('kpiInactiveUsers').innerText = inactiveCount;
    }

    function updateTable(subs) {
        const tbody = document.getElementById('dataTableBody');
        tbody.innerHTML = '';
        if (subs.length === 0) {
            tbody.innerHTML = '<tr><td colspan=\"8\" style=\"text-align:center\">No hay datos para los filtros seleccionados.</td></tr>';
            return;
        }

        let studentStats = {};
        subs.forEach(s => {
            if(!studentStats[s.userid]) {
                let gNames = (s.user_groups || []).map(gid => groupMap[gid] || gid).join(', ');
                if (!gNames) gNames = 'Sin Grupo';
                
                studentStats[s.userid] = {
                    group: gNames, subs: 0, maxGrade: 0, 
                    firstSub: s.datesubmitted, lastSub: s.datesubmitted,
                    runs: 0, evals: 0
                };
            }
            let st = studentStats[s.userid];
            st.subs++;
            if(s.grade !== null && s.grade > st.maxGrade) st.maxGrade = s.grade;
            if(s.datesubmitted < st.firstSub) st.firstSub = s.datesubmitted;
            if(s.datesubmitted > st.lastSub) st.lastSub = s.datesubmitted;
            st.runs += s.run_count;
            st.evals += s.nevaluations;
        });

        let sortedUsers = Object.keys(studentStats).sort((a,b) => a - b);
        sortedUsers.forEach(uid => {
            let st = studentStats[uid];
            let dFirst = new Date(st.firstSub * 1000).toLocaleDateString();
            let dLast = new Date(st.lastSub * 1000).toLocaleDateString();
            
            let tr = document.createElement('tr');
            tr.innerHTML = `
                <td>\${uid}</td>
                <td>\${st.group}</td>
                <td>\${st.subs}</td>
                <td>\${st.maxGrade.toFixed(2)}</td>
                <td>\${dFirst}</td>
                <td>\${dLast}</td>
                <td>\${st.runs}</td>
                <td>\${st.evals}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    function renderChart(type, subs) {
        if (currentChart) currentChart.destroy();
        const ctx = document.getElementById('mainChart').getContext('2d');

        if (subs.length === 0) {
            currentChart = new Chart(ctx, { type: 'bar', data: { labels: ['Sin datos'], datasets: [{data:[0]}] }});
            document.getElementById('zoomControls').style.display = 'none';
            return;
        }

        let zoomControls = document.getElementById('zoomControls');
        if (type === 'esfuerzo' || type === 'evolucion') {
            zoomControls.style.display = 'flex';
        } else {
            zoomControls.style.display = 'none';
        }

        if (type === 'rendimiento') {
            let ranges = {'0-2':0, '2-4':0, '4-5':0, '5-7':0, '7-9':0, '9-10':0};
            subs.forEach(s => {
                if(s.grade === null) return;
                let g = s.grade;
                if(g<2) ranges['0-2']++;
                else if(g<4) ranges['2-4']++;
                else if(g<5) ranges['4-5']++;
                else if(g<7) ranges['5-7']++;
                else if(g<9) ranges['7-9']++;
                else ranges['9-10']++;
            });

            currentChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: Object.keys(ranges),
                    datasets: [{
                        label: 'Nº de Entregas',
                        data: Object.values(ranges),
                        backgroundColor: primaryColor
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { title: { display: true, text: 'Distribución de Notas', font: {size: 16} } },
                    scales: { y: { beginAtZero: true, title: {display:true, text:'Cantidad'} }, x: {title: {display:true, text:'Rango de Notas'}} }
                }
            });

        } else if (type === 'esfuerzo') {
            let scatterData = subs.map(s => ({
                x: s.run_count, y: s.nevaluations, 
                userid: s.userid, vpl_name: s.vpl_name
            }));
            
            currentChart = new Chart(ctx, {
                type: 'scatter',
                data: {
                    datasets: [{
                        label: 'Ejecuciones vs Evals. Automáticas',
                        data: scatterData,
                        backgroundColor: 'rgba(0, 123, 255, 0.6)',
                        pointRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { 
                        title: { display: true, text: 'Esfuerzo Técnico', font: {size: 16} },
                        zoom: zoomOptions,
                        tooltip: {
                            callbacks: {
                                label: function(ctx) { return `Alumno \${ctx.raw.userid}: \${ctx.raw.x} ejecuciones, \${ctx.raw.y} evaluaciones`; }
                            }
                        }
                    },
                    scales: {
                        x: { title: { display: true, text: 'Nº de Ejecuciones' } },
                        y: { title: { display: true, text: 'Nº de Evaluaciones' } }
                    }
                }
            });

        } else if (type === 'evolucion') {
            let dateCounts = {};
            subs.forEach(s => {
                let d = new Date(s.datesubmitted * 1000).toISOString().split('T')[0];
                dateCounts[d] = (dateCounts[d] || 0) + 1;
            });
            let sortedDates = Object.keys(dateCounts).sort();
            let dataPoints = sortedDates.map(d => ({ x: d, y: dateCounts[d] }));

            currentChart = new Chart(ctx, {
                type: 'line',
                data: {
                    datasets: [{
                        label: 'Entregas por Día',
                        data: dataPoints,
                        borderColor: primaryColor,
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        fill: true, tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { 
                        title: { display: true, text: 'Evolución de Entregas en el Tiempo', font: {size: 16} },
                        zoom: zoomOptions
                    },
                    scales: { x: { type: 'time', time: {unit: 'day'} }, y: { beginAtZero: true, title: {display:true, text:'Entregas'} } }
                }
            });
        } else if (type === 'dificultad') {
            let vplStats = {};
            subs.forEach(s => {
                if (!vplStats[s.vpl]) {
                    vplStats[s.vpl] = { name: s.vpl_name, sumGrade: 0, countGrade: 0 };
                }
                if (s.grade !== null) {
                    vplStats[s.vpl].sumGrade += s.grade;
                    vplStats[s.vpl].countGrade++;
                }
            });

            let vplArray = Object.values(vplStats).map(st => ({
                name: st.name,
                avgGrade: st.countGrade > 0 ? parseFloat((st.sumGrade / st.countGrade).toFixed(2)) : 0
            }));
            
            vplArray.sort((a, b) => a.avgGrade - b.avgGrade);

            currentChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: vplArray.map(st => st.name),
                    datasets: [{
                        label: 'Nota Media (Menos = Más difícil)',
                        data: vplArray.map(st => st.avgGrade),
                        backgroundColor: primaryColor
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { 
                        title: { display: true, text: 'Dificultad por Actividad', font: {size: 16} },
                        zoom: zoomOptions
                    },
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
