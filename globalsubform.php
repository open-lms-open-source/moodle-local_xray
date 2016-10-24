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
require_once($CFG->dirroot.'/local/xray/lib.php');

class globalsub_form extends moodleform {
    public function definition() {
        global $COURSE;

        $mform = $this->_form;

        $options = array(
            XRAYSUBSCRIBECOURSE => get_string('globalsubcourse', 'local_xray'),
            XRAYSUBSCRIBEON => get_string('globalsubon', 'local_xray'),
            XRAYSUBSCRIBEOFF => get_string('globalsuboff', 'local_xray')
        );

        $mform->addElement('html', html_writer::tag('p', get_string('globalsubdesc', 'local_xray')));
        $mform->addElement('select', 'type', '', $options);
        $mform->setDefault(local_xray_controller_globalsub::XRAYSUBSCRIBECOURSE);
        $this->add_action_buttons(false);

    }
}