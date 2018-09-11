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
 * Scheduled task for pruning old data from database.
 *
 * @package   local_xray
 * @author    Darko Miletic
 * @copyright Copyright (c) 2016 Blackboard Inc. (http://www.blackboard.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xray\task;

use core\task\scheduled_task;

defined('MOODLE_INTERNAL') || die();

/**
 * Class data_prune_task
 * @package local_xray
 */
class data_prune_task extends scheduled_task {
    /**
     * @return string
     */
    public function get_name() {
        return get_string('dataprune', 'local_xray');
    }

    /**
     * Prune periodicaly all exported records from temporary tables.
     */
    public function execute() {
        global $DB, $CFG;

        $itemsprune = [
            'coursecategories_delete' => 'local_xray_coursecat',
            'courseinfo_delete'       => 'local_xray_course',
            'enrolment_deletev2'      => 'local_xray_enroldel',
            'roles_delete'            => 'local_xray_roleunas',
            'threads_delete'          => 'local_xray_disc',
            'posts_delete'            => 'local_xray_post',
            'hsuthreads_delete'       => 'local_xray_hsudisc',
            'hsuposts_delete'         => 'local_xray_hsupost',
            'activity_delete'         => 'local_xray_cm',
        ];

        $todebug = (($CFG->debug == DEBUG_DEVELOPER) && $CFG->debugdisplay && !PHPUNIT_TEST);
        foreach ($itemsprune as $config => $table) {
            $lastidstore = get_config('local_xray', $config);
            if ($lastidstore > 0) {
                $DB->set_debug($todebug);
                try {
                    $DB->delete_records_select($table, 'id <= :lastid', ['lastid' => $lastidstore]);
                } catch (\Exception $e) {
                    mtrace('Error: '.$e->getMessage());
                }
                $DB->set_debug(false);
            }
        }

    }

}
