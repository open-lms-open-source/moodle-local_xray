<?php
defined('MOODLE_INTERNAL') || die();
/**
 * Local xray lang file
 *
 * @author Pablo Pagnone
 * @author German Vitale
 * @package local/xray
 */


/**
 * Extend navigations
 */
function local_xray_extends_navigation(global_navigation $nav) {

	global $PAGE, $COURSE;
	
	// Add links on block navigation.
	$extranavigation = $PAGE->navigation->add(get_string('navigation_xray', 'local_xray'), 
			                                  new moodle_url('/local/xray/view.php', 
			                                  		        array("controller" => "default", "action" => "view")), 
			                                  navigation_node::TYPE_CONTAINER);
	
	$firstnode = $extranavigation->add(get_string('reports', 'local_xray'), 
												  new moodle_url('/local/xray/view.php', 
															     array("controller" => "reports", "action" => "list")));
	
	// Add links to reports.
	$firstnode->add(get_string('report1', 'local_xray'), 
			        new moodle_url('/local/xray/view.php', array("controller" => "reports", "action" => "reporta")));
	$firstnode->add(get_string('report2', 'local_xray'), 
			        new moodle_url('/local/xray/view.php', array("controller" => "reports", "action" => "reportb")));
	$firstnode->add(get_string('report3', 'local_xray'), 
			        new moodle_url('/local/xray/view.php', array("controller" => "reports", "action" => "reportc")));		

}

/**
 * Extend setting navigation 
 * This example generate links of access on block administration in courses.
 */
 
function local_xray_extends_settings_navigation($settingsnav, $context) {
	global $CFG, $PAGE;

	// Only add this settings item on non-site course pages.
	if (!$PAGE->course or $PAGE->course->id == 1) {
		return;
	}

	if ($settingnode = $settingsnav->find('courseadmin', navigation_node::TYPE_COURSE)) {
			
	// Add links on block navigation.
	$extranavigation = $settingnode->add(get_string('navigation_xray', 'local_xray'), 
			                                  new moodle_url('/local/xray/view.php', 
			                                  		        array("controller" => "default", "action" => "view")), 
			                                  navigation_node::TYPE_CONTAINER);
	
	$firstnode = $extranavigation->add(get_string('reports', 'local_xray'), 
												  new moodle_url('/local/xray/view.php', 
															     array("controller" => "reports", "action" => "list")));
	
	// Add links to reports.
	$firstnode->add(get_string('report1', 'local_xray'), 
			        new moodle_url('/local/xray/view.php', array("controller" => "reports", "action" => "reporta")));
	$firstnode->add(get_string('report2', 'local_xray'), 
			        new moodle_url('/local/xray/view.php', array("controller" => "reports", "action" => "reportb")));
	$firstnode->add(get_string('report3', 'local_xray'), 
			        new moodle_url('/local/xray/view.php', array("controller" => "reports", "action" => "reportc")));	
	
	}
}
