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
		
	public function init() {
		parent::init();
		$this->xraycourseid = required_param('xraycourseid', PARAM_RAW);
		$this->xrayuserid = required_param('xrayuserid', PARAM_RAW);	
	}
	
    public function view_action() {
    	
    	global $PAGE, $USER, $DB;
    	
    	// Add title to breadcrumb.
    	$PAGE->navbar->add("Link to course"); // TODO:: This will be fixed when we work with same db with x-ray side.
    	$title = get_string($this->name, $this->component);
    	$PAGE->set_title($title);
    	// Add nav to return to activityreport.
    	$PAGE->navbar->add(get_string("activityreport", $this->component), 
    			           new moodle_url('/local/xray/view.php', 
    			           		          array("controller" => "activityreport", "xraycourseid" => $this->xraycourseid)));    	
    	$PAGE->navbar->add($title);
    	$output = "";

    	try {
    		$report = "activity";
    		$response = \local_xray\api\wsapi::course($this->xraycourseid, $report, $this->xrayuserid);
    		if(!$response) {
    			// Fail response of webservice.
    			throw new Exception(\local_xray\api\xrayws::instance()->geterrormsg());
    			
    		} else {

    			// Show graphs.
    			$output .= $this->output->inforeport($response->reportdate, 
    					                             $DB->get_field('user', 'username', array("id" => $this->xrayuserid)),
    					                             $DB->get_field('course', 'fullname', array("id" => $this->xraycourseid)));
    			$output .= $this->activity_by_date($response->elements[1]);
    			$output .= $this->activity_last_two_weeks($response->elements[3]);
    			$output .= $this->activity_last_two_weeks_byweekday($response->elements[4]);
		    	
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
    	$output .= $this->output->activityreportindividual_activity_by_date($element);
    	return $output;
    }   
    
    /**
     * Report Activity of course by day.
     *
     */
    private function activity_last_two_weeks($element) {
    	
    	$output = "";
    	$output .= $this->output->activityreportindividual_activity_last_two_weeks($element);
    	return $output; 
    }
    
    /**
     * Report Activity by time of day.
     *
     */    
    private function activity_last_two_weeks_byweekday($element) {

    	$output = "";
    	$output .= $this->output->activityreportindividual_activity_last_two_weeks_byday($element);
    	return $output; 
    }
}
