<?php
defined('MOODLE_INTERNAL') || die;

$settings = new admin_settingpage('reportvpl_analytics_settings', get_string('pluginname', 'report_vpl_analytics'));
$settings->add(new admin_setting_heading(
    'reportvpl_analytics_heading', 
    '', 
    get_string('settings_global_desc', 'report_vpl_analytics')
));
