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
class local_xray_controller_activity_report extends local_xray_controller_reports {

    /**
     * Require capabilities
     */
    public function require_capability() {
    	
    }
    
    public function view_action(){
    	
    	global $PAGE;
    	// Add title to breadcrumb.
    	$PAGE->navbar->add(get_string('activity_report', 'local_xray'));
    	$output = "";
    	
    	// First report, activity of course by day
    	$output .= $this->activity_of_course_by_day();
    	
    	return $output;
    }
    
    /**
     * Report Students activity (table).
     *
     */
    private function students_activiy() {
    
    	$output = "";
    	$report = "activity";
    	$element = "";
    
    	return $output;
    }   
    
    /**
     * Report Activity of course by day.
     *
     */
    private function activity_of_course_by_day() {
    	
        $output = "";
        $report = "activity";
        $element = "element3";

        try {    
        	$response = \local_xray\api\wsapi::courseelement(parent::XRAY_DOMAIN, parent::XRAY_COURSEID, $element, $report);
        	if(!$response) {
        		// TODO:: Evaluate response in error case.
        		$output .= "Error to connect webservice: ".$e->getMessage();
        	} else {
        		$output .= $this->output->activity_of_course_by_day($response);
        	}
 
        } catch(exception $e) {
        
        	// TODO:: Evaluate response in error case.
        	$output .= "Error to connect webservice: ".$e->getMessage();
        }

        return $output;
    }
    
    
}
