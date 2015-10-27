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
use local_xray\event\get_report_failed;

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
        require_login($courseid, false);

        // We want to send relative URL to $PAGE so $PAGE can set it to https or not.
        $moodleurl   = $this->new_url(array('action' => $this->action));
        $relativeurl = str_replace($CFG->wwwroot, '', $moodleurl->out_omit_querystring());
        $PAGE->set_context($this->get_context());
        $PAGE->set_url($relativeurl, $moodleurl->params());
        $this->courseid = $PAGE->course->id;
        if (!AJAX_SCRIPT) {
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
        if (AJAX_SCRIPT) {
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

    /**
     * @return string
     */
    public function print_menu() {
        global $PAGE, $CFG;
        require_once($CFG->dirroot.'/local/xray/lib.php');
        $reportcontroller = $this->url->get_param('controller');
        $reports = local_xray_navigationlinks($PAGE, $PAGE->context);
        return $this->output->print_course_menu($reportcontroller, $reports);
    }

    /**
     * @return string
     */
    public function print_top() {
        $result  = $this->print_menu();
        $result .= $this->mroutput->render($this->heading);
        $this->heading->text = '';
        return $result;
    }

    /**
     * Generic json response for datatables.
     * @param $reportname - Name of report
     * @param $reportelement - Name Element of report
     * @param null|string $functiontoprocessdata - Name of function for process data.
     * By default this will use processdataforresponsedatatable(), but you can not overwritte this method because
     * maybe you need 2 or more specific responses in same class).
     *
     * @return string
     */
    public function genericresponsejsonfordatatables($reportname, $reportelement, $functiontoprocessdata = null) {

        // Pager.
        $count = optional_param('iDisplayLength', 10, PARAM_INT);
        $start = optional_param('iDisplayStart', 0, PARAM_INT);
        // Sortable
        $sortcol = optional_param('iSortCol_0', 0, PARAM_INT); // Number of column to sort.
        $sortorder = optional_param('sSortDir_0', 'asc', PARAM_ALPHA); // Direction of sort.
        $sortfield = optional_param("mDataProp_{$sortcol}", 'id', PARAM_TEXT); // Get column name.

        $return = "";
        try {
            //$report = "activity";
            //$element = "studentList";
            $response = \local_xray\local\api\wsapi::courseelement($this->courseid,
                $reportelement,
                $reportname,
                $this->userid,
                '',
                '',
                $start,
                $count,
                $sortfield,
                $sortorder);

            if (!$response) {
                // Fail response of webservice.
                \local_xray\local\api\xrayws::instance()->print_error();
            } else {
                $data = array();
                if (!empty($response->data)) {
                    if(!empty($functiontoprocessdata) && method_exists($this, $functiontoprocessdata)) {
                        // Specific method to process data.
                        $data = $this->{$functiontoprocessdata}($response);
                    } else {
                        $data = $this->processdataforresponsedatatable($response);
                    }
                }
                // Provide count info to table.
                $return["recordsFiltered"] = $response->itemCount;
                $return["recordsTotal"] = $response->itemCount;
                $return["data"] = $data;
            }
        } catch (Exception $e) {
            // Error, return invalid data, and pluginjs will show error in table.
            get_report_failed::create_from_exception($e, $this->get_context(), $this->name)->trigger();
            $return["data"] = "-";
        }

        return json_encode($return);
    }

    /**
     * This function is used by default for format data sent from xray side.
     * @param $response - Response of xray webservice.
     * @return array
     */
    private function processdataforresponsedatatable($response) {

        $data = array();
        foreach ($response->data as $row) {

            $r = new stdClass();
            // Format of response for columns(if return has columnorder).
            if (!empty($response->columnOrder) && is_array($response->columnOrder)) {
                foreach ($response->columnOrder as $column) {
                    $r->{$column} = '';
                    if (isset($row->{$column}->value)) {
                        $r->{$column} = $row->{$column}->value;
                    }
                }
            } // If report has not specified columnOrder.
            elseif (!empty($response->columnHeaders) && is_object($response->columnHeaders)) {
                $c = get_object_vars($response->columnHeaders);
                foreach ($c as $id => $name) {
                    $r->{$id} = (isset($row->{$id}->value) ? $row->{$id}->value : '');
                }
            }
            $data[] = $r;
        }

        return $data;
    }
}