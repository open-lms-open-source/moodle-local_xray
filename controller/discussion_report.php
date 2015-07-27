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
class local_xray_controller_discussion_report extends local_xray_controller_reports {

    /**
     * Require capabilities
     */
    public function require_capability() {
    	// TODO: To determinate.
    }
    
    public function view_action() {
    	
    	global $PAGE;
    	// Add title to breadcrumb.
    	$PAGE->navbar->add(get_string('discussion_report', 'local_xray'));
    	$output = "";

    	try {
    		$report = "discussion";
    		$response = \local_xray\api\wsapi::course(parent::XRAY_DOMAIN, parent::XRAY_COURSEID, $report);
    		if(!$response) {
    			// Fail response of webservice.
    			throw new Exception(\local_xray\api\xrayws::instance()->geterrormsg());
    			
    		} else {

    			// Show graphs.
    			//$output .= $this->students_activity(); // Its a table, I will get info with new call.
    			$output .= $this->average_words_weekly_by_post($response->elements[5]);
    			$output .= $this->social_structure($response->elements[9]);
    			$output .= $this->social_structure_with_words_count($response->elements[10]);
    			$output .= $this->social_structure_with_contributions_adjusted($response->elements[11]);
    			$output .= $this->social_structure_coefficient_of_critical_thinking($response->elements[12]);
    			//$output .= $this->first_login_non_starters();
    			//$output .= $this->first_login_to_course();
    			//$output .= $this->first_login_date_observed();
		    	
    		}		 
    	} catch(exception $e) {
    		print_error('error_xray', 'local_xray','',null, $e->getMessage());
    	}
    	
    	return $output;
    }
    
    /**
     * Report "A summary table to be added" (table).
     *
     *//*
    private function students_activity() {
    
    	$output = "";
    	$output .= $this->output->students_activity();
    	return $output;
    } */  
    
    /**
     * Json for provide data to students_activity table.
     */
    /*public function jsonstudentsactivity_action() {
    	
    	// TODO:: Review , implement search, sortable, pagination.
    	$return = array();
    	try {
    		$report = "activity";
    		$element = "element1";
    		$response = \local_xray\api\wsapi::course(parent::XRAY_DOMAIN, parent::XRAY_COURSEID, $report);
    		if(!$response) {
    			// TODO:: Fail response of webservice.
    			throw new Exception(\local_xray\api\xrayws::instance()->geterrormsg());
    			 
    		} else {
    			if(!empty($response->elements[0]->data)){
    				foreach($response->elements[0]->data as $row) {
    					$r = new stdClass();
    					$r->firstname = $row->firstname->value;
    					$r->lastname = $row->lastname->value;
    					$r->lastactivity = $row->last_activity->value;
    					$r->discussionposts = $row->discussion_posts->value;
    					$r->postslastweek = $row->discussion_posts_last_week->value;
    					$r->timespentincourse = $row->timeOnTask->value;
    					$r->regularity = $row->regularity->value;
    					$return[] = $r;
    				}
    			}
    			
    		}
    	} catch(exception $e) {
    		// TODO:: Send message error to js.
    		$return = "";
    	}
    	
    	echo json_encode($return);
    	exit();
    }*/
    
    /**
     * Report Average Words Weekly by Post.
     *
     */
    private function average_words_weekly_by_post($element) {
    	
    	$output = "";
    	$output .= $this->output->average_words_weekly_by_post($element);
    	return $output; 
    }
    
    /**
     * Report Social Structure.
     *
     */    
    private function social_structure($element) {

    	$output = "";
    	$output .= $this->output->social_structure($element);
    	return $output; 
    }
    
    /**
     * Report Social Structure With Words Count.
     * @param unknown $element
     */
    private function social_structure_with_words_count($element) {

    	$output = "";
    	$output .= $this->output->social_structure_with_words_count($element);
    	return $output;
    }
    
    /**
     * Report Social Structure With Contributions Adjusted
     */
    private function social_structure_with_contributions_adjusted($element) {
    
    	$output = "";
    	$output .= $this->output->social_structure_with_contributions_adjusted($element);
    	return $output;
    }   
    
    /**
     * Report Social Structure Coefficient of Critical Thinking
     */
    private function social_structure_coefficient_of_critical_thinking($element) {
    
    	$output = "";
    	$output .= $this->output->social_structure_coefficient_of_critical_thinking($element);
    	return $output;
    }   
}
