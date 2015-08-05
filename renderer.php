<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
require_once($CFG->dirroot.'/local/xray/controller/reports.php');

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
     * @param String   $name 
     * @param stdClass $element
     */
    private function show_on_lightbox($name, $element) {
    	 
    	global $PAGE;
    
    	// Load Jquery.
    	$PAGE->requires->jquery();
    	$PAGE->requires->jquery_plugin('ui');
    	$PAGE->requires->jquery_plugin('local_xray-fancybox2', 'local_xray');  // Load jquery fancybox2
    	$PAGE->requires->jquery_plugin('local_xray-show_on_lightbox', 'local_xray'); // Js for show on lightbox.
    
    	$cfg_xray = get_config('local_xray');
    	$img_url = sprintf('%s/%s/%s', $cfg_xray->xrayurl, $cfg_xray->xrayclientid, $element->uuid);
    
    	$output = "";
    	$output .= html_writer::tag('div', get_string($name,"local_xray"), array("class" => "reportsname"));
    	$output .= html_writer::start_tag('a', array("class" => "fancybox", "href" => $img_url));
    	$output .= html_writer::empty_tag('img', array("class" => $name,
										    		   "title" => $element->tooltip,
										    		   "src" => $img_url)
    	                                  );
    	$output .= html_writer::end_tag('a');
    	 
    	return $output;
    	 
    }
    
    /**
     * Standard table Theme with Jquery datatables.
     * 
     * For this use you need:
     * - Add name of report to lang plugin.
     * - Add specific js for jquery plugin with this format:  "local_xray_" + %name% 
     * - Add function "local_xray_" + %name% in js "local_xray_" + %name%
     * 
     * The table will have id equal to name.
     * 
     * @param string $name (Name of report, this will be used by strings and id of table).
     * @param array $columns (Array of columns to show).
     * @param array $jsdata (Data for js).
     * @return string
     */
    private function standard_table ($name, array $columns, array $jsdata = array()) {
    	
    	global $CFG, $PAGE;
    	 
    	// Load Jquery.
    	$PAGE->requires->jquery();
    	$PAGE->requires->jquery_plugin('ui');
    	$PAGE->requires->jquery_plugin("local_xray-dataTables", "local_xray", true);
    	
    	// Load specific js of table.
    	$PAGE->requires->jquery_plugin("local_xray_{$name}", "local_xray", true);    	
    	
    	$output = "";
    	$output .= html_writer::tag('div', get_string($name,"local_xray"), array("class" => "reportsname"));
    	 
    	// Table jquery datatables for show reports.
    	$output .= "<table id='{$name}' class='display' cellspacing='0' width='100%'> <thead><tr>";
    	foreach($columns as $c){
        	$output .= "<th>{$c}</th>";
        }   			            
        $output .=" </tr> </thead> </table>";
        
        // Load table with data.
        $PAGE->requires->js_init_call("local_xray_{$name}", array($jsdata));		 
        
    	return $output;   	
    }
    /************************** End General elements for Reports **************************/
    
    /************************** Elements for Report Activity **************************/
    
    /**
     * Graphic students activity (TABLE)
     */
    public function activityreport_students_activity($courseid) {
    	
    	global $PAGE;
    	// Create standard table.
    	$output = $this->standard_table(__FUNCTION__, 
			    			            array("", // Empty for action column.
			    			            	  get_string('firstname', 'local_xray'),
			    			              	  get_string('lastname', 'local_xray'),
			    			              	  get_string('lastactivity', 'local_xray'),
			    			              	  get_string('discussionposts', 'local_xray'),
			    			              	  get_string('postslastweek', 'local_xray'),
			    			              	  get_string('timespentincourse', 'local_xray'),
                                              get_string('regularityweekly', 'local_xray')),
    			                        array("courseid" => $courseid));    			
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
    	// Create standard table.
    	$output = $this->standard_table(__FUNCTION__, 
			    			            array(get_string('firstname', 'local_xray'),
			    			              	  get_string('lastname', 'local_xray')),
    			                        array("courseid" => $courseid));
    	
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
    public function discussionreport_participation_metrics() {
         
        global $PAGE;
        // Create standard table.
        $output = $this->standard_table(__FUNCTION__,
                array("", // Empty for action column.
                        get_string('firstname', 'local_xray'),
                        get_string('lastname', 'local_xray'),
                        get_string('posts', 'local_xray'),
                        get_string('contribution', 'local_xray'),
                        get_string('ctc', 'local_xray'),
                        get_string('regularityofcontributions', 'local_xray'),
                        get_string('regularityofctc', 'local_xray')));
        return $output;
    }
    
    /**
     * Graphic Discussion Activity by Week (TABLE)
     * @param stdClass $element
     */
    public function discussionreport_discussion_activity_by_week($element) {
         
        global $PAGE;
        // Create standard table.
        
        //Get columns
        $columns = array();
        
        $element->data[0]->week;
        foreach($element->data as $column){
            $columns[] = $column->week->value;
        }

        $output = $this->standard_table(__FUNCTION__,
                                        $columns);
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
    /************************** End Elements for Report Discussion **************************/
    
    
    /************************** Elements for Report Discussion for an individual **************************/
    
   
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
    
    /************************** End Elements for Report Risk **************************/
    
    /**
     * Risk Measures(TABLE)
     * @param stdClass $element
     */
    public function risk_risk_measures($courseid) {
    	global $PAGE;
    	// Create standard table.
    	$output = $this->standard_table(__FUNCTION__,
						    			array(get_string('lastname', 'local_xray'),
						    				  get_string('firstname', 'local_xray'),
						    				  get_string('timespentincourse', 'local_xray'),
						    				  get_string('academicrisk', 'local_xray'),
						    				  get_string('socialrisk', 'local_xray'),
						    				  get_string('totalrisk', 'local_xray')),
    			                        array("courseid" => $courseid));
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
}