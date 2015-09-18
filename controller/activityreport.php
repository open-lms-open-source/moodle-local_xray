<?php
defined('MOODLE_INTERNAL') or die();
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
		parent::init();
		// This report will get data by courseid.
		$this->courseid = required_param('courseid', PARAM_RAW);
	}

    public function view_action() {

    	global $PAGE, $DB;
    	$title = get_string($this->name, $this->component);
    	$PAGE->set_title($title);
    	$this->heading->text = $title;

    	// Add title to breadcrumb.
    	$PAGE->navbar->add(get_string($this->name, $this->component));
    	$output = "";

    	try {
    		$report = "activity";
    		$response = \local_xray\api\wsapi::course($this->courseid, $report);
    		if(!$response) {
    			// Fail response of webservice.
    			\local_xray\api\xrayws::instance()->print_error();

    		} else {

    			// Show graphs.
    			$output .= $this->output->inforeport($response->reportdate,
    					                             null,
    					                             $DB->get_field('course', 'fullname', array("id" => $this->courseid)));
    			$output .= $this->students_activity($response->elements->studentList); // Its a table, I will get info with new call.
    			$output .= $this->activity_of_course_by_day($response->elements->activityLevelTimeline);
    		  $output .= $this->activity_by_time_of_day($response->elements->compassTimeDiagram);
    			$output .= $this->activity_last_two_weeks_by_weekday($response->elements->barplotOfActivityByWeekday);
          $output .= $this->activity_last_two_weeks($response->elements->barplotOfActivityWholeWeek);
    			$output .= $this->activity_by_participant1($response->elements->activityByWeekAsFractionOfTotal);
    			$output .= $this->activity_by_participant2($response->elements->activityByWeekAsFractionOfOwn);
    			$output .= $this->first_login(); // This show 3 reports about login

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
    private function students_activity($element) {

    	$output = "";
    	$output .= $this->output->activityreport_students_activity($this->courseid, $element);
    	return $output;
    }

    /**
     * Json for provide data to students_activity table.
     */
    public function jsonstudentsactivity_action() {

    	global $PAGE;

    	// Pager
    	$count = optional_param('iDisplayLength', 10, PARAM_RAW);
    	$start  = optional_param('iDisplayStart', 0, PARAM_RAW);

    	$return = "";

    	try {
    		$report = "activity";
    		$element = "studentList";
    		$response = \local_xray\api\wsapi::courseelement($this->courseid,
    				                                         $element,
    				                                         $report,
    				                                         null,
    				                                         '',
    				                                         '',
    				                                         $start,
    				                                         $count);

    		if(!$response) {
    			// Fail response of webservice.
    			\local_xray\api\xrayws::instance()->print_error();
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
	    							              		"courseid" => $this->courseid,
	    							              		"userid" => $row->participantId->value
	    							              ));
	    					$r->action = html_writer::link($url, '', array("class" => "icon_activityreportindividual",
	    							                                       "title" => $activityreportind,
	    							                                       "target" => "_blank"));
    					}
    					// Format of response for columns.
    					if(!empty($response->columnOrder)) {
    						foreach($response->columnOrder as $column) {
    							$r->{$column} = (isset($row->{$column}->value) ? $row->{$column}->value : '');
    						}
    					}
    					$data[] = $r;
    				}
    			}
    			// Provide count info to table.
    			$return["recordsFiltered"] = $response->itemCount;
    			$return["data"] = $data;


    		}
    	} catch(exception $e) {
    		// Error, return invalid data, and pluginjs will show error in table.
    		$return["data"] = "-";
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
    		$response = \local_xray\api\wsapi::course($this->courseid, $report);
    		if(!$response) {
    			// Fail response of webservice.
    			\local_xray\api\xrayws::instance()->print_error();

    		} else {

    			// Show graphs.
    			$output .= $this->first_login_non_starters($response->elements->nonStarters); // Call to independient call to show in table.
    			$output .= $this->first_login_to_course($response->elements->firstloginPiechartAdjusted);
    			$output .= $this->first_login_date_observed($response->elements->firstloginBullseyeAdjusted);
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
    private function first_login_non_starters($element) {
    	$output = "";
    	$output .= $this->output->activityreport_first_login_non_starters($this->courseid, $element);
    	return $output;
    }

    /**
     * Json for table non starters.
     *
     */
    public function jsonfirstloginnonstarters_action(){

    	// Pager
    	$count = optional_param('iDisplayLength', 10, PARAM_RAW);
    	$start  = optional_param('iDisplayStart', 0, PARAM_RAW);

    	$return = "";

    	try {
    		$report = "firstLogin";
    		$element = "nonStarters";
    		$response = \local_xray\api\wsapi::courseelement($this->courseid,
    				                                         $element,
    				                                         $report,
    				                                         null,
    				                                         '',
    				                                         '',
    				                                         $start,
    				                                         $count);
    		if(!$response) {
    			// Fail response of webservice.
    			\local_xray\api\xrayws::instance()->print_error();

    		} else {
    			$data = array();
    			if(!empty($response->data)){
  					// Format of response for columns.
  					foreach($response->data as $row) {

  						// This report has not specified columnOrder.
	    				if(!empty($response->columnHeaders) && is_object($response->columnHeaders)) {
	    					$r = new stdClass();
	    					$c = get_object_vars($response->columnHeaders);
	    					foreach($c as $id => $name) {
	    						$r->{$id} = (isset($row->{$id}->value) ? $row->{$id}->value : '');
	    					}
	    					$data[] = $r;
	    				}
  					}
    			}

    			// Provide info to table.
    			$return["recordsFiltered"] = $response->itemCount;
    			$return["data"] = $data;
    		}
    	} catch(exception $e) {
    		// Error, return invalid data, and pluginjs will show error in table.
    		$return["data"] = "-";
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
