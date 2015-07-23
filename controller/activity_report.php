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
    	// TODO: To determinate.
    }
    
    public function view_action(){
    	
    	global $PAGE;
    	// Add title to breadcrumb.
    	$PAGE->navbar->add(get_string('activity_report', 'local_xray'));
    	$output = "";

    	try {
    		$report = "activity";
    		$response = \local_xray\api\wsapi::course(parent::XRAY_DOMAIN, parent::XRAY_COURSEID, $report);
    		if(!$response) {
    			// Fail response of webservice.
    			throw new Exception(\local_xray\api\xrayws::geterrormsg());
    			
    		} else {
    			
		    	// Show graphs.
		    	$output .= $this->students_activity(); // Its a table, I will get info with new call.
		    	$output .= $this->activity_of_course_by_day($response->elements[1]);
		    	$output .= $this->activity_by_time_of_day($response->elements[4]);
		    	$output .= $this->activity_last_two_weeks($response->elements[6]);
		    	$output .= $this->activity_last_two_weeks_by_weekday($response->elements[7]);	    	
		    	$output .= $this->activity_by_participant1($response->elements[9]);
		    	$output .= $this->activity_by_participant2($response->elements[10]); 
		    	//$output .= $this->first_login();	
		    	
    		}		 
    	} catch(exception $e) {
    		print_error('', 'local_xray','',null, $e->getMessage());
    	}
    	
    	return $output;
    }
    
    /**
     * Report Students activity (table).
     *
     */
    private function students_activity() {
    
    	$output = "";
    	$output .= $this->output->students_activity();
    	return $output;
    }   
    
    /**
     * Json for provide data to students_activity table.
     */
    public function jsonstudentsactivity_action() {
    	
    	// TODO:: Review , implement search, sortable, pagination.
    	$return = array();
    	try {
    		$report = "activity";
    		$element = "element1";
    		$response = \local_xray\api\wsapi::course(parent::XRAY_DOMAIN, parent::XRAY_COURSEID, $report);
    		if(!$response) {
    			// TODO:: Fail response of webservice.
    			throw new Exception(\local_xray\api\xrayws::geterrormsg());
    			 
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
    }
    
    /**
     * Report Activity of course by day.
     *
     */
    private function activity_of_course_by_day($element) {
    	
    	$output = "";
    	$output .= $this->output->activity_of_course_by_day($element);
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
    
    /**
     * Report Activity Last Two Weeks by Weekday
     */
    private function activity_last_two_weeks_by_weekday($element) {
    
    	$output = "";
    	$output .= $this->output->activity_last_two_weeks_by_weekday($element);
    	return $output;
    }   
    
    /**
     * Report Activity by Participant 1
     */
    private function activity_by_participant1($element) {
    
    	$output = "";
    	$output .= $this->output->activity_by_participant1($element);
    	return $output;
    }   
    
    /**
     * Report Activity by Participant 2
     */
    private function activity_by_participant2($element) {
    
    	$output = "";
    	$output .= $this->output->activity_by_participant2($element);
    	return $output;
    }
    
    /**
     * Report First login
     * TODO:: Pending to determinate what must I show?
     */
    private function first_login() {
    
    	$output = "";
    	return $output;
    }    
    
}
