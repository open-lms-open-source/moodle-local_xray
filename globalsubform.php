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

class globalsub_form extends moodleform {
    public function definition() {
        global $COURSE;

        $mform = $this->_form;

        $options = array(
            local_xray_controller_globalsub::XRAYSUBSCRIBECOURSE => get_string('globalsubcourse', 'local_xray'),
            local_xray_controller_globalsub::XRAYSUBSCRIBEON => get_string('globalsubon', 'local_xray'),
            local_xray_controller_globalsub::XRAYSUBSCRIBEOFF => get_string('globalsuboff', 'local_xray')
        );

        $mform->addElement('html', '<p>'.get_string('globalsubdesc', 'local_xray').'</p>');
        $mform->addElement('select', 'type', '', $options);
        $mform->setDefault(local_xray_controller_globalsub::XRAYSUBSCRIBECOURSE);
        $this->add_action_buttons(false);

    }
}