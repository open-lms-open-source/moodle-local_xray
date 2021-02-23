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
 * show_time_hours_minutes tests
 *
 * @package   local_xray
 * @copyright Copyright (c) 2015 Open LMS (https://www.openlms.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xray\tests;

defined('MOODLE_INTERNAL') || die();

/**
 * Test show_time_hours_minutes method
 *
 * @author    German Vitale
 * @package   local_xray
 * @group     local_xray
 * @group     local_xray_show_time
 * @copyright Copyright (c) 2015 Open LMS (https://www.openlms.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_xray_show_time_testcase extends \advanced_testcase {

    /**
     * Setup.
     */
    public function setUp() {
        $this->resetAfterTest(true);
        global $CFG;
        /* @noinspection PhpIncludeInspection */
        require_once($CFG->dirroot.'/local/xray/controller/reports.php');
    }

    /**
     * Test remaining seconds minor than 60.
     */
    public function test_remaining_seconds () {
        // Create a value with seconds, representing 0 hours 0 minutes 59 seconds.
        $seconds = 59;

        $result = \local_xray_controller_reports::show_time_hours_minutes($seconds);
        $time = explode(":", $result);
        $hours = $time[0];
        $minutes = $time[1];
        // The result should be 00 hours 00 minutes, because we don't use remaining seconds minor than one minute.
        $this->assertEquals("00", $hours);
        $this->assertEquals("00", $minutes);
    }

    /**
     * Method show_time_hours_minutes.
     * Test result when $time is in seconds.
     * Test result being with two digits format.
     * Test a positive value lower than 24 hours.
     */
    public function test_seconds_param_lower() {
        // Create a value with seconds, representing 23 hours.
        $secondsparam = 23 * 60 * 60;
        // Add additional seconds.
        $additionalseconds = 59 * 60;// 59 minutes.
        $secondsparam = $secondsparam + $additionalseconds;

        $result = \local_xray_controller_reports::show_time_hours_minutes($secondsparam);
        $time = explode(":", $result);
        $hours = $time[0];
        $minutes = $time[1];

        $this->assertEquals("23", $hours);
        $this->assertEquals("59", $minutes);
    }

    /**
     * Method show_time_hours_minutes.
     * Test result when $time is in seconds.
     * Test result being with two digits format.
     * Test a positive value equal to 24 hours.
     */
    public function test_seconds_param_equal() {
        // Create a value with seconds, representing 24 hours.
        $secondsparam = 24 * 60 * 60;

        $result = \local_xray_controller_reports::show_time_hours_minutes($secondsparam);
        $time = explode(":", $result);
        $hours = $time[0];
        $minutes = $time[1];

        $this->assertEquals("24", $hours);
        $this->assertEquals("00", $minutes);
    }

    /**
     * Method show_time_hours_minutes.
     * Test result when $time is in seconds.
     * Test result being with two digits format.
     * Test a positive value higher than 24 hours.
     */
    public function test_seconds_param_higher() {
        // Create a value with seconds, representing 24 hours.
        $secondsparam = 24 * 60 * 60;
        // Add 60 additional seconds.
        $secondsparam = $secondsparam + 60;

        $result = \local_xray_controller_reports::show_time_hours_minutes($secondsparam);
        $time = explode(":", $result);
        $hours = $time[0];
        $minutes = $time[1];

        $this->assertEquals("24", $hours);
        $this->assertEquals("01", $minutes);
    }

    /**
     * Method show_time_hours_minutes.
     * Test result when $time is in minutes.
     * Test result being with two digits format.
     * Test a positive value lower than 24 hours.
     */
    public function test_minutes_param_lower() {
        // Create a value with minutes. 23 hours.
        $minutesparam = 23 * 60;
        // Add additional minutes.
        $minutesparamlower = $minutesparam + 59;// Lower than 24 hours.
        // Test positive values lower than 24 hours.
        $result = \local_xray_controller_reports::show_time_hours_minutes($minutesparamlower, true);
        $time = explode(":", $result);
        $hours = $time[0];
        $minutes = $time[1];
        $this->assertSame("23", $hours);
        $this->assertSame("59", $minutes);
    }

    /**
     * Method show_time_hours_minutes.
     * Test result when $time is in minutes.
     * Test result being with two digits format.
     * Test a positive value equal to 24 hours.
     */
    public function test_minutes_param_equal() {
        // Create a value with minutes. 23 hours.
        $minutesparam = 23 * 60;
        // Add additional minutes.
        $minutesparamequal = $minutesparam + 60;// Equal to 24 hours.
        // Test positive values equal to 24 hours.
        $result = \local_xray_controller_reports::show_time_hours_minutes($minutesparamequal, true);
        $time = explode(":", $result);
        $hours = $time[0];
        $minutes = $time[1];
        $this->assertSame("24", $hours);
        $this->assertSame("00", $minutes);
    }
    /**
     * Method show_time_hours_minutes.
     * Test result when $time is in minutes.
     * Test result being with two digits format.
     * Test a positive value higher than 24 hours.
     */
    public function test_minutes_param_higher() {
        // Create a value with minutes. 23 hours.
        $minutesparam = 23 * 60;
        // Add additional minutes.
        $minutesparamhigher = $minutesparam + 61;// Higher than 24 hours.
        // Test positive values higher than 24 hours.
        $result = \local_xray_controller_reports::show_time_hours_minutes($minutesparamhigher, true);
        $time = explode(":", $result);
        $hours = $time[0];
        $minutes = $time[1];
        $this->assertSame("24", $hours);
        $this->assertSame("01", $minutes);
    }

    /**
     * Method show_time_hours_minutes
     * Test a negative value.
     */
    public function test_negative_values() {
        // Create a negative value.
        $negativevalue = -1;
        $result = \local_xray_controller_reports::show_time_hours_minutes($negativevalue);
        $this->assertSame('-', $result);
    }

    /**
     * Method show_time_hours_minutes
     * Test a non numeric value.
     */
    public function test_non_numeric_values() {
        // Create a not number value.
        $nonnumeric = 'break';
        $result = \local_xray_controller_reports::show_time_hours_minutes($nonnumeric);
        $this->assertSame('-', $result);
    }
}
