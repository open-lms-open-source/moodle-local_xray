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
 * Risk report
 *
 * @package   local_xray
 * @author    Pablo Pagnone
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_xray_controller_risk extends local_xray_controller_reports {

    public function view_action() {

        $this->addiconhelp();
        $output = '';
        try {
            // INT-8186 (add non-active students from firstlogin in this report. This is available in activity report too).
            $inactivestudents = '';
            $report = "firstLogin";
            $responsefirstlogin = \local_xray\local\api\wsapi::course($this->courseid, $report);
            if (!$responsefirstlogin) {
                // Fail response of webservice.
                \local_xray\local\api\xrayws::instance()->print_error();
            } else {

                // If report is empty, show only message. No graphs/tables empties.
                if (isset($responsefirstlogin->elements->reportHeader->emptyReport) &&
                    $responsefirstlogin->elements->reportHeader->emptyReport) {
                    return $this->output->notification(get_string("xray_course_report_empty", $this->component));
                }

                // Show graphs. We need show table first in activity report.
                $datatable = new local_xray\datatables\datatables($responsefirstlogin->elements->nonStarters,
                        "rest.php?controller='risk'&action='jsonfirstloginnonstarters'&courseid=" . $this->courseid);
                $inactivestudents .= $this->output->standard_table((array)$datatable);
            }

            $report = "risk";
            $response = \local_xray\local\api\wsapi::course($this->courseid, $report);
            if (!$response) {
                // Fail response of webservice.
                \local_xray\local\api\xrayws::instance()->print_error();
            } else {
                // Report date.
                $output  = $this->print_top();
                $output .= $this->output->inforeport($response->reportdate);
                // Inactive Students table from firstLogin Report.
                $output .= $inactivestudents;
                // Show table.
                $datatable = new local_xray\datatables\datatables($response->elements->riskMeasures,
                        "rest.php?controller='risk'&action='jsonriskmeasures'&courseid=" . $this->courseid);
                $datatable->sort_order = "desc";
                $datatable->default_field_sort = 5; // New requirement, order by total risk desc.
                $output .= $this->output->standard_table((array)$datatable);

                // Graphs.
                $output .= $this->output->show_graph("riskDensity", $response->elements->riskDensity, $response->id);
                $output .= $this->output->show_graph("riskScatterPlot", $response->elements->riskScatterPlot, $response->id);
            }
        } catch (Exception $e) {
            get_report_failed::create_from_exception($e, $this->get_context(), $this->name)->trigger();
            $output = $this->print_error('error_xray', $e->getMessage());
        }

        return $output;
    }

    /**
     * Json response for table risk measures.
     * @return string
     */
    public function jsonriskmeasures_action() {
        return $this->genericresponsejsonfordatatables("risk", "riskMeasures");
    }

    /**
     * Json for table non starters.
     * @return string
     */
    public function jsonfirstloginnonstarters_action() {
        return $this->genericresponsejsonfordatatables("firstLogin", "nonStarters");
    }
}
