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

                // Default value. This is because webservice when is 0 return "NA" string or not return value.(depend case).
                $usersinrisklastsevendays = "-";
                $usersinrisklastsevendays_previousweek= "-";
                $studentsloggedlastsevendays= "-";
                $studentsloggedlastsevendays_previousweek= "-";
                $postslastsevendays= "-";
                $postslastsevendays_previousweek= "-";
                $averagegradeslastsevendays= "-";
                $averagegradeslastsevendays_previousweek= "-";
                $totalstudents = "-";

                // Get users in risk last 7 days.
                if(isset($response->elements->element3->items[5]->value) && is_number($response->elements->element3->items[5]->value)) {
                    $usersinrisklastsevendays = $response->elements->element3->items[5]->value;
                }

                // Risk previous 7 days.
                if(isset($response->elements->element3->items[6]->value) && is_number($response->elements->element3->items[6]->value)) {
                    $usersinrisklastsevendays_previousweek = $response->elements->element3->items[6]->value;
                }

                // Posts last 7 days.
                if(isset($response->elements->element3->items[7]->value) && is_number($response->elements->element3->items[7]->value)) {
                    $postslastsevendays = $response->elements->element3->items[7]->value;
                }

                // Posts previous 7 days.
                if(isset($response->elements->element3->items[8]->value) && is_number($response->elements->element3->items[8]->value)) {
                    $postslastsevendays_previousweek = $response->elements->element3->items[8]->value;
                }

                // Visits last 7 days.
                if(isset($response->elements->element3->items[0]->value) && is_number($response->elements->element3->items[0]->value)) {
                    $studentsloggedlastsevendays = $response->elements->element3->items[0]->value;
                }

                // Visits previous 7 days.
                if(isset($response->elements->element3->items[1]->value) && is_number($response->elements->element3->items[1]->value)) {
                    $studentsloggedlastsevendays_previousweek = $response->elements->element3->items[1]->value;
                }

                // Average grades last 7 days.
                if(isset($response->elements->element3->items[9]->value) && is_number($response->elements->element3->items[9]->value)) {
                    $averagegradeslastsevendays = $response->elements->element3->items[9]->value;
                }

                // Average grades previous 7 days.
                if(isset($response->elements->element3->items[10]->value) && is_number($response->elements->element3->items[10]->value)) {
                    $averagegradeslastsevendays_previousweek = $response->elements->element3->items[10]->value;
                }

                // Total of students enrolled actives.
                if(isset($response->elements->element3->items[2]->value) && is_number($response->elements->element3->items[2]->value)) {
                    $totalstudents = $response->elements->element3->items[2]->value;
                }

                // Return dashboard_data object.
                $result = new dashboard_data($usersinrisklastsevendays,
                    $usersinrisklastsevendays_previousweek,
                    $studentsloggedlastsevendays,
                    $studentsloggedlastsevendays_previousweek,
                    $postslastsevendays,
                    $postslastsevendays_previousweek,
                    $averagegradeslastsevendays,
                    $averagegradeslastsevendays_previousweek,
                    $totalstudents);

            }
        } catch (\moodle_exception $e) {
            get_report_failed::create_from_exception($e, \context_course::instance($courseid), "dashboard")->trigger();
            $result = false;
        }

        return $result;
    }

}
