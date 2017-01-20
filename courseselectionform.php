<?php
/**
 * Xray Course Selection form class.
 *
 * @package   local_xray
 * @author    David Castro
 * @copyright Copyright (c) 2016 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("$CFG->libdir/formslib.php");
require_once($CFG->dirroot.'/local/xray/lib.php');

class courseselection_form extends moodleform {
    
    const COMP_ID = 'courses';
    const PLUGIN = 'local_xray';
    
    public function definition() {

        $mform = $this->_form;

        // Hidden field with csv based values
        $mform->addElement('hidden', 'joined_'.self::COMP_ID, '', '');
        $mform->setType('joined_'.self::COMP_ID, PARAM_SEQUENCE);
        
        // Hidden field to receive array of courses
        $mform->addElement('hidden', self::COMP_ID, '', '');
        $mform->setType(self::COMP_ID, PARAM_RAW);
        
        $mform->addElement('html', $this->buildContainer());
        
        $this->add_action_buttons(false);
    }
    
    private function buildContainer() {
        $output = '';
        
        // Usage instructions
        $output .= '<p>'.get_string('xraycourses_instructions', 'local_xray').'</p>';
        
        // Categories and courses container
        $output .= '<ul id="cat_0_children">'
                .'<p><div class="xray_validate_loader"></div>'.(new lang_string('loading_please_wait', self::PLUGIN)).'</p>'
                .'</ul>';
        
        return $output;
    }
}