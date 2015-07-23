<?php
defined('MOODLE_INTERNAL') || die();
/**
 * Local xray lang file
 *
 * @author Pablo Pagnone
 * @author German Vitale
 * @package local_xray
 */


/**
 * Extend navigations block.
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
	$reports = local_xray_reports_utils::list_reports();
	if(!empty($reports)) {
		foreach($reports as $report) {
			$firstnode->add($report[1],
					        new moodle_url('/local/xray/view.php', array("controller" => $report[0])));			
		}
	}
}
