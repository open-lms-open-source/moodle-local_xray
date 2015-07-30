<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
require_once($CFG->dirroot.'/local/xray/classes/api/wsapi.php');

/**
 * Xray integration Reports Controller
 *
 * @author Pablo Pagnone
 * @author German Vitale
 * @package local_xray
 */
class local_xray_controller_reports extends mr_controller {
	
	/**
	 * Course id
	 * @var integer
	 */
	protected $xraycourseid;
	
	/**
	 * User id
	 * @var integer
	 */
	protected $xrayuserid;
	
	const XRAY_COURSEID = 7; //TODO:: Example first integration. This is hardcoded for test with xray.
	const XRAY_DOMAIN = "moodlerooms"; //TODO:: Example first integration. This is hardcoded for test with xray.
	const XRAY_USERID = 3; //TODO:: Example first integration. This is hardcoded for test with xray. User Bob Smith.
	
	/**
	 * Require capabilities
	 */
	public function require_capability() {
		require_capability("{$this->plugin}:{$this->name}_view", $this->get_context());
	}
    
    /**
     * Show data of last request to webservice xray.
     */
    public function debugwebservice() {
    	
    	echo "<pre>";
    	var_dump(\local_xray\api\xrayws::instance()->geterrorcode());
    	var_dump(\local_xray\api\xrayws::instance()->geterrormsg());
    	var_dump(\local_xray\api\xrayws::instance()->lastresponse());
    	var_dump(\local_xray\api\xrayws::instance()->lasthttpcode());
    	var_dump(\local_xray\api\xrayws::instance()->getinfo());
    	echo "</pre>";
    	exit();
    }
}
