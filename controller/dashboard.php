<?php
defined('MOODLE_INTERNAL') or die();
require_once($CFG->dirroot.'/local/xray/controller/reports.php');

/**
 * Dashboard
 * TODO:: This will be implemented in renderer method.
 *
 * @author Pablo Pagnone
 * @package local_xray
 */
class local_xray_controller_dashboard extends local_xray_controller_reports {

    public function init() {
        parent::init();
        $this->courseid = required_param('courseid', PARAM_RAW);		
    }

public function view_action() {
        
        global $PAGE, $DB;
        
        $title = get_string($this->name, $this->component);
        $PAGE->set_title($title);
        $this->heading->text = $title;
        
        // Add title to breadcrumb.
        $PAGE->navbar->add(get_string($this->name, $this->component));
        
        $output = $this->output->course_header($this->courseid);
        
        return $output;
    }
    
}
