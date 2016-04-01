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
                $usersinrisk = "-";
                $risktotal = "-";
                $averagerisksevendaybefore = "-";
                $maximumtotalrisksevendaybefore = "-";
                $usersloggedinpreviousweek = "-";
                $usersactivitytotal = "-";
                $averageuserslastsevendays = "-";
                $userstotalprevioussevendays = "-";
                $postslastsevendays = "-";
                $postslastsevendayspreviousweek = "-";
                $averagegradeslastsevendays = "-";
                $averagegradeslastsevendayspreviousweek = "-";

                // Is the number of persons in the risk metrics table, which are in the total risk (Headline Risk).
                if (isset($response->elements->element3->items[5]->value) &&
                    is_number($response->elements->element3->items[5]->value)) {
                    $usersinrisk = $response->elements->element3->items[5]->value;
                }

                // Is the total number of persons in the risk metrics table (Headline Risk).
                if (isset($response->elements->element3->items[2]->value) &&
                    is_number($response->elements->element3->items[2]->value)) {
                    $risktotal = $response->elements->element3->items[2]->value;
                }

                // Is the average of the number of persons at risk during the seven days before (Headline Risk).
                if (isset($response->elements->element3->items[6]->value) &&
                    is_number($response->elements->element3->items[6]->value)) {
                    $averagerisksevendaybefore = $response->elements->element3->items[6]->value;
                }

                // Maximum of the total number of persons in the risk metrics table seven days before (Headline Risk).
                if (isset($response->elements->element3->items[3]->value) &&
                    is_number($response->elements->element3->items[3]->value)) {
                    $maximumtotalrisksevendaybefore = $response->elements->element3->items[3]->value;
                }

                // Is the number of 'yes' in the activity metrics table (Headline Activity).
                if (isset($response->elements->element3->items[0]->value) &&
                    is_number($response->elements->element3->items[0]->value)) {
                    $usersinrisk = $response->elements->element3->items[0]->value;
                }

                // Is the maximum of the total number of persons in the activity metrics (Headline Activity).
                if (isset($response->elements->element3->items[4]->value) &&
                    is_number($response->elements->element3->items[4]->value)) {
                    $risktotal = $response->elements->element3->items[4]->value;
                }

                // Is the number of 'yes' in the activity metrics table (Headline Activity).
                if (isset($response->elements->element3->items[1]->value) &&
                    is_number($response->elements->element3->items[1]->value)) {
                    $averagerisksevendaybefore = $response->elements->element3->items[2]->value;
                }

                // Is the maximum of the total number of persons in the activity metrics(Headline Activity).
                if (isset($response->elements->element3->items[11]->value) &&
                    is_number($response->elements->element3->items[11]->value)) {
                    $maximumtotalrisksevendaybefore = $response->elements->element3->items[11]->value;
                }

                // Average grades last 7 days (Headline Gradebook).
                if (isset($response->elements->element3->items[9]->value) &&
                    is_number($response->elements->element3->items[9]->value)) {
                    $averagegradeslastsevendays = $response->elements->element3->items[9]->value;
                }

                // Average grades previous 7 days (Headline Gradebook).
                if (isset($response->elements->element3->items[10]->value) &&
                    is_number($response->elements->element3->items[10]->value)) {
                    $averagegradeslastsevendayspreviousweek = $response->elements->element3->items[10]->value;
                }

                // Posts last 7 days (Headline Discussions).
                if (isset($response->elements->element3->items[7]->value) &&
                    is_number($response->elements->element3->items[7]->value)) {
                    $postslastsevendays = $response->elements->element3->items[7]->value;
                }

                // Posts previous 7 days (Headline Discussions).
                if (isset($response->elements->element3->items[8]->value) &&
                    is_number($response->elements->element3->items[8]->value)) {
                    $postslastsevendayspreviousweek = $response->elements->element3->items[8]->value;
                }

                // Return dashboard_data object.
                $result = new dashboard_data($usersinrisk,
                    $risktotal,
                    $averagerisksevendaybefore,
                    $maximumtotalrisksevendaybefore,
                    $usersloggedinpreviousweek,
                    $usersactivitytotal,
                    $averageuserslastsevendays,
                    $userstotalprevioussevendays,
                    $averagegradeslastsevendays,
                    $averagegradeslastsevendayspreviousweek,
                    $postslastsevendays,
                    $postslastsevendayspreviousweek);

            }
        } catch (\moodle_exception $e) {
            get_report_failed::create_from_exception($e, \context_course::instance($courseid), "dashboard")->trigger();
            $result = false;
        }

        return $result;
    }

}
