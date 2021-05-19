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
 * Report for users sessions and session atributes.
 *
 * @package     report_sessionsduration
 * @category    admin
 * @copyright   2021 Lukas Celinak <lukascelinak@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();
$context = context_system::instance();
require_capability('report/sessionsduration:view', $context);

$download = optional_param('download', '', PARAM_ALPHA);

// Paging params for paging bars.
$page = optional_param('page', 0, PARAM_INT); // Which page to show.
$pagesize = optional_param('perpage', 25, PARAM_INT); // How many per page.

$url = new moodle_url('/report/sessionsduration/index.php');
$PAGE->set_url($url);
$PAGE->set_context($context);
admin_externalpage_setup('reportsessionsduration', '', null, '', array('pagelayout' => 'report'));
$PAGE->set_title(get_string('pluginname', 'report_sessionsduration'));
$PAGE->set_heading(get_string('pluginname', 'report_sessionsduration'));

$mtable = new \report_sessionsduration\table\sessionsduration_table('reportsessionsduration');
$mtable->is_downloading($download, time(), 'sessionsdurationexport');
$mtable->define_baseurl($url);
if (!$mtable->is_downloading()) {
   echo $OUTPUT->header();
}
$mtable->out($pagesize, false);

if (!$mtable->is_downloading()) {
    echo $OUTPUT->footer();
}