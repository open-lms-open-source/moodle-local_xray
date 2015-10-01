<?php
defined('MOODLE_INTERNAL') or die();

/* @var object $CFG */
require_once($CFG->dirroot . '/local/xray/controller/reports.php');

/**
 * Xray integration Reports Controller
 *
 * @author Pablo Pagnone
 * @author German Vitale
 * @package local_xray
 */
class local_xray_controller_activityreport extends local_xray_controller_reports {
	
	/**
	 * Require capabilities
	 */
	public function require_capability() {
		// Change INT-8194 , this report show 3 differents reports.
		$ctx = $this->get_context();
        if(!has_capability("local/xray:activityreport_view", $ctx) &&
           !has_capability("local/xray:discussionendogenicplagiarism_view", $ctx) &&
           !has_capability("local/xray:discussiongrading_view", $ctx)) {
           	
           	throw new required_capability_exception($ctx, "local/xray:activityreport_view", 'nopermissions', '');
        }
	}
	
    public function view_action() {
        global $PAGE;

        $output = '';
        $ctx = $this->get_context();

        try {
        	
        	if (has_capability("local/xray:activityreport_view", $ctx)) {
        		      		
        		$report = "firstLogin";
        		$response_firstlogin = \local_xray\api\wsapi::course($this->courseid, $report);
        		if (!$response_firstlogin) {
        			// Fail response of webservice.
        			\local_xray\api\xrayws::instance()->print_error();
        		
        		} else {
        			
        			$output .= $this->output->inforeport($response_firstlogin->reportdate, null, $PAGE->course->fullname);
        			// Show graphs. We need show table first in activity report.(INT-8186)
        			$output .= $this->first_login_non_starters($response_firstlogin->elements->nonStarters); // Call to independient call to show in table.       			
        		}	        		
        		
	            $report = "activity";
	            $response = \local_xray\api\wsapi::course($this->courseid, $report);
	            if (!$response) {
	                // Fail response of webservice.
	                \local_xray\api\xrayws::instance()->print_error();
	
	            } else {
	            	
		                // Show graphs Activity report.		                
		                $output .= $this->students_activity($response->elements->studentList); // Its a table, I will get info with new call.
		                $output .= $this->activity_of_course_by_day($response->elements->activityLevelTimeline);
		                $output .= $this->activity_by_time_of_day($response->elements->compassTimeDiagram);
		                $output .= $this->activity_last_two_weeks_by_weekday($response->elements->barplotOfActivityByWeekday);
		                $output .= $this->activity_last_two_weeks($response->elements->barplotOfActivityWholeWeek);
		                $output .= $this->activity_by_participant1($response->elements->activityByWeekAsFractionOfTotal);
		                $output .= $this->activity_by_participant2($response->elements->activityByWeekAsFractionOfOwn);

		                // We need show graph of firstlogin in last place.(INT-8186)
		                $output .= $this->first_login_to_course($response_firstlogin->elements->firstloginPiechartAdjusted);
		                // Not show (INT-8186)
		                //$output .= $this->first_login_date_observed($response_firstlogin->elements->firstloginBullseyeAdjusted);
	            }
        	}               
                // Show reports Discussion endogenic (INT-8194)
            if (has_capability("local/xray:discussionendogenicplagiarism_view", $ctx)) {
                	
                $report = "discussionEndogenicPlagiarism";
                $response = \local_xray\api\wsapi::course($this->courseid, $report);
                if (!$response) {
                	$this->debugwebservice();
                	// Fail response of webservice.
                	\local_xray\api\xrayws::instance()->print_error();
                	
                } else {
                	
                	// show graphs.
	                $output .= html_writer::tag("div", 
	                			                html_writer::tag("h2", get_string("discussionendogenicplagiarism", $this->component), array("class" => "main")), 
	                			                array("class" => "mr_html_heading"));
	                $output .= $this->output->inforeport($response->reportdate, null, $PAGE->course->fullname);
	                $output .= $this->heatmap_endogenic_plagiarism_students($response->elements->endogenicPlagiarismStudentsHeatmap);
	                $output .= $this->heatmap_endogenic_plagiarism_instructors($response->elements->endogenicPlagiarismHeatmap);                	
                }
             }
   
             // Show reports discussion grading. (INT-8194)
             if (has_capability("local/xray:discussiongrading_view", $ctx)) {
                	
                $report = "discussionGrading";
                $response = \local_xray\api\wsapi::course($this->courseid, $report);
                if (!$response) {
                	// Fail response of webservice.
                	\local_xray\api\xrayws::instance()->print_error();
                } else {
                	
                	// Show graphs.
	                $output .= html_writer::tag("div", 
	                			                html_writer::tag("h2", get_string("discussiongrading", $this->component), array("class" => "main")), 
	                			                array("class" => "mr_html_heading"));
		            $output .= $this->output->inforeport($response->reportdate, null, $PAGE->course->fullname);
		            $output .= $this->students_grades_based_on_discussions($response->elements->studentDiscussionGrades); // Its a table, I will get info with new call.
		            $output .= $this->barplot_of_suggested_grades($response->elements->discussionSuggestedGrades);
                }
             }
                
        } catch (Exception $e) {
            print_error('error_xray', $this->component, '', null, $e->getMessage().' '.$PAGE->pagetype);
        }

        return $output;
    }

    /**
     * Report Students activity (table).
     * @param mixed $element
     * @return string
     */
    private function students_activity($element) {

        $output = "";
        $output .= $this->output->activityreport_students_activity($this->courseid, $element);
        return $output;
    }

    /**
     * Json for provide data to students_activity table.
     * @return string
     */
    public function jsonstudentsactivity_action() {
        global $PAGE;

        // Pager
        $count = (int)optional_param('iDisplayLength', 10, PARAM_ALPHANUM);
        $start = (int)optional_param('iDisplayStart', 0, PARAM_ALPHANUM);

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

            if (!$response) {
                // Fail response of webservice.
                \local_xray\api\xrayws::instance()->print_error();
            } else {

                $data = array();
                if (!empty($response->data)) {
                    $activityreportind = get_string('activityreportindividual', $this->component);
                    foreach ($response->data as $row) {

                        $r = new stdClass();
                        $r->action = "";
                        if (has_capability('local/xray:activityreportindividual_view', $PAGE->context)) {
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
                        if (!empty($response->columnOrder)) {
                            foreach ($response->columnOrder as $column) {
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
        } catch (Exception $e) {
            // Error, return invalid data, and pluginjs will show error in table.
            $return["data"] = "-";
        }

        return json_encode($return);
    }

    /**
     * Report Activity of course by day.
     * @param mixed $element
     * @return string
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
     * @param mixed $element
     * @return string
     */
    private function activity_last_two_weeks($element) {

        $output = "";
        $output .= $this->output->activityreport_activity_last_two_weeks($element);
        return $output;
    }

    /**
     * Report Activity Last Two Weeks by Weekday
     * @oaram mixed $element
     * @return string
     */
    private function activity_last_two_weeks_by_weekday($element) {

        $output = "";
        $output .= $this->output->activityreport_activity_last_two_weeks_by_weekday($element);
        return $output;
    }

    /**
     * Report Activity by Participant 1
     * @param mixed $element
     * @return string
     */
    private function activity_by_participant1($element) {

        $output = "";
        $output .= $this->output->activityreport_activity_by_participant1($element);
        return $output;
    }

    /**
     * Report Activity by Participant 2
     * @param mixed $element
     * @return string
     */
    private function activity_by_participant2($element) {

        $output = "";
        $output .= $this->output->activityreport_activity_by_participant2($element);
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
    public function jsonfirstloginnonstarters_action() {
        // Pager
        $count = (int)optional_param('iDisplayLength', 10, PARAM_ALPHANUM);
        $start = (int)optional_param('iDisplayStart', 0, PARAM_ALPHANUM);

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
            if (!$response) {
                // Fail response of webservice.
                \local_xray\api\xrayws::instance()->print_error();

            } else {
                $data = array();
                if (!empty($response->data)) {
                    // Format of response for columns.
                    foreach ($response->data as $row) {

                        // This report has not specified columnOrder.
                        if (!empty($response->columnHeaders) && is_object($response->columnHeaders)) {
                            $r = new stdClass();
                            $c = get_object_vars($response->columnHeaders);
                            foreach ($c as $id => $name) {
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
        } catch (Exception $e) {
            // Error, return invalid data, and pluginjs will show error in table.
            $return["data"] = "-";
        }

        return json_encode($return);
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
    
    /**
     * Report Student Grades Based on Discussions(table)
     * @param object $element
     * @return string
     */
    private function students_grades_based_on_discussions($element) {
    	$output = "";
    	$output .= $this->output->discussiongrading_students_grades_based_on_discussions($this->courseid, $element);
    	return $output;
    }
    
    /**
     * Json for provide data to students_grades_based_on_discussions table.
     */
    public function jsonstudentsgrades_action() {
    	// Pager
    	$count = (int)optional_param('iDisplayLength', 10, PARAM_ALPHANUM);
    	$start = (int)optional_param('iDisplayStart', 0, PARAM_ALPHANUM);
    
    	$return = "";
    
    	try {
    		$report = "discussionGrading";
    		$element = "studentDiscussionGrades";
    		$response = \local_xray\api\wsapi::courseelement($this->courseid, $element, $report, null, '', '', $start, $count);
    
    		if (!$response) {
    			throw new Exception (\local_xray\api\xrayws::instance()->geterrormsg());
    		} else {
    
    			$data = array();
    			if (!empty ($response->data)) {
    				foreach ($response->data as $row) {
    					// Format of response for columns.
    					if (!empty($response->columnOrder)) {
    						$r = new stdClass();
    						foreach ($response->columnOrder as $column) {
    							$r->{$column} = (isset($row->{$column}->value) ? $row->{$column}->value : '');
    						}
    						$data[] = $r;
    					}
    				}
    			}
    			// Provide count info to table.
    			$return ["recordsFiltered"] = $response->itemCount;
    			$return ["data"] = $data;
    		}
    	} catch (Exception $e) {
    		// Error, return invalid data, and pluginjs will show error in table.
    		$return["data"] = "-";
    	}
    
    	return json_encode($return);
    }
    
    /**
     * Report Barplot of Suggested Grades
     * @param object $element
     * @return string
     */
    private function barplot_of_suggested_grades($element) {
    	$output = "";
    	$output .= $this->output->discussiongrading_barplot_of_suggested_grades($element);
    	return $output;
    }
    
}
