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

/* @var stdClass $CFG */
require_once($CFG->dirroot . '/local/xray/controller/reports.php');
use local_xray\event\get_report_failed;

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

        $output = '';
        try {
            if (has_capability("local/xray:gradebookreport_view", $this->get_context())) {

                $report = "gradebook";
                $response = \local_xray\local\api\wsapi::course($this->courseid, $report);
                if (!$response) {
                    // Fail response of webservice.
                    \local_xray\local\api\xrayws::instance()->print_error();
                } else {
                    // Show graphs.
                    // Report date.
                    $output .= $this->print_top();
                    $output .= $this->output->inforeport($response->elements->element1->date);
                    // Its a table, I will get info with new call.
                    $datatable = new local_xray\datatables\datatables($response->elements->element2,
                        "rest.php?controller='gradebookreport'&action='jsonstudentgrades'&courseid=".$this->courseid);
                    $output .= $this->output->standard_table((array)$datatable);
                    // Graph.
                    $output .= $this->output->show_on_lightbox("densityofstandardizedscores",
                        $response->elements->studentScoreDistribution);

                    // Its a table, I will get info with new call.
                    $datatable = new local_xray\datatables\datatables($response->elements->element4,
                        "rest.php?controller='gradebookreport'&action='jsonsummaryquizzes'&courseid=".$this->courseid);
                    $output .= $this->output->standard_table((array)$datatable);

                    // Graphs.
                    $output .= $this->output->show_on_lightbox("boxplotofstandardizedscoresperquiz",
                        $response->elements->scoreDistributionByItem);
                    $output .= $this->output->show_on_lightbox("scoresassignedbyxrayversusresultsfromquizzes",
                        $response->elements->scatterPlot);
                    $output .= $this->output->show_on_lightbox("comparisonofscoresinquizzes",
                        $response->elements->itemsHeatmap);
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
        return $this->genericresponsejsonfordatatables("gradebook", "element2");
    }

    /**
     * Json for provide data to students_grades_for_course table.
     * @return string
     */
    public function jsonsummaryquizzes_action() {
        return $this->genericresponsejsonfordatatables("gradebook", "element4");
    }
}
