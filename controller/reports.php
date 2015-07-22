<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
require($CFG->dirroot.'/local/xray/classes/api/wsapi.php');

/**
 * Xray integration Reports Controller
 *
 * @author Pablo Pagnone
 * @author German Vitale
 * @package local_xray
 */
class local_xray_controller_reports extends mr_controller {
	
	const XRAY_COURSEID = 7; //TODO:: Example first integration. This is hardcoded for test with xray.
	const XRAY_DOMAIN = "moodlerooms"; //TODO:: Example first integration. This is hardcoded for test with xray.
	
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
     * List of reports (Example using jquery datatable).
     *
     */
    public function list_action() {
        global $OUTPUT, $PAGE, $COURSE;
        
        // Add title to breadcrumb.
        $PAGE->navbar->add(get_string('pluginname', 'local_xray'));
        $output  = $this->output->list_reports();
        return $output;
    }
    
    /**
     * Return json with list of reports.
     */
    public function jsonlist_action() {
        
        echo json_encode(array("data" => local_xray_reports_utils::list_reports()));
        exit();
    }
}
