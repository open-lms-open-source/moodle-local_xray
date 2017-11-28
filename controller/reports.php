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

require_once($CFG->dirroot.'/local/mr/framework/controller.php');
use local_xray\event\get_report_failed;
use local_xray\local\api\course_manager;

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

    // Time range in minutes from xray side.
    const XRAYTIMERANGEMINUTE = 'timerangeminute';
    const XRAYGREENCOLOR = 'green';
    const XRAYREDCOLOR = 'red';
    const XRAYYELLOWCOLOR = 'yellow';
    const XRAYSYSTEMREPORTS = 'systemreports';

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
     * Is it Accesed from headline?
     * @var bool
     */
    protected $header = 0;

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
        $this->header = optional_param('header', 0, PARAM_INT);
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
        // Add specific class for xray heading.
        $this->heading->classes = 'xray-report-page-title';
    }

    /**
     * Add icon for redirect to help in heading.
     */
    public function addiconhelp() {

        global $PAGE;

        // Set url for report.
        switch($this->name) {
            case "activityreport":
            case "activityreportindividual":
                $report = 'https://help.blackboard.com/005_008';
                $reportshelp = 'activityreports_help';
                break;
            case "discussionreport":
            case "discussionreportindividual":
            case "discussionreportindividualforum":
                $report = 'https://help.blackboard.com/005_009';
                $reportshelp = 'discussionreports_help';
                break;
            case "gradebookreport":
                $report = 'https://help.blackboard.com/005_010';
                $reportshelp = 'gradebookreports_help';
                break;
            case "risk":
                $report = 'https://help.blackboard.com/005_011';
                $reportshelp = 'riskreports_help';
                break;
            default:
                $report = 'https://help.blackboard.com/005_007';
        }

        $helpurl = $report.$this->resolve_language_key();

        // Add icon for help of report. Link redirect to external url.
        $newheading = $PAGE->title.$this->output->help_icon_external_url(get_string($reportshelp, $this->component), $helpurl);
        $this->heading->text = $newheading;
    }

    /**
     * Given a Moodle language code, return the language and country code for the knowledge base.
     *
     * @param string $lang The current language (defaults to user's language).
     * @return string
     */
    private function resolve_language_key($lang = null) {
        if (is_null($lang)) {
            $lang = current_language();
        }

        // Avoid doing too much dynamic work here as the KB doesn't have every language available.  So we must fallback to
        // the English translation if no known translation exists.
        switch ($lang) {
            case 'cs':
                return 'cs_CZ';
            case 'ja':
                return 'ja_JP';
            case 'pt_br':
                return 'pt_BR';
            case 'zh_tw':
                return 'zh_TW';
            case 'de':
            case 'es':
            case 'fi':
            case 'fr':
            case 'it':
            case 'nl':
            case 'pl':
                return $lang.'_'.\core_text::strtoupper($lang);
        }

        return 'en_US';
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
        global $CFG;
        require_once($CFG->dirroot.'/local/xray/locallib.php');
        if (!local_xray_reports() || $this->name == self::XRAYSYSTEMREPORTS) {
            require_capability("{$this->plugin}:{$this->name}_view", $this->get_context());
        }
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
        global $CFG, $OUTPUT;

        $baserr = get_string($errorstring, $this->component) . \html_writer::empty_tag('br') . \html_writer::empty_tag('br');
        if (!empty($debuginfo) && isset($CFG->debugdisplay) && $CFG->debugdisplay && ($CFG->debug == DEBUG_DEVELOPER)) {
            $debuginfotitle = \html_writer::tag('strong', get_string('debuginfo', $this->component)) .
                \html_writer::empty_tag('br');
            $baserr .= $OUTPUT->notification($debuginfotitle . $debuginfo);
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

        $return = [];
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
                // Xray ws return a phantom row empty when tables is empty.
                // Xray side can not change this response, so they added emptyData property to check if table is empty.
                if (isset($response->emptyData) && !empty($response->emptyData)) {
                    // Table is empty, return 0 and table will show standard message.
                    $response->data = array();
                    $response->itemCount = 0;
                }

                if (!empty($response->data)) {
                    if (!empty($functiontoprocessdata) && method_exists($this, $functiontoprocessdata)) {
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
        // Check if dataformat is defined.
        $dataformat = (isset($response->dataFormat) ? $response->dataFormat : false);

        foreach ($response->data as $row) {

            $r = new stdClass();
            // Format of response for columns(if return has columnorder).
            if (!empty($response->columnOrder) && is_array($response->columnOrder)) {
                foreach ($response->columnOrder as $column) {
                    $category = false;
                    if (isset($row->{$column}->colorCode)) {
                        $category = $row->{$column}->colorCode;
                    }
                    $r->{$column} = $this->show_intuitive_value(
                        $row->{$column}->value,
                        $response->elementName,
                        $column,
                        $dataformat,
                        $category
                    );
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
     * Show intuituve values
     *
     * @param  float $value
     * @param  string $table The elementname of the table
     * @param  string $column
     * @param  string $dateformat
     * @param  string/boolean $xraycategory
     * @return string
     */
    public function show_intuitive_value($value, $table, $column, $dateformat, $xraycategory = false) {
        switch ($table) {
            case 'riskMeasures':
                // Table Risk Measures from Risk Report.
                switch ($column) {
                    case 'timeOnTask':
                        // Column Time spent in course (hours).
                        // Check if timerange is defined.
                        $minutes = false;
                        if (isset($dateformat->{$column}) && $dateformat->{$column} == self::XRAYTIMERANGEMINUTE) {
                            $minutes = true;
                        }
                        return $this->show_time_hours_minutes($value, $minutes);
                        break;
                    case 'fail':
                        // Column Academic risk.
                    case 'DW':
                        // Column Social risk.
                    case 'DWF':
                        // Column Total risk.
                        // Add category.
                        return $this->add_category($value, $xraycategory, true, false, true);
                        break;
                    default:
                        return $value;
                        break;
                }
            case 'studentList':
                // Table Student Activity from Activity Report.
                switch ($column) {
                    case 'discussion_posts':
                        // Column Total discussion posts.
                        // Add '-' for strange values.
                        if (isset($value) && is_numeric(trim($value))) {
                            return $value;
                        } else {
                            return '-';
                        }
                        break;
                    case 'timeOnTask':
                        // Column Time spent in course (hours).
                        // Check if timerange is defined.
                        $minutes = false;
                        if (isset($dateformat->{$column}) && $dateformat->{$column} == self::XRAYTIMERANGEMINUTE) {
                            $minutes = true;
                        }
                        return $this->show_time_hours_minutes($value, $minutes);
                        break;
                    case 'regularity':
                        // Column Visit regularity (daily).
                        // Add category.
                        return $this->add_category($value, $xraycategory, false, true);
                        break;
                    default:
                        return $value;
                        break;
                }
            case 'discussionMetrics':
                // Table Participation Metrics from Discussion Report.
                // Table Participation Metrics from Discussion Report Individual.
                switch ($column) {
                    case 'posts':
                        // Column Total discussion posts.
                    case 'discussion_posts_last_week':
                        // Column Posts last week.
                        // Add '-' for strange values.
                        if (isset($value) && is_numeric(trim($value))) {
                            return $value;
                        } else {
                            return '-';
                        }
                        break;
                    case 'contrib':
                        // Column Average original contribution.
                    case 'ctc':
                        // Column Average critical thought.
                        // Add category.
                        return $this->add_category($value, $xraycategory, true);
                        break;
                    case 'regularityContrib':
                        // Column Regularity of original contribution.
                    case 'regularityCTC':
                        // Column Regularity of critical thought.
                        // Add category.
                        return $this->add_category($value, $xraycategory, false, true);
                        break;
                    default:
                        return $value;
                        break;
                }
            case 'studentDiscussionGrades':
                // Table Student Grades Based on Discussions from Discussion Report.
                switch ($column) {
                    case 'letterGrade':
                        // Column Grade recommendation.
                        // Check if the value exists.
                        if (!empty($value)) {
                            return $value;
                        } else {
                            return '-';
                        }
                    case 'posts':
                        // Column Total posts.
                    case 'wc':
                        // Column Word count (rel.).
                        // It should be numeric.
                        if (isset($value) && is_numeric(trim($value))) {
                            $roundvalue = round($value, 2);
                            return $roundvalue;
                        } else {
                            return '-';
                        }
                        break;
                    case 'ctc':
                        // Column CTC.
                        // Add category.
                        return $this->add_category($value, $xraycategory, true);
                        break;
                    case 'regularityContrib':
                        // Column Regularity of contributions.
                        // Add category.
                        return $this->add_category($value, $xraycategory, false, true);
                        break;
                    default:
                        return $value;
                        break;
                }
            case 'courseGradeTable':
                // Table Student Grades from Gradebook Report.
                switch ($column) {
                    // Column Current course grade.
                    case 'courseGrade':
                    case 'meanQuiz':
                    case 'meanAssign':
                    case 'meanForum':
                    case 'meanOther':
                        // It should be numeric.
                        if (isset($value) && is_numeric(trim($value))) {
                            $roundvalue = round($value, 2);
                            return $roundvalue . '%';
                        } else {
                            return '-';
                        }
                        break;
                    default:
                        return $value;
                }
            case 'gradableItemsTable':
                // Table Summary of Quizzes from Gradebook Report.
                switch ($column) {
                    case 'nStudents':
                        // Column Number of students.
                        // It should be numeric.
                        if (isset($value) && is_numeric(trim($value))) {
                            return $value;
                        } else {
                            return '-';
                        }
                        break;
                    case 'standardScore':
                        // Column Average score.
                    case 'courseGradeCorrelation':
                        // Column Relationship with course grade
                        // It should be numeric.
                        if (isset($value) && is_numeric(trim($value))) {
                            $roundvalue = round($value, 2);
                            return $roundvalue . '%';
                        } else {
                            return '-';
                        }
                        break;
                    default:
                        return $value;
                }
            default:
                return $value;
        }
    }

    /**
     * Add categories in the values.
     *
     * @param $value
     * @param $xraycategory
     * @param bool|false $percentage
     * @param bool|false $regularity
     * @param bool|false $inverted
     * @return float|string
     */
    public static function add_category ($value, $xraycategory, $percentage = false, $regularity = false, $inverted = false) {
        // Check if the value is a number.
        if (!isset($value) || !is_numeric(trim($value))) {
            return '-';
        }
        // Get the correct string.
        if ($regularity) {
            $danger = get_string('irregular', 'local_xray');
            $warning = get_string('regular', 'local_xray');
            $success = get_string('highlyregular', 'local_xray');
        } else if ($inverted) {
            $danger = get_string('high', 'local_xray');
            $warning = get_string('medium', 'local_xray');
            $success = get_string('low', 'local_xray');
        } else {
            $danger = get_string('low', 'local_xray');
            $warning = get_string('medium', 'local_xray');
            $success = get_string('high', 'local_xray');
        }
        // Round value and check if it needs percentage sign.
        $value = round($value, 2);
        if ($percentage) {
            $value = $value . '%';
        }
        // Add category.
        switch ($xraycategory) {
            case self::XRAYREDCOLOR:
                $category = html_writer::span($danger, 'label label-danger');
                break;
            case self::XRAYYELLOWCOLOR:
                $category = html_writer::span($warning, 'label label-warning');
                break;
            case self::XRAYGREENCOLOR:
                $category = html_writer::span($success, 'label label-success');
                break;
            default:
                return $value;
        }
        return $category . ' ' . $value;
    }

    /**
     * @param int $time seconds by default or minutes if $minutes is true
     * @param bool|false $minutes
     * @return mixed|string
     */
    public static function show_time_hours_minutes($time, $minutes = false) {
        if (is_numeric($time) && $time >= 0) {
            if ($minutes) { // Time range is sent in minutes.
                // Get hours from minutes.
                $hours = floor($time / 60);
                // Get the remaining minutes.
                $remainingminutes = $time % 60;
            } else { // Time range is sent in seconds.
                // Get minutes from seconds.
                $minutes = floor($time / 60);
                // Get hours from seconds.
                $hours = floor($minutes / 60);
                // Get the remaining minutes.
                $remainingminutes = $minutes % 60;
            }
            // Two digits.
            $hours = sprintf("%02d", $hours);
            $remainingminutes = sprintf("%02d", $remainingminutes);
            // Return the time range in format.
            $timeformat = str_ireplace('%M', $remainingminutes,
                str_ireplace('%H', $hours, get_string('strftimehoursminutes', 'local_xray')));
            return $timeformat;
        } else {
            return '-';
        }
    }

    /**
     * Print Footer.
     */
    public function print_footer() {
        parent::print_footer();
        // Call Report Viewed Event.
        // Discussion Report Individual Forum and Accessible Data are special cases.
        if ($this->name != 'discussionreportindividualforum' && $this->name != 'accessibledata') {
            $data = array(
                'context' => $this->get_context(),
                'relateduserid' => $this->userid,
                'other' => array('reportname' => $this->name)
            );
            $this->trigger_report_viewed_event($data);
        }
    }

    /**
     * Trigger the report_viewed event when the user views a Report.
     * @param array $data
     */
    public function trigger_report_viewed_event ($data) {
        // Only for non-ajax views.
        if (!defined(AJAX_SCRIPT) || !AJAX_SCRIPT) {
            $event = \local_xray\event\report_viewed::create($data);
            $event->trigger();
        }
    }

    /**
     * Check if the shiny reports are available.
     */
    public function message_reports_disabled() {
        global $CFG;
        require_once($CFG->dirroot.'/local/xray/locallib.php');
        if (local_xray_reports() && !defined('BEHAT_SITE_RUNNING')) {
            $this->print_header();
            echo $this->output->notification(get_string('noaccessoldxrayreports', $this->component), 'error');
            $this->print_footer();
            die();
        }
    }
}