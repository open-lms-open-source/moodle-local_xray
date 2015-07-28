<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
require_once($CFG->dirroot.'/local/xray/controller/reports.php');


class local_xray_renderer_activityreport extends local_xray_renderer {
	
}

/**
 * Renderer
 *
 * @author Pablo Pagnone
 * @package local_xray
 */
class local_xray_renderer extends plugin_renderer_base {

    /**
     * Welcome page.
     */
    public function welcome() {
        global $CFG, $PAGE;
        $output = get_string("welcome_xray","local_xray");
        return html_writer::tag('div', $output, array());
    }
    
    /**
     * List reports.
     * Example of implementation of table with jquery datatable.
     */
    public function reports_list() {
    	
    	global $PAGE;
    	// Create standard table.
    	$output = $this->standard_table(__FUNCTION__,
						    			array(get_string('fullname', 'local_xray')));
    	return $output;
    }
    
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
    	$output .= html_writer::tag("p", get_string(("date")).": ".$reportdate);
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
    
    	$baseurl  = get_config("local_xray", 'xrayurl');
    	$domain = local_xray_controller_reports::XRAY_DOMAIN;
    	$img_url = sprintf('%s/%s/%s', $baseurl, $domain, $element->uuid);
    
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
     * - Add specific js for jquery plugin with this format:  "local_xray-" + %name% 
     * 
     * The table will have id equal to name.
     * 
     * @param string $name (Name of report, this will be used by strings and id of table).
     * @param array $columns (Array of columns to show).
     * @return string
     */
    private function standard_table ($name, array $columns) {
    	
    	global $CFG, $PAGE;
    	 
    	// Load Jquery.
    	$PAGE->requires->jquery();
    	$PAGE->requires->jquery_plugin('ui');
    	$PAGE->requires->jquery_plugin("local_xray-dataTables", "local_xray");
    	
    	// Load specific js of table.
    	$PAGE->requires->jquery_plugin("local_xray-{$name}", "local_xray");    	
    	
    	$output = "";
    	$output .= html_writer::tag('div', get_string($name,"local_xray"), array("class" => "reportsname"));
    	 
    	// Table jquery datatables for show reports.
    	$output .= "<table id='{$name}' class='display' cellspacing='0' width='100%'> <thead><tr>";
    	foreach($columns as $c){
        	$output .= "<th>{$c}</th>";
        }   			            
        $output .=" </tr> </thead> </table>";
    	 
    	return $output;   	
    }
    /************************** End General elements for Reports **************************/
    
    /************************** Elements for Report Activity **************************/
    
    /**
     * Graphic students activity (TABLE)
     */
    public function activityreport_students_activity() {
    	
    	global $PAGE;
    	// Create standard table.
    	$output = $this->standard_table(__FUNCTION__, 
			    			            array(get_string('firstname', 'local_xray'),
			    			              	  get_string('lastname', 'local_xray'),
			    			              	  get_string('lastactivity', 'local_xray'),
			    			              	  get_string('discussionposts', 'local_xray'),
			    			              	  get_string('postslastweek', 'local_xray'),
			    			              	  get_string('timespentincourse', 'local_xray'),
                                              get_string('regularityweekly', 'local_xray')));    			
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
    public function activityreport_first_login_non_starters() {   	
    	global $PAGE;
    	// Create standard table.
    	$output = $this->standard_table(__FUNCTION__, 
			    			            array(get_string('firstname', 'local_xray'),
			    			              	  get_string('lastname', 'local_xray')));    			
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
                array(get_string('firstname', 'local_xray'),
                        get_string('lastname', 'local_xray'),
                        get_string('lastactivity', 'local_xray'),
                        get_string('posts', 'local_xray'),
                        get_string('contribution', 'local_xray'),
                        get_string('ctc', 'local_xray'),
                        get_string('regularityofcontributions', 'local_xray'),
                        get_string('regularityofctc', 'local_xray')));
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
    
    public function discussion_by_user(){
    
        global $CFG, $PAGE, $OUTPUT;
    
        // Load Jquery.
        $PAGE->requires->jquery();
        $PAGE->requires->jquery_plugin('ui');
        $PAGE->requires->jquery_plugin('local_xray-jssor', 'local_xray');
        $PAGE->requires->jquery_plugin('local_xray-image_gallery_with_vertical_thumbnail', 'local_xray');
    
        $output = "";
        $output .= html_writer::tag('div', get_string("report_activity_of_student_by_day","local_xray"), array("class" => "reportsname"));
    
        // TODO:: Change url of image to load.
        /*$output .= html_writer::start_tag('a', array("class" => "", "href" => "http://www.techjournal.org/wp-content/uploads/2011/06/Moodlerooms-01.jpg"));
        $output .= html_writer::empty_tag('img', array("class" => "",
        "src" => $OUTPUT->pix_url("image-test-1", "local_xray")
        ));
        $output .= html_writer::end_tag('a');*/
    
    
    
    
        $loading_screen_content = html_writer::tag('div', '', array('style' => 'filter: alpha(opacity=70); opacity:0.7; position: absolute; display: block;
                background-color: #000000; top: 0px; left: 0px;width: 100%;height:100%;'));
        $loading_screen_content .= html_writer::tag('div', '', array('style' => 'position: absolute; display: block; background: url(../img/loading.gif) no-repeat center center;
                top: 0px; left: 0px;width: 100%;height:100%;'));
        $loading_screen = html_writer::tag('div', $loading_screen_content, array('u' => 'loading', 'style' => 'position: absolute; top: 0px; left: 0px;'));
    
                    //TODO It will be modified when we get real data
        $n = 1;
        $images = '';
        while ($n <= 9){
        $normal = html_writer::img('pix/discussion_user/'.$n.'.png', '??', array('u' => 'image'));
        $thumb = html_writer::img('pix/discussion_user/'.$n.'.png', '??', array('u' => 'thumb'));
        $image = html_writer::tag('div', $normal.$thumb);
        $images .= $image;
                $n++;
        }
    
        $slides_container = html_writer::tag('div', $images, array('u' => 'slides', 'style' => 'cursor: move; position: absolute; left: 240px; top: 0px; width: 720px; height: 480px; overflow: hidden;'));
    
        $arrowleft = html_writer::span('', '', array('u' => 'arrowleft', 'class' => 'jssora05l', 'style' => 'top: 158px; left: 248px;'));
        $arrowright = html_writer::span('', '', array('u' => 'arrowright', 'class' => 'jssora05r', 'style' => 'top: 158px; right: 8px'));
    
        $w = html_writer::tag('div', html_writer::empty_tag('div', array('u' => 'thumbnailtemplate', 'class' => 't')));
        $c = html_writer::tag('div', '', array('class' => 'c'));
    
        $prototype = html_writer::tag('div', $w.$c, array('u' => 'prototype', 'class' => 'p'));
        $thumbnail_item_skin_begin = html_writer::tag('div', $prototype, array('u' => 'slides', 'style' => 'cursor: default;'));
        $thumbnail_navigator_container = html_writer::tag('div', $thumbnail_item_skin_begin, array('u' => 'thumbnavigator', 'class' => 'jssort02', 'style' => 'left: 0px; bottom: 0px;'));
    
                $output .= html_writer::tag('div', $loading_screen.$slides_container.$arrowleft.$arrowright.$thumbnail_navigator_container, array('id' => 'slider1_container', 'style' => 'position: relative; top: 0px; left: 0px; width: 960px;
                height: 480px; background: #191919; overflow: hidden;'));//TODO add in css
    
                return $output;
    }
    
}
