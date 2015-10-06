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
require_once($CFG->dirroot . '/local/xray/controller/reports.php');

/**
 * Risk report
 *
 * @author Pablo Pagnone
 * @package local_xray
 */
class local_xray_controller_risk extends local_xray_controller_reports {

    public function view_action() {
        global $PAGE;

        $output = '';
        try {
            // INT-8186 (add non-active students from firstlogin in this report. This is available in activity report too).
            $report = "firstLogin";
            $responsefirstlogin = \local_xray\api\wsapi::course($this->courseid, $report);
            if (!$responsefirstlogin) {
                // Fail response of webservice.
                \local_xray\api\xrayws::instance()->print_error();
            } else {
                $output .= $this->output->inforeport($responsefirstlogin->reportdate, null, $PAGE->course->fullname);
                // Show graphs. We need show table first in activity report.(INT-8186)
                // Call to independient call to show in table.
                $output .= $this->first_login_non_starters($responsefirstlogin->elements->nonStarters);
            }

            $report = "risk";
            $response = \local_xray\api\wsapi::course($this->courseid, $report);
            if (!$response) {
                // Fail response of webservice.
                \local_xray\api\xrayws::instance()->print_error();
            } else {
                // Show graphs.
                $output .= $this->risk_measures($response->elements->riskMeasures); // TABLE.
                $output .= $this->total_risk_profile($response->elements->riskDensity);
                $output .= $this->academic_vs_social_risk($response->elements->riskScatterPlot);
            }
        } catch (Exception $e) {
            print_error('error_xray', $this->component, '', null, $e->getMessage());
        }

        return $output;
    }

    /**
     * Report Risk measures.(TABLE)
     * @param mixed $element
     * @return string
     */
    private function risk_measures($element) {
        $output = "";
        $output .= $this->output->risk_risk_measures($this->courseid, $element);
        return $output;
    }

    public function jsonriskmeasures_action() {
        global $PAGE;
        
        // Pager.
        $count = (int)optional_param('iDisplayLength', 10, PARAM_ALPHANUM);
        $start = (int)optional_param('iDisplayStart', 0, PARAM_ALPHANUM);

        $return = "";

        try {
            $report = "risk";
            $element = "riskMeasures";
            $response = \local_xray\api\wsapi::courseelement($this->courseid,
                $element,
                $report,
                null,
                '',
                '',
                $start,
                $count);
            if (!$response) {
                // Fail response of webservice.
                \local_xray\api\xrayws::instance()->print_error();
            } else {
                $data = array();
                if (!empty($response->data)) {
                    foreach ($response->data as $row) {

                        // Format of response for columns.
                        if (!empty($response->columnOrder)) {
                            $r = new stdClass();
                            foreach ($response->columnOrder as $column) {
                                
                                if ($column == 'fail' || $column == 'DW' || $column == 'DWF') {//Add categories low medium high
                                    $localxrayrenderer = $PAGE->get_renderer('local_xray');
                                    $r->{$column} = (isset($row->{$column}->value) ? $localxrayrenderer->set_category($row->{$column}->value) : '');
                                }else{
                                    $r->{$column} = (isset($row->{$column}->value) ? $row->{$column}->value : '');
                                }
                            }
                            $data[] = $r;
                        }
                    }
                }
                // Provide count info to table.
                $return["recordsFiltered"] = $response->itemCount;
                $return["data"] = $data;
            }
        } catch (Exception $e) {
            // Error, return invalid data, and pluginjs will show error in table.
            $return["data"] = "-";
        }

        return json_encode($return);
    }

    /**
     * Report total risk profile
     * @param object $element
     * @return string
     */
    private function total_risk_profile($element) {
        $output = "";
        $output .= $this->output->risk_total_risk_profile($element);
        return $output;
    }

    /**
     * Report total risk profile
     * @param object $element
     * @return string
     */
    private function academic_vs_social_risk($element) {

        $output = "";
        $output .= $this->output->risk_academic_vs_social_risk($element);
        return $output;
    }

    /**
     * Report First login
     * - Element to show: table users not starters in course.
     * @param object $element
     * @return string
     */
    private function first_login_non_starters($element) {
        $output = "";
        $output .= $this->output->risk_first_login_non_starters($this->courseid, $element);
        return $output;
    }

    /**
     * Json for table non starters.
     * @return string
     */
    public function jsonfirstloginnonstarters_action() {
        // Pager.
        $count = (int)optional_param('iDisplayLength', 10, PARAM_ALPHANUM);
        $start = (int)optional_param('iDisplayStart', 0, PARAM_ALPHANUM);

        $return = "";
        try {
            $report = "firstLogin";
            $element = "nonStarters";
            $response = \local_xray\api\wsapi::courseelement($this->courseid,
                $element,
                $report,
                null,
                '',
                '',
                $start,
                $count);
            if (!$response) {
                // Fail response of webservice.
                \local_xray\api\xrayws::instance()->print_error();
            } else {
                $data = array();
                if (!empty($response->data)) {
                    // Format of response for columns.
                    foreach ($response->data as $row) {
                        // This report has not specified columnOrder.
                        if (!empty($response->columnHeaders) && is_object($response->columnHeaders)) {
                            $r = new stdClass();
                            $c = get_object_vars($response->columnHeaders);
                            foreach ($c as $id => $name) {
                                $r->{$id} = (isset($row->{$id}->value) ? $row->{$id}->value : '');
                            }
                            $data[] = $r;
                        }
                    }
                }
                // Provide info to table.
                $return["recordsFiltered"] = $response->itemCount;
                $return["data"] = $data;
            }
        } catch (Exception $e) {
            // Error, return invalid data, and pluginjs will show error in table.
            $return["data"] = "-";
        }

        return json_encode($return);
    }
}
