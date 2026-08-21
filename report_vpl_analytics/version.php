<?php
defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2026052701;
$plugin->requires  = 2023100900;
$plugin->component = 'report_vpl_analytics';
$plugin->dependencies = [
    'mod_vpl' => 2023050100
];
$plugin->maturity  = MATURITY_ALPHA;
$plugin->release   = '0.1';
