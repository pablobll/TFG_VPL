<?php
defined('MOODLE_INTERNAL') || die;


function report_vpl_analytics_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('report/vpl_analytics:view', $context)) {
        $url = new moodle_url('/report/vpl_analytics/index.php', array('id' => $course->id));
        $navigation->add(
            get_string('pluginname', 'report_vpl_analytics'),
            $url,
            navigation_node::TYPE_SETTING,
            null,
            null,
            new pix_icon('i/report', '')
        );
    }
}
