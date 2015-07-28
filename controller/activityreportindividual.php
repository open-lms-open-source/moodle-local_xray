<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
require_once($CFG->dirroot.'/local/xray/controller/reports.php');

/**
 * Activity Report Individual
 *
 * @author Pablo Pagnone
 * @package local_xray
 */
class local_xray_controller_activityreportindividual extends local_xray_controller_reports {
 
    public function view_action() {
    	
    	global $PAGE, $USER;
    	// Add title to breadcrumb.
    	$PAGE->navbar->add(get_string($this->name, $this->component));
    	$output = "";

    	try {
    		$report = "activity";
    		$response = \local_xray\api\wsapi::course(parent::XRAY_DOMAIN, parent::XRAY_COURSEID, $report, $USER->id);
    		if(!$response) {
    			// Fail response of webservice.
    			throw new Exception(\local_xray\api\xrayws::instance()->geterrormsg());
    			
    		} else {

    			// Show graphs.
    			$output .= $this->activity_by_date($response->elements[1]);
    			$output .= $this->activity_last_two_weeks($response->elements[4]);
    			$output .= $this->activity_last_two_weeks($response->elements[6]);
		    	
    		}		 
    	} catch(exception $e) {
    		print_error('error_xray', $this->component,'',null, $e->getMessage());
    	}
    	
    	return $output;
    }
    
    /**
     * Report Students activity (table).
     *
     */
    private function activity_by_date($element) {
    
    	$output = "";
    	$output .= $this->output->activity_by_date($element);
    	return $output;
    }   
    
    /**
     * Report Activity of course by day.
     *
     */
    private function activity_last_two_weeks($element) {
    	
    	$output = "";
    	$output .= $this->output->activity_last_two_weeks($element);
    	return $output; 
    }
    
    /**
     * Report Activity by time of day.
     *
     */    
    private function activity_last_two_weeks_byday($element) {

    	$output = "";
    	$output .= $this->output->activity_last_two_weeks_byday($element);
    	return $output; 
    }
}
