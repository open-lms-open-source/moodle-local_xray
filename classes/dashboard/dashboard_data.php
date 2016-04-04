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
     * Is the number of persons in the risk metrics table, which are in the total risk category yellow or red.
     * @var integer
     */
    public $usersinrisk;

    /**
     * Is the total number of persons in the risk metrics table.
     * @var integer
     */
    public $risktotal;

    /**
     * Is the average of the number of persons at risk during the seven days before day before yesterday.
     * It cannot be seen in the current reports.
     * @var integer
     */
    public $averagerisksevendaybefore;

    /**
     * Is the maximum of the total number of persons in the risk metrics table during the seven days before day
     * before yesterday. It cannot be seen in the current reports.
     * @var integer
     */
    public $maximumtotalrisksevendaybefore;

    /**
     * Is the number of 'yes' in the activity metrics table in column 'Logged in during previous week'.
     * @var integer
     */
    public $usersloggedinpreviousweek;

    /**
     * Is the maximum of the total number of persons in the activity metrics table during the last seven days.
     * It cannot be seen in the current reports.
     * @var integer
     */
    public $usersactivitytotal;

    /**
     * Is the number of 'yes' in the activity metrics table in column 'Logged in during previous week' from
     * the report seven days ago. It cannot be seen in the current reports.
     * @var integer
     */
    public $averageuserslastsevendays;

    /**
     * Is the maximum of the total number of persons in the activity metrics table during the previous seven days.
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
                                $postslastsevendayspreviousweek) {

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
    }

    /**
     * Calculate colour and arrow for headline (compare current value and value in the previous week).
     * Return array with class and string for reader.
     *
     * Same value = return class for yellow colour.
     * Increment value = return class for green colour.
     * Decrement value = return class for red colour.
     *
     * @param $valuenow
     * @param $valuepreviousweek
     * @return array
     */
    public static function get_status_simple($valuenow, $valuepreviousweek) {

        // Default, same value.
        $stylestatus = "xray-headline-yellow";
        $arrow = "arrow_same";

        if ($valuenow < $valuepreviousweek) {
            // Decrement.
            $stylestatus = "xray-headline-red";
            $arrow = "arrow_decrease";
        } else if ($valuenow > $valuepreviousweek) {
            // Increment.
            $stylestatus = "xray-headline-green";
            $arrow = "arrow_increase";
        }
        $langstatus = get_string($arrow, "local_xray");
        return array($stylestatus, $langstatus);
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
     * @param string $valuenow1
     * @param string $valuenow2
     * @param string $valuepreviousweek
     * @param string $valuepreviousweek2
     * @param boolean $inversebehavior - Arrow will have inverse behavior (increment/decrement). Used by risk.
     * @return array
     */
    public static function get_status_with_average($valuenow1,
                                                   $valuenow2,
                                                   $valuepreviousweek,
                                                   $valuepreviousweek2,
                                                   $inversebehavior = false) {

        $firstaverage = 0;
        // Value can be null or "-" too.
        if (!empty($valuenow1) && !empty($valuenow2) && is_number($valuenow1) && is_number($valuenow2)) {
            $firstaverage = $valuenow1 / $valuenow2;
        }
        $secondaverage = 0;
        if (!empty($valuepreviousweek) && !empty($valuepreviousweek2) &&
            is_number($valuepreviousweek) && is_number($valuepreviousweek2)) {
            $secondaverage = $valuepreviousweek / $valuepreviousweek2;
        }

        // Default, same value.
        $stylestatus = "xray-headline-yellow";
        $arrow = "arrow_same";

        if ($firstaverage < $secondaverage) {

            // Decrement.
            $stylestatus = "xray-headline-red";
            $arrow = "arrow_decrease";
            if ($inversebehavior) {
                // Increment.
                $stylestatus = "xray-headline-green-caserisk";
                $arrow = "arrow_increase";
            }

        } else if ($firstaverage > $secondaverage) {

            // Increment.
            $stylestatus = "xray-headline-green";
            $arrow = "arrow_increase";
            if ($inversebehavior) {
                // Decrement.
                $stylestatus = "xray-headline-red-caserisk";
                $arrow = "arrow_decrease";
            }
        }

        $langstatus = get_string($arrow, "local_xray");
        return array($stylestatus, $langstatus);
    }
}
