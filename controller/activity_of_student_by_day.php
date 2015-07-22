<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
require($CFG->dirroot.'/local/xray/controller/reports.php');

/**
 * Xray integration Reports Controller
 *
 * @author Pablo Pagnone
 * @author German Vitale
 * @package local_xray
 */
class local_xray_controller_activity_of_student_by_day extends local_xray_controller_reports {

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
     * Report A example
     * Example: Activity of student by day.
     *
     */
    public function view_action() {
    	
        global $OUTPUT, $PAGE, $COURSE;
        
        // Add title to breadcrumb.
        $PAGE->navbar->add(get_string('report_activity_of_student_by_day', 'local_xray'));
        $output = "";

        // TODO:: Test example call webservice
        $domain = parent::XRAY_DOMAIN;
        $courseid = parent::XRAY_COURSEID;
        $report = "activity";
        $element = "element3"; // Only we need show one graphic here.

        try {
     
        	$response = \local_xray\api\wsapi::courseelement($domain, $courseid, $element, $report);
        	if(!$response) {
        		// TODO:: Evaluate response in error case.
        		$output .= "Error to connect webservice: ".$e->getMessage();
        	} else {
        		$output .= $this->output->activity_of_student_by_day($response);
        	}
 
        } catch(exception $e) {
        
        	// TODO:: Evaluate response in error case.
        	$output .= "Error to connect webservice: ".$e->getMessage();
        }

        return $output;
    }
}
