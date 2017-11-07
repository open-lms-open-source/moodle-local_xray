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
 * Event listeners used in this plugin.
 *
 * @package   local_xray
 * @author    Darko MIletic
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright Moodlerooms
 */

defined('MOODLE_INTERNAL') || die();

use local_xray\local\api\course_manager;

/**
 * Listener for course delete event
 *
 * @param \core\event\course_deleted $event
 */
function local_xray_course_deleted(\core\event\course_deleted $event) {
    global $DB;
    $data = [
        'course'      => $event->courseid,
        'timedeleted' => $event->timecreated
    ];
    $DB->insert_record_raw('local_xray_course', $data, false);
    // If the course is in local_xray_selectedcourse table, it should be deleted and saved in X-Ray side.
    \local_xray\local\api\course_manager::check_xray_course_to_delete($event->courseid);
}

/**
 * Listener for course category delete
 *
 * @param \core\event\course_category_deleted $event
 */
function local_xray_course_category_deleted(\core\event\course_category_deleted $event) {
    global $DB;
    $data = [
        'category'    => $event->objectid,
        'timedeleted' => $event->timecreated
    ];
    $DB->insert_record_raw('local_xray_coursecat', $data, false);
}

/**
 * Listener for Moodlerooms forum discussion delete
 *
 * @param \mod_hsuforum\event\discussion_deleted $event
 */
function local_xray_hsu_discussion_deleted(\mod_hsuforum\event\discussion_deleted $event) {
    global $DB;
    $data = [
        'discussion'  => $event->objectid,
        'cm'          => $event->contextinstanceid,
        'timedeleted' => $event->timecreated
    ];
    $DB->insert_record_raw('local_xray_hsudisc', $data, false);
}

/**
 * Listener for Moodlerooms forum post delete
 *
 * @param \mod_hsuforum\event\post_deleted $event
 */
function local_xray_hsu_post_deleted(\mod_hsuforum\event\post_deleted $event) {
    global $DB;
    $data = [
        'post'        => $event->objectid,
        'discussion'  => $event->other['discussionid'],
        'cm'          => $event->contextinstanceid,
        'timedeleted' => $event->timecreated
    ];
    $DB->insert_record_raw('local_xray_hsupost', $data, false);
}

/**
 * Listener for forum discussion delete
 *
 * @param \mod_forum\event\discussion_deleted $event
 */
function local_xray_discussion_deleted(\mod_forum\event\discussion_deleted $event) {
    global $DB;
    $data = [
        'discussion'  => $event->objectid,
        'cm'          => $event->contextinstanceid,
        'timedeleted' => $event->timecreated
    ];
    $DB->insert_record_raw('local_xray_disc', $data, false);
}

/**
 * Listener for forum post delete
 *
 * @param \mod_forum\event\post_deleted $event
 */
function local_xray_post_deleted(\mod_forum\event\post_deleted $event) {
    global $DB;
    $data = [
        'post'        => $event->objectid,
        'discussion'  => $event->other['discussionid'],
        'cm'          => $event->contextinstanceid,
        'timedeleted' => $event->timecreated
    ];
    $DB->insert_record_raw('local_xray_post', $data, false);
}

/**
 * Listener for activity delete from course
 *
 * @param \core\event\course_module_deleted $event
 */
function local_xray_course_module_deleted(\core\event\course_module_deleted $event) {
    global $DB;
    // We handle only gradable activities.
    if (plugin_supports('mod', $event->other['modulename'], FEATURE_GRADE_HAS_GRADE, false)) {
        $data = [
            'cm'          => $event->objectid,
            'course'      => $event->courseid,
            'timedeleted' => $event->timecreated
        ];
        $DB->insert_record_raw('local_xray_cm', $data, false);
    }
}

/**
 * Listener for role unasignment on a course context ONLY!
 *
 * @param \core\event\role_unassigned $event
 * @throws coding_exception
 */
function local_xray_role_unassigned(\core\event\role_unassigned $event) {
    global $DB;
    // Strangely can not use course_context::instance_by_id since it throws exception...
    $courseid = $DB->get_field('context', 'instanceid', ['id' => $event->contextid, 'contextlevel' => CONTEXT_COURSE]);
    if ($courseid) {
        $data = [
            'role'        => $event->objectid,
            'userid'      => $event->relateduserid,
            'course'      => $courseid,
            'timedeleted' => $event->timecreated
        ];
        $DB->insert_record_raw('local_xray_roleunas', $data, false);
    }
}

/**
 * Listener for removal of user enrollment from a course
 * @param \core\event\user_enrolment_deleted $event
 * @throws coding_exception
 */
function local_xray_user_enrolment_deleted(\core\event\user_enrolment_deleted $event) {
    global $DB;
    $data = [
        'enrolid'     => $event->objectid,
        'userid'      => $event->relateduserid,
        'courseid'    => $event->courseid,
        'timedeleted' => $event->timecreated
    ];
    $DB->insert_record_raw('local_xray_enroldel', $data, false);
}

/**
 * Listener for group deletion from a course
 * @param \core\event\group_deleted $event
 * @throws coding_exception
 */
function local_xray_group_deleted(\core\event\group_deleted $event) {
    global $DB;
    $data = [
        'groupid'     => $event->objectid,
        'timedeleted' => $event->timecreated
    ];
    $DB->insert_record_raw('local_xray_groupdel', $data, false);
}

/**
 * Listener for group member removal
 * @param \core\event\group_member_removed $event
 * @throws coding_exception
 */
function local_xray_group_member_removed(\core\event\group_member_removed $event) {
    global $DB;
    $data = [
        'groupid'       => $event->objectid,
        'participantid' => $event->relateduserid,
        'timedeleted'   => $event->timecreated
    ];
    $DB->insert_record_raw('local_xray_gruserdel', $data, false);
}

/**
 * Listener for send email to admin/s when X-Ray Learning Analytics data sync failed.
 * @param \local_xray\event\sync_failed $event
 * @throws coding_exception
 */
function local_xray_sync_failed(\local_xray\event\sync_failed $event) {

    $error = $event->get_description();
    $subject = get_string('syncfailed', 'local_xray');
    // We will send email to each administrator.
    $userfrom = get_admin();
    $admins = get_admins();
    foreach ($admins as $admin) {
        $eventdata                    = new \core\message\message();
        $eventdata->courseid          = SITEID;
        $eventdata->component         = 'moodle';
        $eventdata->name              = 'errors';
        $eventdata->userfrom          = $userfrom;
        $eventdata->userto            = $admin;
        $eventdata->subject           = $subject;
        $eventdata->fullmessage       = $error;
        $eventdata->fullmessageformat = FORMAT_PLAIN;
        $eventdata->fullmessagehtml   = '';
        $eventdata->smallmessage      = '';
        message_send($eventdata);
    }
}

/**
 * Listener for user enrolment updated. Implemented in order to remedy MDL-58079.
 * @param \core\event\user_enrolment_updated $event
 * @return void
 */
function local_xray_user_enrolment_updated(\core\event\user_enrolment_updated $event) {
    global $DB, $CFG;

    // In cases where we need to explicitly enforce this fix we can use:
    // $CFG->local_xray_userenrolfix_force_enable variable.
    // When set to true no version checking will be performed and event code will always be executed.
    if (empty($CFG->local_xray_userenrolfix_force_enable)) {
        // If Moodle 3.3 with fix just skip it.
        if ($CFG->version >= 2017033000) {
            return;
        }

        // Are we within 3.0 - 3.3.dev Moodle range?
        if ($CFG->version >= 2015111600) {
            $versionwithfixes = [
                '30' => 2015111609.02, // 3.0.9+ .
                '31' => 2016052305.03, // 3.1.5+ .
                '32' => 2016120502.03, // 3.2.2+ .
                '33' => null           // 3.3.dev .
            ];
            foreach ($versionwithfixes as $branch => $version) {
                if ($CFG->branch == $branch) {
                    if (!empty($version)) {
                        if ($CFG->version >= $version) {
                            return;
                        }
                    }
                    break;
                }
            }
        }
    }

    $data = [
        'id'           => $event->objectid,
        'timemodified' => $event->timecreated
    ];
    $DB->update_record_raw('user_enrolments', $data);
}

/**
 * @param \core\event\course_category_updated $event
 * @@throws dml_exception
 */
function local_xray_course_category_updated(\core\event\course_category_updated $event) {
    global $DB;
    $disabled = get_config('local_xray', 'disablecoursecatevent');
    if (!empty($disabled)) {
        return;
    }
    $dt = \local_xray\event\course_category_updated_override::createfrom($event);
    if ($dt->is_hide() or $dt->is_show()) {
        // Update timemodified for all courses in the category.
        $path = $DB->get_field('course_categories', 'path', ['id' => $event->objectid]);
        // Get all child categories and hide too.
        $cats = [$event->objectid];
        $count = 1;
        if ($subcats = $DB->get_records_select('course_categories', "path LIKE ?", ["$path/%"], 'id')) {
            foreach ($subcats as $cat) {
                $cats[] = $cat->id;
                $count++;
            }
        }
        // Split update into batches to avoid sending overly large queries to the server.
        // We also avoid executing lot's of queries in the loop.
        try {
            $transaction = $DB->start_delegated_transaction();
            for ($pos = 0, $step = 100; $pos < $count; $pos += $step) {
                list($sql, $params) = $DB->get_in_or_equal(array_slice($cats, $pos, $step), SQL_PARAMS_NAMED);
                $params['t'] = $event->timecreated;
                $DB->execute('UPDATE {course} c SET c.timemodified = :t WHERE c.category '.$sql, $params);
            }
            $transaction->allow_commit();
        } catch (Exception $e) {
            if (!empty($transaction) && !$transaction->is_disposed()) {
                $transaction->rollback($e);
            }
        }
    }
}