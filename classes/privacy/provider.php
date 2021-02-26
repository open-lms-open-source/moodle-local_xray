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
 * Privacy class for requesting user data.
 *
 * @package    local_xray
 * @author     Jonathan Garcia Gomez jonathan.garcia@openlms.net
 * @copyright  Copyright (c) 2018 Open LMS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xray\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\metadata\provider as metadataprovider;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\plugin\provider as pluginprovider;
use core_privacy\local\request\transform;
use core_privacy\local\request\writer;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\core_userlist_provider;


/**
 * Privacy class for requesting user data.
 *
 * @package    local_xray
 * @author     Jonathan Garcia Gomez
 * @copyright  Copyright (c) 2018 Open LMS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements metadataprovider, pluginprovider, core_userlist_provider {

    public static function get_metadata(collection $collection) : collection {

        $collection->add_external_location_link('xray', [
            'userid' => 'privacy:metadata:xray:userid',
            'enroleid' => 'privacy:metadata:xray:enroleid',
            'forumname' => 'privacy:metadata:xray:forumname',
            'forumintro' => 'privacy:metadata:xray:forumintro',
            'timemodified' => 'privacy:metadata:xray:forumtimemodified',
            'finalgrade' => 'privacy:metadata:xray:gradesfinalgrade',
            'loggeduser' => 'privacy:metadata:xray:grades_history:loggeduser',
            'groupid' => 'privacy:metadata:xray:groups_members:groupid',
            'message' => 'privacy:metadata:xray:message',
            'subject' => 'privacy:metadata:xray:subject',
            'username' => 'privacy:metadata:xray_userlistv2:username',
            'firstname' => 'privacy:metadata:xray_userlistv2:firstname',
            'lastname' => 'privacy:metadata:xray_userlistv2:lastname',
            'gender' => 'privacy:metadata:xray_userlistv2:gender',
            'email' => 'privacy:metadata:xray_userlistv2:email',
            'timecreated' => 'privacy:metadata:xray_userlistv2:timecreated',
            'firstaccess' => 'privacy:metadata:xray_userlistv2:firstaccess',
            'lastaccess' => 'privacy:metadata:xray_userlistv2:lastaccess',
            'ip' => 'privacy:metadata:xray_accesslog:ip',
            'action' => 'privacy:metadata:xray_accesslog:action',
            'time' => 'privacy:metadata:xray_accesslog:time',
            'attempts' => 'privacy:metadata:xray_quiz:attempts',
        ], 'privacy:metadata:xray');

        $collection->add_database_table('local_xray_roleunas', [
            'role' => 'privacy:metadata:xray_roleunas:role',
            'userid' => 'privacy:metadata:xray_roleunas:userid',
            'course' => 'privacy:metadata:xray_roleunas:course',
            'timedeleted' => 'privacy:metadata:xray_roleunas:timedeleted'
        ], 'privacy:metadata:xray_roleunas');

        $collection->add_database_table('local_xray_subscribe', [
            'userid' => 'privacy:metadata:xray_subscribe:userid',
            'courseid' => 'privacy:metadata:xray_subscribe:course'
        ], 'privacy:metadata:xray_suscribe');

        $collection->add_database_table('local_xray_globalsub', [
            'userid' => 'privacy:metadata:xray_globalsub:userid',
            'type' => 'privacy:metadata:xray_globalsub:type'
        ], 'privacy:metadata:xray_globalsub');

        $collection->add_database_table('local_xray_enroldel', [
            'enrollid' => 'privacy:metadata:xray_enroldel:enrolid',
            'userid' => 'privacy:metadata:xray_enroldel:userid',
            'courseid' => 'privacy:metadata:xray_enroldel:courseid',
            'timedeleted' => 'privacy:metadata:xray_enroldel:timedeleted'
        ], 'privacy:metadata:xray_enroldel');

        $collection->add_database_table('local_xray_gruserdel', [
            'groupid' => 'privacy:metadata:xray_gruserdel:groupid',
            'participantid' => 'privacy:metadata:xray_gruserdel:participantid',
            'timedeleted' => 'privacy:metadata:xray_gruserdel:timedeleted'
        ], 'privacy:metadata:xray_gruserdel');

        return $collection;
    }

    /**
     * Returns all of the contexts that has information relating to the userid.
     *
     * @param  int $userid The user ID.
     * @return contextlist an object with the contexts related to a userid.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        $sql = "SELECT cx.id
                  FROM {context} cx
             LEFT JOIN {local_xray_roleunas} role ON role.userid = cx.instanceid 
             LEFT JOIN {local_xray_subscribe} subs ON subs.userid = cx.instanceid
             LEFT JOIN {local_xray_globalsub} globalsub ON globalsub.userid = cx.instanceid
             LEFT JOIN {local_xray_enroldel} enroldel ON enroldel.userid = cx.instanceid
             LEFT JOIN {local_xray_gruserdel} gruserdel ON gruserdel.participantid = cx.instanceid
                 WHERE cx.instanceid = :userid AND cx.contextlevel = :usercontext
              GROUP BY cx.id";
        $params = [
            'userid' => $userid,
            'usercontext' => CONTEXT_USER
        ];

        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, $params);
        return $contextlist;
    }

    /**
     * Write out the user data filtered by contexts.
     *
     * @param approved_contextlist $contextlist contexts that we are writing data out from.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        if (empty($contextlist)) {
            return;
        }
        $userid = $contextlist->get_user()->id;
        $context = \context_user::instance($userid);
        if (!in_array($context->id, $contextlist->get_contextids())) {
            return;
        }
        static::export_role_unassignments($userid, $context);
        static::export_subscriptions($userid, $context);
        static::export_global_subscriptions($userid, $context);
        static::export_enrol_deletions($userid, $context);
        static::export_groupmember_deletions($userid, $context);
    }

    /**
     * Export the role assignments deletion data.
     *
     * @param   int         $userid The userid of the user whose data is to be exported.
     * @return  array       Which forums had data written for them.
     */
    protected static function export_role_unassignments($userid, $context) {
        global $DB;
        $data = $DB->get_records('local_xray_roleunas', array('userid' =>$userid));
        $exportdata = [];
        foreach ($data as $record) {
            $exportdata[] = (object) [
                'course' => $record->course,
                'role' => $record->role,
                'timedeleted' => transform::datetime($record->timedeleted)
            ];
        }
        if (!empty($exportdata)) {
            writer::with_context($context)->export_data(['local_xray/role_unassignments'], (object) ['role_unassignments' => $exportdata]);
        }
        return $exportdata;
    }

    /**
     * Export the user subscription data.
     *
     * @param   int         $userid The userid of the user whose data is to be exported.
     * @return  array       Which forums had data written for them.
     */
    protected static function export_subscriptions($userid, $context) {
        global $DB;
        $data = $DB->get_records('local_xray_subscribe', array('userid' => $userid));
        $exportdata = [];
        foreach ($data as $record) {
            $exportdata[] = (object) [
                'course' => $record->courseid
            ];
        }
        if (!empty($exportdata)) {
            writer::with_context($context)->export_data(['local_xray/subscriptions'],
                (object) ['subscriptions' => $exportdata]);
        }

        return $exportdata;
    }

    /**
     * Export the global subscription configuration for the given user.
     *
     * @param   int         $userid The userid of the user whose data is to be exported.
     * @return  array       Which forums had data written for them.
     */
    protected static function export_global_subscriptions($userid, $context) {
        global $DB;
        $data = $DB->get_records('local_xray_globalsub', array('userid' => $userid));
        $exportdata = [];
        foreach ($data as $record) {
            switch ($record->type) {
                case 0: $type = get_string('courselevel_subscription', 'local_xray');
                    break;
                case 1: $type = get_string('no_subscription', 'local_xray');
                    break;
                case 2: $type = get_string('all_subscription', 'local_xray');
                    break;
            }
            $exportdata[] = (object) [
                'type' => $type
            ];
        }
        if (!empty($exportdata)) {
            writer::with_context($context)->export_data(['local_xray/globalsubs'],
                (object) ['global_subscriptions' => $exportdata]);
        }
        return $exportdata;
    }

    /**
     * Export the enrolment deletion records fot the given user.
     *
     * @param   int         $userid The userid of the user whose data is to be exported.
     * @return  array       Which forums had data written for them.
     */
    protected static function export_enrol_deletions($userid, $context) {
        global $DB;
        $data = $DB->get_records('local_xray_enroldel', array('userid' => $userid));
        $exportdata = [];
        foreach ($data as $record) {
            $exportdata[] = (object) [
                'course' => $record->courseid,
                'enrolment_id' => $record->enrolid,
                'time_deleted' => transform::datetime($record->timedeleted)
            ];
        }
        if (!empty($exportdata)) {
            writer::with_context($context)->export_data(['local_xray/enrol_deletions'],
                (object) ['enrol_deletions' => $exportdata]);
        }
        return $exportdata;
    }

    /**
     *  Export the related data to group members deletions
     *
     * @param   int         $userid The userid of the user whose data is to be exported.
     * @return  array       Which forums had data written for them.
     */
    protected static function export_groupmember_deletions($userid, $context) {
        global $DB;
        $data = $DB->get_records('local_xray_gruserdel', array('participantid' => $userid));
        $exportdata = [];
        foreach ($data as $record) {
            $exportdata[] = (object) [
                'group_id' => $record->groupid,
                'time_deleted' => transform::datetime($record->timedeleted)
            ];
        }
        if (!empty($exportdata)) {
            writer::with_context($context)->export_data(['local_xray/groupmemeber_deletions'],
                (object) ['group_deletions' => $exportdata]);
        }
        return $exportdata;
    }

    /**
     * Delete all use data which matches the specified context.
     *
     * @param context $context The course context.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;
        if (empty($context)) {
            return;
        }
        if ($context->contextlevel != CONTEXT_COURSE) {
            return;
        }
        $courseid = $context->instanceid;
        $DB->delete_records('local_xray_roleunas', ['course' => $courseid]);
        $DB->delete_records('local_xray_subscribe', ['courseid' => $courseid]);
        $DB->delete_records('local_xray_enroldel', ['courseid' => $courseid]);
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;
        $userid = $contextlist->get_user()->id;
        $context = \context_user::instance($userid);
        if (empty($context)) {
            return;
        }
        if ($context->contextlevel != CONTEXT_USER) {
            return;
        }
        $userid = $context->instanceid;
        $DB->delete_records('local_xray_roleunas', ['userid' => $userid]);
        $DB->delete_records('local_xray_subscribe', ['userid' => $userid]);
        $DB->delete_records('local_xray_globalsub', ['userid' => $userid]);
        $DB->delete_records('local_xray_enroldel', ['userid' => $userid]);
        $DB->delete_records('local_xray_gruserdel', ['participantid' => $userid]);
    }

    /**
     * Get the list of users within a specific context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {

        $context = $userlist->get_context();
        if ($context->contextlevel != CONTEXT_COURSE) {
            return;
        }

        $courseid = $context->instanceid;

        $sql = "SELECT DISTINCT ru.userid as userid
                           FROM {local_xray_roleunas} ru
                          WHERE ru.course = :course";

        $userlist->add_from_sql('userid', $sql, ['course' => $courseid]);

        $sql = "SELECT DISTINCT su.userid as userid
                           FROM {local_xray_subscribe} su
                          WHERE su.courseid = :courseid";

        $userlist->add_from_sql('userid', $sql, ['courseid' => $courseid]);

        $sql = "SELECT DISTINCT ed.userid as userid
                           FROM {local_xray_enroldel} ed
                          WHERE ed.courseid = :courseid";

        $userlist->add_from_sql('userid', $sql, ['courseid' => $courseid]);

    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();
        if ($context->contextlevel != CONTEXT_COURSE) {
            return;
        }
        $courseid = $context->instanceid;
        $usersid = $userlist->get_userids();

        foreach ($usersid as $userid) {
            $DB->delete_records('local_xray_roleunas', ['userid' => $userid, 'course' => $courseid]);
            $DB->delete_records('local_xray_subscribe', ['userid' => $userid, 'courseid' => $courseid]);
            $DB->delete_records('local_xray_enroldel', ['userid' => $userid, 'courseid' => $courseid]);
        }
    }

}
