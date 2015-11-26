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
        global $CFG, $PAGE, $COURSE;

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
            $PAGE->set_heading($COURSE->fullname);
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
                        $r->{$column} = $this->show_intuitive_value($row->{$column}->value, $response->elementName, $column);
                    }
                }
            } else if (!empty($response->columnHeaders) && is_object($response->columnHeaders)) {
                $c = get_object_vars($response->columnHeaders);
                foreach ($c as $id => $name) {
                    $r->{$id} = (isset($row->{$id}->value) ? $row->{$id}->value : '');// TODO see values here.
                }
            }
            $data[] = $r;
        }

        return $data;
    }

    /**
     * Set Categories for difficult values in tables
     *
     * @param  float $value
     * @param  string $table The elementname of the table
     * @param  string $column
     * @return string
     */
    public function show_intuitive_value($value, $table, $column) {
        $plugin = 'local_xray';
        switch ($table) {
            case 'riskMeasures':
                // Table Risk Measures from Risk Report.
                switch ($column) {
                    case 'timeOnTask':
                        // Column Time spent in course (hours).
                        return $this->show_time_hours_minutes($value);
                        break;
                    case 'fail':
                        // Column Academic risk.
                    case 'DW';
                        // Column Social risk.
                    case 'DWF';
                        // Column Total risk.
                        $roundvalue = round($value, 2);
                        $category = 'high';
                        $risk1 = get_config($plugin, 'risk1');
                        $risk2 = get_config($plugin, 'risk2');
                        if ($risk1 && $risk2) {
                            if ($roundvalue < $risk1) {
                                $category = 'low';
                            } else if ($roundvalue < $risk2) {
                                $category = 'medium';
                            }
                            return get_string($category, 'local_xray') . ' ' . $roundvalue;
                        } else {
                            return $roundvalue;
                        }
                        break;
                    default:
                        return $value;
                        break;
                }
            case 'studentList';
                // Table Student Activity from Activity Report.
                switch ($column) {
                    case 'timeOnTask':
                        // Column Time spent in course (hours).
                        return $this->show_time_hours_minutes($value);
                        break;
                    case 'weeklyRegularity':
                        // Column Visit regularity (weekly).
                        $roundvalue = round($value, 2);
                        $visitreg1 = get_config($plugin, 'visitreg1');
                        $visitreg2 = get_config($plugin, 'visitreg2');
                        if ($visitreg1 && $visitreg2) {
                            $category = 'irregular';
                            if ($roundvalue < $visitreg1) {
                                $category = 'highlyregularity';
                            } else if ($roundvalue < $visitreg2) {
                                $category = 'somewhatregularity';
                            }
                            return get_string($category, 'local_xray') . ' ' . $roundvalue;
                        } else {
                            return $roundvalue;
                        }
                        break;
                    default:
                        return $value;
                        break;
                }
            case 'discussionMetrics';
                // Table Participation Metrics from Discussion Report.
                // Table Participation Metrics from Discussion Report Individual.
                switch ($column) {
                    case 'contrib':
                        // Column Contribution.
                    case 'ctc':
                        // Column CTC.
                        $percentage = $value * 100;
                        $roundvalue = round($percentage, 2);
                        $partc1 = get_config($plugin, 'partc1');
                        $partc2 = get_config($plugin, 'partc2');
                        if ($partc1 && $partc2) {
                            $category = 'high';
                            if ($roundvalue < $partc1) {
                                $category = 'low';
                            } else if ($roundvalue < $partc2) {
                                $category = 'medium';
                            }
                            return get_string($category, 'local_xray') . ' ' . $roundvalue . '%';
                        } else {
                            return $roundvalue . '%';
                        }
                        break;
                    case 'regularityContrib';
                        // Column Regularity of contributions.
                    case 'regularityCTC':
                        // Column Regularity of CTC.
                        $roundvalue = round($value, 2);
                        $partreg1 = get_config($plugin, 'partreg1');
                        $partreg2 = get_config($plugin, 'partreg2');
                        if ($partreg1 and $partreg2) {
                            $category = 'irregular';
                            if ($roundvalue < $partreg1) {
                                $category = 'highlyregularity';
                            } else if ($roundvalue < $partreg2) {
                                $category = 'somewhatregularity';
                            }
                            return get_string($category, 'local_xray') . ' ' . $roundvalue;
                        } else {
                            return $roundvalue;
                        }
                        break;
                    default:
                        return $value;
                        break;
                }
            case 'studentDiscussionGrades';
                // Table Student Grades Based on Discussions from Discussion Report.
                switch ($column) {
                    case 'wc':
                        // Column Word count (rel.).
                        $roundvalue = round($value, 2);
                        return $roundvalue;
                        break;
                    case 'ctc':
                        // Column CTC.
                        $percentage = $value * 100;
                        $roundvalue = round($percentage, 2);
                        $partc1 = get_config($plugin, 'partc1');
                        $partc2 = get_config($plugin, 'partc2');
                        if ($partc1 && $partc2) {
                            $category = 'high';
                            if ($roundvalue < $partc1) {
                                $category = 'low';
                            } else if ($roundvalue < $partc2) {
                                $category = 'medium';
                            }
                            return get_string($category, 'local_xray') . ' ' . $roundvalue . '%';
                        } else {
                            return $roundvalue . '%';
                        }
                        break;
                    case 'regularityContrib';
                        // Column Regularity of contributions.
                        $roundvalue = round($value, 2);
                        $partreg1 = get_config($plugin, 'partreg1');
                        $partreg2 = get_config($plugin, 'partreg2');
                        if ($partreg1 and $partreg2) {
                            $category = 'irregular';
                            if ($roundvalue < $partreg1) {
                                $category = 'highlyregularity';
                            } else if ($roundvalue < $partreg2) {
                                $category = 'somewhatregularity';
                            }
                            return get_string($category, 'local_xray') . ' ' . $roundvalue;
                        } else {
                            return $roundvalue;
                        }
                        break;
                    default:
                        return $value;
                        break;
                }
            case 'element2':
                // Table Student Grades from Gradebook Report.
                switch ($column) {
                    case 'standarScore':
                        // Column Standardized score.
                        return round($value, 2);
                        break;
                    default:
                        return $value;
                }
            case 'element4':
                // Table Summary of Quizzes from Gradebook Report.
                switch ($column) {
                    case 'earnScore':
                        // Column Average score.
                    case 'standarScore':
                        // Column Average standardized score.
                    case 'finalGradeCorrelation':
                        // Column Correlation between final score and score from this quiz.
                        return round($value, 2);
                        break;
                    default:
                        return $value;
                }
            default:
                return $value;
        }
    }
    /**
     * Show time in format hours:minutes
     * @param int $minutes
     * @return string
     */
    public function show_time_hours_minutes($minutes) {
        return date('H:i', mktime(0, $minutes));
    }
}