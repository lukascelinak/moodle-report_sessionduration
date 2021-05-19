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
 * The table class.
 *
 * @package     report_sessionsduration
 * @copyright   2021 Lukas Celinak <lukascelinak@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
declare(strict_types=1);

namespace report_sessionsduration\table;

use moodle_url;

defined('MOODLE_INTERNAL') || die;

global $CFG;

require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot . '/user/lib.php');

/**
 * Class for the displaying the table.
 *
 * @package     report_sessionsduration
 * @copyright   2021 Lukas Celinak <lukascelinak@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sessionsduration_table extends \table_sql {

    /**
     * Render the table.
     *
     * @param int $pagesize Size of page for paginated displayed table.
     * @param bool $useinitialsbar Whether to use the initials bar which will only be used if there is a fullname column defined.
     * @param string $downloadhelpbutton
     */
    public function out($pagesize, $useinitialsbar, $downloadhelpbutton = '') {
        global $CFG, $DB, $USER, $OUTPUT, $PAGE;
        $this->downloadable = true;
        $this->set_attribute('class', 'table-bordered');
        // Define the headers and columns.
        $headers = [get_string('fullname'),
            get_string('email'),
            get_string('successfullogins', 'report_sessionsduration'),
            get_string('starttime', 'report_sessionsduration'),
            get_string('endtime', 'report_sessionsduration'),
            get_string('duration', 'report_sessionsduration'),
            get_string('totalduration', 'report_sessionsduration')];

        $columns = ['fullname',
            'email',
            'successfullogins',
            'currentlogin',
            'lastaccess',
            'duration',
            'totalduration'];

        $extrafields = ['successfullogins',
            'currentlogin',
            'lastaccess',
            'duration',
            'totalduration'];

        $this->define_columns($columns);
        $this->define_headers($headers);
        $this->no_sorting('totalduration');
        // The name column is a header.
        $this->define_header_column('fullname');

        // Make this table sorted by last name by default.
        $this->sortable(true, 'lastname');
        $this->extrafields = $extrafields;
        $this->set_attribute('id', 'sessionsduration');
        parent::out($pagesize, $useinitialsbar, $downloadhelpbutton);
    }

    /**
     * Generate the fullname column.
     *
     * @param \stdClass $data
     * @return string
     */
    public function col_email($data) {
        global $OUTPUT;

        $userurl = new moodle_url('/user/view.php', array('id' => $data->id));

        if ($this->is_downloading()) {
            return $data->email;
        } else {
            return '<a href="mailto:">' . $data->email . '</a>';
        }
    }

    /**
     * Generate the email column.
     *
     * @param \stdClass $data
     * @return string
     */
    public function col_fullname($data) {
        global $OUTPUT;

        $userurl = new moodle_url('/user/view.php', array('id' => $data->id));

        if ($this->is_downloading()) {
            return fullname($data);
        } else {
            return '<a href="' . $userurl->out() . '">' . $OUTPUT->user_picture($data, array('size' => 35, 'includefullname' => true, 'link' => false)) . '</a>';
        }
    }

    /**
     * Generate endtime last session column.
     *
     * @param \stdClass $data
     * @return string
     */
    public function col_lastaccess($data) {
        return userdate($data->lastaccess) . "/" . $data->lastaccess;
    }

    /**
     * Generate the starttime last session column.
     *
     * @param \stdClass $data
     * @return string
     */
    public function col_currentlogin($data) {
        return userdate($data->currentlogin) . "/" . $data->currentlogin;
    }

    /**
     * Generate total duration of all sessions column.
     *
     * @param \stdClass $data
     * @return string
     */
    public function col_successfullogins($data) {
        return $data->successfullogins;
    }

    /**
     * Generate total duration of all sessions column.
     *
     * @param \stdClass $data
     * @return string
     */
    public function col_totalduration($data) {
        global $DB;
        $totalduration = 0;
        $previous = 0;
        $loggedinrows = $DB->get_records('logstore_standard_log', array('userid' => $data->id), "timecreated DESC");
        foreach ($loggedinrows as $loggedin) {
            if ($previous == 0) {
                $sql = "SELECT * FROM {logstore_standard_log} WHERE userid=:userid AND id >:logedinid ORDER BY timecreated ASC LIMIT 1";
                $params = ['logedinid' => $loggedin->id, 'userid' => $data->id];
            } else {
                $sql = "SELECT * FROM {logstore_standard_log} WHERE userid=:userid AND id >:logedinid AND id < :previous ORDER BY timecreated ASC LIMIT 1";
                $params = ['logedinid' => $loggedin->id, 'userid' => $data->id, 'previous' => $previous];
            }

            if ($endsession = $DB->get_record_sql($sql, $params)) {
                $previous = $loggedin->id;
                if (($endsession->timecreated - $loggedin->timecreated) < 10800 && ($endsession->timecreated - $loggedin->timecreated) > 60) {
                    $totalduration += ($endsession->timecreated - $loggedin->timecreated);
                }
            }
        }
        return sprintf('%02dh %02dm', ($totalduration / 3600), ($totalduration / 60 % 60), $totalduration % 60);
    }

    /**
     * This function is used for the extra user fields.
     *
     * These are being dynamically added to the table so there are no functions 'col_<userfieldname>' as
     * the list has the potential to increase in the future and we don't want to have to remember to add
     * a new method to this class. We also don't want to pollute this class with unnecessary methods.
     *
     * @param string $colname The column name
     * @param \stdClass $data
     * @return string
     */
    public function other_cols($colname, $data) {
        // Do not process if it is not a part of the extra fields.
        if (!in_array($colname, $this->extrafields)) {
            return '';
        }

        return $data->{$colname};
    }

    /**
     * Query the database for results to display in the table.
     *
     * @param int $pagesize size of page for paginated displayed table.
     * @param bool $useinitialsbar do you want to use the initials bar.
     */
    public function query_db($pagesize, $useinitialsbar = true) {
        global $DB;

        $total = $DB->count_records('user');

        if ($this->is_downloading()) {
            $this->pagesize($total, $total);
        } else {
            $this->pagesize($pagesize, $total);
        }

        $rawdata = $this->get_users($this->get_sql_sort(), $this->get_page_start(), $this->get_page_size());

        $this->rawdata = [];
        foreach ($rawdata as $user) {
            $this->rawdata[$user->id] = $user;
        }

        // Set initial bars.
        if ($useinitialsbar) {
            $this->initialbars(true);
        }
    }

    /**
     * Override the table show_hide_link to not show for select column.
     *
     * @param string $column the column name, index into various names.
     * @param int $index numerical index of the column.
     * @return string HTML fragment.
     */
    protected function show_hide_link($column, $index) {
        return '';
    }

    /**
     * Guess the base url for the participants table.
     */
    public function guess_base_url(): void {
        $this->baseurl = new moodle_url('/report/sessionsduration/index.php');
    }

    public function get_users($sort, $start, $size) {
        global $DB;
        $sql = "SELECT u.*,TIME_FORMAT(SEC_TO_TIME(u.lastaccess-u.currentlogin),'%H:%i') as  duration,"
                . "(SELECT COUNT(sl.id) FROM {logstore_standard_log} sl WHERE sl.userid =u.id  AND action LIKE \"loggedin\") as successfullogins "
                . "FROM {user} u "
                . "WHERE "
                . "u.suspended=:suspended AND u.auth LIKE \"ldap\" ORDER BY {$sort}"; //auth atr only for testing on my big db

        $params = ['suspended' => 0];
        return $DB->get_records_sql($sql, $params, $start, $size);
    }

}
