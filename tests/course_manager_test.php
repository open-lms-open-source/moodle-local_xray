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

use \local_xray\local\api\course_manager;

/**
 * Class local_xray_course_manager_testcase
 * Test Course Manager.
 * @author    German Vitale
 * @author    David Castro
 * @group local_xray
 * @group local_xray_course_validation
 * @copyright Copyright (c) 2017 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_xray_course_manager_testcase extends \advanced_testcase {

    /**
     * Number of courses to use in tests
     */
    const NUM_COURSES = 10;

    /**
     * @return void
     */
    public function test_compute_check_status_disabled() {
        $res = course_manager::compute_check_status(0, 0, 0);
        $this->assertFalse($res->checked);
        $this->assertFalse($res->indeterminate);
    }

    /**
     * @return void
     */
    public function test_compute_check_status_unchecked() {
        $res = course_manager::compute_check_status(0, self::NUM_COURSES, 0);
        $this->assertFalse($res->checked);
        $this->assertFalse($res->indeterminate);
    }

    /**
     * @return void
     */
    public function test_compute_check_status_checked() {
        $res = course_manager::compute_check_status(self::NUM_COURSES, self::NUM_COURSES, 0);
        $this->assertTrue($res->checked);
        $this->assertFalse($res->indeterminate);
    }

    /**
     * @return void
     */
    public function test_compute_check_status_checked_indeterminate() {
        $res = course_manager::compute_check_status(self::NUM_COURSES / 2, self::NUM_COURSES, 0);
        $this->assertTrue($res->checked);
        $this->assertTrue($res->indeterminate);
    }

    /**
     * The course is enabled for X-ray.
     *
     * @return void
     */
    public function test_course_enabled() {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course(array('visible' => 1));
        $result = course_manager::is_xray_course($course);
        $this->assertTrue($result);
    }
}
