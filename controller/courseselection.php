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
 * Course selection controller class
 *
 * @package   local_xray
 * @copyright Copyright (c) 2016 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/* @var stdClass $CFG */
require_once($CFG->libdir.'/adminlib.php');

/**
 * Xray course selection controller
 *
 * @author    David Castro
 * @package   local_xray
 * @copyright Copyright (c) 2016 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_xray_controller_courseselection extends mr_controller_admin {
    
    const PLUGIN = 'local_xray';

    /**
     * Set heading
     */
    protected function init() {
        $this->heading->set('courseselection');
    }
    
    /**
     * 
     */
    public function admin_setup() {
        admin_externalpage_setup('courseselection_view');
    }
    
    private function require_js_libs(){
        global $PAGE, $CFG;
        
        // Load Jquery.
        $PAGE->requires->jquery();
        // Load specific js for validation.
        $PAGE->requires->jquery_plugin('local_xray-config_toggle_categories', self::PLUGIN);
        // Initialize js
        $data = array(
            'lang_strs' => array(
                'loading_please_wait' => new lang_string('loading_please_wait', self::PLUGIN)
            ),
            'www_root' => $CFG->wwwroot
        );
        $strdata = array(json_encode($data));
        $PAGE->requires->js_init_call('config_toggle_categories', $strdata);
    }
    
    /**
     * 
     */
    public function view_action() {
        $login_result = \local_xray\local\api\wsapi::login();
        if (!$login_result) {
            global $OUTPUT;
            
            $globalset_url = new moodle_url('/admin/settings.php',
                array('section'         => self::PLUGIN.'_global'));
            
            $globalset_link = '&nbsp;<a href="'.$globalset_url->out(false).'">'
                    .new lang_string('xray_check_global_settings_link', self::PLUGIN)
                    .'</a>';
            
            return $OUTPUT->notification(
                new lang_string('xray_check_global_settings', self::PLUGIN)
                .$globalset_link, 'warning');
        }
        
        $this->require_js_libs();
        
        global $CFG, $DB, $OUTPUT;
        $saved = optional_param('saved', 0, PARAM_INT);
        
        $selcourTable = 'local_xray_selectedcourse';

        require_once($CFG->dirroot.'/local/xray/lib.php');
        require_once($CFG->dirroot.'/local/xray/courseselectionform.php');
        
        // Prepare the form
        $mform = new courseselection_form($this->url);
        if ($currentvalue = $DB->get_records($selcourTable)) {
            $toform = new stdClass();
            $formVal = array();
            foreach ($currentvalue as $selCourse) {
                $formVal[] = $selCourse->cid;
            }
            $toform->joined_courses = implode(',',$formVal);
            $mform->set_data($toform);
        }
        
        if ($fromform = $mform->get_data()) {
            $courseselection = array();
            if($fromform->joined_courses && $fromform->joined_courses !== '') {
                $courseselection = explode(',',$fromform->joined_courses);
            }
            
            \local_xray\local\api\course_manager::save_selected_courses($courseselection);
            
            $this->url->param('action', 'view');
            $this->url->param('saved', 1);
            redirect($this->url);
        }
        
        // Print all output
        $output = '';
        if ($saved) {
            $output .= $OUTPUT->notification(get_string('changessaved'), 'notifysuccess');
        }
        if (!\local_xray\local\api\course_manager::courses_match()) {
            $output .= $OUTPUT->notification(get_string('warn_courses_do_not_match', 'local_xray'), 'notifymessage');
        }
        $output .= $mform->render();
        return $this->output->box($output, 'boxwidthwide');
    }
    
}
