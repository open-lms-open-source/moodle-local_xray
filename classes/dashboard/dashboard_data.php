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
}
