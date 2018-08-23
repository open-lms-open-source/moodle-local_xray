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
 * Xray Course Selection form class.
 *
 * @package   local_xray
 * @author    David Castro
 * @copyright Copyright (c) 2016 Blackboard Inc. (http://www.blackboardopenlms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

/**
 * Class courseselection_form
 */
class courseselection_form extends moodleform {

    const COMP_ID = 'courses';
    const PLUGIN = 'local_xray';

    public function definition() {

        $mform = $this->_form;

        // Hidden field with csv based values.
        $mform->addElement('hidden', 'joined_'.self::COMP_ID, '', '');
        $mform->setType('joined_'.self::COMP_ID, PARAM_RAW);

        // Hidden field to receive array of courses.
        $mform->addElement('hidden', self::COMP_ID, '', '');
        $mform->setType(self::COMP_ID, PARAM_RAW);

        $mform->addElement('html', $this->build_container());

        $this->add_action_buttons(false);
    }

    private function build_container() {
        $output = '';

        $output .= '<div class="form-inline">';
        $output .= '<button id="xrayexpandallbtn" class="btn btn-link">'.get_string('expand_all', self::PLUGIN).'</button>';
        $output .= '<button id="xraycollapseallbtn" class="btn btn-link">'.get_string('collapse_all', self::PLUGIN).'</button>';
        $output .= '</div>';

        // Categories and courses container.
        $output .= '<ul id="cat_0_children" class="xray-category-tree">'
                .'<p><div class="xray_validate_loader"></div>'.(new lang_string('loading_please_wait', self::PLUGIN)).'</p>'
                .'</ul>';

        return $output;
    }
}
