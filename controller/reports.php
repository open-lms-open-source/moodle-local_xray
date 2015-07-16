<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');

/**
 * Xray integration Reports Controller
 *
 * @author Pablo Pagnone
 * @author German Vitale
 * @package local_xray
 */
class local_xray_controller_reports extends mr_controller {

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
     * List of reports (Example using jquery datatable).
     *
     */
    public function list_action() {
        global $OUTPUT, $PAGE, $COURSE;
        
        // Add title to breadcrumb.
        $PAGE->navbar->add(get_string('pluginname', 'local_xray'));
        $output  = $this->output->list_reports();
        return $output;
    }
    
    /**
     * Return json with list of reports.
     */
    public function jsonlist_action() {
        
        echo json_encode(array("data" => local_xray_reports_utils::list_reports()));
        exit();
    }
    
    /**
     * Report A example
     * Example: Activity of student by day.
     *
     */
    public function reportactivityofstudentbyday_action() {
    	
        global $OUTPUT, $PAGE, $COURSE;
        // Add title to breadcrumb.
        $PAGE->navbar->add(get_string('report_activity_of_student_by_day', 'local_xray'));
        $output  = $this->output->report_activity_of_student_by_day();

        return $output;
    }
    
    /**
     * Report B
     *
     */
    public function reportb_action() {
        global $OUTPUT, $PAGE, $COURSE;
        $output = "";
    
    	return $output;
    }
    
    /**
     * Report C
     *
     */
    public function reportc_action() {
        global $OUTPUT, $PAGE, $COURSE;
        $output = "";
    
        return $output;
    }
    
}
