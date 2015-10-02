<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') or die();

/* @var object $CFG */
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
    /**
     * @var null|core_renderer_ajax
     */
    private $ajaxrenderer = null;

    public function __construct($plugin, $identifier, $component, $action) {
        parent::__construct($plugin, $identifier, $component, $action);
        // We need to do this here since renderer gets overwritten in original ctor.
        if ($this->ajax) {
            $this->setajaxoutput();
        }
    }

    public function setup() {
        global $CFG, $PAGE;

        $courseid = (int)optional_param('courseid', SITEID, PARAM_ALPHANUM);
        $this->ajax = (stripos($this->action, 'json') === 0);
        $setwantsurltome = true;
        $preventredirect = false;
        if ($this->ajax) {
            $setwantsurltome = false;
            $preventredirect = true;
            $this->setajaxoutput();
        }

        require_login($courseid, false, null, $setwantsurltome, $preventredirect);

        // We want to send relative URL to $PAGE so $PAGE can set it to https or not.
        $moodleurl   = $this->new_url(array('action' => $this->action));
        $relativeurl = str_replace($CFG->wwwroot, '', $moodleurl->out_omit_querystring());

        $PAGE->set_context($this->get_context());
        $PAGE->set_url($relativeurl, $moodleurl->params());
        $this->courseid = $PAGE->course->id;

        if (!$this->ajax) {
            $title = format_string(get_string($this->name, $this->component));
            $PAGE->set_title($title);
            $this->heading->text = $title;
            $PAGE->set_pagelayout('report');
        }
    }


    public function init() {
        parent::init();
        if (is_callable('mr_off') and mr_off('xray', 'local')) {
            exit();
        }
    }

    /**
     * @return core_renderer_ajax|null
     */
    protected function getajaxrenderer() {
        global $PAGE;
        if ($this->ajaxrenderer === null) {
            $this->ajaxrenderer = new core_renderer_ajax($PAGE, RENDERER_TARGET_AJAX);
        }
        return $this->ajaxrenderer;
    }

    /**
     * @return void
     */
    protected function setajaxoutput() {
        global $OUTPUT;
        // This renders the page correctly using standard Moodle ajax renderer.
        $this->output = $this->getajaxrenderer();
        $OUTPUT = $this->getajaxrenderer();;
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
