<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
require_once $CFG->dirroot.'/local/xray/classes/local_xray_reports_utils.php';

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
     */
    public function list_reports() {
    	global $CFG, $PAGE;
    	
    	// Load Jquery.
    	$PAGE->requires->jquery();
    	$PAGE->requires->jquery_plugin('ui');
        $PAGE->requires->jquery_plugin('local_xray-dataTables', 'local_xray');  // Load jquery datatables  
        $PAGE->requires->js('/local/xray/js/list_reports.js', true);
        
        // Strings for js.
        //$PAGE->requires->string_for_js('', 'local_xray');
    	
    	$output = "";
    	$output .= html_writer::tag('div', get_string("list_reports","local_xray"), array());
    	
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
    
}
