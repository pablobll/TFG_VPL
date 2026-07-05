<?php
defined('MOODLE_INTERNAL') || die;

$ADMIN->add('reports', new admin_externalpage(
    'reportvpl_analytics',
    get_string('pluginname', 'report_vpl_analytics'),
    new moodle_url('/report/vpl_analytics/index.php'),
    'report/vpl_analytics:view'
));

$settings = new admin_settingpage('reportvpl_analytics_settings', get_string('pluginname', 'report_vpl_analytics'));
$settings->add(new admin_setting_heading('reportvpl_analytics_heading', '', 'Este plugin no requiere configuración técnica. Por favor, navega a la pestaña de "Informes" (Reports) en la Administración del sitio para acceder al Dashboard interactivo.'));
