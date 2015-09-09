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
    	
    	global $PAGE, $USER, $DB;
    	
    	$output = "";

    	try {
    		$report = "dashboard";
    		$response = \local_xray\api\wsapi::course($this->courseid, $report);

    		if(!$response) {
    			// Fail response of webservice.
    			\local_xray\api\xrayws::instance()->print_error();
    			
    		} else {
    			
    			$count_students_risk = (isset($response->elements[4]->items[5]->value) ? $response->elements[4]->items[5]->value : "-");
    			$count_students_enrolled = (isset($response->elements[4]->items[2]->value) ? $response->elements[4]->items[2]->value : "-");
    			$count_students_visits_lastsevendays = (isset($response->elements[4]->items[0]->value) ? $response->elements[4]->items[0]->value : "-");
    			
    			// TODO:: Get list of students in risk (Pending in webservice).
    			
    			
    			//TODO:: Call to renderer
    			$output .= "";
		    	
    		}		 
    	} catch(exception $e) {
    		print_error('error_xray', $this->component,'',null, $e->getMessage());
    	}
    	
    	return $output;
    }
    
}
