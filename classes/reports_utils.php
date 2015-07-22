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
        return array(array("activity_of_student_by_day", get_string("report_activity_of_student_by_day", "local_xray")),
		             array("reportb", get_string("reportb", "local_xray")),
		             array("discussion_by_user", get_string("report_discussion_by_user", "local_xray")),
		             array("reportd", get_string("reportd", "local_xray"))
        );
    }
}
