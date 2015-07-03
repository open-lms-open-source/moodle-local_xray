<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');

/**
 * Xray integration Default Controller
 *
 * @author Pablo Pagnone
 * @author German Vitale
 * @package local/xray
 */
class local_xray_controller_default extends mr_controller {

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
     * Main view action
     *
     */
    public function view_action() {
        global $OUTPUT, $PAGE, $COURSE;
        $output = get_string("welcome_xray","local_xray");

        return $output;
    }
}