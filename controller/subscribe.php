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
 * Xray integration Reports Controller
 *
 * @package   local_xray
 * @author    Pablo Pagnone
 * @author    German Vitale
 * @copyright Copyright (c) 2016 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/* @var stdClass $CFG */

class local_xray_controller_subscribe extends mr_controller {

    public function view_action() {

        Global $CFG, $USER, $DB, $PAGE;
        $courseid = required_param('courseid', PARAM_INT);

        require_once($CFG->dirroot.'/local/xray/subscribeform.php');

        // Add the heading text.
        $this->heading->text = get_string("subscriptiontitle", $this->component);
        // Add title.
        $PAGE->set_title(get_string("subscriptiontitle", $this->component));
        // Add params in URL.
        $params = array('controller' => 'subscribe');
        $this->url->params($params);

        $mform = new subscribe_form($this->url, array('courseid' => $courseid));

        // Create navbar.
        $PAGE->navbar->add(get_string("navigation_xray", $this->component));
        $PAGE->navbar->add(get_string("subscriptiontitle", $this->component), $this->url);

        // Process data.
        if ($fromform = $mform->get_data()) {

            $exists = false;
            if ($DB->record_exists('local_xray_subscribe', array('courseid' => $courseid, 'userid' => $USER->id))) {
                $exists = true;
            }
            if ($fromform->subscribe && !$exists) {
                $subscribed = new stdClass();
                $subscribed->courseid = $courseid;
                $subscribed->userid = $USER->id;
                $DB->insert_record('local_xray_subscribe', $subscribed);
            } else if (!$fromform->subscribe && $exists) {
                $DB->delete_records('local_xray_subscribe', array('courseid' => $courseid, 'userid' => $USER->id));
            }
        }

        // Set the current value.
        if ($DB->get_records('local_xray_subscribe', array('courseid' => $courseid, 'userid' => $USER->id))) { // TODO.
            $toform = new stdClass();
            $toform->subscribe = 1;
            $mform->set_data($toform);
        }

        $this->print_header();
        $mform->display();
        $this->print_footer();
    }

}