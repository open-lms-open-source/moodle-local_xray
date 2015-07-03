<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');

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
    	$output = get_string("list_reports", "local_xray");
    
    	return $output;
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