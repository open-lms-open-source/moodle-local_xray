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

use local_xray\local\api\course_validation;

/**
 * Test Course Status Validation.
 * @author    German Vitale
 * @group local_xray
 * @group local_xray_course_validation
 * @copyright Copyright (c) 2016 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_xray_course_validation_test extends \advanced_testcase {

    /**
     * The course is enabled for X-ray.
     *
     * @return void
     */
    public function test_course_enabled() {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course(array('visible'=>1));
        $result = course_validation::is_xray_course($course->id);
        $this->assertTrue($result);
    }

    /**
     * The course is disabled for X-ray.
     *
     * @return void
     */
    public function test_course_disabled() {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course(array('visible'=>0));
        $result = course_validation::is_xray_course($course->id);
        $this->assertFalse($result);
    }

    /**
     * Test the validation info.
     *
     * @return void
     */
    public function test_validate_course() {
        $this->resetAfterTest();

        $category = $this->getDataGenerator()->create_category();
        $course = $this->getDataGenerator()->create_course(array('category'=>$category->id));
        $result = course_validation::validate_course($course);
        $this->assertTrue(is_array($result));

        $courseinfo = array();
        $courseinfo['id'] = $course->id;
        $courseinfo['students'] = false;
        $courseinfo['single'] = false;
        $courseinfo['hidden'] = false;
        $courseinfo['checked'] = true;
        $courseinfo['status'] = 1;
        $this->assertSame($courseinfo, $result);
    }

    /**
     * Test the extra validation info.
     *
     * @return void
     */
    public function test_validate_course_extrainfo() {
        $this->resetAfterTest();

        $category = $this->getDataGenerator()->create_category();
        $course = $this->getDataGenerator()->create_course(array('category'=>$category->id));
        $result = course_validation::validate_course($course, true);
        $this->assertTrue(is_array($result));

        $courseinfo = array();
        $courseinfo['id'] = $course->id;
        $courseinfo['students'] = false;
        $courseinfo['single'] = false;
        $courseinfo['hidden'] = false;
        $courseinfo['checked'] = true;
        $courseinfo['status'] = 1;
        $courseinfo['name'] = $course->fullname;
        $courseinfo['disabled'] = false;
        $courseinfo['status'] = 1;
        $this->assertSame($courseinfo, $result);
    }

    /**
     * Get the X-ray courses info in the category.
     *
     * @return void
     */
    public function test_validate_category_courses() {
        $this->resetAfterTest();

        $category = $this->getDataGenerator()->create_category();
        $course = $this->getDataGenerator()->create_course(array('category'=>$category->id));
        $result = course_validation::get_courses($category->id);
        $this->assertTrue(is_array($result));

        $courseinfo = array();
        $courseinfo['id'] = $course->id;
        $courseinfo['students'] = false;
        $courseinfo['single'] = false;
        $courseinfo['hidden'] = false;
        $courseinfo['checked'] = true;
        $courseinfo['status'] = 1;
        $courseinfo['name'] = $course->fullname;
        $courseinfo['disabled'] = false;
        $courseinfo['status'] = 1;
        $this->assertSame(array($course->id => $courseinfo), $result);
    }

    /**
     * Non existing category.
     *
     * @return void
     */
    public function test_validate_unexisting_category() {
        global $DB;

        $this->resetAfterTest();

        $category = $this->getDataGenerator()->create_category();
        $course = $this->getDataGenerator()->create_course(array('category'=>$category->id));

        $unexistingcat = $category->id + 50000;
        $result = course_validation::get_courses($unexistingcat);
        $this->assertTrue(is_array($result));
        $this->assertSame(array(), $result);
    }
}