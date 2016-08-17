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
 * Scheduled task for send emails with XRay.
 *
 * @package   local_xray
 * @author    German Vitale
 * @copyright Copyright (c) 2016 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xray\task;

use core\task\scheduled_task;
use local_xray\event\email_log;
use local_xray\event\email_failed;

defined('MOODLE_INTERNAL') || die();

/**
 * Class send_emails - implementation of the task
 *
 * @package   local_xray
 * @copyright Copyright (c) 2016 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class send_emails extends scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('sendemails', 'local_xray');
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        global $CFG, $DB, $OUTPUT;

        require_once($CFG->dirroot.'/local/xray/lib.php');

        try {

            // Create array.
            $coursesusers = array();

            // Get admins subscribed in all courses.
            if ($subscribedall = $DB->get_records_select('local_xray_subscribe', "whole = 1", null, '', 'userid')) {
                $courses = $DB->get_recordset_select('course', "format != 'site'", null, null, 'id');
                foreach($courses as $course) {
                    foreach ($subscribedall as $userid) {
                        $coursesusers[] = array($course->id => $userid->userid);
                    }
                }
            }

            // Get the other users.
            if ($subscribedusers = $DB->get_recordset_select('local_xray_subscribe', "whole IS NULL", null, 'courseid', 'id, courseid, userid')) {
                foreach ($subscribedusers as $record) {
                    $coursesusers[] = array($record->courseid => $record->userid);
                }
            }

            // course id is diferent to null or 0.
            // Send emails to users subscribed only in some courses.
            if ($subscribedusers = $DB->get_records('local_xray_subscribe', null, 'courseid')) {
                $currentid = 0;
                $messagehtml = '';
                foreach ($coursesusers as $value) {

                    $courseid = $first_key = key($value);
                    $userid = $value[$courseid];

                    // Check if the user has capabilities to receive email.
                    if (local_xray_email_capability($courseid, $userid)) {
                        $to = $DB->get_record('user', array('id' => $userid));
                        $from = get_admin();
                        $courseshortname = $DB->get_field('course', 'shortname', array('id' => $courseid));
                        $subject = get_string('emailsubject', 'local_xray', $courseshortname);
                        $messagetext = '';

                        // Get messagehtml only if the course change.
                        if ($currentid != $courseid) {
                            $headlinedata = local_xray_template_data($courseid);

                            if ($headlinedata) {
                                // Add the link to the subscription page.
                                $subscriptionurl = new \moodle_url('/local/xray/view.php', array('controller' => 'subscribe', 'courseid' => $courseid));
                                $headlinedata->subscription = \html_writer::link($subscriptionurl, get_string('unsubscribeemail', 'local_xray'));
                                // Add the title.
                                $headlinedata->title = get_string('pluginname', 'local_xray');
                                // Add the data in the template.
                                $messagehtml = $OUTPUT->render_from_template('local_xray/email', $headlinedata);
                            } else {
                                $data = array(
                                    'context' => \context_course::instance($courseid),
                                    'other' => array(
                                        'message' => get_string('erroremailheadline', 'local_xray', $courseid)
                                    )
                                );
                                $event = email_failed::create($data);
                                $event->trigger();
                                continue;
                            }
                        }
                        // Send Email.
                        $email = email_to_user($to, $from, $subject, $messagetext, $messagehtml);
                        if ($email) {
                            $data = array(
                                'context' => \context_course::instance($courseid),
                                'other' => array(
                                    'to' => $userid
                                )
                            );
                            $event = email_log::create($data);
                            $event->trigger();
                        }
                    }
                    $currentid = $courseid;
                }
            }

        } catch (\Exception $e) {
            if ($DB->get_debug()) {
                $DB->set_debug(false);
            }
            mtrace($e->getMessage());
            mtrace($e->getTraceAsString());
            email_failed::create_from_exception($e)->trigger();
        }
    }
}
