<?php
defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $ADMIN->add('reports', new admin_externalpage(
        'reportvplanalytics',
        get_string('pluginname', 'report_vpl_analytics'),
        new moodle_url('/report/vpl_analytics/index.php'),
        'report/vpl_analytics:view'
    ));
}
