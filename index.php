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
 * The report Users sessions duration.
 *
 * @package     report_usessduration
 * @category    admin
 * @copyright   2021 Lukas Celinak <lukascelinak@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();
$context = context_system::instance();
require_capability('report/usessduration:view', $context);

$download = optional_param('download', '', PARAM_ALPHA);

// Paging params for paging bars.
$page = optional_param('page', 0, PARAM_INT); // Which page to show.
$pagesize = optional_param('perpage', 25, PARAM_INT); // How many per page.

$url = new moodle_url('/report/usessduration/index.php');
$PAGE->set_url($url);
$PAGE->set_context($context);
admin_externalpage_setup('report_usessduration', '', null, '', array('pagelayout' => 'report'));
$PAGE->set_title(get_string('pluginname', 'report_usessduration'));
$PAGE->set_heading(get_string('pluginname', 'report_usessduration'));

$mtable = new \report_usessduration\table\usessduration_table('reportusessdurationtable');
$mtable->is_downloading($download, time(), 'usessdurationexport');
$mtable->define_baseurl($url);
if (!$mtable->is_downloading()) {
   echo $OUTPUT->header();
   echo $OUTPUT->heading(get_string('pluginname', 'report_usessduration'));
}
ob_start();
$mtable->out($pagesize, false);
$mtablehtml = ob_get_contents();
ob_end_clean();

if (!$mtable->is_downloading()) {
    echo html_writer::tag(
    'p',
    get_string('userstotal', 'report_usessduration', $mtable->totalrows),
    [
        'data-region' => 'reportusessdurationtable-count',
    ]
);
}

if (!$mtable->is_downloading()) {
    echo $mtablehtml;
}

if (!$mtable->is_downloading()) {
    echo $OUTPUT->footer();
}