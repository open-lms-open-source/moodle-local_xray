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

/**
 * Base controller class.
 *
 * @package   local_xray
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/* @var stdClass $CFG */
require_once($CFG->dirroot.'/local/mr/framework/controller.php');

/**
 * Xray integration Reports Controller
 *
 * @author    Pablo Pagnone
 * @author    German Vitale
 * @package   local_xray
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_xray_controller_reports extends mr_controller {
    /**
     * Course id
     * @var integer
     */
    protected $courseid;

    /**
     * @var local_xray_renderer|core_renderer
     */
    protected $output;

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

        $courseid = optional_param('courseid', SITEID, PARAM_INT);
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
        var_dump(\local_xray\local\api\xrayws::instance()->geterrorcode());
        var_dump(\local_xray\local\api\xrayws::instance()->geterrormsg());
        var_dump(\local_xray\local\api\xrayws::instance()->lastresponse());
        var_dump(\local_xray\local\api\xrayws::instance()->lasthttpcode());
        var_dump(\local_xray\local\api\xrayws::instance()->getinfo());
        echo "</pre>";
        exit();
    }

    /**
     * @param string $errorstring
     * @param null|string $debuginfo
     * @return string
     */
    public function print_error($errorstring, $debuginfo = null) {
        global $CFG;
        $baserr = get_string($errorstring, $this->component);
        if (!empty($debuginfo) && isset($CFG->debugdisplay) && $CFG->debugdisplay && ($CFG->debug == DEBUG_DEVELOPER)) {
            $baserr .= print_collapsible_region($debuginfo, '', 'error_xray',
                                                get_string('debuginfo', $this->component), '', true, true);
        }
        $output = $this->output->error_text($baserr);
        return $output;
    }
}
