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
 * Object data for dashboard
 *
 * @package local_xray
 * @author Pablo Pagnone
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xray\dashboard;
defined('MOODLE_INTERNAL') || die();

/**
 * Object with the data provided from xray to show in dashboard.
 * @package local_xray\dashboard
 */
class dashboarddata {
    /**
     * Users in risk
     * @var array
     */
    public $usersinrisk;
    /**
     * Student enrolled
     * @var integer
     */
    public $studentsenrolled;

    /**
     * Students in risk
     * @var integer
     */
    public $studentsrisk;

    /**
     * Students visit last 7 days.
     * @var integer
     */
    public $studentsvisitslastsevendays;

    /**
     * Risk from last week
     * @var float
     */
    public $riskfromlastweek;

    /**
     * Visitors from last week
     * @var float
     */
    public $visitorsfromlastweek;

    /**
     * Students visits by weekday
     * @var array
     */
    public $studentsvisitsbyweekday;

    public function __construct($usersinrisk, $studentsenrolled, $studentsrisk, $studentsvisitslastsevendays,
                                $riskfromlastweek, $visitorsfromlastweek, $studentsvisitsbyweekday) {

        $this->usersinrisk = $usersinrisk;
        $this->studentsenrolled = $studentsenrolled;
        $this->studentsrisk = $studentsrisk;
        $this->studentsvisitslastsevendays = $studentsvisitslastsevendays;
        $this->riskfromlastweek = $riskfromlastweek;
        $this->visitorsfromlastweek = $visitorsfromlastweek;
        $this->studentsvisitsbyweekday = $studentsvisitsbyweekday;
    }
}
