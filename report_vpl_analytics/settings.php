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
 * @copyright  2026 Pablobll
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$settings = new admin_settingpage('reportvpl_analytics_settings', get_string('pluginname', 'report_vpl_analytics'));
$settings->add(new admin_setting_heading(
    'reportvpl_analytics_heading', 
    '', 
    get_string('settings_global_desc', 'report_vpl_analytics')
));
$settings->add(new admin_setting_configtext(
    'report_vpl_analytics/pass_threshold',
    get_string('setting_pass_threshold', 'report_vpl_analytics'),
    get_string('setting_pass_threshold_desc', 'report_vpl_analytics'),
    '0.5',
    PARAM_FLOAT
));

$settings->add(new admin_setting_configtext(
    'report_vpl_analytics/exc_threshold',
    get_string('setting_exc_threshold', 'report_vpl_analytics'),
    get_string('setting_exc_threshold_desc', 'report_vpl_analytics'),
    '0.9',
    PARAM_FLOAT
));
