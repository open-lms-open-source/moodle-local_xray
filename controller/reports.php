<?php
defined('MOODLE_INTERNAL') or die();

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
    protected $courseid;

    /**
     * User id
     * @var integer
     */
    protected $userid;

    public function init() {
        parent::init();
        if(is_callable('mr_on') && !mr_on("xray", "_MR_LOCAL")) {
            exit();
        }
    }

    public function setup(){
        global $PAGE;
        $PAGE->set_context($this->get_context());
        parent::setup();
    }


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
