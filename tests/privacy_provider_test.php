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
 * Tests for the lcoal_xray implementation of the Privacy Provider API.
 *
 * @package    local_xray
 * @author     Jonathan Garcia Gomez jonathan.garcia@openlms.net
 * @copyright  Copyright (c) 2018 Open LMS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use \core_privacy\local\request\approved_contextlist;
use \core_privacy\local\request\writer;
use \core_privacy\local\metadata\collection;
use \local_xray\privacy\provider;

/**
 * Tests for the local_xray implementation of the Privacy Provider API.
 *
 * @copyright  Copyright (c) 2018 Open LMS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_xray_privacy_provider_testcase extends \core_privacy\tests\provider_testcase {

    /**
     * Ensure that get_metadata exports valid content.
     */
    public function setUp() {
        $this->resetAfterTest(true);
    }

    public function test_get_metadata() {
        $items = new collection('local_xray');
        $result = provider::get_metadata($items);
        $this->assertSame($items, $result);
        $this->assertInstanceOf(collection::class, $result);
    }

    public function set_test_data($users) {
        global $DB;
        $course = $this->getDataGenerator()->create_course();
        if(!is_array($users)){
            $users =[$users];
        }
        foreach ($users as $user) {
            $record = new stdClass();
            $record->userid = $user->id;
            $record->type = 1;
            $DB->insert_record('local_xray_globalsub', $record);
            $record->role = 5;
            $record->course = $course->id;
            $record->timedeleted = time();
            $DB->insert_record('local_xray_roleunas', $record);
            $record->courseid = $course->id;
            $DB->insert_record('local_xray_subscribe', $record);
            $record->enrolid = 10;
            $DB->insert_record('local_xray_enroldel', $record);
            $record->groupid = 12;
            $record->participantid = $user->id;
            $DB->insert_record('local_xray_gruserdel', $record);
        }
        return $course->id;
    }


    /**
     * Ensure that export_user_data returns data when there is any.
     */
    public function test_export_user_data() {
        $this->resetAfterTest();
        $this->setAdminUser();
        $user = \core_user::get_user_by_username('admin');
        $courseid = $this->set_test_data($user);
        $context = context_user::instance($user->id);
        $approvedcontextlist = new approved_contextlist($user, 'local_xray', [$context->id]);
        provider::export_user_data($approvedcontextlist);
        $writer = writer::with_context($context);
        $globalsubs = $writer->get_data(['local_xray/globalsubs']);
        $this->assertNotEmpty($globalsubs);
        $this->assertEquals($globalsubs->global_subscriptions[0]->type, 'Not subscribed.');
        $roleunassign = $writer->get_data(['local_xray/role_unassignments']);
        $this->assertNotEmpty($roleunassign);
        $this->assertEquals($roleunassign->role_unassignments[0]->course, $courseid);
        $subscrptions = $writer->get_data(['local_xray/subscriptions']);
        $this->assertNotEmpty($subscrptions->subscriptions[0]->course, $courseid);
        $enroldeletions = $writer->get_data(['local_xray/enrol_deletions']);
        $this->assertNotEmpty($enroldeletions);
        $this->assertEquals($enroldeletions->enrol_deletions[0]->course, $courseid);
        $groupdeletions = $writer->get_data(['local_xray/groupmemeber_deletions']);
        $this->assertNotEmpty($groupdeletions);
        $this->assertEquals($groupdeletions->group_deletions[0]->group_id, 12);
    }

    /**
     * Ensure data for the given context is deleted
     */

    public function test_delete_data_for_all_users_in_context() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $user = \core_user::get_user_by_username('admin');
        $courseid1 = $this->set_test_data($user);
        $courseid2 = $this->set_test_data($user);
        $context1 = context_course::instance($courseid1);
        $context2 = context_course::instance($courseid2);
        provider::delete_data_for_all_users_in_context($context1);
        $this->assertNotEmpty($DB->get_records('local_xray_globalsub'));
        $this->assertEmpty($DB->get_records('local_xray_roleunas', ['course' => $courseid1]));
        $this->assertNotEmpty($DB->get_records('local_xray_roleunas', ['course' => $courseid2]));
        provider::delete_data_for_all_users_in_context($context2);
        $this->assertEmpty($DB->get_records('local_xray_subscribe'));
    }

    /**
     * Ensure data for the given user is deleted.
     */

    public function test_delete_data_for_user() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $user = \core_user::get_user_by_username('admin');
        $courseid = $this->set_test_data($user);
        $this->assertNotEmpty($DB->get_records('local_xray_roleunas', ['userid' => $user->id]));
        $this->assertNotEmpty($DB->get_records('local_xray_subscribe', ['userid' => $user->id]));
        $this->assertNotEmpty($DB->get_records('local_xray_globalsub', ['userid' => $user->id]));
        $this->assertNotEmpty($DB->get_records('local_xray_enroldel', ['userid' => $user->id]));
        $this->assertNotEmpty($DB->get_records('local_xray_gruserdel', ['participantid' => $user->id]));
        $context = context_user::instance($user->id);
        $approvedcontextlist = new approved_contextlist($user, 'local_xray', [$context->id]);
        provider::delete_data_for_user($approvedcontextlist);
        $this->assertEmpty($DB->get_records('local_xray_roleunas', ['userid' => $user->id]));
        $this->assertEmpty($DB->get_records('local_xray_subscribe', ['userid' => $user->id]));
        $this->assertEmpty($DB->get_records('local_xray_globalsub', ['userid' => $user->id]));
        $this->assertEmpty($DB->get_records('local_xray_enroldel', ['userid' => $user->id]));
        $this->assertEmpty($DB->get_records('local_xray_gruserdel', ['participantid' => $user->id]));

    }

    /**
     * Test that only users within a user context are fetched.
     */
    public function test_get_users_in_context() {
        global $DB;
        $this->resetAfterTest();
        $users = [];
        $users[] = $this->getDataGenerator()->create_user();
        $users[] = $this->getDataGenerator()->create_user();
        $users[] = $this->getDataGenerator()->create_user();
        $users2 = [];
        $users2[] = $this->getDataGenerator()->create_user();
        $users2[] = $this->getDataGenerator()->create_user();
        $users2[] = $this->getDataGenerator()->create_user();
        $courseid = $this->set_test_data($users);
        $courseid2 = $this->set_test_data($users2);

        $this->assertCount(6, $DB->get_records('local_xray_roleunas'));
        $this->assertCount(6, $DB->get_records('local_xray_subscribe'));
        $this->assertCount(6, $DB->get_records('local_xray_globalsub'));
        $this->assertCount(6, $DB->get_records('local_xray_enroldel'));
        $this->assertCount(6, $DB->get_records('local_xray_gruserdel'));

        $context = context_course::instance($courseid);
        $contextlist = new \core_privacy\local\request\userlist($context, 'local_xray');
        provider::get_users_in_context($contextlist);
        $this->assertCount(3,$contextlist->get_userids());

        $context = context_course::instance($courseid2);
        $contextlist = new \core_privacy\local\request\userlist($context, 'local_xray');
        provider::get_users_in_context($contextlist);
        $this->assertCount(3,$contextlist->get_userids());

    }

    /**
     * Test that data for users in approved userlist is deleted.
     */
    public function test_delete_data_for_users() {
        global $DB;
        $this->resetAfterTest();
        $users = [];
        $users[] = $this->getDataGenerator()->create_user();
        $users[] = $this->getDataGenerator()->create_user();
        $users[] = $this->getDataGenerator()->create_user();
        $users2 = [];
        $users2[] = $this->getDataGenerator()->create_user();
        $users2[] = $this->getDataGenerator()->create_user();
        $users2[] = $this->getDataGenerator()->create_user();
        $courseid = $this->set_test_data($users);
        $courseid2 = $this->set_test_data($users2);

        $this->assertCount(6, $DB->get_records('local_xray_roleunas'));
        $this->assertCount(6, $DB->get_records('local_xray_subscribe'));
        $this->assertCount(6, $DB->get_records('local_xray_enroldel'));

        $context = context_course::instance($courseid);
        $usersid = [];
        foreach ($users as $user) {
            $usersid[] = $user->id;
        }
        $contextlist = new \core_privacy\local\request\approved_userlist($context, 'local_xray', $usersid);
        provider::delete_data_for_users($contextlist);
        $this->assertCount(3, $DB->get_records('local_xray_roleunas'));
        $this->assertCount(3, $DB->get_records('local_xray_subscribe'));
        $this->assertCount(3, $DB->get_records('local_xray_enroldel'));

        $context = context_course::instance($courseid2);
        $usersid = [];
        foreach ($users2 as $user) {
            $usersid[] = $user->id;
        }
        $contextlist = new \core_privacy\local\request\approved_userlist($context, 'local_xray', $usersid);
        provider::delete_data_for_users($contextlist);
        $this->assertCount(0, $DB->get_records('local_xray_roleunas'));
        $this->assertCount(0, $DB->get_records('local_xray_subscribe'));
        $this->assertCount(0, $DB->get_records('local_xray_enroldel'));
    }
}