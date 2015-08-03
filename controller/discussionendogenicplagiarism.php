<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
require_once($CFG->dirroot.'/local/xray/controller/reports.php');

/**
 * Discussion Endogenic Plagiarism.
 *
 * @author Pablo Pagnone
 * @package local_xray
 */
class local_xray_controller_discussionendogenicplagiarism extends local_xray_controller_reports {
 
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
    		$report = "discussionEndogenicPlagiarism";
    		$response = \local_xray\api\wsapi::course($this->xraycourseid, $report);
    		if(!$response) {
    			// Fail response of webservice.
    			throw new Exception(\local_xray\api\xrayws::instance()->geterrormsg());
    			
    		} else {
    			// Show graphs.
    			$output .= $this->heatmap_endogenic_plagiarism_students($response->elements[0]);
    			$output .= $this->heatmap_endogenic_plagiarism_instructors($response->elements[1]);
		    	
    		}		 
    	} catch(exception $e) {
    		print_error('error_xray', $this->component,'',null, $e->getMessage());
    	}
    	
    	return $output;
    }

    /**
     * Report Heatmap for students.
     */
    private function heatmap_endogenic_plagiarism_students($element) {
    
    	$output = "";
    	$output .= $this->output->discussionendogenicplagiarism_heatmap_endogenic_plagiarism_students($element);
    	return $output;
    }   
    
    /**
     * Report Heatmap for instructors.
     */
    private function heatmap_endogenic_plagiarism_instructors($element) {
    
    	$output = "";
    	$output .= $this->output->discussionendogenicplagiarism_heatmap_endogenic_plagiarism_instructors($element);
    	return $output;
    }   
}
