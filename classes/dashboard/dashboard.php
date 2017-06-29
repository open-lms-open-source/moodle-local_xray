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

    const XRAYRECOMMENDATIONADMIN = 0;
    const XRAYRECOMMENDATIONTEACHER = 1;
    const XRAYRECOMMENDATIONALL = 2;

    /**
     * Connect with xray webservice and get data for dashboard.
     * @param $courseid
     * @param  int|bool $userid
     * @param  bool $xrayreports
     * @return bool|dashboard_data
     */
    public static function get($courseid, $userid = false, $xrayreports = false) {
        global $CFG;

        $result = "";
        $context = \context_course::instance($courseid);

        try {

            $report = "dashboard";
            if ($xrayreports) {
                $response = api\wsapi::dashboard($courseid);
            } else {
                $response = api\wsapi::course($courseid, $report);
            }
            // The object can be empty.
            $resvalidation = (array)$response;

            if (!$response || empty($resvalidation)) {

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

                if ($xrayreports) {

                    // The number of persons in the risk metrics table, which are in the total risk (Headline Risk).
                    $usersinriskisset = isset($response->headlineData[0]->value);
                    if ($usersinriskisset && is_numeric($response->headlineData[0]->value)) {
                        $usersinrisk = $response->headlineData[0]->value;
                    }

                    // The total number of persons in the risk metrics table (Headline Risk).
                    $risktotalisset = isset($response->headlineData[2]->value);
                    if ($risktotalisset && is_numeric($response->headlineData[2]->value)) {
                        $risktotal = $response->headlineData[2]->value;
                    }

                    // The average of the number of persons at risk during the seven days before (Headline Risk).
                    $averagerisksevendaybeforeisset = isset($response->headlineData[1]->value);
                    if ($averagerisksevendaybeforeisset && is_numeric($response->headlineData[1]->value)) {
                        $averagerisksevendaybefore = $response->headlineData[1]->value;
                    }

                    // Maximum of the total number of persons in the risk metrics table seven days before (Headline Risk).
                    $maximumtotalrisksevendaybeforeisset = isset($response->headlineData[3]->value);
                    if ($maximumtotalrisksevendaybeforeisset && is_numeric($response->headlineData[3]->value)) {
                        $maximumtotalrisksevendaybefore = $response->headlineData[3]->value;
                    }

                    // The number of 'yes' in the activity metrics table (Headline Activity).
                    $usersloggedinpreviousweekisset = isset($response->headlineData[4]->value);
                    if ($usersloggedinpreviousweekisset && is_numeric($response->headlineData[4]->value)) {
                        $usersloggedinpreviousweek = $response->headlineData[4]->value;
                    }

                    // The maximum of the total number of persons in the activity metrics (Headline Activity).
                    $usersactivitytotalisset = isset($response->headlineData[6]->value);
                    if ($usersactivitytotalisset && is_numeric($response->headlineData[6]->value)) {
                        $usersactivitytotal = $response->headlineData[6]->value;
                    }

                    // The number of 'yes' in the activity metrics table (Headline Activity).
                    $averageuserslastsevendaysisset = isset($response->headlineData[5]->value);
                    if ($averageuserslastsevendaysisset && is_numeric($response->headlineData[5]->value)) {
                        $averageuserslastsevendays = $response->headlineData[5]->value;
                    }

                    // The maximum of the total number of persons in the activity metrics(Headline Activity).
                    $userstotalprevioussevendaysisset = isset($response->headlineData[7]->value);
                    if ($userstotalprevioussevendaysisset &&  is_numeric($response->headlineData[7]->value)) {
                        $userstotalprevioussevendays = $response->headlineData[7]->value;
                    }

                    // Average grades last 7 days (Headline Gradebook).
                    $avgradeslastsevendaysisset = isset($response->headlineData[8]->value);
                    if ($avgradeslastsevendaysisset && is_numeric($response->headlineData[8]->value)) {
                        $averagegradeslastsevendays = $response->headlineData[8]->value;
                    }

                    // Average grades previous 7 days (Headline Gradebook).
                    $avlastsevendayspreviousweekisset = isset($response->headlineData[9]->value);
                    if ($avlastsevendayspreviousweekisset && is_numeric($response->headlineData[9]->value)) {
                        $averagegradeslastsevendayspreviousweek = $response->headlineData[9]->value;
                    }

                    // Posts last 7 days (Headline Discussions).
                    $postslastsevendaysisset = isset($response->headlineData[10]->value);
                    if ($postslastsevendaysisset && is_numeric($response->headlineData[10]->value)) {
                        $postslastsevendays = $response->headlineData[10]->value;
                    }

                    // Posts previous 7 days (Headline Discussions).
                    $postspreviousweekisset = isset($response->headlineData[11]->value);
                    if ($postspreviousweekisset && is_numeric($response->headlineData[11]->value)) {
                        $postslastsevendayspreviousweek = $response->headlineData[11]->value;
                    }

                    // Recommended actions.
                    $countrecommendations = 0;
                    $recommendations = false;
                    $reportdate = '';

                    $recommendationslist = array();
                    if ($userid) {
                        $addrecommendations = '';
                        require_once($CFG->dirroot . '/local/xray/locallib.php');
                        $isteacherincourse = local_xray_is_teacher_in_course($courseid, $userid);
                        if (has_capability("local/xray:adminrecommendations_view", $context, $userid)) {
                            $addrecommendations = self::XRAYRECOMMENDATIONADMIN;
                            if ($isteacherincourse) {
                                $addrecommendations = self::XRAYRECOMMENDATIONALL;
                            }
                        } else if (has_capability("local/xray:teacherrecommendations_view", $context, $userid)
                                   || $isteacherincourse) {
                            $addrecommendations = self::XRAYRECOMMENDATIONTEACHER;
                        }
                        // Recommendations for Admin user..
                        if ($addrecommendations == self::XRAYRECOMMENDATIONADMIN ||
                            $addrecommendations == self::XRAYRECOMMENDATIONALL) {
                            // Recommendations Admin.
                            if (isset($response->recommendationsAdmin)) {
                                foreach ($response->recommendationsAdmin as $recommendation) {
                                    if ($recommendation->text) {
                                        $recommendationslist[] = $recommendation->text;
                                    }
                                }
                            }
                        }
                        // Recommendations for Instructor user..
                        if ($addrecommendations == self::XRAYRECOMMENDATIONTEACHER ||
                            $addrecommendations == self::XRAYRECOMMENDATIONALL) {
                            // Recommendations Instructor.
                            if (isset($response->recommendationsInstructor)) {
                                foreach ($response->recommendationsInstructor as $recommendation) {
                                    if ($recommendation->text) {
                                        $recommendationslist[] = $recommendation->text;
                                    }
                                }
                            }
                            // Recommendations Positive.
                            if (isset($response->recommendationsPositive)) {
                                foreach ($response->recommendationsPositive as $recommendation) {
                                    if ($recommendation->text) {
                                        $recommendationslist[] = $recommendation->text;
                                    }
                                }
                            }
                        }
                        if ($recommendationslist) {
                            $recommendations = $recommendationslist;
                            // Count recommendations.
                            $countrecommendations = count($recommendations);
                        }
                        // Report date.
                        if (isset($response->reportdate)) {
                            $reportdate = $response->reportdate;
                        }
                    }

                } else {
                    // The number of persons in the risk metrics table, which are in the total risk (Headline Risk).
                    $usersinriskisset = isset($response->elements->element3->items[0]->value);
                    if ($usersinriskisset && is_numeric($response->elements->element3->items[0]->value)) {
                        $usersinrisk = $response->elements->element3->items[0]->value;
                    }

                    // The total number of persons in the risk metrics table (Headline Risk).
                    $risktotalisset = isset($response->elements->element3->items[2]->value);
                    if ($risktotalisset && is_numeric($response->elements->element3->items[2]->value)) {
                        $risktotal = $response->elements->element3->items[2]->value;
                    }

                    // The average of the number of persons at risk during the seven days before (Headline Risk).
                    $averagerisksevendaybeforeisset = isset($response->elements->element3->items[1]->value);
                    if ($averagerisksevendaybeforeisset && is_numeric($response->elements->element3->items[1]->value)) {
                        $averagerisksevendaybefore = $response->elements->element3->items[1]->value;
                    }

                    // Maximum of the total number of persons in the risk metrics table seven days before (Headline Risk).
                    $maximumtotalrisksevendaybeforeisset = isset($response->elements->element3->items[3]->value);
                    if ($maximumtotalrisksevendaybeforeisset && is_numeric($response->elements->element3->items[3]->value)) {
                        $maximumtotalrisksevendaybefore = $response->elements->element3->items[3]->value;
                    }

                    // The number of 'yes' in the activity metrics table (Headline Activity).
                    $usersloggedinpreviousweekisset = isset($response->elements->element3->items[4]->value);
                    if ($usersloggedinpreviousweekisset && is_numeric($response->elements->element3->items[4]->value)) {
                        $usersloggedinpreviousweek = $response->elements->element3->items[4]->value;
                    }

                    // The maximum of the total number of persons in the activity metrics (Headline Activity).
                    $usersactivitytotalisset = isset($response->elements->element3->items[6]->value);
                    if ($usersactivitytotalisset && is_numeric($response->elements->element3->items[6]->value)) {
                        $usersactivitytotal = $response->elements->element3->items[6]->value;
                    }

                    // The number of 'yes' in the activity metrics table (Headline Activity).
                    $averageuserslastsevendaysisset = isset($response->elements->element3->items[5]->value);
                    if ($averageuserslastsevendaysisset && is_numeric($response->elements->element3->items[5]->value)) {
                        $averageuserslastsevendays = $response->elements->element3->items[5]->value;
                    }

                    // The maximum of the total number of persons in the activity metrics(Headline Activity).
                    $userstotalprevioussevendaysisset = isset($response->elements->element3->items[7]->value);
                    if ($userstotalprevioussevendaysisset &&  is_numeric($response->elements->element3->items[7]->value)) {
                        $userstotalprevioussevendays = $response->elements->element3->items[7]->value;
                    }

                    // Average grades last 7 days (Headline Gradebook).
                    $avgradeslastsevendaysisset = isset($response->elements->element3->items[8]->value);
                    if ($avgradeslastsevendaysisset && is_numeric($response->elements->element3->items[8]->value)) {
                        $averagegradeslastsevendays = $response->elements->element3->items[8]->value;
                    }

                    // Average grades previous 7 days (Headline Gradebook).
                    $avlastsevendayspreviousweekisset = isset($response->elements->element3->items[9]->value);
                    if ($avlastsevendayspreviousweekisset && is_numeric($response->elements->element3->items[9]->value)) {
                        $averagegradeslastsevendayspreviousweek = $response->elements->element3->items[9]->value;
                    }

                    // Posts last 7 days (Headline Discussions).
                    $postslastsevendaysisset = isset($response->elements->element3->items[10]->value);
                    if ($postslastsevendaysisset && is_numeric($response->elements->element3->items[10]->value)) {
                        $postslastsevendays = $response->elements->element3->items[10]->value;
                    }

                    // Posts previous 7 days (Headline Discussions).
                    $postspreviousweekisset = isset($response->elements->element3->items[11]->value);
                    if ($postspreviousweekisset && is_numeric($response->elements->element3->items[11]->value)) {
                        $postslastsevendayspreviousweek = $response->elements->element3->items[11]->value;
                    }

                    // Recommended actions.
                    $countrecommendations = 0;
                    $recommendations = false;
                    $reportdate = '';

                    $recommendationslist = array();
                    if ($userid) {
                        $addrecommendations = '';
                        require_once($CFG->dirroot.'/local/xray/locallib.php');
                        $isteacherincourse = local_xray_is_teacher_in_course($courseid, $userid);
                        if (has_capability("local/xray:adminrecommendations_view", $context, $userid)) {
                            $addrecommendations = self::XRAYRECOMMENDATIONADMIN;
                            if ($isteacherincourse) {
                                $addrecommendations = self::XRAYRECOMMENDATIONALL;
                            }
                        } else if (has_capability("local/xray:teacherrecommendations_view", $context, $userid)
                                   || $isteacherincourse) {
                            $addrecommendations = self::XRAYRECOMMENDATIONTEACHER;
                        }
                        // Recommendations for Admin user..
                        if ($addrecommendations == self::XRAYRECOMMENDATIONADMIN ||
                            $addrecommendations == self::XRAYRECOMMENDATIONALL) {
                            // Recommendations Admin.
                            if (isset($response->elements->recommendationsAdmin)) {
                                foreach ($response->elements->recommendationsAdmin->data as $recommendation) {
                                    if ($recommendation->text->value) {
                                        $recommendationslist[] = $recommendation->text->value;
                                    }
                                }
                            }
                        }
                        // Recommendations for Instructor user..
                        if ($addrecommendations == self::XRAYRECOMMENDATIONTEACHER ||
                            $addrecommendations == self::XRAYRECOMMENDATIONALL) {
                            // Recommendations Instructor.
                            if (isset($response->elements->recommendationsInstructor)) {
                                foreach ($response->elements->recommendationsInstructor->data as $recommendation) {
                                    if ($recommendation->text->value) {
                                        $recommendationslist[] = $recommendation->text->value;
                                    }
                                }
                            }
                            // Recommendations Positive.
                            if (isset($response->elements->recommendationsPositive)) {
                                foreach ($response->elements->recommendationsPositive->data as $recommendation) {
                                    if ($recommendation->text->value) {
                                        $recommendationslist[] = $recommendation->text->value;
                                    }
                                }
                            }
                        }
                        if ($recommendationslist) {
                            $recommendations = $recommendationslist;
                            // Count recommendations.
                            $countrecommendations = count($recommendations);
                        }
                        // Report date.
                        if (isset($response->reportdate)) {
                            $reportdate = $response->reportdate;
                        }
                    }
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
                    $postslastsevendayspreviousweek,
                    $recommendations,
                    $countrecommendations,
                    $reportdate);

            }
        } catch (\moodle_exception $e) {
            get_report_failed::create_from_exception($e, $context, "dashboard")->trigger();
            $result = false;
        }

        return $result;
    }
}