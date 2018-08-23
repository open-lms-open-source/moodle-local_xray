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
 * Xray Subscription form class.
 *
 * @package   local_xray
 * @author    German Vitale
 * @copyright Copyright (c) 2016 Blackboard Inc. (http://www.blackboardopenlms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') or die();

require_once("$CFG->libdir/formslib.php");

/**
 * Class globalsub_form
 */
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
