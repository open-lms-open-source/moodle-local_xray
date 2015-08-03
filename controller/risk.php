<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
require_once($CFG->dirroot.'/local/xray/controller/reports.php');

/**
 * Risk report
 *
 * @author Pablo Pagnone
 * @package local_xray
 */
class local_xray_controller_risk extends local_xray_controller_reports {
 
	public function init() {
		// This report will get data by courseid.
		// TODO:: I am using xraycourseid for prevent validation of if exist course with courseid param.
		$this->xraycourseid = required_param('xraycourseid', PARAM_RAW);
		
		// TODO:: Hardcoded to get of specific course in xray.
		$this->xraycourseid = parent::XRAY_COURSEID; 
	}
	
    public function view_action() {
    	
    	global $PAGE;
    	// Add title to breadcrumb.
        $PAGE->navbar->add("Link to course"); // TODO:: This will be fixed when we work with same db with x-ray side. 	
    	$PAGE->navbar->add(get_string($this->name, $this->component));
    	$output = "";

    	try {
    		$report = "risk";
    		$response = \local_xray\api\wsapi::course($this->xraycourseid, $report);
    		if(!$response) {
    			// Fail response of webservice.
    			throw new Exception(\local_xray\api\xrayws::instance()->geterrormsg());
    			
    		} else {
    			// Show graphs.
    			$output .= $this->risk_measures();
    			$output .= $this->total_risk_profile($response->elements[1]);
    			$output .= $this->academic_vs_social_risk($response->elements[2]);		    	
    		}		 
    	} catch(exception $e) {
    		print_error('error_xray', $this->component,'',null, $e->getMessage());
    	}
    	
    	return $output;
    }

    /**
     * Report Risk measures.(TABLE)
     */
    private function risk_measures() {
    
    	$output = "";
    	$output .= $this->output->risk_risk_measures($this->xraycourseid);
    	return $output;
    }   
    
    public function jsonriskmeasures_action(){

    	global $PAGE;
    	 
    	// Pager
    	$count = optional_param('iDisplayLength', 10, PARAM_RAW);
    	$start  = optional_param('iDisplayStart', 10, PARAM_RAW);
    	 
    	$return = "";
    	
    	try {
    		$report = "risk";
    		$element = "element2";
    		$response = \local_xray\api\wsapi::courseelement($this->xraycourseid,
										    				$element,
										    				$report,
										    				null,
										    				'',
										    				'',
										    				$start,
										    				$count);
    		if(!$response) {
    			throw new Exception(\local_xray\api\xrayws::instance()->geterrormsg());
    		} else {
    			 
    			$data = array();
    			if(!empty($response->data)){
    				foreach($response->data as $row) {
   						
    					$r = new stdClass();					
    					$r->lastname = $row->lastname->value;    						
    					$r->firstname = $row->firstname->value;
    					$r->timespentincourse = $row->timeOnTask->value;
    					$r->academicrisk = $row->fail->value;
    					$r->socialrisk = $row->DW->value;
    					$r->totalrisk = $row->DWF->value;
    					$data[] = $r;
    				}
    			}
    			// Provide count info to table.
    			$return["recordsFiltered"] = $response->itemCount;
    			$return["data"] = $data;
    			
    		}
    	} catch(exception $e) {
    		// TODO:: Send message error to js.
    		$return = "";
    	}
    	 
    	echo json_encode($return);
    	exit();    	
    }
    
    /**
     * Report total risk profile
     */
    private function total_risk_profile($element) {
    
    	$output = "";
    	$output .= $this->output->risk_total_risk_profile($element);
    	return $output;
    }   
    
    /**
     * Report total risk profile
     */
    private function academic_vs_social_risk($element) {
    
    	$output = "";
    	$output .= $this->output->risk_academic_vs_social_risk($element);
    	return $output;
    }    
}
