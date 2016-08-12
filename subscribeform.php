<?php
/**
 * Xray Subscription form class.
 *
 * @package   local_xray
 * @author    German Vitale
 * @copyright Copyright (c) 2016 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("$CFG->libdir/formslib.php");

class subscribe_form extends moodleform {
    public function definition() {
        global $COURSE;

        $mform = $this->_form;
        $courseid = $this->_customdata['courseid'];

        if ($courseid == 1) {
            $mform->addElement('advcheckbox', 'subscribeall', get_string('subscribeall', 'local_xray'),
                get_string('coursesubscribedesc', 'local_xray'), null, array(false, true));
            $this->add_action_buttons(false);
        } else {
            $mform->addElement('advcheckbox', 'subscribe', get_string('coursesubscribe', 'local_xray', $COURSE->shortname),
                get_string('coursesubscribedesc', 'local_xray'), null, array(false, true));
            $this->add_action_buttons(false);
        }
    }
}