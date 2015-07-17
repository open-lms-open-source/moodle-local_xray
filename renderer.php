<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');

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
     * Example of report activity of student by day.
     */
    public function report_activity_of_student_by_day() {
    	
    	global $CFG, $PAGE, $OUTPUT;
    	
    	// Load Jquery.
    	$PAGE->requires->jquery();
    	$PAGE->requires->jquery_plugin('ui');
    	$PAGE->requires->jquery_plugin('local_xray-fancybox2', 'local_xray');  // Load jquery fancybox2 	
        $PAGE->requires->jquery_plugin('local_xray-show_on_lightbox', 'local_xray'); // Js for show on lightbox.
        
    	$output = "";
    	$output .= html_writer::tag('div', get_string("report_activity_of_student_by_day","local_xray"), array("class" => "reportsname"));
    	
    	// TODO:: Change url of image to load.
    	$output .= html_writer::start_tag('a', array("class" => "fancybox", "href" => "http://www.techjournal.org/wp-content/uploads/2011/06/Moodlerooms-01.jpg"));
    	$output .= html_writer::empty_tag('img', array("class" => "report_activity_of_student_by_day",
    			                                       "src" => $OUTPUT->pix_url("report_activity_of_student_by_day", "local_xray")
    	                                  ));
    	$output .= html_writer::end_tag('a');
    	
    	return $output;    	
    	
    }
    
}
