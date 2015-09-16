<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
require_once($CFG->dirroot.'/local/xray/controller/reports.php');
require_once($CFG->dirroot.'/local/xray/classes/local_xray_datatables.php');

/**
 * Renderer
 *
 * @author Pablo Pagnone
 * @package local_xray
 */
class local_xray_renderer extends plugin_renderer_base {
   
    /************************** General elements for Reports **************************/
    
    /**
     * Show data about report
     * @param String   $reportdate 
     * @param String    $user
     * @param String  $course
     */
    public function inforeport($reportdate, $user = null, $course = null) {

    	$output = "";
    	$output .=html_writer::start_div('inforeport');
    	$output .= html_writer::tag("p", get_string("reportdate", "local_xray").": ".$reportdate);
    	if(!empty($user)) {
    		$output .= html_writer::tag("p", get_string(("username")).": ".$user);
    	}
    	/*
    	 * We will show in course in navbar.
    	if(!empty($course)) {
    		$output .= html_writer::tag("p", get_string(("course")).": ".$course);
    	} */   	
    	$output .=html_writer::end_div();    
    	
    	return $output;
    }
    
    /**
     * Generate img with lightbox.
     * General template for show graph with lightbox.
     * 
     * Structure:
     * <div id=$name">
     * <div class='reportsname'>$name</div>
     * <a><img class='xray_graph'></a>
     * <div class='xray_graph_legend'>legend element</div>
     * </div>
     * 
     * Important: Link to image will have id fancybox + "name of report".
     * 
     * @param String   $name 
     * @param stdClass $element
     */
    private function show_on_lightbox($name, $element) {
    
        global $PAGE;
        $plugin = "local_xray";
    
        // Load Jquery.
        $PAGE->requires->jquery();
        $PAGE->requires->jquery_plugin('ui');
        $PAGE->requires->jquery_plugin('local_xray-show_on_lightbox', $plugin); // Js for show on lightbox.
        $PAGE->requires->jquery_plugin('local_xray-create_thumb', $plugin); // Js for dynamic thumbnails.
    
        $cfg_xray = get_config('local_xray');
        $img_url = sprintf('%s/%s/%s', $cfg_xray->xrayurl, $cfg_xray->xrayclientid, $element->uuid);
    
        //Access Token
        $accesstoken = local_xray\api\wsapi::accesstoken();
        $img_url = new moodle_url($img_url, array('accesstoken' => $accesstoken));
    
        $output = "";
        $output .= html_writer::start_tag('div', array("id" => $name, "class" => "xray_element xray_element_graph"));
    
        /* Graph Name */
        $output .= html_writer::tag('div', $element->title, array("class" => "reportsname"));
        /* End Graph Name */
    
        /* Img */
        $tooltip = '';
        if(isset($element->tooltip) && !empty($element->tooltip)){
            $tooltip = $element->tooltip;
        }
        $output .= html_writer::start_tag('div', array("class" => "xray_element_img"));
    
        // Validate if url of image is valid.
        if (@fopen($img_url, "r")) {
            $id_img = "fancybox_".$name;
            $output .= html_writer::start_tag('a', array("id" => $id_img, "href" => $img_url));
            $output .= html_writer::empty_tag('img', array("title" => $tooltip,
                    "src" => $img_url,
                    "class" => "thumb") // Used by dynamic thumbnails.
            );
    
            $output .= html_writer::end_tag('a');  
    
            /* End Img */
    
            // Send data to js.
            $PAGE->requires->js_init_call("local_xray_show_on_lightbox", array($id_img, $element));
    
        } else{
            // Incorrect url img. Show error message.
            $output .= html_writer::tag("div", get_string('error_loadimg', $plugin), array("class" => "error_loadmsg"));
        }
        
        $output .= html_writer::end_tag('div');	
        $output .= html_writer::end_tag('div');
    
        return $output; 
    }
    
    /**
     * Standard table Theme with Jquery datatables.
     * 
     * @param Array $data - Array containing object DataTable.
     * @param String $classes - Classes for table.
     * @return string
     */
    private function standard_table ($data, $classes = '', $width = '100%') {

        global $CFG, $PAGE;

        // Load Jquery.
        $PAGE->requires->jquery();
        $PAGE->requires->jquery_plugin('ui');
        // Load specific js for tables.
        $PAGE->requires->jquery_plugin("local_xray-show_on_table", "local_xray");    	
    
        $output = "";
        $output .= html_writer::start_tag('div', array("id" => $data['id'], "class" => "xray_element xray_element_table"));
        $output .= html_writer::tag('div', $data['title'], array("class" => "reportsname eventtoogletable"));

        // Table jquery datatables for show reports.
        $output .= "<table id='table_{$data['id']}' class='display {$classes}' cellspacing='0' width='{$width}'> <thead><tr>";
        foreach($data['columns'] as $c){
            $output .= "<th>".$c->text."</th>";
        }
        $output .=" </tr> </thead> </table>";
        $output .= html_writer::end_tag('div');
        
        // Load table with data.
        $PAGE->requires->js_init_call("local_xray_show_on_table", array($data));		 

        return $output;   	
    }
    /************************** End General elements for Reports **************************/
    
    /************************** Elements for Report Activity **************************/
    
    /**
     * Graphic students activity (TABLE)
     */
    public function activityreport_students_activity($courseid, $element) {
    	
    	$columns = array(new local_xray_datatableColumn('action', ''));  	
    	if(!empty($element->columnOrder) && is_array($element->columnOrder)) {
    		foreach($element->columnOrder as $c) {
    			$columns[] = new local_xray_datatableColumn($c, $element->columnHeaders->{$c});
    		}
    	}

    	$datatable = new local_xray_datatable(__FUNCTION__, 
    			                              $element->title,
    			                              "view.php?controller='activityreport'&action='jsonstudentsactivity'&courseid=".$courseid, 
    			                              $columns);
    	
    	// Create standard table.
    	$output = $this->standard_table((array) $datatable);    	
    	
    	return $output;    	 
    }
    
    /**
     * Graphic activity of course by day.(Graph)
     * @param stdClass $element
     */
    public function activityreport_activity_of_course_by_day($element) {    	
    	return $this->show_on_lightbox(__FUNCTION__, $element);	    	
    }
    
    /**
     * Graphic activity by time of day.(Graph)
     * @param stdClass $element
     */    
    public function activityreport_activity_by_time_of_day($element) {
    	return $this->show_on_lightbox(__FUNCTION__, $element);     	
    }
    
    /**
     * Graphic activity last two weeks.(Graph)
     * @param stdClass $element
     */
    public function activityreport_activity_last_two_weeks($element) {
    	return $this->show_on_lightbox(__FUNCTION__, $element);
    }  
    
    /**
     * Graphic activity last two weeks BY weekday.(Graph)
     * @param stdClass $element
     */
    public function activityreport_activity_last_two_weeks_by_weekday($element) {
    	return $this->show_on_lightbox(__FUNCTION__, $element); 	
    }
    
    /**
     * Graphic activity by participant 1
     * @param stdClass $element
     */
    public function activityreport_activity_by_participant1($element) {
    	return $this->show_on_lightbox(__FUNCTION__, $element); 	
    }
    
    /**
     * Graphic activity by participant 2
     * @param stdClass $element
     */
    public function activityreport_activity_by_participant2($element) {
    	return $this->show_on_lightbox(__FUNCTION__, $element);
    }    
    
    /**
     * Graphic first login non startes (TABLE)
     * 
     */    
    public function activityreport_first_login_non_starters($courseid, $element) {   	
    	global $PAGE;
    	
    	$columns = array();
    	// This report has not specified columnOrder.
    	if(!empty($element->columnHeaders) && is_object($element->columnHeaders)) {
    		$c = get_object_vars($element->columnHeaders);
    		foreach($c as $id => $name) {
    			$columns[] = new local_xray_datatableColumn($id, $name);
    		}
    	}
    	 
    	$datatable = new local_xray_datatable(__FUNCTION__,
    			                              $element->title,
    			                              "view.php?controller='activityreport'&action='jsonfirstloginnonstarters'&courseid=".$courseid,
    			                               $columns);
    	 
    	// Create standard table.
    	$output = $this->standard_table((array) $datatable);
    	return $output;	
    }
    
    /**
     * Graphic frist login to course
     * @param stdClass $element
     */    
    public function activityreport_first_login_to_course($element) {
    	return $this->show_on_lightbox(__FUNCTION__, $element);    	
    }
    
    /**
     * Graphic first login date observed
     * @param stdClass $element
     */    
    public function activityreport_first_login_date_observed($element) {
    	return $this->show_on_lightbox(__FUNCTION__, $element);
    }
    /************************** End Elements for Report Activity **************************/
    
    /************************** Elements for Report Activity Individual **************************/
    
    /**
     * Graphic activity individual by date
     * @param stdClass $element
     */
    public function activityreportindividual_activity_by_date($element) {
    	return $this->show_on_lightbox(__FUNCTION__, $element);
    }
    
    /**
     * Graphic activity individual last two week
     * @param stdClass $element
     */
    public function activityreportindividual_activity_last_two_weeks($element) {
    	return $this->show_on_lightbox(__FUNCTION__, $element);
    }
    
    /**
     * Graphic activity individual last two week by weekday
     * @param stdClass $element
     */
    public function activityreportindividual_activity_last_two_weeks_byday($element) {
    	return $this->show_on_lightbox(__FUNCTION__, $element);
    }
    
    /************************** End Elements for Report Activity Individual **************************/
    
    /************************** Elements for Report Discussion **************************/
    
    /**
     * Graphic Participation Metrics (TABLE)
     */
    public function discussionreport_participation_metrics($courseid, $element) {
         
        global $PAGE;
        
        $columns = array(new local_xray_datatableColumn('action', ''));
        if(!empty($element->columnOrder) && is_array($element->columnOrder)) {
        	foreach($element->columnOrder as $c) {
        		$columns[] = new local_xray_datatableColumn($c, $element->columnHeaders->{$c});
        	}
        }
         
        $datatable = new local_xray_datatable(__FUNCTION__,
								        		$element->title,
								                "view.php?controller='discussionreport'&action='jsonparticipationdiscussion'&courseid=".$courseid,
								                $columns);
         
        // Create standard table.
        $output = $this->standard_table((array) $datatable);
         
        return $output;
    }
    
    /**
     * Graphic Discussion Activity by Week (TABLE)
     * @param stdClass $element
     */
    public function discussionreport_discussion_activity_by_week($courseid, $element) {

        global $PAGE;
        // Create standard table.
        $columns = array();
        $columns[] = new local_xray_datatableColumn('weeks', get_string('weeks', 'local_xray'));
        foreach($element->data as $column){
            $columns[] = new local_xray_datatableColumn($column->week->value, $column->week->value);
        }

        $number_of_weeks = count($columns)-1;//get number of weeks - we need to rest the "week" title column
        
        $datatable = new local_xray_datatable(__FUNCTION__,
							        		$element->title,
							                "view.php?controller='discussionreport'&action='jsonweekdiscussion'&courseid=".$courseid."&count=".$number_of_weeks,
							                $columns, 
							                false, 
							                false,// We don't need pagination because we have only four rows
							                '<"xray_table_scrool"t>');//Only the table

        // Create standard table.
        $output = $this->standard_table((array) $datatable);

        return $output;
    }
    
    /**
     * Average words weekly by post. (Graph)
     * @param stdClass $element
     */
    public function discussionreport_average_words_weekly_by_post($element) {
    	return $this->show_on_lightbox(__FUNCTION__, $element);
    }
    
    /**
     * Social structure.(Graph)
     * @param stdClass $element
     */
    public function discussionreport_social_structure($element) {
    	return $this->show_on_lightbox(__FUNCTION__, $element);
    }
    
    /**
     * Social structure with contributions adjusted.(Graph)
     * @param stdClass $element
     */
    public function discussionreport_social_structure_with_contributions_adjusted($element) {
    	return $this->show_on_lightbox(__FUNCTION__, $element);
    }
    
    /**
     * Social structure coefficient of critical thinking
     * @param stdClass $element
     */
    public function discussionreport_social_structure_coefficient_of_critical_thinking($element) {
    	return $this->show_on_lightbox(__FUNCTION__, $element);
    }

    /**
     * Main Terms
     * @param stdClass $element
     */
    public function discussionreport_main_terms($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }
    
    /************************** End Elements for Report Discussion **************************/
    
    
    /************************** Elements for Report Discussion for an individual **************************/
    
    /**
     * Graphic Participation Metrics (TABLE)
     */
    public function discussionreportindividual_participation_metrics($courseid, $element, $userid) {
         
        $columns = array();
    	if(!empty($element->columnOrder) && is_array($element->columnOrder)) {
    		foreach($element->columnOrder as $c) {
    			$columns[] = new local_xray_datatableColumn($c, $element->columnHeaders->{$c});
    		}
    	}
         
        $datatable = new local_xray_datatable(__FUNCTION__,
        		$element->title,
                "view.php?controller='discussionreportindividual'&action='jsonparticipationdiscussionindividual'&courseid=".$courseid."&userid=".$userid,
                $columns);
         
        // Create standard table.
        $output = $this->standard_table((array) $datatable);
         
        return $output;
    }
    
    /**
     * Graphic Discussion Activity by Week (TABLE)
     * @param stdClass $element
     */
    public function discussionreportindividual_discussion_activity_by_week($courseid, $userid, $element) {
    
        global $PAGE;
        // Create standard table.
    
        $columns = array();
        $columns[] = new local_xray_datatableColumn('weeks', get_string('weeks', 'local_xray'));
        foreach($element->data as $column){
            $columns[] = new local_xray_datatableColumn($column->week->value, $column->week->value);
        }
    
        $number_of_weeks = count($columns)-1;//get number of weeks - we need to rest the "week" title column
    
        $datatable = new local_xray_datatable(__FUNCTION__,
        		$element->title,
                "view.php?controller='discussionreportindividual'&action='jsonweekdiscussionindividual'&courseid=".$courseid."&userid=".$userid."&count=".$number_of_weeks,
                $columns, 
                false, 
                false,// We don't need pagination because we have only four rows
                '<"xray_table_scrool"t>');//Only the table
    
        // Create standard table.
        $output = $this->standard_table((array) $datatable);
    
        return $output;
    }
    
    /**
     * Social structure.(Graph)
     * @param stdClass $element
     */
    public function discussionreportindividual_social_structure($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }
    
    /**
     * Social structure with word count.(Graph)
     * @param stdClass $element
     */
    public function discussionreport_social_structure_with_word_count($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }
    
    /**
     * Main terms.(Graph)
     * @param stdClass $element
     */
    public function discussionreportindividual_main_terms($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }
    
    /**
     * Main terms histogram.(Graph)
     * @param stdClass $element
     */
    public function discussionreportindividual_main_terms_histogram($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }
    
    /************************** End Elements for Report Discussion for an individual **************************/
    
    /************************** Elements for Report Discussion individual forum **************************/
    
      
    /**
     * Social structure.(Graph)
     * @param stdClass $element
     */
    public function discussionreportindividualforum_wordshistogram($element) {
    	return $this->show_on_lightbox(__FUNCTION__, $element);
    }
    
    /**
     * Main terms.(Graph)
     * @param stdClass $element
     */
    public function discussionreportindividualforum_socialstructure($element) {
    	return $this->show_on_lightbox(__FUNCTION__, $element);
    }
    
    /**
     * Main terms histogram.(Graph)
     * @param stdClass $element
     */
    public function discussionreportindividualforum_wordcloud($element) {
    	return $this->show_on_lightbox(__FUNCTION__, $element);
    }
    
    /************************** End Elements for Report Discussion Endogenic Plagiarism **************************/   
    
    /** 
     * Heatmap endogenic plagiarism student
     * @param stdClass $element
     */
    public function discussionendogenicplagiarism_heatmap_endogenic_plagiarism_students($element) {
    	return $this->show_on_lightbox(__FUNCTION__, $element);
    }
    
    /**
     * Heatmap endogenic plagiarism instructor
     * @param stdClass $element
     */
    public function discussionendogenicplagiarism_heatmap_endogenic_plagiarism_instructors($element) {
    	return $this->show_on_lightbox(__FUNCTION__, $element);
    }
    /************************** End Elements for Report Discussion Endogenic Plagiarism **************************/ 
    
    /************************** Elements for Report Risk **************************/
    
    /**
     * Risk Measures(TABLE)
     * @param stdClass $element
     */
    public function risk_risk_measures($courseid, $element) {
    	global $PAGE;
    	
    	$columns = array();
    	if(!empty($element->columnOrder) && is_array($element->columnOrder)) {
    		foreach($element->columnOrder as $c) {
    			$columns[] = new local_xray_datatableColumn($c, $element->columnHeaders->{$c});
    		}
    	}
    	
    	$datatable = new local_xray_datatable(__FUNCTION__,
    			                             $element->title,
    			                             "view.php?controller='risk'&action='jsonriskmeasures'&courseid=".$courseid,
    			                             $columns);
    	 
    	// Create standard table.
    	$output = $this->standard_table((array) $datatable);
    	 
    	return $output;
    }
    
    /**
     * Total risk profile
     * @param stdClass $element
     */
    public function risk_total_risk_profile($element) {
    	return $this->show_on_lightbox(__FUNCTION__, $element);
    }
    
    /**
     * Academic vs social risk
     * @param stdClass $element
     */    
    public function risk_academic_vs_social_risk($element) {
    	return $this->show_on_lightbox(__FUNCTION__, $element);
    }
    
    /************************** End Elements for Report Risk **************************/
    /**************************  Elements for Report Discussion grading **************************/
    
    /**
     * Discussion grading students grades (TABLE)
     * @param integer $courseid
     */
    public function discussiongrading_students_grades_based_on_discussions($courseid, $element) {
    	
    	global $PAGE;
    	
    	$columns = array();
    	if(!empty($element->columnOrder) && is_array($element->columnOrder)) {
    		foreach($element->columnOrder as $c) {
    			$columns[] = new local_xray_datatableColumn($c, $element->columnHeaders->{$c});
    		}
    	}
    	$datatable = new local_xray_datatable(__FUNCTION__,
    			                              $element->title,
    			                              "view.php?controller='discussiongrading'&action='jsonstudentsgrades'&courseid=".$courseid,
    			                              $columns);
    
    	// Create standard table.
    	$output = $this->standard_table((array) $datatable);
    
    	return $output;
    }
    
    /**
     * Discussion grading barplot
     * @param stdClass $element
     */
    public function discussiongrading_barplot_of_suggested_grades($element) {
    	return $this->show_on_lightbox(__FUNCTION__, $element);
    }
    
    /************************** End Elements for Report Discussion grading **************************/   
    /**************************  Elements for Gradebook Report **************************/
    
    /**
     * Students' Grades for course (TABLE)
     */
    public function gradebookreport_students_grades_for_course($courseid, $element) {
         
    	
    	$columns = array(new local_xray_datatableColumn('action', ''));
    	if(!empty($element->columnOrder) && is_array($element->columnOrder)) {
    		foreach($element->columnOrder as $c) {
    			$columns[] = new local_xray_datatableColumn($c, $element->columnHeaders->{$c});
    		}
    	}

        $datatable = new local_xray_datatable(__FUNCTION__,
        		$element->title,
                "view.php?controller='gradebookreport'&action='jsonstudentsgradesforcourse'&courseid=".$courseid,
                $columns);
         
        // Create standard table.
        $output = $this->standard_table((array) $datatable);
         
        return $output;
    }
    
    /**
     * Students' Grades on completed items course (TABLE)
     */
    public function gradebookreport_students_grades_on_completed_items_course($courseid, $element) {
         
        $columns = array(new local_xray_datatableColumn('action', ''));
    	if(!empty($element->columnOrder) && is_array($element->columnOrder)) {
    		foreach($element->columnOrder as $c) {
    			$columns[] = new local_xray_datatableColumn($c, $element->columnHeaders->{$c});
    		}
    	}
         
        $datatable = new local_xray_datatable(__FUNCTION__,
        		$element->title,
                "view.php?controller='gradebookreport'&action='jsonstudentsgradesoncompleteditemscourse'&courseid=".$courseid,
                $columns);
         
        // Create standard table.
        $output = $this->standard_table((array) $datatable);
         
        return $output;
    }
    
    /**
     * Distribution of grades in course
     * @param stdClass $element
     */
    public function gradebookreport_distribution_of_grades_in_course($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }
    
    /**
     * Distribution of grades completed items.
     * @param stdClass $element
     */
    public function gradebookreport_distribution_of_grades_completed_items($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }
    
    /**
     * Density plot: all items.
     * @param stdClass $element
     */
    public function gradebookreport_density_plot_all_items($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }
    
    /**
     * Density plot: completed items.
     * @param stdClass $element
     */
    public function gradebookreport_density_plot_completed_items($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }
    
    /**
     * Test for normality on course grades.
     * @param stdClass $element
     */
    public function gradebookreport_test_for_normality_on_course_grades($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }
    
    /**
     * Test for normality on course grades.
     * @param stdClass $element
     *//*
    public function gradebookreport_test_for_normality_on_course_grades($element) {//TODO repeated - waiting instructions
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }*/
    
    /**
     * Heat map of grade distribution.
     * @param stdClass $element
     */
    public function gradebookreport_heat_map_of_grade_distribution($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }
    
    /************************** End Elements for Gradebook Report **************************/
    
    /************************** Course Header **************************/
    
    /**
     * Snap Dashboard Xray
     */
    public function snap_dashboard_xray() {
        global $COURSE;
        
        $output = "";
        
        try {
            $report = "dashboard";
            $response = \local_xray\api\wsapi::course($COURSE->id, $report);
        
            if(!$response) {
                // Fail response of webservice.
                \local_xray\api\xrayws::instance()->print_error();
                
            } else {
                
                // Get users in risk.
                $users_in_risk = array();
                if(isset($response->elements[1]->data) && !empty($response->elements[1]->data)) {
                    foreach($response->elements[1]->data as $key => $obj) {
                        if($obj->severity->value == "high") {
                            $users_in_risk[] = $obj->participantId->value;
                        }
                    }
                }
                // Student ins risk.
                $count_students_risk = (isset($response->elements[4]->items[5]->value) ? $response->elements[4]->items[5]->value : "-");
                // Students enrolled.
                $count_students_enrolled = (isset($response->elements[4]->items[2]->value) ? $response->elements[4]->items[2]->value : "-");
                // Visits last 7 days.
                $count_students_visits_lastsevendays = (isset($response->elements[4]->items[0]->value) ? $response->elements[4]->items[0]->value : "-");              
                // Risk previous 7 days.
                $count_students_risk_prev = (isset($response->elements[4]->items[6]->value) ? $response->elements[4]->items[6]->value : "-");         
                // Visits previous 7 days.
                $count_students_visits_prev = (isset($response->elements[4]->items[1]->value) ? $response->elements[4]->items[1]->value : "-");
                // Diff risk.
                $diff_risk = round((($count_students_risk - $count_students_risk_prev) / $count_students_risk_prev) * 100, 2);
                // Diff visits.
                $diff_visits = round((($count_students_visits_lastsevendays - $count_students_visits_prev) / $count_students_visits_prev) * 100, 2);
                //Students visits by week day
                $students_visits_by_weekday = (isset($response->elements[3]->data) ? $response->elements[3]->data : "-");
                
                $output .= $this->snap_dashboard_xray_output($users_in_risk, 
                		                                     $count_students_enrolled, 
                		                                     $count_students_risk, 
                		                                     $count_students_visits_lastsevendays,
                		                                     $diff_risk,
                		                                     $diff_visits,
                                                             $students_visits_by_weekday);
            }
        } catch(exception $e) {
        	$output .= get_string('error_xray', 'local_xray');
        }
        
        return $output;
    }
    
    /**
     * Snap Dashboard Xray
     * 
     * @param Int $users_in_risk
     * @param Int $students_enrolled
     * @param Int $students_risk
     * @param Int $students_visits_lastsevendays
     * @return string
     * */
    public function snap_dashboard_xray_output($users_in_risk, 
                                               $students_enrolled, 
                                               $students_risk, 
                                               $students_visits_lastsevendays, 
                                               $risk_fromlastweek, 
                                               $visitors_fromlastweek,
                                               $students_visits_by_weekday){
        
        global $DB, $OUTPUT;
        
        //JQuery to show all students
        $jscode = "$(function(){
            $('.xray_dashboard_seeall').click(function()
                {
                    var div = $('.xray_dashboard_users_risk_hidden');
                    startAnimation();
                    function startAnimation(){
                        div.slideToggle('slow');
                    }
                });
            });";
        
        $xray_dashboard_jquery = html_writer::script($jscode);
        
        $of = html_writer::tag('small', get_string('of', 'local_xray'));
        
        //Students at risk
        $atrisk = html_writer::tag('h3', get_string('atrisk', 'local_xray'));
        $students_atrisk = html_writer::div($students_risk.$of.$students_enrolled, 'h1');
        $studentatrisk = html_writer::div(get_string('studentatrisk', 'local_xray'));
        $atriskfromlastweek = html_writer::div(get_string('fromlastweek', 'local_xray', $risk_fromlastweek), 'xray-comparitor text-danger');
        
        $users_profile = "";
        $users_profile_hidden = "";
        $count_users = 1;
        $hide = false;
        if(!empty($users_in_risk)) {
            foreach($users_in_risk as $key => $id) {
                if($count_users > 6){
                    $users_profile_hidden .= $this->print_student_profile($DB->get_record('user', array("id" => $id)));
                }else{
                    $users_profile .= $this->print_student_profile($DB->get_record('user', array("id" => $id)));
                }
                
                $count_users++;
            }
        }
        
        $users_profile_box = html_writer::div($users_profile);
        $users_profile_box_hidden = html_writer::div($users_profile_hidden, 'xray_dashboard_users_risk_hidden');
        
        $showall = '';
        if(count($users_in_risk) > 6){
            $showall = html_writer::div('Show all', 'btn btn-default btn-sm xray_dashboard_seeall');
        }

        $atrisk_column = html_writer::div($xray_dashboard_jquery.$atrisk.$students_atrisk.$studentatrisk.$atriskfromlastweek.$users_profile_box.$users_profile_box_hidden.$showall, 'col-sm-6');
        
        //Students Visitors
        $visitors = html_writer::tag('h3', get_string('visitors', 'local_xray'));
        $students_visitors = html_writer::div($students_visits_lastsevendays.$of.$students_enrolled, 'h1');
        $studentvisitslastdays = html_writer::div(get_string('studentvisitslastdays', 'local_xray'));
        $visitorsfromlastweek = html_writer::div(get_string('fromlastweek', 'local_xray', $visitors_fromlastweek), 'xray-comparitor text-danger');
        
        //Create table for Students visits by Week Day
        $students_visits_weekday_htmltable = new html_table();
        $row = array();
        foreach($students_visits_by_weekday as $key => $value){
            $students_visits_weekday_htmltable->head[] = $value->day_of_week->value;
            $row[] = $value->number_of_visits->value;
        }
        
        $students_visits_weekday_htmltable->data[] = $row;
        $students_visits_weekday = html_writer::table($students_visits_weekday_htmltable);
        
        $visitors_column = html_writer::div($visitors.$students_visitors.$studentvisitslastdays.$visitorsfromlastweek.$students_visits_weekday, 'col-sm-6');
        
        return html_writer::div($atrisk_column.$visitors_column);
    }
    
    /**
     * 
     * @param array $users
     * @return string
     * 
     */
    public function snap_dashboard_xray_users_li($users){
        global $DB, $OUTPUT;

        $li = '';
        foreach($users as $key => $value){
            $user = $DB->get_record('user', array('id' => $value));
            $pic = html_writer::span($OUTPUT->user_picture($user, array('size'=>50)));
            $name = html_writer::span($user->firstname.' '.$user->lastname);
            $li .= html_writer::span($pic.$name);
        }
        
        return $li;
    }
    
    /**
     * Renderer (copy of print_teacher_profile in renderer.php of snap theme).
     * @param stdClass $user
     */
    public function print_student_profile($user) {
        global $CFG, $COURSE;
    
        $userpicture = new user_picture($user);
        $userpicture->link = false;
        $userpicture->alttext = false;
        $userpicture->size = 100;
        $picture = $this->render($userpicture);
    
        $fullname = '<a href="'.$CFG->wwwroot.'/user/profile.php?id='.$user->id.'">'.format_string(fullname($user)).'</a>';
        $coursecontext = context_course::instance($COURSE->id);
        $user->description = file_rewrite_pluginfile_urls($user->description,
                                                          'pluginfile.php', $coursecontext->id, 'user', 'profile', $user->id);
        $description = format_text($user->description, $user->descriptionformat);
    
        return "<div class='snap-media-object dashboard_xray_users_profile'>
                $picture
                <div class=snap-media-body>
                $fullname
                $description
                </div>
                </div>";
    }
    
    /************************** End Course Header **************************/

}