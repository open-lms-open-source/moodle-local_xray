<?php
defined('MOODLE_INTERNAL') or die();
require_once($CFG->dirroot.'/local/mr/framework/controller.php');

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

    /**
     * @var bool
     */
    protected $ajax = false;

    public function init() {
        global $PAGE;
        parent::init();
        if(is_callable('mr_off') and mr_off('xray', 'local')) {
            exit();
        }
        // Use standard page layout for Moodle reports.
        $PAGE->set_pagelayout('report');
        $this->courseid = $PAGE->course->id;
    }

    protected function setajaxoutput() {
        global $PAGE;
        // This renders the page correctly using standard Moodle ajax renderer
        $this->output = $PAGE->get_renderer('core', null, RENDERER_TARGET_AJAX);
        $this->ajax = true;
    }

    public function print_header() {
        if ($this->ajax) {
            echo $this->output->header();
            return;
        }

        parent::print_header();
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
