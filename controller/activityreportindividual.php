<?php
defined('MOODLE_INTERNAL') or die();
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
		//$this->courseid = required_param('courseid', PARAM_STRINGID);
		// TODO:: Hardcodeid by test.
		$this->courseid = required_param('xraycourseid', PARAM_RAW);		
		$this->xrayuserid = required_param('xrayuserid', PARAM_RAW);	
	}
	
    public function view_action() {
    	
    	global $PAGE, $USER, $DB;
    	
    	$title = get_string($this->name, $this->component);
    	$PAGE->set_title($title);
    	$this->heading->text = $title;

    	// Add nav to return to activityreport.
    	$PAGE->navbar->add(get_string("activityreport", $this->component), 
    			           new moodle_url('/local/xray/view.php', 
    			           		          array("controller" => "activityreport", 
    			           		          		"courseid" => $this->courseid)));    	
    	$PAGE->navbar->add($title);
    	$output = "";

    	try {
    		$report = "activity";
    		// TODO: Hardcoded id for test.
    		$response = \local_xray\api\wsapi::course(parent::XRAY_COURSEID, $report, $this->xrayuserid);
    		if(!$response) {
    			// Fail response of webservice.
    			throw new Exception(\local_xray\api\xrayws::instance()->geterrormsg());
    			
    		} else {

    			// Show graphs.
    			$output .= $this->output->inforeport($response->reportdate, 
    					                             $DB->get_field('user', 'username', array("id" => $this->xrayuserid)),
    					                             $DB->get_field('course', 'fullname', array("id" => $this->courseid)));
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
