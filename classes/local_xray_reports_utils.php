<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');

/**
 * Xray general functions for reports
 *
 * @author Pablo Pagnone
 * @package local/xray
 */
class local_xray_reports_utils {
	
	/**
	 * Return list of reports of xray
	 * TODO: Example
	 */
	static function list_reports() {
		
		return array(new local_xray_report("reporta", get_string("reporta", "local_xray")),
				     new local_xray_report("reportb", get_string("reportb", "local_xray")),
				     new local_xray_report("reportc", get_string("reportc", "local_xray")),
				     new local_xray_report("reportd", get_string("reportd", "local_xray"))
		);
	} 
}

class local_xray_report {
	
	public $id;
	public $fullname;
	
	public function __construct($id, $fullname) {
		$this->id = $id;
		$this->fullname = $fullname;
	}
	
}