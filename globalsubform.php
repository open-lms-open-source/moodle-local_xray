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
        global $CFG;
        $mform = $this->_form;

        require_once($CFG->dirroot.'/local/xray/locallib.php');
        $options = array(
            XRAYSUBSCRIBECOURSE => get_string('globalsubcourse', 'local_xray'),
            XRAYSUBSCRIBEON => get_string('globalsubon', 'local_xray'),
            XRAYSUBSCRIBEOFF => get_string('globalsuboff', 'local_xray')
        );

        $description = html_writer::tag('div', get_string('globalsubdesctitle', 'local_xray'),
            array('style' => 'margin-bottom:10px;'));
        $list = html_writer::alist(array(
            get_string('globalsubdescfirst', 'local_xray'),
            get_string('globalsubdescsecond', 'local_xray')
            )
        );

        $mform->addElement('html', $description.$list);
        $mform->addElement('select', 'type', '', $options);
        $mform->setDefault('select', XRAYSUBSCRIBECOURSE);
        $this->add_action_buttons(false);
    }
}