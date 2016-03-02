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
     * Users in risk last seven days.
     * @var integer
     */
    public $usersinrisklastsevendays;

    /**
     * Users in risk previous week.
     * @var integer
     */
    public $usersinrisklastsevendays_previousweek;

    /**
     * Students logged last 7 days.
     * @var integer
     */
    public $studentsloggedlastsevendays;

    /**
     * Students logged previous week.
     * @var integer
     */
    public $studentsloggedlastsevendays_previousweek;

    /**
     * Posts last seven days.
     * @var integer
     */
    public $postslastsevendays;

    /**
     * Posts last previous week.
     * @var integer
     */
    public $postslastsevendays_previousweek;

    /**
     * Displays student average grade last 7 days.
     * @var integer
     */
    public $averagegradeslastsevendays;

    /**
     * Displays student average grade previous week.
     * @var integer
     */
    public $averagegradeslastsevendays_previousweek;

    /**
     * Displays total of students.
     * @var integer
     */
    public $totalstudents;

    /**
     * Construct
     * @param integer $usersinrisklastsevendays
     * @param integer $usersinrisklastsevendays_previousweek
     * @param integer $studentsloggedlastsevendays
     * @param integer $studentsloggedlastsevendays_previousweek
     * @param integer $postslastsevendays
     * @param integer $postslastsevendays_previousweek
     * @param integer $averagegradeslastsevendays
     * @param integer $averagegradeslastsevendays_previousweek
     * @param integer $totalstudents
     */
    public function __construct($usersinrisklastsevendays,
                                $usersinrisklastsevendays_previousweek,
                                $studentsloggedlastsevendays,
                                $studentsloggedlastsevendays_previousweek,
                                $postslastsevendays,
                                $postslastsevendays_previousweek,
                                $averagegradeslastsevendays,
                                $averagegradeslastsevendays_previousweek,
                                $totalstudents) {


        $this->usersinrisklastsevendays = $usersinrisklastsevendays;
        $this->usersinrisklastsevendays_previousweek = $usersinrisklastsevendays_previousweek;
        $this->studentsloggedlastsevendays = $studentsloggedlastsevendays;
        $this->studentsloggedlastsevendays_previousweek = $studentsloggedlastsevendays_previousweek;
        $this->postslastsevendays = $postslastsevendays;
        $this->postslastsevendays_previousweek = $postslastsevendays_previousweek;
        $this->averagegradeslastsevendays = $averagegradeslastsevendays;
        $this->averagegradeslastsevendays_previousweek = $averagegradeslastsevendays_previousweek;
        $this->totalstudents = $totalstudents;

    }
}
