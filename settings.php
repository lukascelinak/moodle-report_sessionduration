<?php

// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin administration pages are defined here.
 *
 * @package     report_sessionduration
 * @category    admin
 * @copyright   2021 Lukas Celinak <lukascelinak@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();



$ADMIN->add('reports', new admin_externalpage('report_usessduration', get_string('pluginname',
                        'report_usessduration'),
                "$CFG->wwwroot/report/usessduration/index.php",
                'report/usessduration:view'));

$settings->add(new admin_setting_configcheckbox('report_usessduration/includesuspended',
                                                get_string('includesuspended', 'report_usessduration'),
                                                get_string('includesuspendeddesc', 'report_usessduration'),
                                                '0'));

$settings->add(new admin_setting_configduration('report_usessduration/maxtimelimit', 
        get_string('maxtimelimit', 'report_usessduration'),
        get_string('maxtimelimitdesc', 'report_usessduration'), 12*3600,3600));



