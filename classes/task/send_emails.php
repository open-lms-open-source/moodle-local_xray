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
            // Get suscribed.
            $subscribedusers = $DB->get_records('local_xray_subscribe', null, 'courseid');

            $currentid = 0;
            $messagehtml = '';
            foreach ($subscribedusers as $value) {
                // Check if the user has capabilities to receive email.
                if (local_xray_email_capability($value->courseid, $value->userid)) {
                    $to = $DB->get_record('user', array('id' => $value->userid));
                    $from = get_admin();
                    $courseshortname = $DB->get_field('course', 'shortname', array('id' => $value->courseid));
                    $subject = get_string('emailsubject', 'local_xray', $courseshortname);
                    $messagetext = '';

                    // Get messagehtml only if the course change.
                    if ($currentid != $value->courseid) {
                        $headlinedata = local_xray_template_data($value->courseid);

                        if ($headlinedata) {
                            // Add the link to the subscription page.
                            $subscriptionurl = new \moodle_url('/local/xray/view.php', array('controller' => 'subscribe', 'courseid' => $value->courseid));
                            $headlinedata->subscription = \html_writer::link($subscriptionurl, get_string('unsubscribeemail', 'local_xray'));
                            // Add the title.
                            $headlinedata->title = get_string('pluginname', 'local_xray');
                            // Add the data in the template.
                            $messagehtml = $OUTPUT->render_from_template('local_xray/email', $headlinedata);
                        } else {
                            $data = array(
                                'context' => \context_course::instance($value->courseid),
                                'other' => array(
                                    'message' => get_string('erroremailheadline', 'local_xray', $value->courseid)
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
                            'context' => \context_course::instance($value->courseid),
                            'other' => array(
                                'to' => $value->userid
                            )
                        );
                        $event = email_log::create($data);
                        $event->trigger();
                    }
                }
                $currentid = $value->courseid;
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
