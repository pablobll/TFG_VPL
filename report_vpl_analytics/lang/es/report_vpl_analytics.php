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

$string['pluginname'] = 'Dashboard Analítico VPL';
$string['dashboard_title'] = 'Dashboard Analítico VPL';
$string['kpi_avg_grade'] = 'Nota Media Global';
$string['kpi_total_subs'] = 'Entregas Totales';
$string['kpi_active_users'] = 'Alumnos Activos';
$string['kpi_inactive_users'] = 'Alumnos Sin Actividad';
$string['mode_analysis'] = 'Modo de Análisis';
$string['mode_global'] = 'Análisis Global';
$string['mode_compare_groups'] = 'Comparar Grupos';
$string['mode_compare_users'] = 'Comparar Alumnos';
$string['mode_matrix'] = 'Matriz Estudiante-Actividad';
$string['search_student'] = 'Buscar alumno...';
$string['chart_type'] = 'Tipo de Visualización';
$string['chart_rendimiento'] = 'Distribución de Notas Finales';
$string['chart_evolucion'] = 'Evolución de Entregas en el Tiempo';
$string['chart_esfuerzo'] = 'Desempeño Práctico';
$string['chart_heatmap'] = 'Heatmap Temporal';
$string['filter_vpl'] = 'Actividad VPL';
$string['all_vpls'] = 'Todas las actividades';
$string['cat_graded'] = 'Actividades con nota';
$string['cat_ungraded'] = 'Actividades sin nota';
$string['cat_open'] = 'Actividades abiertas';
$string['cat_closed'] = 'Actividades cerradas';
$string['cat_group'] = 'Actividades grupales';
$string['cat_individual'] = 'Actividades individuales';
$string['filter_group'] = 'Filtrar por Grupo';
$string['all_groups'] = 'Todos los grupos';
$string['group_1'] = 'Grupo 1 (Azul)';
$string['group_2'] = 'Grupo 2 (Verde)';
$string['user_1'] = 'Alumno 1 (Azul)';
$string['user_2'] = 'Alumno 2 (Verde)';
$string['zoom_reset'] = 'Reset';
$string['scroll_indicator'] = '↓ Desliza hacia abajo dentro de la tabla para ver más alumnos';
$string['col_activity'] = 'Actividad';
$string['col_student'] = 'Alumno';
$string['col_group'] = 'Grupo';
$string['col_subs'] = 'Entregas';
$string['col_grade'] = 'Nota Final';
$string['col_first_sub'] = 'Primera Entrega';
$string['col_last_sub'] = 'Última Entrega';
$string['col_runs'] = 'Ejecuciones';
$string['col_debugs'] = 'Depuraciones';
$string['col_evals'] = 'Evals. Auto.';
$string['filter_date_from'] = 'Fecha Desde';
$string['filter_date_to'] = 'Fecha Hasta';
$string['kpi_pass_rate'] = 'Tasa Aprobado';
$string['kpi_exc_rate'] = 'Excelencia';
$string['kpi_median'] = 'Mediana';
$string['btn_settings'] = 'Configuración';
$string['settings_title'] = 'Configuración del Dashboard';
$string['settings_desc'] = 'Configura la escala de notas y los umbrales para detectar alumnos en riesgo automáticamente.';
$string['settings_stagnant'] = '[Riesgo] Estancamiento';
$string['settings_stagnant_runs'] = 'Mínimo evaluaciones:';
$string['settings_stagnant_grade'] = 'Nota máxima:';
$string['settings_proc_init'] = '[Procrastina] Empezó tarde';
$string['settings_proc_init_hours'] = 'Horas (inicio) antes del cierre:';
$string['settings_proc_final'] = '[Procrastina] Apuró entrega';
$string['settings_proc_final_hours'] = 'Horas (fin) antes del cierre:';
$string['settings_cancel'] = 'Cancelar';
$string['btn_export_csv'] = 'Exportar a CSV';
$string['settings_save'] = 'Guardar';
$string['tooltip_chart_type'] = 'Selecciona la visualización de los datos';
$string['tooltip_date_from'] = 'Filtra entregas posteriores a esta fecha';
$string['tooltip_date_to'] = 'Filtra entregas anteriores a esta fecha';
$string['tooltip_avg_grade'] = 'Nota media de todas las entregas filtradas';
$string['tooltip_pass_rate'] = 'Porcentaje de alumnos que han superado el 5.0';
$string['tooltip_exc_rate'] = 'Porcentaje de alumnos con nota excelente (>= 9.0)';
$string['tooltip_median'] = 'Nota que divide a los alumnos filtrados exactamente por la mitad';
$string['tooltip_total_subs'] = 'Número total de entregas procesadas en este filtro';
$string['tooltip_active_users'] = 'Alumnos filtrados que han realizado al menos una entrega';
$string['tooltip_inactive_users'] = 'Alumnos filtrados que no tienen ninguna entrega registrada';
$string['badge_risk'] = '[Riesgo]';
$string['badge_risk_desc'] = 'Demasiadas evaluaciones con nota baja';
$string['badge_proc_init'] = '[Proc. Inicial]';
$string['badge_proc_init_desc'] = 'Primera entrega muy cerca del cierre';
$string['badge_proc_final'] = '[Proc. Final]';
$string['badge_proc_final_desc'] = 'Última entrega muy cerca del cierre';
$string['label_student'] = 'Alumno';
$string['label_group'] = 'Grupo';
$string['label_no_group'] = 'Sin Grupo';
$string['label_no_students'] = 'No hay alumnos en esta selección.';
$string['label_no_data'] = 'Sin datos';
$string['label_subs'] = 'Entregas';
$string['label_students'] = 'Alumnos';
$string['label_avg_grade'] = 'Nota Media';
$string['label_qty'] = 'Cantidad';
$string['label_grade_range'] = 'Rango de Notas';
$string['label_execs'] = 'Nº de Ejecuciones';
$string['label_evals'] = 'Nº de Evaluaciones';
$string['label_num_subs'] = 'Nº de Entregas';
$string['settings_grade_scale'] = 'Escala de Notas';
$string['scale_base10'] = 'Base 10 (0-10)';
$string['scale_base100'] = 'Base 100 (0-100)';
$string['scale_letters'] = 'Letras (A-F)';
$string['none_selected'] = '< Sin seleccionar >';
$string['settings_global_desc'] = 'Configura aquí los umbrales de evaluación globales por defecto para el panel analítico. Puedes acceder al Dashboard interactivo desde la pestaña de "Informes" (Reports) de cualquier asignatura.';
$string['report/vpl_analytics:view'] = 'Ver dashboard de VPL Analytics';
$string['setting_pass_threshold'] = 'Umbral de Aprobado';
$string['setting_pass_threshold_desc'] = 'Nota normalizada (entre 0.0 y 1.0) a partir de la cual una actividad se considera aprobada. Por defecto: 0.5 (equivalente a un 5 sobre 10).';
$string['setting_exc_threshold'] = 'Umbral de Excelencia';
$string['setting_exc_threshold_desc'] = 'Nota normalizada (entre 0.0 y 1.0) a partir de la cual una actividad se considera excelente. Por defecto: 0.9 (equivalente a un 9 sobre 10).';
$string['warning_scales_excluded'] = 'Atención: Algunas actividades VPL utilizan escalas no numéricas (como Suspenso/Aprobado) en lugar de una puntuación máxima. Estas actividades han sido excluidas temporalmente de los cálculos estadísticos para mantener la integridad de las medias y gráficos del panel.';
$string['privacy:metadata'] = 'El plugin Dashboard Analítico VPL no almacena ningún dato personal. Toda la información mostrada se calcula al vuelo a partir de los datos existentes en Moodle.';

