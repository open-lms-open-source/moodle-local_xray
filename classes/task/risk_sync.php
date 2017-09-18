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
 * Scheduled task for check if risk is disable on X-Ray side..
 *
 * @package   local_xray
 * @author    German Vitale
 * @copyright Copyright (c) 2017 Blackboard Inc.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xray\task;

use core\task\scheduled_task;
use local_xray\local\api\course_manager;
use local_xray\event\risk_sync_log;
use local_xray\event\risk_sync_failed;

defined('MOODLE_INTERNAL') || die();

/**
 * Class risk_sync - implementation of the task.
 *
 * @package   local_xray
 * @copyright Copyright (c) 2017 Blackboard Inc.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class risk_sync extends scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('risksync', 'local_xray');
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        global $DB, $CFG;

        try {
            require_once($CFG->dirroot.'/local/xray/locallib.php');

            $xrayriskdisabled = course_manager::is_risk_disabled();
            $riskdisabled = local_xray_risk_disabled();

            if ($xrayriskdisabled && !$riskdisabled) {
                set_config('riskdisabled', 1, 'local_xray');
            } else if (!$xrayriskdisabled && $riskdisabled) {
                set_config('riskdisabled', 0, 'local_xray');
            }
            $event = risk_sync_log::create();
            $event->trigger();

        } catch (\Exception $e) {
            if ($DB->get_debug()) {
                $DB->set_debug(false);
            }
            mtrace($e->getMessage());
            mtrace($e->getTraceAsString());
            risk_sync_failed::create_from_exception($e)->trigger();
        }
    }
}