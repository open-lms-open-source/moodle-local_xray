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
    public function list_reports() {
        global $CFG, $PAGE;

        // Load Jquery.
        $PAGE->requires->jquery();
        $PAGE->requires->jquery_plugin('ui');
        $PAGE->requires->jquery_plugin('local_xray-dataTables', 'local_xray');  // Load jquery datatables  
        $PAGE->requires->jquery_plugin('local_xray-list_reports', 'local_xray');

        // Strings for js.
        //$PAGE->requires->string_for_js('', 'local_xray');

        $output = "";
        $output .= html_writer::tag('div', get_string("reports","local_xray"), array("class" => "reportsname"));
        
        // Table jquery datatables for show reports.
        $output .= "<table id='reportslist' class='display' cellspacing='0' width='100%'>
                    <thead>
                        <tr>
                            <th>Fullname</th>
                        </tr>
                    </thead>
                    </table>";

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

    /************************** Elements for Report Activity **************************/
    
    /**
     * Graphic students activity (TABLE)
     * @param stdClass $element
     */
    public function students_activity() {
    	
    	global $CFG, $PAGE;
    	
    	// Load Jquery.
    	$PAGE->requires->jquery();
    	$PAGE->requires->jquery_plugin('ui');
    	$PAGE->requires->jquery_plugin('local_xray-studentsactivity', 'local_xray');
    	
    	$output = "";
    	$output .= html_writer::tag('div', get_string("students_activity","local_xray"), array("class" => "reportsname"));
    	
    	// Table jquery datatables for show reports.
    	$output .= "<table id='students_activity' class='display' cellspacing='0' width='100%'>
                    <thead>
                        <tr>
                            <th>Lastname</th>
    			            <th>Firstname</th>
    			            <th>Last Activity</th>
    			            <th>Discussion Posts</th>
    			            <th>Posts last week</th>
    			            <th>Time spent in course</th>
    			            <th>Regularity (weekly)</th>
                        </tr>
                    </thead>
                    </table>";
    	
    	return $output;    	 
    }
    
    /**
     * Graphic activity of course by day.(Graph)
     * @param stdClass $element
     */
    public function activity_of_course_by_day($element) {    	
    	return $this->show_on_lightbox("activity_of_course_by_day", $element);	    	
    }
    
    /**
     * Graphic activity by time of day.(Graph)
     * @param stdClass $element
     */    
    public function activity_by_time_of_day($element) {
    	return $this->show_on_lightbox("activity_by_time_of_day", $element);     	
    }
    
    /**
     * Graphic activity last two weeks.(Graph)
     * @param stdClass $element
     */
    public function activity_last_two_weeks($element) {
    	return $this->show_on_lightbox("activity_last_two_weeks", $element);
    }  
    
    /**
     * Graphic activity last two weeks BY weekday.(Graph)
     * @param stdClass $element
     */
    public function activity_last_two_weeks_by_weekday($element) {
    	return $this->show_on_lightbox("activity_last_two_weeks_by_weekday", $element); 	
    }
    
    /**
     * Graphic activity by participant 1
     * @param stdClass $element
     */
    public function activity_by_participant1($element) {
    	return $this->show_on_lightbox("activity_by_participant1", $element); 	
    }
    
    /**
     * Graphic activity by participant 2
     * @param stdClass $element
     */
    public function activity_by_participant2($element) {
    	return $this->show_on_lightbox("activity_by_participant2", $element);
    }    
    
    /**
     * Graphic first login non startes (TABLE)
     * @param stdClass $element
     */    
    public function first_login_non_starters() {
    	
    	global $CFG, $PAGE;
    	$output = "";
    	 
    	// Load Jquery.
    	$PAGE->requires->jquery();
    	$PAGE->requires->jquery_plugin('ui');
    	$PAGE->requires->jquery_plugin('local_xray-firstloginnonstarters', 'local_xray');
    	 
    	$output .= html_writer::tag('div', get_string("first_login_non_starters","local_xray"), array("class" => "reportsname"));   	 
    	
    	// Table jquery datatables for show reports.
    	$output .= "<table id='first_login_non_starters' class='display' cellspacing='0' width='100%'>
                    <thead>
                        <tr>
                            <th>Lastname</th>
    			            <th>Firstname</th>
                        </tr>
                    </thead>
                    </table>";
    	 
    	return $output;    	
    }
    
    /**
     * Graphic frist login to course
     * @param stdClass $element
     */    
    public function first_login_to_course($element) {
    	return $this->show_on_lightbox("first_login_to_course", $element);    	
    }
    
    /**
     * Graphic first login date observed
     * @param stdClass $element
     */    
    public function first_login_date_observed($element) {
    	return $this->show_on_lightbox("first_login_date_observed", $element);
    }
    
    
    
    /************************** End Elements for Report Activity **************************/
    
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
