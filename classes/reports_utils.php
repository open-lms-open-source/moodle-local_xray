<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');

/**
 * Xray general functions for reports
 *
 * @author Pablo Pagnone
 * @package local_xray
 */
class local_xray_reports_utils {

    /**
     * Return list of reports of xray
     * TODO: Example
     */
    static function list_reports() {
        return array(array("activityreport", get_string("activityreport", "local_xray")),
                     //array("activityreportindividual", get_string("activityreportindividual", "local_xray")), // TODO:: This is accessed from activityreport.
                     array("discussionreport", get_string("discussionreport", "local_xray")),
                     array("discussionreportindividual", get_string("discussionreportindividual", "local_xray")),
        );
    }
}
