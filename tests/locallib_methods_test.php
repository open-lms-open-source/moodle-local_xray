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

require_once(__DIR__.'/api_data_export_base.php');

/**
 * Class local_xray_locallib_methods_testcase
 *
 * @group local_xray
 */
class local_xray_locallib_methods_testcase extends local_xray_api_data_export_base_testcase {

    /**
     * Confirm method works before and after user enrollment
     */
    public function test_is_teacher_in_any_course() {
        global $CFG;

        require_once($CFG->dirroot.'/local/xray/locallib.php');

        $this->resetAfterTest(true);

        $courses = $this->addcourses(1);
        $users = $this->addusers(1);
        $instructor = reset($users);

        $this->assertFalse(local_xray_is_teacher($instructor->id));

        $this->users_enrol($courses, $users, 'teacher');

        $this->assertTrue(local_xray_is_teacher($instructor->id));
    }

}
