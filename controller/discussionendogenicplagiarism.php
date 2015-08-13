<?php
defined('MOODLE_INTERNAL') or die();
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
		$this->courseid = required_param('courseid', PARAM_STRINGID);
	}
	
    public function view_action() {
    	
    	global $PAGE;
    	
    	$title = get_string($this->name, $this->component);
    	$PAGE->set_title($title);
    	$this->heading->text = $title;
    	
    	// Add title to breadcrumb.
    	$PAGE->navbar->add($title);
    	$output = "";

    	try {
    		$report = "discussionEndogenicPlagiarism";
    		$response = \local_xray\api\wsapi::course(parent::XRAY_COURSEID, $report);
    		if(!$response) {
    			// Fail response of webservice.
    			\local_xray\api\xrayws::instance()->print_error();
    			
    		} else {
    			// Show graphs.
    			$output .= $this->heatmap_endogenic_plagiarism_students($response->elements[1]);
    			$output .= $this->heatmap_endogenic_plagiarism_instructors($response->elements[2]);
		    	
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
