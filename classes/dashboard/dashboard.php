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
use local_xray\local\api;

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
            $response = api\wsapi::course($courseid, $report);
            if (!$response) {

                // Fail response of webservice.
                api\xrayws::instance()->print_error();

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
                $averagegradeslastsevendays = "-";
                $averagegradeslastsevendayspreviousweek = "-";
                $postslastsevendays = "-";
                $postslastsevendayspreviousweek = "-";

                // The number of persons in the risk metrics table, which are in the total risk (Headline Risk).
                $usersinriskisset = isset($response->elements->element3->items[0]->value);
                if ($usersinriskisset && is_number($response->elements->element3->items[0]->value)) {
                    $usersinrisk = $response->elements->element3->items[0]->value;
                }

                // The total number of persons in the risk metrics table (Headline Risk).
                $risktotalisset = isset($response->elements->element3->items[2]->value);
                if ($risktotalisset && is_number($response->elements->element3->items[2]->value)) {
                    $risktotal = $response->elements->element3->items[2]->value;
                }

                // The average of the number of persons at risk during the seven days before (Headline Risk).
                $averagerisksevendaybeforeisset = isset($response->elements->element3->items[1]->value);
                if ($averagerisksevendaybeforeisset && is_number($response->elements->element3->items[1]->value)) {
                    $averagerisksevendaybefore = $response->elements->element3->items[1]->value;
                }

                // Maximum of the total number of persons in the risk metrics table seven days before (Headline Risk).
                $maximumtotalrisksevendaybeforeisset = isset($response->elements->element3->items[3]->value);
                if ($maximumtotalrisksevendaybeforeisset && is_number($response->elements->element3->items[3]->value)) {
                    $maximumtotalrisksevendaybefore = $response->elements->element3->items[3]->value;
                }

                // The number of 'yes' in the activity metrics table (Headline Activity).
                $usersloggedinpreviousweekisset = isset($response->elements->element3->items[4]->value);
                if ($usersloggedinpreviousweekisset && is_number($response->elements->element3->items[4]->value)) {
                    $usersloggedinpreviousweek = $response->elements->element3->items[4]->value;
                }

                // The maximum of the total number of persons in the activity metrics (Headline Activity).
                $usersactivitytotalisset = isset($response->elements->element3->items[6]->value);
                if ($usersactivitytotalisset && is_number($response->elements->element3->items[6]->value)) {
                    $usersactivitytotal = $response->elements->element3->items[6]->value;
                }

                // The number of 'yes' in the activity metrics table (Headline Activity).
                $averageuserslastsevendaysisset = isset($response->elements->element3->items[5]->value);
                if ($averageuserslastsevendaysisset && is_number($response->elements->element3->items[5]->value)) {
                    $averageuserslastsevendays = $response->elements->element3->items[5]->value;
                }

                // The maximum of the total number of persons in the activity metrics(Headline Activity).
                $userstotalprevioussevendaysisset = isset($response->elements->element3->items[7]->value);
                if ($userstotalprevioussevendaysisset &&  is_number($response->elements->element3->items[7]->value)) {
                    $userstotalprevioussevendays = $response->elements->element3->items[7]->value;
                }

                // Average grades last 7 days (Headline Gradebook).
                $avgradeslastsevendaysisset = isset($response->elements->element3->items[8]->value);
                if ($avgradeslastsevendaysisset && is_number($response->elements->element3->items[8]->value)) {
                    $averagegradeslastsevendays = $response->elements->element3->items[8]->value;
                }

                // Average grades previous 7 days (Headline Gradebook).
                $avlastsevendayspreviousweekisset = isset($response->elements->element3->items[9]->value);
                if ($avlastsevendayspreviousweekisset && is_number($response->elements->element3->items[9]->value)) {
                    $averagegradeslastsevendayspreviousweek = $response->elements->element3->items[9]->value;
                }

                // Posts last 7 days (Headline Discussions).
                $postslastsevendaysisset = isset($response->elements->element3->items[10]->value);
                if ($postslastsevendaysisset && is_number($response->elements->element3->items[10]->value)) {
                    $postslastsevendays = $response->elements->element3->items[10]->value;
                }

                // Posts previous 7 days (Headline Discussions).
                $postspreviousweekisset = isset($response->elements->element3->items[11]->value);
                if ($postspreviousweekisset && is_number($response->elements->element3->items[11]->value)) {
                    $postslastsevendayspreviousweek = $response->elements->element3->items[11]->value;
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
