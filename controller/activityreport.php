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
use local_xray\local\api\course_manager;
/**
 * Xray integration Reports Controller
 *
 * @package   local_xray
 * @author    Pablo Pagnone
 * @author    German Vitale
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_xray_controller_activityreport extends local_xray_controller_reports {

    public function view_action() {

        $this->message_reports_disabled();
        if (!course_manager::is_course_selected($this->courseid)) {
            return $this->output->notification(get_string('warn_course_disabled', 'local_xray'), 'notifymessage');
        }
        $this->validate_course();
        $this->addiconhelp();
        $output = '';

        try {

            if (has_capability("local/xray:activityreport_view", $this->get_context())) {
                $report = "activity";
                $response = \local_xray\local\api\wsapi::course($this->courseid, $report);
                if (!$response) {
                    // Fail response of webservice.
                    \local_xray\local\api\xrayws::instance()->print_error();
                } else {

                    // Menu (Always show menu).
                    $output .= $this->print_top();

                    // If report is empty, show only message. No graphs/tables empties.
                    if (isset($response->elements->reportHeader->emptyReport) &&
                        $response->elements->reportHeader->emptyReport) {

                        $output .= $this->output->notification(get_string("xray_course_report_empty", $this->component));
                        return $output;

                    } else {

                        // Report date.
                        $output .= $this->output->inforeport($response->reportdate);

                        // Inactive Students table from firstLogin Report.
                        $report = "firstLogin";
                        $responsefirstlogin = \local_xray\local\api\wsapi::course($this->courseid, $report);
                        if (!$responsefirstlogin) {
                            // Fail response of webservice.
                            \local_xray\local\api\xrayws::instance()->print_error();
                        } else {

                            // We need show table first in activity report.
                            $datatable = new local_xray\datatables\datatables($responsefirstlogin->elements->nonStarters,
                                "rest.php?controller='activityreport'&action='jsonfirstloginnonstarters'&courseid=" . $this->courseid);
                            $output .= $this->output->standard_table((array)$datatable);
                        }

                        // Show table Activity report.
                        $datatable = new local_xray\datatables\datatables($response->elements->studentList,
                            "rest.php?controller='activityreport'&action='jsonstudentsactivity'&courseid=" . $this->courseid,
                            array(),
                            true,
                            true,
                            '<"top">rt<"bottom"flp><"clear">');// add column action.
                        // If the user comes from header.
                        if ($this->header) {
                            $datatable->default_field_sort = 3; // Sort by column "Last activity".
                            $datatable->sort_order = "desc";
                        } else {
                            $datatable->default_field_sort = 1; // Sort by first column "Lastname".Because table has action column);
                        }
                        $output .= $this->output->standard_table((array)$datatable);

                        // Show graphs Activity report.
                        $output .= $this->output->show_graph("activityLevelTimeline",
                            $response->elements->activityLevelTimeline, $response->id);
                        $output .= $this->output->show_graph("compassTimeDiagram",
                            $response->elements->compassTimeDiagram, $response->id);
                        $output .= $this->output->show_graph("barplotOfActivityByWeekday",
                            $response->elements->barplotOfActivityByWeekday, $response->id);
                        $output .= $this->output->show_graph("barplotOfActivityWholeWeek",
                            $response->elements->barplotOfActivityWholeWeek, $response->id);
                        $output .= $this->output->show_graph("activityByWeekAsFractionOfTotal",
                            $response->elements->activityByWeekAsFractionOfTotal, $response->id);
                        $output .= $this->output->show_graph("activityByWeekAsFractionOfOwn",
                            $response->elements->activityByWeekAsFractionOfOwn, $response->id);
                        $output .= $this->output->show_graph("firstloginPiechartAdjusted",
                            $responsefirstlogin->elements->firstloginPiechartAdjusted, $responsefirstlogin->id);
                    }
                }
            }

        } catch (Exception $e) {
            get_report_failed::create_from_exception($e, $this->get_context(), $this->name)->trigger();
            $output = $this->print_error('error_xray', $e->getMessage());
        }

        return $output;
    }

    /**
     * Json for provide data to students_activity table.
     * Get data for table "studentList" of "activity" report.
     *
     * @return string
     */
    public function jsonstudentsactivity_action() {
        return $this->genericresponsejsonfordatatables("activity", "studentList", "processdatastudentactivity");
    }

    /**
     * Specific format for response of table "studentList" of "activity" report.
     * We add action column and format times.
     * This will be call from genericresponsejsonfordatatables().
     *
     * @param $response
     * @return array
     */
    public function processdatastudentactivity($response) {

        global $PAGE;
        $data = array();
        // Check if dataformat is defined.
        $dataformat = (isset($response->dataFormat) ? $response->dataFormat : false);

        $activityreportind = get_string('activityreportindividual', $this->component);
        foreach ($response->data as $row) {

            $r = new stdClass();
            $r->action = "";
            if (has_capability('local/xray:activityreportindividual_view', $PAGE->context)) {
                // Url for activityreportindividual.
                $url = new moodle_url("/local/xray/view.php",
                    array("controller" => "activityreportindividual",
                        "courseid" => $this->courseid,
                        "userid" => $row->participantId->value
                    ));
                $r->action = html_writer::link($url, '', array("class" => "xray_icon_activityreportindividual",
                    "title" => $activityreportind,
                    "target" => "_blank"));
            }
            // Format of response for columns.
            if (!empty($response->columnOrder)) {
                foreach ($response->columnOrder as $column) {
                    $category = false;
                    if (isset($row->{$column}->colorCode)) {
                        $category = $row->{$column}->colorCode;
                    }
                    $r->{$column} = $this->show_intuitive_value($row->{$column}->value, $response->elementName, $column, $dataformat, $category);
                }
            }
            $data[] = $r;
        }

        return $data;
    }

    /**
     * Json for table first login non starters.
     * Get data for table "firstLogin" of "nonStarters" report.
     *
     */
    public function jsonfirstloginnonstarters_action() {
        return $this->genericresponsejsonfordatatables("firstLogin", "nonStarters");
    }
}