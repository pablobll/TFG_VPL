<?php
require(__DIR__ . '/../../config.php');
require_login();

$context = context_system::instance();
require_capability('report/vpl_analytics:view', $context);

$url = new moodle_url('/report/vpl_analytics/index.php');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title('Dashboard Analítico VPL');
$PAGE->set_heading('Dashboard Analítico VPL');

echo $OUTPUT->header();

$dashboard_data = \report_vpl_analytics\data_manager::get_dashboard_data();
$dashboard_json = json_encode($dashboard_data);

echo '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
echo '<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>';
echo '<script src="https://cdn.jsdelivr.net/npm/hammerjs@2.0.8"></script>';
echo '<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1"></script>';

echo '<style>
    .vpl-dashboard-wrapper {
        padding: 20px;
        font-family: inherit;
    }
    .vpl-control-panel {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        display: flex;
        gap: 20px;
        align-items: flex-end;
        flex-wrap: wrap;
    }
    .vpl-control-group {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-width: 250px;
    }
    .vpl-control-group label {
        font-weight: bold;
        margin-bottom: 8px;
        color: #495057;
        font-size: 0.9em;
    }
    .vpl-control-group select {
        padding: 8px;
        border-radius: 4px;
        border: 1px solid #ced4da;
        background: #ffffff;
        color: #212529;
    }
    .vpl-canvas-container {
        background: #ffffff;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 30px;
        height: 500px;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    canvas {
        max-width: 100%;
        max-height: 100%;
    }
    .vpl-table-container {
        margin-top: 20px;
        background: #ffffff;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
        max-height: 400px;
        overflow-y: auto;
    }
    .vpl-table {
        width: 100%;
        border-collapse: collapse;
        color: #212529;
        font-size: 0.9em;
    }
    .vpl-table th {
        background: #f8f9fa;
        padding: 12px;
        text-align: left;
        font-weight: bold;
        border-bottom: 2px solid #dee2e6;
    }
    .vpl-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #e9ecef;
    }
    .vpl-table tbody tr:hover {
        background: #f1f3f5;
    }
    .badge-pass { color: #28a745; font-weight: bold; }
    .badge-fail { color: #dc3545; font-weight: bold; }
</style>';

echo '<div class="vpl-dashboard-wrapper">';

echo '<div class="vpl-control-panel">';

echo '<div class="vpl-control-group">';
echo '<label>Tipo de Visualización</label>';
echo '<select id="chartType">';
echo '<option value="rendimiento">Distribución de Rendimiento (Notas)</option>';
echo '<option value="esfuerzo">Patrón de Frustración (Intentos vs Nota)</option>';
echo '<option value="dificultad">Dificultad por Actividad (Nota Media)</option>';
echo '<option value="evolucion">Evolución Temporal (Entregas diarias)</option>';
echo '</select>';
echo '</div>';

echo '<div class="vpl-control-group">';
echo '<label>Modo de Filtro (Dividir por)</label>';
echo '<select id="filterMode">';
echo '<option value="global">Todos los datos (Global)</option>';
echo '<option value="curso">Por Curso</option>';
echo '<option value="grupo">Por Grupo</option>';
echo '<option value="alumno">Evolución Individual (Por Alumno)</option>';
echo '</select>';
echo '</div>';

echo '<div class="vpl-control-group" id="specificFilterGroup" style="display: none;">';
echo '<label id="specificFilterLabel">Seleccionar...</label>';
echo '<select id="filterSpecific"></select>';
echo '</div>';

echo '</div>'; // Fin Panel

echo '<div class="vpl-canvas-container" style="position:relative;">';
echo '<div style="position:absolute; top: 15px; right: 20px; display:flex; gap: 8px; z-index: 10;">';
echo '<button type="button" id="btnZoomIn" style="padding: 6px 12px; background: #e9ecef; color: #212529; border: 1px solid #ced4da; border-radius: 4px; cursor: pointer; font-weight:bold;">+</button>';
echo '<button type="button" id="btnZoomOut" style="padding: 6px 12px; background: #e9ecef; color: #212529; border: 1px solid #ced4da; border-radius: 4px; cursor: pointer; font-weight:bold;">-</button>';
echo '<button type="button" id="btnZoomReset" style="padding: 6px 12px; background: #e9ecef; color: #212529; border: 1px solid #ced4da; border-radius: 4px; cursor: pointer;">Reset</button>';
echo '</div>';
echo '<canvas id="mainChart"></canvas>';
echo '</div>';

echo '<div class="vpl-table-container">';
echo '<table class="vpl-table">';
echo '<thead><tr><th>Alumno</th><th>Curso</th><th>Grupo</th><th>Actividad</th><th>Nº Ejecuciones</th><th>Nota</th><th>Fecha</th></tr></thead>';
echo '<tbody id="dataTableBody"></tbody>';
echo '</table>';
echo '</div>';

echo '</div>'; // Fin Wrapper

echo "
<script>
document.addEventListener('DOMContentLoaded', function() {
    const rawData = {$dashboard_json};
    let currentChart = null;

    const zoomOptions = {
        pan: { enabled: true, mode: 'xy' },
        zoom: { wheel: { enabled: false }, pinch: { enabled: false }, mode: 'xy' }
    };

    const primaryColor = '#007bff'; // Azul clásico Bootstrap/Moodle

    const chartTypeEl = document.getElementById('chartType');
    const filterModeEl = document.getElementById('filterMode');
    const filterSpecificEl = document.getElementById('filterSpecific');
    const specificFilterGroup = document.getElementById('specificFilterGroup');
    const specificFilterLabel = document.getElementById('specificFilterLabel');
    const tableBody = document.getElementById('dataTableBody');

    document.getElementById('btnZoomIn').addEventListener('click', () => {
        if (currentChart && typeof currentChart.zoom === 'function') currentChart.zoom(1.2);
    });
    document.getElementById('btnZoomOut').addEventListener('click', () => {
        if (currentChart && typeof currentChart.zoom === 'function') currentChart.zoom(0.8);
    });
    document.getElementById('btnZoomReset').addEventListener('click', () => {
        if (currentChart && typeof currentChart.resetZoom === 'function') currentChart.resetZoom();
    });

    chartTypeEl.addEventListener('change', renderChart);
    filterModeEl.addEventListener('change', updateSpecificFilters);
    filterSpecificEl.addEventListener('change', renderChart);

    function updateSpecificFilters() {
        const mode = filterModeEl.value;
        filterSpecificEl.innerHTML = ''; 

        if (mode === 'global') {
            specificFilterGroup.style.display = 'none';
        } else {
            specificFilterGroup.style.display = 'flex';
            let options = [];
            
            if (mode === 'curso') {
                specificFilterLabel.innerText = 'Seleccionar Curso';
                options = rawData.courses;
            } else if (mode === 'grupo') {
                specificFilterLabel.innerText = 'Seleccionar Grupo';
                options = rawData.groups;
            } else if (mode === 'alumno') {
                specificFilterLabel.innerText = 'Seleccionar Alumno (ID)';
                options = rawData.users;
            }

            options.forEach(opt => {
                let el = document.createElement('option');
                el.value = opt;
                if (mode === 'curso') el.innerText = 'Curso ' + opt;
                else if (mode === 'grupo') el.innerText = opt;
                else if (mode === 'alumno') el.innerText = 'Alumno ' + opt;
                else el.innerText = opt;
                filterSpecificEl.appendChild(el);
            });
        }
        renderChart();
    }

    function renderChart() {
        if (currentChart) {
            currentChart.destroy();
        }

        const mode = filterModeEl.value;
        const specific = filterSpecificEl.value;
        const type = chartTypeEl.value;

        let filteredSubmissions = rawData.submissions;
        if (mode === 'curso') {
            filteredSubmissions = filteredSubmissions.filter(s => s.course == specific);
        } else if (mode === 'grupo') {
            filteredSubmissions = filteredSubmissions.filter(s => s.groupid == specific);
        } else if (mode === 'alumno') {
            filteredSubmissions = filteredSubmissions.filter(s => s.userid == specific);
        }

        const ctx = document.getElementById('mainChart').getContext('2d');

        if (filteredSubmissions.length === 0) {
            currentChart = new Chart(ctx, { type: 'bar', data: { labels: ['Sin datos'], datasets: [{data:[0]}] } });
            tableBody.innerHTML = '<tr><td colspan=\"7\" style=\"text-align:center\">No hay entregas registradas.</td></tr>';
            return;
        }

        tableBody.innerHTML = '';
        let sortedForTable = [...filteredSubmissions].sort((a,b) => b.datesubmitted - a.datesubmitted);
        
        sortedForTable.forEach(s => {
            let tr = document.createElement('tr');
            let dateObj = new Date(s.datesubmitted * 1000);
            let dateStr = dateObj.toLocaleDateString();
            let gradeClass = s.grade >= 5.0 ? 'badge-pass' : 'badge-fail';
            
            tr.innerHTML = `
                <td>Alumno \${s.userid}</td>
                <td>Curso \${s.course}</td>
                <td>\${s.groupid == '0' ? '-' : s.groupid}</td>
                <td>\${s.vpl_name}</td>
                <td>\${s.run_count}</td>
                <td class=\"\${gradeClass}\">\${s.grade.toFixed(2)}</td>
                <td>\${dateStr}</td>
            `;
            tableBody.appendChild(tr);
        });

        if (type === 'rendimiento') {
            let gradesDist = { 'Suspenso (<5)': 0, 'Aprobado (5-7)': 0, 'Notable (7-9)': 0, 'Sobresaliente (>9)': 0 };
            
            let userGrades = {};
            filteredSubmissions.forEach(s => {
                if(!userGrades[s.userid]) userGrades[s.userid] = {sum:0, count:0};
                userGrades[s.userid].sum += s.grade;
                userGrades[s.userid].count++;
            });

            Object.values(userGrades).forEach(ug => {
                let grade = ug.sum / ug.count;
                if (grade < 5) gradesDist['Suspenso (<5)']++;
                else if (grade < 7) gradesDist['Aprobado (5-7)']++;
                else if (grade < 9) gradesDist['Notable (7-9)']++;
                else gradesDist['Sobresaliente (>9)']++;
            });

            currentChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(gradesDist),
                    datasets: [{
                        data: Object.values(gradesDist),
                        backgroundColor: ['#dc3545', '#ffc107', '#17a2b8', '#28a745'],
                        borderWidth: 2
                    }]
                },
                options: { 
                    responsive: true, 
                    plugins: { 
                        title: { display: true, text: 'Distribución de Notas Medias', font: {size: 16, weight: 'normal'} }
                    } 
                }
            });

        } else if (type === 'esfuerzo') {
            let scatterData = filteredSubmissions.map(s => ({
                x: s.run_count + s.debug_count, 
                y: s.grade
            }));

            currentChart = new Chart(ctx, {
                type: 'scatter',
                data: {
                    datasets: [{
                        label: 'Intentos vs Nota',
                        data: scatterData,
                        backgroundColor: primaryColor,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { 
                        title: { display: true, text: 'Correlación de Esfuerzo (Ejecuciones) y Nota', font: {size: 16, weight: 'normal'} },
                        zoom: zoomOptions
                    },
                    scales: {
                        x: { title: { display: true, text: 'Nº Ejecuciones + Depuraciones' } },
                        y: { title: { display: true, text: 'Nota' }, min: 0, max: 10 }
                    }
                }
            });

        } else if (type === 'dificultad') {
            let vplGrades = {};
            filteredSubmissions.forEach(s => {
                if(!vplGrades[s.vpl_name]) vplGrades[s.vpl_name] = {sum:0, count:0};
                vplGrades[s.vpl_name].sum += s.grade;
                vplGrades[s.vpl_name].count++;
            });
            
            let labels = Object.keys(vplGrades).sort();
            let data = labels.map(l => vplGrades[l].sum / vplGrades[l].count);

            currentChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Nota Media',
                        data: data,
                        backgroundColor: primaryColor,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { 
                        title: { display: true, text: 'Dificultad Promedio por Ejercicio', font: {size: 16, weight: 'normal'} },
                        zoom: zoomOptions
                    },
                    scales: { 
                        x: { grid: {display: false} },
                        y: { min: 0, max: 10 } 
                    }
                }
            });
            
        } else if (type === 'evolucion') {
            let sortedSubs = [...filteredSubmissions].sort((a,b) => a.datesubmitted - b.datesubmitted);
            let timeData = sortedSubs.map(s => ({
                x: new Date(s.datesubmitted * 1000), 
                y: s.grade
            }));

            currentChart = new Chart(ctx, {
                type: 'line',
                data: {
                    datasets: [{
                        label: 'Evolución de Notas',
                        data: timeData,
                        borderColor: primaryColor,
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        borderWidth: 2,
                        pointBackgroundColor: primaryColor,
                        tension: 0.2,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { 
                        title: { display: true, text: 'Evolución Temporal de las Calificaciones', font: {size: 16, weight: 'normal'} },
                        zoom: zoomOptions
                    },
                    scales: {
                        x: { 
                            type: 'time', 
                            time: { unit: 'day' },
                            title: { display: true, text: 'Fecha de Entrega' }
                        },
                        y: { title: { display: true, text: 'Nota' }, min: 0, max: 10 }
                    }
                }
            });
        }
    }

    updateSpecificFilters();
});
</script>
";

echo $OUTPUT->footer();
