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

defined('MOODLE_INTERNAL') or die();

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
class local_xray_controller_gradebookreport extends local_xray_controller_reports {

    public function view_action() {

        $this->message_reports_disabled();
        if (!course_manager::is_course_selected($this->courseid)) {
            return $this->output->notification(get_string('warn_course_disabled', 'local_xray'), 'notifymessage');
        }
        $this->validate_course();
        $this->addiconhelp();
        $output = '';
        try {
            if (has_capability("local/xray:gradebookreport_view", $this->get_context())) {
                $report = "gradebook";
                $response = \local_xray\local\api\wsapi::course($this->courseid, $report);
                if (!$response) {
                    // Fail response of webservice.
                    \local_xray\local\api\xrayws::instance()->print_error();
                } else {

                    $output .= $this->print_top();

                    // If report is empty, show only message. No graphs/tables empties.
                    if (isset($response->elements->reportHeader->emptyReport) &&
                        $response->elements->reportHeader->emptyReport) {
                        $output .= $this->output->notification(get_string("xray_course_report_empty", $this->component));
                        return $output;
                    }

                    // Show graphs.
                    // Report date.
                    $output .= $this->output->inforeport($response->reportdate);
                    // Its a table, I will get info with new call.
                    $datatable = new local_xray\datatables\datatables($response->elements->courseGradeTable,
                        "rest.php?controller='gradebookreport'&action='jsonstudentgrades'&courseid=".$this->courseid);
                    // If the user comes from header.
                    if ($this->header) {
                        $datatable->default_field_sort = 2; // Sort by column "Course Grade".
                        $datatable->sort_order = "desc";
                    }
                    $output .= $this->output->standard_table((array)$datatable);
                    // Graph.
                    $output .= $this->output->show_graph("densityofstandardizedscores",
                        $response->elements->studentScoreDistribution, $response->id);

                    // Its a table, I will get info with new call.
                    $datatable = new local_xray\datatables\datatables($response->elements->gradableItemsTable,
                        "rest.php?controller='gradebookreport'&action='jsonsummaryquizzes'&courseid=".$this->courseid);
                    $output .= $this->output->standard_table((array)$datatable);

                    // Graphs.
                    $output .= $this->output->show_graph("boxplotofstandardizedscoresperquiz",
                        $response->elements->scoreDistributionByItem, $response->id);
                    $output .= $this->output->show_graph("scoresassignedbyxrayversusresultsfromquizzes",
                        $response->elements->scatterPlot, $response->id);
                    $output .= $this->output->show_graph("comparisonofscoresinquizzes",
                        $response->elements->itemsHeatmap, $response->id);
                }
            }
        } catch (Exception $e) {
            get_report_failed::create_from_exception($e, $this->get_context(), $this->name)->trigger();
            $output = $this->print_error('error_xray', $e->getMessage());
        }

        return $output;
    }

    /**
     * Json for provide data to students_grades_for_course table.
     *
     * @return string
     */
    public function jsonstudentgrades_action() {
        return $this->genericresponsejsonfordatatables("gradebook", "courseGradeTable");
    }

    /**
     * Json for provide data to students_grades_for_course table.
     * @return string
     */
    public function jsonsummaryquizzes_action() {
        return $this->genericresponsejsonfordatatables("gradebook", "gradableItemsTable");
    }
}
