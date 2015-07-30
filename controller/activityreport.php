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
class local_xray_controller_activityreport extends local_xray_controller_reports {
 
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
    	$PAGE->navbar->add(get_string($this->name, $this->component));
    	$output = "";

    	try {
    		$report = "activity";
    		$response = \local_xray\api\wsapi::course($this->xraycourseid, $report);
    		if(!$response) {
    			// Fail response of webservice.
    			throw new Exception(\local_xray\api\xrayws::instance()->geterrormsg());
    			
    		} else {

    			// Show graphs.
    			$output .= $this->students_activity(); // Its a table, I will get info with new call.
    			$output .= $this->activity_of_course_by_day($response->elements[1]);
    			$output .= $this->activity_by_time_of_day($response->elements[4]);
    			$output .= $this->activity_last_two_weeks($response->elements[6]);
    			$output .= $this->activity_last_two_weeks_by_weekday($response->elements[7]);
    			$output .= $this->activity_by_participant1($response->elements[9]);
    			$output .= $this->activity_by_participant2($response->elements[10]);
    			$output .= $this->first_login();
		    	
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
    private function students_activity() {
    
    	$output = "";
    	$output .= $this->output->activityreport_students_activity($this->xraycourseid);
    	return $output;
    }   
    
    /**
     * Json for provide data to students_activity table.
     */
    public function jsonstudentsactivity_action() {
    	
    	global $PAGE;
    	
    	// Pager
    	$count = optional_param('iDisplayLength', 10, PARAM_RAW);
    	$start  = optional_param('iDisplayStart', 10, PARAM_RAW);
    	
    	$return = "";

    	try {
    		$report = "activity";
    		$element = "studentList";
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
    				$activityreportind = get_string('activityreportindividual', $this->component);
    				foreach($response->data as $row) {
    					
    					$r = new stdClass();
    					$r->action = "";
    					if(has_capability('local/xray:activityreportindividual_view', $PAGE->context)) {    						
	    					// Url for activityreportindividual.
	    					$url = new moodle_url("/local/xray/view.php", 
	    							              array("controller" => "activityreportindividual",
	    							              		"xraycourseid" => $row->courseId->value,
	    							              		"xrayuserid" => $row->participantId->value
	    							              ));
	    					$r->action = html_writer::link($url, '', array("class" => "icon_activityreportindividual",
	    							                                       "title" => $activityreportind,
	    							                                       "target" => "_blank"));
    					}
    					
    					$r->firstname = $row->firstname->value;
    					$r->lastname = $row->lastname->value;
    					$r->lastactivity = $row->last_activity->value;
    					$r->discussionposts = $row->discussion_posts->value;
    					$r->postslastweek = $row->discussion_posts_last_week->value;
    					$r->timespentincourse = $row->timeOnTask->value;	
    					// TODO:: Not exist value for weeklyRegularity on xray webservice. NOTIFY
    					$r->regularity = "";
    					$data[] = $r;
    				}
    			}
    			// Provide info to table.
    			$return["recordsFiltered"] = 100; // TODO:: Get from webservice.
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
     * Report Activity of course by day.
     *
     */
    private function activity_of_course_by_day($element) {
    	
    	$output = "";
    	$output .= $this->output->activityreport_activity_of_course_by_day($element);
    	return $output; 
    }
    
    /**
     * Report Activity by time of day.
     *
     */    
    private function activity_by_time_of_day($element) {

    	$output = "";
    	$output .= $this->output->activityreport_activity_by_time_of_day($element);
    	return $output; 
    }
    
    /**
     * Report Activity last two weeks.
     * @param unknown $element
     */
    private function activity_last_two_weeks($element) {

    	$output = "";
    	$output .= $this->output->activityreport_activity_last_two_weeks($element);
    	return $output;
    }
    
    /**
     * Report Activity Last Two Weeks by Weekday
     */
    private function activity_last_two_weeks_by_weekday($element) {
    
    	$output = "";
    	$output .= $this->output->activityreport_activity_last_two_weeks_by_weekday($element);
    	return $output;
    }   
    
    /**
     * Report Activity by Participant 1
     */
    private function activity_by_participant1($element) {
    
    	$output = "";
    	$output .= $this->output->activityreport_activity_by_participant1($element);
    	return $output;
    }   
    
    /**
     * Report Activity by Participant 2
     */
    private function activity_by_participant2($element) {
    
    	$output = "";
    	$output .= $this->output->activityreport_activity_by_participant2($element);
    	return $output;
    }
    
    /**
     * First Login
     * Here we will show three graphs: 2 images and 1 table.
     * @throws Exception
     */
    private function first_login() {
    	
    	$output = "";
    	 
    	try {
    		$report = "firstLogin";
    		$response = \local_xray\api\wsapi::course($this->xraycourseid, $report);
    		if(!$response) {
    			// Fail response of webservice.
    			throw new Exception(\local_xray\api\xrayws::instance()->geterrormsg());
    			 
    		} else {
    			 
    			// Show graphs.
    			$output .= $this->first_login_non_starters(); // Call to independient call to show in table.
    			$output .= $this->first_login_to_course($response->elements[3]);
    			$output .= $this->first_login_date_observed($response->elements[4]);
    		}
    	} catch(exception $e) {
    		print_error('error_xray', $this->component,'',null, $e->getMessage());
    	}
    	 
    	return $output;
    }
    
    /**
     * Report First login
     * - Element to show: table users not starters in course.
     * 
     */
    private function first_login_non_starters() {
    	$output = "";
    	$output .= $this->output->activityreport_first_login_non_starters($this->xraycourseid);
    	return $output;
    } 
    
    /**
     * Json for table non starters.
     *
     */    
    public function jsonfirstloginnonstarters_action(){
    	
    	// Pager
    	$count = optional_param('iDisplayLength', 10, PARAM_RAW);
    	$start  = optional_param('iDisplayStart', 10, PARAM_RAW);
    	
    	$return = "";
    	
    	try {
    		$report = "firstLogin";
    		$element = "nonStarters";
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

    			if(!empty($response->data)){
    				$data = array();
    				foreach($response->data as $row) {
    					$r = new stdClass();
    					$r->firstname = $row->firstname->value;
    					$r->lastname = $row->lastname->value;
    					$data[] = $r;
    				}
    				
    				// Provide info to table.
    				$return["recordsFiltered"] = 100; // TODO:: Get from webservice.
    				$return["data"] = $data;
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
     * Report First login
     * - Element to show: 5 , first login to course.
     *
     */
    private function first_login_to_course($element) {
    	$output = "";
    	$output .= $this->output->activityreport_first_login_to_course($element);
    	return $output;
    }
    
    /**
     * Report First login
     * - Element to show: 9 , first login in date observed.
     *
     */
    private function first_login_date_observed($element) {
    	$output = "";
    	$output .= $this->output->activityreport_first_login_date_observed($element);
    	return $output;
    }
}
