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

defined('MOODLE_INTERNAL') || die();

/**
 * Test for Headline implementation.
 *
 * @author    Pablo Pagnone
 * @package   local_xray
 * @group local_xray
 * @group local_xray_headline
 * @copyright Copyright (c) 2016 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_xray_headline_testcase extends \advanced_testcase {

    /**
     * Course created for test.
     */
    private $course;

    /**
     * Plugin name.
     */
    const PLUGIN_NAME = "local_xray";

    /**
     * Setup test data.
     */
    public function setUp() {
        $this->course = $this->getDataGenerator()->create_course();

        // Set correct setting for xray plugin.
        set_config("xrayurl", "http://xrayunitest", self::PLUGIN_NAME);
        set_config("xrayusername", "unittest", self::PLUGIN_NAME);
        set_config("xraypassword", 1234, self::PLUGIN_NAME);
        set_config("xrayclientid", "test", self::PLUGIN_NAME);

    }

    /**
     * Method test_get_correct_data
     * Check if data returned by webservice is correct for show in headline.
     */
    public function test_get_correct_data() {
        $this->resetAfterTest();

        $baseurl = "http://xrayunitest";
        $basecourse = $baseurl.'/test/course/'.$this->course->id;
        /* @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        \local_xray\local\api\testhelper::push_pair($baseurl.'/user/login', 'user-login-final.json');
        /* @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        \local_xray\local\api\testhelper::push_pair($basecourse.'/dashboard', 'course-report-dashboard-final.json');

        /* @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        $dashboarddata = \local_xray\dashboard\dashboard::get($this->course->id);
        // This must be an instance of dashboard_data.
        $this->assertInstanceOf('\local_xray\dashboard\dashboard_data', $dashboarddata);
    }

    /**
     * Method test_get_incorrect_data
     * Data returned by webservice is incorrect for show headline.
     */
    public function test_get_incorrect_data() {

        $this->resetAfterTest();

        // Set clientid, with clientid "error", webservice class send us error when phpunit is running.
        set_config("xrayclientid", "error", self::PLUGIN_NAME);

        /* @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        $dashboarddata = \local_xray\dashboard\dashboard::get($this->course->id);
        // This must return false.
        $this->assertFalse($dashboarddata);
    }

    /**
     * Test correct attributes of class dashboard_data.
     */
    public function test_attributes_class_dashboard_data() {

        $this->resetAfterTest();
        $dashboadrobj = new \local_xray\dashboard\dashboard_data(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12);
        $this->assertObjectHasAttribute("usersinrisk", $dashboadrobj);
        $this->assertObjectHasAttribute("risktotal", $dashboadrobj);
        $this->assertObjectHasAttribute("averagerisksevendaybefore", $dashboadrobj);
        $this->assertObjectHasAttribute("maximumtotalrisksevendaybefore", $dashboadrobj);
        $this->assertObjectHasAttribute("usersloggedinpreviousweek", $dashboadrobj);
        $this->assertObjectHasAttribute("usersactivitytotal", $dashboadrobj);
        $this->assertObjectHasAttribute("averageuserslastsevendays", $dashboadrobj);
        $this->assertObjectHasAttribute("usersactivitytotal", $dashboadrobj);
        $this->assertObjectHasAttribute("averageuserslastsevendays", $dashboadrobj);
        $this->assertObjectHasAttribute("averagegradeslastsevendays", $dashboadrobj);
        $this->assertObjectHasAttribute("postslastsevendays", $dashboadrobj);
        $this->assertObjectHasAttribute("postslastsevendayspreviousweek", $dashboadrobj);
    }

    /**
     * Check correct return of get_status_simple() in class dashboard_data.
     * This function return class to use in headline checking the values of each column.
     */
    public function test_get_status_simple() {

        $this->resetAfterTest();

        // Return arrow class decrement (red).
        $result = \local_xray\dashboard\dashboard_data::get_status_simple(1, 2);
        $this->assertEquals("xray-headline-decrease", $result[0]);

        // Return arrow class same (yellow).
        $result = \local_xray\dashboard\dashboard_data::get_status_simple(2, 2);
        $this->assertEquals("xray-headline-same", $result[0]);

        // Return arrow class increment (green).
        $result = \local_xray\dashboard\dashboard_data::get_status_simple(5, 2);
        $this->assertEquals("xray-headline-increase", $result[0]);

        // Check if webservice sent null or "-" (This is same than 0).
        $result = \local_xray\dashboard\dashboard_data::get_status_simple("-", 2);
        $this->assertEquals("xray-headline-decrease", $result[0]);

        $result = \local_xray\dashboard\dashboard_data::get_status_simple("-", "-");
        $this->assertEquals("xray-headline-same", $result[0]);

        $result = \local_xray\dashboard\dashboard_data::get_status_simple(2, "-");
        $this->assertEquals("xray-headline-increase", $result[0]);

        // Check float string values.
        $result = \local_xray\dashboard\dashboard_data::get_status_simple("2.0", "4.0");
        $this->assertEquals("xray-headline-decrease", $result[0]);

        // Return arrow class same (yellow).
        $result = \local_xray\dashboard\dashboard_data::get_status_simple("4.5", "4.5");
        $this->assertEquals("xray-headline-same", $result[0]);

        // Return arrow class increment (green).
        $result = \local_xray\dashboard\dashboard_data::get_status_simple("3.5", "2.5");
        $this->assertEquals("xray-headline-increase", $result[0]);
    }

    /**
     * Check correct return of test_get_status_with_average() in class dashboard_data.
     * This function return class to use in headline checking the average of values of each column.
     */
    public function test_get_status_with_average() {

        $this->resetAfterTest();

        // Return arrow class decrement (red), 0.50 vs 0.75.
        $result = \local_xray\dashboard\dashboard_data::get_status_with_average(1, 2, 3, 4);
        $this->assertEquals("xray-headline-decrease", $result[0]);

        // Return arrow class same (yellow).
        $result = \local_xray\dashboard\dashboard_data::get_status_with_average(2, 2, 4, 4);
        $this->assertEquals("xray-headline-same", $result[0]);

        // Return arrow class increment (green) 1 vs 0.50.
        $result = \local_xray\dashboard\dashboard_data::get_status_with_average(4, 4, 2, 4);
        $this->assertEquals("xray-headline-increase", $result[0]);

        // Inverse case, return arrow class decrement (green), 0.50 vs 0.75.
        $result = \local_xray\dashboard\dashboard_data::get_status_with_average(1, 2, 3, 4, true);
        $this->assertEquals("xray-headline-increase-caserisk", $result[0]);

        // Inverse case, return arrow class same (yellow).
        $result = \local_xray\dashboard\dashboard_data::get_status_with_average(2, 2, 4, 4, true);
        $this->assertEquals("xray-headline-same", $result[0]);

        // Inverse case, return arrow class increment (red) 1 vs 0.50.
        $result = \local_xray\dashboard\dashboard_data::get_status_with_average(2, 2, 2, 4, true);
        $this->assertEquals("xray-headline-decrease-caserisk", $result[0]);

        // Check if webservice sent null or "-" (This is same than 0).
        $result = \local_xray\dashboard\dashboard_data::get_status_with_average("-", "-", 3, 4);
        $this->assertEquals("xray-headline-decrease", $result[0]);

        $result = \local_xray\dashboard\dashboard_data::get_status_with_average(2, 2, "-", 4);
        $this->assertEquals("xray-headline-increase", $result[0]);

        $result = \local_xray\dashboard\dashboard_data::get_status_with_average("-", "-", "-", "-");
        $this->assertEquals("xray-headline-same", $result[0]);

        // Check float string values.
        $result = \local_xray\dashboard\dashboard_data::get_status_with_average("4.0", "8.0", "2.5", "2.5");
        $this->assertEquals("xray-headline-decrease", $result[0]);

        $result = \local_xray\dashboard\dashboard_data::get_status_with_average("2.5", "2.5", "4.0", "8.0");
        $this->assertEquals("xray-headline-increase", $result[0]);

        $result = \local_xray\dashboard\dashboard_data::get_status_with_average("2.5", "2.5", "2.5", "2.5");
        $this->assertEquals("xray-headline-same", $result[0]);
    }
}
