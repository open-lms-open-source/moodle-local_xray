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

    public function setup() {
        global $CFG, $COURSE, $PAGE;

        require_login((int)optional_param('courseid', SITEID, PARAM_ALPHANUM));

        // We want to send relative URL to $PAGE so $PAGE can set it to https or not
        $moodleurl   = $this->new_url(array('action' => $this->action));
        $relativeurl = str_replace($CFG->wwwroot, '', $moodleurl->out_omit_querystring());

        $title = format_string(get_string($this->name, $this->component));
        $PAGE->set_title($title);
        $this->heading->text = $title;

        //$PAGE->set_title(format_string($COURSE->fullname));
        //$PAGE->set_heading(format_string($COURSE->fullname));
        $PAGE->set_context($this->get_context());
        $PAGE->set_url($relativeurl, $moodleurl->params());
        //$this->heading->set($this->identifier);

        $PAGE->set_pagelayout('report');
        $this->courseid = $PAGE->course->id;
    }


    public function init() {
        parent::init();
        if(is_callable('mr_off') and mr_off('xray', 'local')) {
            exit();
        }
    }

    protected function setajaxoutput() {
        global $PAGE, $OUTPUT;
        // This renders the page correctly using standard Moodle ajax renderer
        $this->output = $PAGE->get_renderer('core', null, RENDERER_TARGET_AJAX);
        $OUTPUT = $this->output;
        $this->ajax = true;
    }

    public function print_header() {
        if ($this->ajax) {
            echo $this->output->header();
            return;
        }

        parent::print_header();
    }

    protected function activate_nodelink($key) {
        global $PAGE;
        $node = $PAGE->settingsnav->find($key, navigation_node::TYPE_CUSTOM);
        if ($node) {
            $node->make_active();
        }
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
