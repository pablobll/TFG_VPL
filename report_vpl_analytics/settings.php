<?php
defined('MOODLE_INTERNAL') || die;

$settings = new admin_settingpage('reportvpl_analytics_settings', get_string('pluginname', 'report_vpl_analytics'));
$settings->add(new admin_setting_heading(
    'reportvpl_analytics_heading', 
    '', 
    'Este plugin no requiere configuración técnica global. Por favor, navega a la pestaña de "Informes" (Reports) dentro de cualquier Asignatura para acceder al Dashboard interactivo.'
));
