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
            // Check frequency.
            $currentdayofweek = date("w");
            // Get current day of week.
            $frequency = get_config('weekly', 'local_xray');
            // If the frequency is daily, the email should be sent.
            // If the frequency is weekly, check if the current day of week is Sunday.
            if ($frequency == 'daily' || ($frequency == 'weekly' && $currentdayofweek == 0)) {
                // Create an array with courses and users.
                $coursesusers = array();
                if (local_xray_email_enable()) {
                    // Global settings.
                    $params = array('on' => XRAYSUBSCRIBEON, 'off' => XRAYSUBSCRIBEOFF);
                    $globalsettings = $DB->get_records_select('local_xray_globalsub', "type = :on OR type = :off", $params);

                    $skipusers = array();
                    if ($globalsettings) {
                        foreach ($globalsettings as $record) {
                            if ($record->type == XRAYSUBSCRIBEON) {
                                // If is Admin.
                                if (array_key_exists($record->userid, get_admins())) {
                                    $courses = get_courses("all", "c.sortorder ASC", "c.id");
                                    foreach ($courses as $course) {
                                        if ($course->id != SITEID) {
                                            $coursesusers[] = array($course->id => $record->userid);
                                        }
                                    }
                                } else if ($courses = local_xray_get_teacher_courses($record->userid)) {
                                    foreach ($courses as $course) {
                                        $coursesusers[] = array($course->courseid => $record->userid);
                                    }
                                }
                            }
                            // Skip these users in the next search.
                            $skipusers[] = $record->userid;
                        }
                    }
                    // Get the other users.
                    $select = '';
                    $increase = 1;
                    if ($skipusers) {
                        foreach ($skipusers as $skipuser) {
                            $select .= 'userid <> '.$skipuser;
                            if ($increase < count($skipusers)) {
                                $select .= ' AND ';
                            }
                            $increase++;
                        }
                    }
                    if ($subscribedusers = $DB->get_recordset_select('local_xray_subscribe', $select, null, 'courseid', 'id, courseid, userid')) {
                        foreach ($subscribedusers as $record) {
                            $coursesusers[] = array($record->courseid => $record->userid);
                        }
                    }
                }

                // Course ID is diferent to null or 0.
                // Send emails to users subscribed only in some courses.
                if ($coursesusers) {
                    $messagehtml = '';
                    foreach ($coursesusers as $value) {

                        $courseid = $first_key = key($value);
                        $userid = $value[$courseid];

                        // Check if the user has capabilities to receive email.
                        if (local_xray_email_capability($courseid, $userid)) {
                            $to = $DB->get_record('user', array('id' => $userid));
                            $from = local_xray_get_support_user();
                            $courseshortname = $DB->get_field('course', 'shortname', array('id' => $courseid));
                            $subject = get_string('emailsubject', 'local_xray', $courseshortname);
                            $messagetext = '';

                            $headlinedata = local_xray_template_data($courseid, $userid);

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
                    }
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
