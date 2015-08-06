<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
require_once($CFG->dirroot.'/local/xray/controller/reports.php');
require_once($CFG->dirroot.'/local/xray/classes/local_xray_utils.php');

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
    private function standard_table ($data) {
    	
    	global $CFG, $PAGE;
    	
    	// Load Jquery.
    	$PAGE->requires->jquery();
    	$PAGE->requires->jquery_plugin('ui');
    	// Load specific js for tables.
    	$PAGE->requires->jquery_plugin("local_xray-show_on_table", "local_xray", true);    	
    	
    	$output = "";
    	$output .= html_writer::tag('div', get_string($data['id'],"local_xray"), array("class" => "reportsname"));
    	 
    	// Table jquery datatables for show reports.
    	$output .= "<table id='{$data['id']}' class='display' cellspacing='0' width='100%'> <thead><tr>";
    	foreach($data['columns'] as $c){
    	    if(is_int($c->mData)){//TODO delete this, it is only to see discussion by week columns
    	        $output .= "<th>".$c->mData."</th>";
    	    }else{
    	        $output .= "<th>".get_string($c->mData,"local_xray")."</th>";
    	    }
        }   			            
        $output .=" </tr> </thead> </table>";
        
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
    	
    	$columns = array(new local_xray_datatableColumn('action'),
    			         new local_xray_datatableColumn('firstname'),
    			         new local_xray_datatableColumn('lastname'),
		    			 new local_xray_datatableColumn('lastactivity'),    			
		    			 new local_xray_datatableColumn('discussionposts'),    			
		    			 new local_xray_datatableColumn('postslastweek'), 
		    			 new local_xray_datatableColumn('timespentincourse'),
		    			 new local_xray_datatableColumn('regularityweekly')
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
    	
    	$columns = array(new local_xray_datatableColumn('firstname'),
    			         new local_xray_datatableColumn('lastname')
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
        $columns = array(new local_xray_datatableColumn('action'),
                new local_xray_datatableColumn('firstname'),
                new local_xray_datatableColumn('lastname'),
                new local_xray_datatableColumn('posts'),
                new local_xray_datatableColumn('contribution'),
                new local_xray_datatableColumn('ctc'),
                new local_xray_datatableColumn('regularityofcontributions'),
                new local_xray_datatableColumn('regularityofctc')
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
        foreach($element->data as $column){
            $columns[] = new local_xray_datatableColumn($column->week->value);
        }

        $datatable = new local_xray_datatable(__FUNCTION__,
                "view.php?controller='discussionreport'&action='jsonweekdiscussion'&courseid=".$courseid,
                $columns);

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
    	
    	$columns = array(new local_xray_datatableColumn('lastname'),
		    			 new local_xray_datatableColumn('firstname'),
		    			 new local_xray_datatableColumn('timespentincourse'),
		    			 new local_xray_datatableColumn('academicrisk'),
		    			 new local_xray_datatableColumn('socialrisk'),
		    			 new local_xray_datatableColumn('totalrisk')
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
}