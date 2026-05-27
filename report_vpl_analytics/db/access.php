<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = array(
    'report/vpl_analytics:view' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    )
);
