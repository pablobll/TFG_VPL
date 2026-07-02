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

echo $OUTPUT->footer();
