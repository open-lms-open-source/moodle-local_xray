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
 * Headline implementation tests
 *
 * @package   local_xray
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xray\tests;
defined('MOODLE_INTERNAL') || die();

/**
 * Test for Headline implementation.
 *
 * @author    Pablo Pagnone
 * @package   local_xray
 * @group local_xray
 * @group local_xray_headline
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
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
        $this->resetAfterTest();
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

        $dashboard_data = \local_xray\dashboard\dashboard::get($this->course->id);
        // This must be an instance of dashboard_data.
        $this->assertInstanceOf('\local_xray\dashboard\dashboard_data', $dashboard_data);
    }

    /**
     * Method test_get_incorrect_data
     * Data returned by webservice is incorrect for show headline.
     */
    public function test_get_incorrect_data() {

        // Reset this setting after current test.
        $this->resetAfterTest(true);

        // Set clientid, with clientid "error", webservice class send us error when phpunit is running.
        set_config("xrayclientid", "error", self::PLUGIN_NAME);

        $dashboard_data = \local_xray\dashboard\dashboard::get($this->course->id);
        // This must return false.
        $this->assertFalse($dashboard_data);
    }


}