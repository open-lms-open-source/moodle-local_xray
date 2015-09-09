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
    	if(!empty($course)) {
    		$output .= html_writer::tag("p", get_string(("course")).": ".$course);
    	}    	
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
        $output .= html_writer::tag('div', get_string($name,$plugin), array("class" => "reportsname"));
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
    
            /* Legend */
            /*
             $legend = "";
             if(isset($element->legend) && !empty($element->legend)) {
             $legend = $element->legend;
             }
             $output .= html_writer::tag("div", $legend, array("class" => "xray_graph_legend"));*/
            /* End legend */
    
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
        $output .= html_writer::tag('div', get_string($data['id'],"local_xray"), array("class" => "reportsname eventtoogletable"));

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
    public function activityreport_students_activity($courseid) {
    	
    	$columns = array(new local_xray_datatableColumn('action', ''),
    			         new local_xray_datatableColumn('firstname', get_string('firstname', 'local_xray')),
    			         new local_xray_datatableColumn('lastname', get_string('lastname', 'local_xray')),
		    			 new local_xray_datatableColumn('lastactivity', get_string('lastactivity', 'local_xray')),    			
		    			 new local_xray_datatableColumn('discussionposts', get_string('discussionposts', 'local_xray')),    			
		    			 new local_xray_datatableColumn('postslastweek', get_string('postslastweek', 'local_xray')), 
		    			 new local_xray_datatableColumn('timespentincourse', get_string('timespentincourse', 'local_xray')),
		    			 new local_xray_datatableColumn('regularityweekly', get_string('regularityweekly', 'local_xray'))
    	);
    	
    	$datatable = new local_xray_datatable(__FUNCTION__, 
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
    public function activityreport_first_login_non_starters($courseid) {   	
    	global $PAGE;
    	
    	$columns = array(new local_xray_datatableColumn('firstname', get_string('firstname', 'local_xray')),
    			         new local_xray_datatableColumn('lastname', get_string('lastname', 'local_xray'))
    	);
    	 
    	$datatable = new local_xray_datatable(__FUNCTION__,
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
    public function discussionreport_participation_metrics($courseid) {
         
        global $PAGE;
        // Create standard table.
        $columns = array(new local_xray_datatableColumn('action', ''),
                new local_xray_datatableColumn('firstname', get_string('lastname', 'local_xray')),
                new local_xray_datatableColumn('lastname', get_string('lastname', 'local_xray')),
                new local_xray_datatableColumn('posts', get_string('posts', 'local_xray')),
                new local_xray_datatableColumn('contribution', get_string('contribution', 'local_xray')),
                new local_xray_datatableColumn('ctc', get_string('ctc', 'local_xray')),
                new local_xray_datatableColumn('regularityofcontributions', get_string('regularityofcontributions', 'local_xray')),
                new local_xray_datatableColumn('regularityofctc', get_string('regularityofctc', 'local_xray'))
        );
         
        $datatable = new local_xray_datatable(__FUNCTION__,
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
     * Social structure with words count.(Graph)
     * @param stdClass $element
     */
    public function discussionreport_social_structure_with_words_count($element) {
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
    public function discussionreportindividual_participation_metrics($courseid, $userid) {
         
        global $PAGE;
        // Create standard table.
        $columns = array(new local_xray_datatableColumn('lastname', get_string('lastname', 'local_xray')),
                new local_xray_datatableColumn('firstname', get_string('lastname', 'local_xray')),
                new local_xray_datatableColumn('posts', get_string('posts', 'local_xray')),
                new local_xray_datatableColumn('contribution', get_string('contribution', 'local_xray')),
                new local_xray_datatableColumn('ctc', get_string('ctc', 'local_xray')),
                new local_xray_datatableColumn('regularityofcontributions', get_string('regularityofcontributions', 'local_xray')),
                new local_xray_datatableColumn('regularityofctc', get_string('regularityofctc', 'local_xray'))
        );
         
        $datatable = new local_xray_datatable(__FUNCTION__,
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
    public function risk_risk_measures($courseid) {
    	global $PAGE;
    	
    	$columns = array(new local_xray_datatableColumn('lastname', get_string('lastname', 'local_xray')),
		    			 new local_xray_datatableColumn('firstname', get_string('firstname', 'local_xray')),
		    			 new local_xray_datatableColumn('timespentincourse', get_string('timespentincourse', 'local_xray')),
		    			 new local_xray_datatableColumn('academicrisk', get_string('academicrisk', 'local_xray')),
		    			 new local_xray_datatableColumn('socialrisk', get_string('socialrisk', 'local_xray')),
		    			 new local_xray_datatableColumn('totalrisk', get_string('totalrisk', 'local_xray'))
    	);
    	 
    	$datatable = new local_xray_datatable(__FUNCTION__,
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
    public function discussiongrading_students_grades_based_on_discussions($courseid) {
    	
    	global $PAGE;
    	 
    	$columns = array(new local_xray_datatableColumn('lastname', get_string('lastname', 'local_xray')),
		    			 new local_xray_datatableColumn('firstname', get_string('firstname', 'local_xray')),
		    			 new local_xray_datatableColumn('numposts', get_string('numposts', 'local_xray')),
		    			 new local_xray_datatableColumn('wordcount', get_string('wordcount', 'local_xray')),
		    			 new local_xray_datatableColumn('regularity_contributions', get_string('regularity_contributions', 'local_xray')),
		    			 new local_xray_datatableColumn('critical_thinking_coefficient', get_string('critical_thinking_coefficient', 'local_xray')),
		    			 new local_xray_datatableColumn('grade', get_string('grade', 'local_xray'))
    	);
    
    	$datatable = new local_xray_datatable(__FUNCTION__,
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
    public function gradebookreport_students_grades_for_course($courseid) {
         
        $columns = array(new local_xray_datatableColumn('action', ''),
                new local_xray_datatableColumn('lastname', get_string('lastname', 'local_xray')),
                new local_xray_datatableColumn('firstname', get_string('firstname', 'local_xray')),
                new local_xray_datatableColumn('grade', get_string('grade', 'local_xray')),
                new local_xray_datatableColumn('percentage', get_string('percentage', 'local_xray'))
        );
         
        $datatable = new local_xray_datatable(__FUNCTION__,
                "view.php?controller='gradebookreport'&action='jsonstudentsgradesforcourse'&courseid=".$courseid,
                $columns);
         
        // Create standard table.
        $output = $this->standard_table((array) $datatable);
         
        return $output;
    }
    
    /**
     * Students' Grades on completed items course (TABLE)
     */
    public function gradebookreport_students_grades_on_completed_items_course($courseid) {
         
        $columns = array(new local_xray_datatableColumn('action', ''),
                new local_xray_datatableColumn('lastname', get_string('lastname', 'local_xray')),
                new local_xray_datatableColumn('firstname', get_string('firstname', 'local_xray')),
                new local_xray_datatableColumn('completed', get_string('completed', 'local_xray')),
                new local_xray_datatableColumn('percentage', get_string('percentage', 'local_xray')),
                new local_xray_datatableColumn('grade', get_string('grade', 'local_xray'))
        );
         
        $datatable = new local_xray_datatable(__FUNCTION__,
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
     * Course Header
     */
    public function course_header() {
        
        //TODO harcoded
        $students_total = 150;
        $students_risk = 6;
        $students_visitors = 20;
        $percentaje_risk_flw = 4;
        $percentaje_visitors_flw = 4; 
        
        
        $of = html_writer::tag('small', get_string('of', 'local_xray'));
        
        //Students at risk
        $atrisk = html_writer::tag('h3', get_string('atrisk', 'local_xray'));
        $students_atrisk = html_writer::div($students_risk.$of.$students_total, 'h1');
        $studentatrisk = html_writer::div(get_string('studentatrisk', 'local_xray'));
        $atriskfromlastweek = html_writer::div(get_string('fromlastweek', 'local_xray', $percentaje_risk_flw), 'xray-comparitor text-danger');
        
        //TODO shall we use col-sm-6 class?
        $atrisk_column = html_writer::div($atrisk.$students_atrisk.$studentatrisk.$atriskfromlastweek, 'local_xray_course_atrisk');
        
        //Students Visitors
        $visitors = html_writer::tag('h3', get_string('visitors', 'local_xray'));
        $students_visitors = html_writer::div($students_visitors.$of.$students_total, 'h1');
        $studentvisitslastdays = html_writer::div(get_string('studentvisitslastdays', 'local_xray'));
        $visitorsfromlastweek = html_writer::div(get_string('fromlastweek', 'local_xray', $percentaje_visitors_flw), 'xray-comparitor text-danger');
        
        //TODO shall we use col-sm-6 class?
        $visitors_column = html_writer::div($visitors.$students_visitors.$studentvisitslastdays.$visitorsfromlastweek, 'local_xray_course_visitors');
        
        return html_writer::div($atrisk_column.$visitors_column);
    
    }
    
    /************************** End Course Header **************************/

}