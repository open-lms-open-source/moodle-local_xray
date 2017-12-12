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

/**
 * Object with the data provided from xray to show in dashboard.
 *
 * @package local_xray
 * @author Pablo Pagnone
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dashboard_data {

    /**
     * The number of persons in the risk metrics table, which are in the total risk category yellow or red.
     * @var integer
     */
    public $usersinrisk;

    /**
     * The total number of persons in the risk metrics table.
     * @var integer
     */
    public $risktotal;

    /**
     * The average of the number of persons at risk during the seven days before day before yesterday.
     * It cannot be seen in the current reports.
     * @var integer
     */
    public $averagerisksevendaybefore;

    /**
     * The maximum of the total number of persons in the risk metrics table during the seven days before day
     * before yesterday. It cannot be seen in the current reports.
     * @var integer
     */
    public $maximumtotalrisksevendaybefore;

    /**
     * The number of 'yes' in the activity metrics table in column 'Logged in during previous week'.
     * @var integer
     */
    public $usersloggedinpreviousweek;

    /**
     * The maximum of the total number of persons in the activity metrics table during the last seven days.
     * It cannot be seen in the current reports.
     * @var integer
     */
    public $usersactivitytotal;

    /**
     * The number of 'yes' in the activity metrics table in column 'Logged in during previous week' from
     * the report seven days ago. It cannot be seen in the current reports.
     * @var integer
     */
    public $averageuserslastsevendays;

    /**
     * The maximum of the total number of persons in the activity metrics table during the previous seven days.
     * It cannot be seen in the current reports.
     * @var integer
     */
    public $userstotalprevioussevendays;

    /**
     * Displays student average grade last 7 days.
     * @var integer
     */
    public $averagegradeslastsevendays;

    /**
     * Displays student average grade previous week.
     * @var integer
     */
    public $averagegradeslastsevendayspreviousweek;

    /**
     * Posts last seven days.
     * @var integer
     */
    public $postslastsevendays;

    /**
     * Posts last previous week.
     * @var integer
     */
    public $postslastsevendayspreviousweek;

    /**
     * Recommended Actions.
     * @var false/array
     */

    public $recommendations;

    /**
     * Count Recommended Actions.
     * @var integer
     */
    public $countrecommendations;

    /**
     * Report Date.
     * @var string
     */
    public $reportdate;

    /**
     * Construct
     *
     * @param integer $usersinrisk
     * @param integer $risktotal
     * @param integer $averagerisksevendaybefore
     * @param integer $maximumtotalrisksevendaybefore
     * @param integer $usersloggedinpreviousweek
     * @param integer $usersactivitytotal
     * @param integer $averageuserslastsevendays
     * @param integer $userstotalprevioussevendays
     * @param integer $averagegradeslastsevendays
     * @param integer $averagegradeslastsevendayspreviousweek
     * @param integer $postslastsevendays
     * @param integer $postslastsevendayspreviousweek
     * @param mixed   $recommendations
     * @param mixed   $countrecommendations
     * @param mixed   $reportdate
     */
    public function __construct($usersinrisk,
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
                                $reportdate) {

        $this->usersinrisk = $usersinrisk;
        $this->risktotal = $risktotal;
        $this->averagerisksevendaybefore = $averagerisksevendaybefore;
        $this->maximumtotalrisksevendaybefore = $maximumtotalrisksevendaybefore;
        $this->usersloggedinpreviousweek = $usersloggedinpreviousweek;
        $this->usersactivitytotal = $usersactivitytotal;
        $this->averageuserslastsevendays = $averageuserslastsevendays;
        $this->userstotalprevioussevendays = $userstotalprevioussevendays;
        $this->averagegradeslastsevendays = $averagegradeslastsevendays;
        $this->averagegradeslastsevendayspreviousweek = $averagegradeslastsevendayspreviousweek;
        $this->postslastsevendays = $postslastsevendays;
        $this->postslastsevendayspreviousweek = $postslastsevendayspreviousweek;
        $this->recommendations = $recommendations;
        $this->countrecommendations = $countrecommendations;
        $this->reportdate = $reportdate;
    }

    /**
     * Calculate colour and arrow for headline (compare current value and value in the previous week).
     * Return array with class and string for reader.
     *
     * Same value = return class for yellow colour.
     * Increment value = return class for green colour.
     * Decrement value = return class for red colour.
     *
     * @param null|string|int $valuenow - Webservice can return "-" or null.
     * @param null|string|int $valuepreviousweek - Webservice can return "-" or null.
     * @return array
     */
    public static function get_status_simple($valuenow, $valuepreviousweek) {

        // File name for email case.
        $filename = '';

        // Value can be null or "-" too.
        if (empty($valuenow1) && !is_numeric($valuenow)) {
            $valuenow = 0;
        }
        if (empty($valuepreviousweek) && !is_numeric($valuepreviousweek)) {
            $valuepreviousweek = 0;
        }

        // Default, same value.
        $stylestatus = "xray-headline-same";
        $statuslang = "arrow_same";
        $filename = "arrow_yellow";

        if ($valuenow < $valuepreviousweek) {
            // Decrement.
            $stylestatus = "xray-headline-decrease";
            $statuslang = "arrow_decrease";
            $filename = "arrow_red";
        } else if ($valuenow > $valuepreviousweek) {
            // Increment.
            $stylestatus = "xray-headline-increase";
            $statuslang = "arrow_increase";
            $filename = "arrow_green";
        }
        $langstatus = get_string($statuslang, "local_xray");
        return array($stylestatus, $langstatus, $filename);
    }

    /**
     * Calculate colour and arrow for headline comparing  current values and values in the previous week.
     * Return array with class and string for reader.
     * This case is used in Activity and risk columns..
     *
     * Same value = return class for yellow colour.
     * Decrement value = return class for green colour.
     * Increment value = return class for red colour.
     *
     * If you use $inversebehavior, you will have inverse behavior in the arrow (special for risk column).
     *
     * @param null|string|int $valuenow1 - Webservice can return "-" or null.
     * @param null|string|int $valuenow2 - Webservice can return "-" or null.
     * @param null|string|int $valuepreviousweek - Webservice can return "-" or null.
     * @param null|string|int $valuepreviousweek2 - Webservice can return "-" or null.
     * @param boolean $inversebehavior - Arrow will have inverse behavior (increment/decrement). Used by risk.
     * @return array
     */
    public static function get_status_with_average($valuenow1,
                                                   $valuenow2,
                                                   $valuepreviousweek,
                                                   $valuepreviousweek2,
                                                   $inversebehavior = false) {

        // File name for email case.
        $filename = '';
        $firstaverage = 0;
        // Value can be null or "-" too.
        if (!empty($valuenow1) && !empty($valuenow2) && is_numeric($valuenow1) && is_numeric($valuenow2)) {
            $firstaverage = $valuenow1 / $valuenow2;
        }
        $secondaverage = 0;
        if (!empty($valuepreviousweek) && !empty($valuepreviousweek2) &&
            is_numeric($valuepreviousweek) && is_numeric($valuepreviousweek2)) {
            $secondaverage = $valuepreviousweek / $valuepreviousweek2;
        }

        // Default, same value.
        $stylestatus = "xray-headline-same";
        $statuslang = "arrow_same";
        $filename = "arrow_yellow";

        if ($firstaverage < $secondaverage) {

            // Decrement.
            $stylestatus = "xray-headline-decrease";
            $statuslang = "arrow_decrease";
            $filename = "arrow_red";

            if ($inversebehavior) {
                // Arrow will be inverse.
                $stylestatus = "xray-headline-increase-caserisk";
                $filename = "arrow_green_risk";
            }

        } else if ($firstaverage > $secondaverage) {

            // Increment.
            $stylestatus = "xray-headline-increase";
            $statuslang = "arrow_increase";
            $filename = "arrow_green";

            if ($inversebehavior) {
                // Arrow will be inverse.
                $stylestatus = "xray-headline-decrease-caserisk";
                $filename = "arrow_red_risk";
            }
        }

        $langstatus = get_string($statuslang, "local_xray");
        return array($stylestatus, $langstatus, $filename);
    }
}
