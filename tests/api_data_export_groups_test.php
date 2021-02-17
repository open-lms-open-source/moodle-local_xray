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
 * Class local_xray_api_data_export_groups_testcase
 * @group local_xray
 */
class local_xray_api_data_export_groups_testcase extends local_xray_api_data_export_base_testcase {

    /**
     * preset
     */
    public function setUp(): void {
        $this->init_base();
    }

    /**
     * Reset stuff
     */
    public function test_groups_export_init() {
        $this->resetAfterTest();
        $this->assertTrue(true);
    }

    /**
     * @depends test_groups_export_init
     * @return stdClass[]
     */
    public function test_groups_export() {
        $this->resetAfterTest(false);

        $courses = $this->addcourses(5);
        $groups = $this->add_course_groups(5, $courses);

        $groupdata = [];
        foreach ($groups as $group) {
            $groupdata[] = array_values((array)$group);
        }

        $typedef = [
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'string' ],
            ['optional' => true , 'type' => 'string' ],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
        ];

        $this->export_check('groups', $typedef, time(), false, count($groupdata), $groupdata);

        return [$courses, $groups];
    }

    /**
     * @param   array $params
     * @depends test_groups_export
     * @return  stdClass[]
     */
    public function test_group_members_export(array $params) {
        $this->resetAfterTest(false);
        list($courses, $groups) = $params;
        ($groups);
        $this->waitForSecond();
        $gmusers = $this->add_course_groups_members(5, $courses);
        $userdata = [];
        foreach ($gmusers as $user) {
            $userdata[] = array_values((array)$user);
        }

        $typedef = [
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
        ];

        $this->export_check('groups_members', $typedef, time(), false, count($userdata), $userdata);

        return [$courses, $groups, $gmusers];
    }

    /**
     * @param   array $params
     * @depends test_group_members_export
     * @return  stdClass[]
     */
    public function test_groups_members_delete_export(array $params) {
        $this->resetAfterTest(false);

        list($courses, $groups, $gmusers) = $params;
        ($groups);

        $this->waitForSecond();
        $this->delete_course_groups_members($courses);
        $gmdata = [];
        foreach ($gmusers as $user) {
            $gmdata[] = [null, (int)$user->groupid, (int)$user->userid, null];
        }

        $typedef = [
            ['optional' => true , 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => true , 'type' => 'numeric'],
        ];

        $this->export_check('groups_members_deleted', $typedef, time(), false, count($gmdata), $gmdata);

        return [$courses, $groups];
    }

    /**
     * @param   array $params
     * @depends test_groups_members_delete_export
     * @return  void
     */
    public function test_groups_delete_export(array $params) {
        $this->resetAfterTest();

        list($courses, $groups) = $params;

        $this->waitForSecond();
        $this->delete_course_groups($courses);
        $gdata = [];
        foreach ($groups as $group) {
            $gdata[] = [null, (int)$group->id, null];
        }

        $typedef = [
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
        ];

        $this->export_check('groups_deleted', $typedef, time(), false, count($gdata), $gdata);
    }

}
