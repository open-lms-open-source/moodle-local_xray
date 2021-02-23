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
 * Scheduled task for sync courses with X-Ray.
 *
 * @package   local_xray
 * @author    German Vitale
 * @copyright Copyright (c) 2017 Open LMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xray\task;

use core\task\scheduled_task;
use local_xray\local\api\course_manager;
use local_xray\event\course_sync_log;
use local_xray\event\course_sync_failed;

defined('MOODLE_INTERNAL') || die();

/**
 * Class course_sync - implementation of the task.
 *
 * @package   local_xray
 * @copyright Copyright (c) 2017 Open LMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_sync extends scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('coursesync', 'local_xray');
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        global $DB;

        try {
            // Get selected courses in moodle side.
            $cids = course_manager::list_selected_course_ids();
            // Get selected courses in X-Ray side.
            $xraycids = course_manager::load_course_ids_from_xray();
            // Delete extra courses that are not present in X-Ray.
            if ($deletecids = array_diff($cids, $xraycids)) {
                $DB->delete_records_list('local_xray_selectedcourse', 'cid', $deletecids);
            }
            // Add X-Ray courses that are not present in moodle.
            if ($addcids = array_diff($xraycids, $cids)) {
                // Create array of new records.
                $xraycidobjects = array();
                $uniquexraycids = array_unique($addcids);
                foreach ($uniquexraycids as $xraycid) {
                    $xraycidobject = new \stdClass();
                    $xraycidobject->cid = $xraycid;
                    $xraycidobjects[] = $xraycidobject;
                }
                // Save all records.
                $DB->insert_records('local_xray_selectedcourse', $xraycidobjects);
            }
            $event = course_sync_log::create();
            $event->trigger();

        } catch (\Exception $e) {
            if ($DB->get_debug()) {
                $DB->set_debug(false);
            }
            mtrace($e->getMessage());
            mtrace($e->getTraceAsString());
            course_sync_failed::create_from_exception($e)->trigger();
        }
    }
}