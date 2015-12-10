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
 * Report controller.
 *
 * @package   local_xray
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/* @var stdClass $CFG */
require_once($CFG->dirroot . '/local/xray/controller/reports.php');
use local_xray\event\get_report_failed;
/**
 * Xray integration Accessible data
 * Show accessible data for each graph in each report.
 *
 * @package   local_xray
 * @author    Pablo Pagnone
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_xray_controller_accessibledata extends local_xray_controller_reports {

    /**
     * Origin controller
     * @var String
     */
    private $origincontroller;

    /**
     * Report id
     * @var integer
     */
    private $reportid;

    /**
     * Name of element
     * @var String
     */
    private $elementname;

    /**
     * Graph name
     * @var String
     */
    private $graphname;

    /**
     * Init
     */
    public function init() {
        // Dont need userid because each element is different for each user(
        $this->origincontroller = required_param("origincontroller", PARAM_ALPHANUMEXT);
        $this->reportid = required_param("reportid", PARAM_ALPHANUMEXT);
        $this->elementname = required_param("elementname", PARAM_ALPHANUMEXT);
        $this->graphname = rawurldecode(required_param("graphname", PARAM_TEXT));
    }

    /**
     * Require capability.
     */
    public function require_capability() {
        require_capability("{$this->plugin}:{$this->origincontroller}_view", $this->get_context());
    }

    /**
     * View accessible version.
     * @return string
     */
    public function view_action() {

        global $PAGE;

        $output = "";

        // Add nav to return to report.
        $PAGE->navbar->add(get_string($this->origincontroller, $this->component),
            new moodle_url('/local/xray/view.php',
                array("controller" => $this->origincontroller,
                      "courseid" => $this->courseid)));
        // Set title.
        $title = format_string(get_string($this->name, $this->component).get_string("of",$this->component).$this->graphname);
        $PAGE->set_title($title);
        $PAGE->navbar->add($title);
        $this->heading->text = $title;

        try{
            $response = \local_xray\local\api\wsapi::report_accessibility($this->reportid, $this->elementname);
            if (!$response) {
                // Fail response of webservice.
                \local_xray\local\api\xrayws::instance()->print_error();
            } else {

                // Empty data.
                if($response->status == "emptydata") {
                    $output .= html_writer::tag("p", get_string("accessible_emptydata", $this->component));
                }
                // Show data in tables.
                if($response->status == "OK") {
                    switch($this->elementname){
                        // Special cases.
                        case "endogenicPlagiarismHeatmap":
                        case "endogenicPlagiarismStudentsHeatmap":
                        case "barplotOfActivityByWeekday":
                            $output .= $this->specialcase1($response->data);
                            break;
                        //Specials cases, these has 1 table and 2 single values to show.
                        case "riskDensity":
                        case "scatterPlot":
                        case "riskScatterPlot":
                            $output .= $this->specialcase2($response);
                            break;
                        //Special case, has 2 tables to show (and we need show the tables names).
                        case "activityLevelTimeline":
                            $output .= $this->specialcase2($response, true);
                            break;
                        default:
                            $output .= $this->standard($response->data);
                            break;
                    }
                }

            }
        } catch(Exception $e) {
            get_report_failed::create_from_exception($e, context::instance_by_id($this->courseid), "accessibledata")->trigger();
            $output = $this->print_error('accessible_error', $e->getMessage());
        }

        return $output;
    }

    /**
     * Standard process of data received from webservice.
     * We dont show column sent "_row".
     *
     * @param $data
     * @return array
     */
    private function standard($data) {

        $output = "";
        $columnsnames = array();
        $rows = array();
        // Get data.
        if(!empty($data)) {

            // Get columns names.
            $columns = get_object_vars($data[0]);
            $exclude_columns = array("_row");
            foreach($columns as $c => $value){
                if(in_array($c, $exclude_columns)){
                    continue;
                }
                $columnsnames[] = $c;
            }

            // Get columns data.
            foreach($data as $val){
                $row = new html_table_row();
                foreach($val as $k => $value) {
                    if(in_array($k, $exclude_columns)){
                        continue;
                    }
                    $c  = new html_table_cell($value);
                    $row->cells[] = $c;
                }
                $rows[] = $row;
            }
        }

        $output .= $this->output->accessibledata($columnsnames, $rows, $this->graphname);
        return $output;
    }

    /**
     * Special case 1, we need to show first column with the value sent in "_row".
     * @param $data
     * @return array
     */
    private function specialcase1($data) {

        $output = "";
        $columnsnames = array();
        $rows = array();
        // Get data.
        if(!empty($data)) {

            // Get columns names.
            $columns = get_object_vars($data[0]);
            if(isset($columns["_row"])){
                $columnsnames[] = ""; // Empty name of columns when we have _row.
            }

            foreach($columns as $c => $value){
                if($c == "_row"){
                    continue;
                }
                $columnsnames[] = $c;
            }

            // Get columns data.
            foreach($data as $val){
                $row = new html_table_row();

                // When we have _row in object, we show this in first place.
                if(isset($val->_row)) {
                    $row->cells[] = $val->_row;;
                }

                foreach($val as $k => $value) {
                    if($k == "_row"){
                        continue;
                    }
                    $c  = new html_table_cell($value);
                    $row->cells[] = $c;
                }
                $rows[] = $row;
            }
        }

        $output .= $this->output->accessibledata($columnsnames, $rows, $this->graphname);
        return $output;
    }

    /**
     * Special case 2, show table and single values.
     * @param $data
     * @param boolean $showtablename - Show the table names
     * @return string
     */
    private function specialcase2($data, $showtablename = false) {

        $output = "";
        $props = get_object_vars($data->data);
        foreach($props as $key => $val) {
            if(is_array($val)) {
                if($showtablename) {
                    $output .= html_writer::tag("h3", $key);
                }
                $output .= $this->standard($val);

            } else {
                $output .= html_writer::tag("p",$key.": ".$val);
            }
        }

        return $output;
    }
}