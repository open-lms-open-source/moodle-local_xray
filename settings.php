<?php
defined('MOODLE_INTERNAL') || die;
/**
 * Local xray settings
 *
 * @author Pablo Pagnone
 * @author German Vitale
 * @package local/xray
 */
if ($hassiteconfig) { // needs this condition or there is error on login page
	
	require_once $CFG->dirroot.'/local/xray/classes/local_xray_reports_utils.php';
	
	$settings = new admin_settingpage('local_xray',
			                          get_string('pluginname', 'local_xray'));
	
	// Xray url webservice
	$settings->add(new admin_setting_configtext("xrayurl",
					get_string("xrayurl","local_xray"),
					get_string("xrayurl_desc","local_xray"),
					""));

	// Xray user webservice
	$settings->add(new admin_setting_configtext("xrayusername",
					get_string("xrayusername","local_xray"),
					get_string("xrayusername_desc","local_xray"),
					""));
	// Xray password webservice
	$settings->add(new admin_setting_password_unmask_encrypted("xraypassword",
					get_string("xraypassword","local_xray"),
					get_string("xraypassword_desc","local_xray"),
					""));
	
	// Xray client identifier webservice
	$settings->add(new admin_setting_configtext("xrayclientid",
					get_string("xrayclientid","local_xray"),
					get_string("xrayclientid_desc","local_xray"),
					""));
	
	$reports = local_xray_reports_utils::list_reports();
	if(!empty($reports)) {
		$r = array();
		foreach($reports as $reportid => $reportname) {
			$r[$reportid] = $reportname;
		}
		
		$settings->add(new admin_setting_configmulticheckbox("enabledreports",
						get_string("enabledreports","local_xray"),
						get_string("enabledreports_desc","local_xray"),
						"",
						$r));
	}
	
	$ADMIN->add('localplugins', $settings);
}
