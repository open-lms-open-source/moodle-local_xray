<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
require_once $CFG->dirroot.'/local/xray/classes/local_xray_reports_utils.php';

/**
 * Xray integration Reports Controller
 *
 * @author Pablo Pagnone
 * @author German Vitale
 * @package local/xray
 */
class local_xray_controller_reports extends mr_controller {

    /**
     * Require capabilities
     */
    public function require_capability() {
    }

    /**
     * Controller Initialization
     *
     */
    public function init() {

    }
    
    /**
     * List of reports
     *
     */
    public function list_action() {
    	global $OUTPUT, $PAGE, $COURSE;
    	
    	// Add title to breadcrumb.
    	$PAGE->navbar->add(get_string('pluginname', 'local_xray'));
    	$output  = $this->output->list_reports();
    	return $output;
    }
    
    public function jsonlist_action() {
    	
        echo json_encode(array("data" => local_xray_reports_utils::list_reports()));
    	exit();
    }
    
    /**
     * Report A
     *
     */
    public function reporta_action() {
        global $OUTPUT, $PAGE, $COURSE;
        $output = "";

        return $output;
    }
    
    /**
     * Report B
     *
     */
    public function reportb_action() {
    	global $OUTPUT, $PAGE, $COURSE;
    	$output = "";
    
    	return $output;
    }
    
    /**
     * Report C
     *
     */
    public function reportc_action() {
    	global $OUTPUT, $PAGE, $COURSE;
    	$output = "";
    
    	return $output;
    }    
    
}