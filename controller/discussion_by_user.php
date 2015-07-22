<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
require_once($CFG->dirroot.'/local/xray/controller/reports.php');

/**
 * Xray integration Reports Controller
 *
 * @author Pablo Pagnone
 * @author German Vitale
 * @package local_xray
 */
class local_xray_controller_discussion_by_user extends local_xray_controller_reports {

    /**
     * Require capabilities
     */
    public function require_capability() {    
    	
    }

    /**
     * Controller Initialization
     *
     */
    public function init() {
    	
    }
    
    /**
     * Report A example
     * Example: Activity of student by day.
     *
     */
    public function view_action() {
    	
        global $OUTPUT, $PAGE, $COURSE;
        
        // Add title to breadcrumb.
        $PAGE->navbar->add(get_string('report_discussion_by_user', 'local_xray'));
        $output = "";

        $output .= $this->output->discussion_by_user();

        return $output;
    }
}
