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

class local_xray_controller_globalsub extends mr_controller {

    public function view_action() {

        global $CFG, $USER, $DB, $OUTPUT;
        $saved = optional_param('saved', 0, PARAM_INT);

        require_once($CFG->dirroot.'/local/xray/locallib.php');
        require_once($CFG->dirroot.'/local/xray/globalsubform.php');

        if (local_xray_email_enable()) {
            $mform = new globalsub_form($this->url);
            if ($currentvalue = $DB->get_record('local_xray_globalsub', array('userid' => $USER->id), 'id, type', IGNORE_MULTIPLE)) {
                $toform = new stdClass();
                $toform->type = $currentvalue->type;
                $mform->set_data($toform);
            }

            // Process data.
            if ($fromform = $mform->get_data()) {
                $globalsub = new stdClass();
                if ($currentvalue) {
                    $globalsub->id = $currentvalue->id;
                    $globalsub->userid = $USER->id;
                    $globalsub->type = $fromform->type;
                    $DB->update_record('local_xray_globalsub', $globalsub);
                } else {
                    $globalsub->userid = $USER->id;
                    $globalsub->type = $fromform->type;
                    $DB->insert_record('local_xray_globalsub', $globalsub);
                }
                $this->url->param('saved', 1);
                redirect($this->url);
            }

            $this->print_header();
            if ($saved) {
                echo $OUTPUT->notification(get_string('changessaved'), 'notifysuccess');
            }
            $mform->display();
            $frequency = get_config('local_xray', 'emailfrequency');
            if ($frequency == XRAYNEVER) {
                echo $OUTPUT->notification(get_string("emailsdisabled", $this->component), 'info');
            }
            $this->print_footer();
        }
    }

    /**
     * Require capabilities.
     */
    public function require_capability() {
        global $USER, $CFG;
        require_once($CFG->dirroot.'/local/xray/locallib.php');
        if (!local_xray_get_teacher_courses($USER->id)) {
            require_capability("{$this->plugin}:globalsub_view", $this->get_context());
        }
    }

    /**
     * Setup.
     */
    public function setup() {
        global $CFG, $PAGE, $COURSE, $USER;

        require_login();

        $PAGE->set_context($this->get_context());
        // Add the heading text.
        $this->heading->text = get_string("globalsubtitle", $this->component);
        // Add title.
        $PAGE->set_title(get_string("globalsubtitle", $this->component));
        $url = new moodle_url('/local/xray/view.php', array('controller' => 'globalsub'));
        $PAGE->set_url($url);
        $PAGE->set_pagelayout('standard');
        // Create navbar.
        if (isset($CFG->defaulthomepage) && ($CFG->defaulthomepage == HOMEPAGE_SITE)) {
            $myurl = new moodle_url($CFG->wwwroot.'/my/');
            $PAGE->navbar->add(get_string("myhome"), $myurl);
        }
        $profileurl = new moodle_url('/user/profile.php', array('id'=>$USER->id));
        $PAGE->navbar->add(get_string("profile"), $profileurl);
        $PAGE->navbar->add(get_string("navigation_xray", $this->component));
        $PAGE->navbar->add(get_string("globalsubtitle", $this->component), $url);
    }

    /**
     * Get controller context
     *
     * @return \context
     */
    public function get_context() {
        global $USER;

        return context_user::instance($USER->id, MUST_EXIST);
    }
}