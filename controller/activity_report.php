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
    	
    	$output .= $this->students_activiy();
    	$output .= $this->activity_of_course_by_day();

    	/*
    	try {
    		$report = "activity";
    		$response = \local_xray\api\wsapi::course(parent::XRAY_DOMAIN, parent::XRAY_COURSEID, $report);
    		if(!$response) {
    			// TODO:: Evaluate response in error case.
    			$output .= "Error to connect webservice";
    		} else {
    			
		    	// Show graphs.
		    	$output .= $this->students_activiy();
		    	$output .= $this->activity_of_course_by_day();
		    	$output .= $this->activity_by_time_of_day($response->elements[4]);
		    	$output .= $this->activity_last_two_weeks($response->elements[6]);
    	
    		}
    		 
    	} catch(exception $e) {
    		 
    		// TODO:: Evaluate response in error case.
    		$output .= "Error to connect webservice: ".$e->getMessage();
    	}*/
    	


    	
    	return $output;
    }
    
    /**
     * Report Students activity (table).
     *
     */
    private function students_activiy() {
    
    	$output = "";
    	$output .= $this->output->students_activity($response);
    	return $output;
    }   
    
    /**
     * Json for provide data to students_activity table.
     */
    public function jsonstudentsactivity_action() {
    	// TODO:: Implement.
    }
    
    /**
     * Report Activity of course by day.
     *
     */
    private function activity_of_course_by_day() {
    	
    	/*
    	var_dump($element); exit();
    	$output = "";
    	$output .= $this->output->activity_of_course_by_day($element);
    	return $output; */
    	
 
    	$output = "";
    	$report = "activity";
    	$element = "element3";
    	
    	try {
    		$response = \local_xray\api\wsapi::courseelement(parent::XRAY_DOMAIN, parent::XRAY_COURSEID, $element, $report);
    		if(!$response) {
    			// TODO:: Evaluate response in error case.
    			$output .= "Error to connect webservice: ";
    		} else {
    			$output .= $this->output->activity_of_course_by_day($response);
    		}
    	
    	} catch(exception $e) {
    	
    		// TODO:: Evaluate response in error case.
    		$output .= "Error to connect webservice: ".$e->getMessage();
    	}
    	
    	return $output;
    	
    	
    	
    	
    }
    
    /**
     * Report Activity by time of day.
     *
     */    
    private function activity_by_time_of_day($element) {
    	
    	$output = "";
    	$output .= $this->output->activity_by_time_of_day($element);
    	return $output;    	
    }
    
    /**
     * Report Activity last two weeks.
     * @param unknown $element
     */
    private function activity_last_two_weeks($element) {
    	
    	$output = "";
    	$output .= $this->output->activity_last_two_weeks($element);
    	return $output;    	
    }
    
    
}
