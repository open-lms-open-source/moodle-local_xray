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
 * @group local_xray
 */
class local_xray_course_manager_testcase extends local_xray_base_testcase {

    /**
     * Number of courses to use in tests
     */
    const NUM_COURSES = 10;

    /**
     * @return void
     */
    public function test_compute_check_status_disabled() {
        $res = course_manager::compute_check_status(0, 0);
        $this->assertTrue($res->disabled);
        $this->assertFalse($res->checked);
        $this->assertFalse($res->indeterminate);
    }

    /**
     * @return void
     */
    public function test_compute_check_status_unchecked() {
        $res = course_manager::compute_check_status(0, self::NUM_COURSES);
        $this->assertFalse($res->disabled);
        $this->assertFalse($res->checked);
        $this->assertFalse($res->indeterminate);
    }

    /**
     * @return void
     */
    public function test_compute_check_status_checked() {
        $res = course_manager::compute_check_status(self::NUM_COURSES, self::NUM_COURSES);
        $this->assertFalse($res->disabled);
        $this->assertTrue($res->checked);
        $this->assertFalse($res->indeterminate);
    }

    /**
     * @return void
     */
    public function test_compute_check_status_checked_indeterminate() {
        $res = course_manager::compute_check_status(self::NUM_COURSES / 2, self::NUM_COURSES);
        $this->assertFalse($res->disabled);
        $this->assertTrue($res->checked);
        $this->assertTrue($res->indeterminate);
    }

}
