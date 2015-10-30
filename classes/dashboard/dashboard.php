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

namespace local_xray\dashboard;
defined('MOODLE_INTERNAL') || die();
use local_xray\event\get_report_failed;

/**
 * Communication with xray to get data for dashboard
 *
 * @package local_xray
 * @author Pablo Pagnone
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dashboard {

    /**
     * Connect with xray webservice and get data for dashboard.
     * @param $courseid
     * @return bool|dashboard_data
     */
    public static function get($courseid) {

        $result = "";

        try {

            $report = "dashboard";
            $response = \local_xray\local\api\wsapi::course($courseid, $report);

            if (!$response) {
                // Fail response of webservice.
                \local_xray\local\api\xrayws::instance()->print_error();

            } else {

                // Get users in risk.
                $usersinrisk = array();

                if (isset($response->elements->element3->data) && !empty($response->elements->element3->data)) {
                    foreach ($response->elements->element3->data as $key => $obj) {
                        if ($obj->severity->value == "high") {
                            $usersinrisk[] = $obj->participantId->value;
                        }
                    }
                }
                // Student ins risk.
                $countstudentsrisk = $response->elements->element6->items[5]->value;
                // Students enrolled.
                $countstudentsenrolled = $response->elements->element6->items[2]->value;
                // Visits last 7 days.
                $countstudentsvisitslastsevendays = $response->elements->element6->items[0]->value;
                // Risk previous 7 days.
                $countstudentsriskprev = $response->elements->element6->items[6]->value;
                // Visits previous 7 days.
                $countstudentsvisitsprev = $response->elements->element6->items[1]->value;

                // Calculate percentajes from last weeks.
                $precentajevalueperstudent = 100 / $countstudentsenrolled;

                // Diff risk.
                $percentajestudentsriskprev = $precentajevalueperstudent * $countstudentsriskprev;
                $percentajestudentsrisk = $precentajevalueperstudent * $countstudentsrisk;
                $diffrisk = round($percentajestudentsrisk - $percentajestudentsriskprev);

                // Diff visits.
                $percentajestudentsvisitsprev = $precentajevalueperstudent * $countstudentsvisitsprev;
                $percentajestudentsvisitslastsevendays = $precentajevalueperstudent * $countstudentsvisitslastsevendays;
                $diffvisits = round($percentajestudentsvisitslastsevendays - $percentajestudentsvisitsprev);

                // Students visits by week day.
                $studentsvisitsbyweekday = $response->elements->activity_level->data;

                $result = new dashboarddata($usersinrisk, $countstudentsenrolled, $countstudentsrisk,
                    $countstudentsvisitslastsevendays, $diffrisk, $diffvisits, $studentsvisitsbyweekday);

            }
        } catch (\moodle_exception $e) {
            get_report_failed::create_from_exception($e, \context_course::instance($courseid), "dashboard")->trigger();
            $result = false;
        }

        return $result;
    }

}
