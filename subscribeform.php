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
 * @copyright Copyright (c) 2016 Blackboard Inc. (http://www.blackboard.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') or die();

require_once("$CFG->libdir/formslib.php");

/**
 * Class subscribe_form
 */
class subscribe_form extends moodleform {

    public function definition() {
        global $COURSE;
        // Check if settings are enabled.
        $disabled = null;
        if ($this->_customdata['disabled']) {
            $disabled = array('disabled' => 'disabled');
        }

        $mform = $this->_form;
        $mform->addElement(
            'advcheckbox',
            'subscribe',
            get_string('coursesubscribe', 'local_xray', $COURSE->shortname),
            get_string('coursesubscribedesc', 'local_xray'),
            $disabled,
            array(false, true)
        );
        $mform->addElement('submit', 'submitbutton', get_string('savechanges'), $disabled);
    }

}
