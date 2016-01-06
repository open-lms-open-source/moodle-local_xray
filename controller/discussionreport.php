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
 * Xray integration Reports Controller
 *
 * @package   local_xray
 * @author    Pablo Pagnone
 * @author    German Vitale
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_xray_controller_discussionreport extends local_xray_controller_reports {

    /**
     * Require capabilities
     */
    public function require_capability() {
        // Change INT-8194 , this report show 3 differents reports.
        $ctx = $this->get_context();
        if (!has_capability("local/xray:discussionreport_view", $ctx) &&
            !has_capability("local/xray:discussionendogenicplagiarism_view", $ctx) &&
            !has_capability("local/xray:discussiongrading_view", $ctx)
        ) {

            throw new required_capability_exception($ctx, "local/xray:discussionreport_view", 'nopermissions', '');
        }
    }

    public function view_action() {

        $this->addiconhelp();
        $output = '';
        $ctx = $this->get_context();
        try {

            if (has_capability("local/xray:discussionreport_view", $ctx)) {

                $report = "discussion";
                $response = \local_xray\local\api\wsapi::course($this->courseid, $report);
                if (!$response) {
                    // Fail response of webservice.
                    \local_xray\local\api\xrayws::instance()->print_error();
                } else {
                    // Show graphs.
                    // Report date.
                    $output  = $this->print_top();
                    $output .= $this->output->inforeport($response->elements->element1->date);

                    // Its a table, I will get info with new call.
                    $datatable = new local_xray\datatables\datatables($response->elements->discussionMetrics,
                        "rest.php?controller='discussionreport'&action='jsonparticipationdiscussion'&courseid=" . $this->courseid,
                        array(),
                        true,
                        true,
                        '<"top">rt<"bottom"flp><"clear">'); // Add column action.
                    $datatable->default_field_sort = 1; // Sort by first column "Lastname".Because table has action column);
                    $output .= $this->output->standard_table((array)$datatable);

                    // Special Table with variable columns.
                    $output .= $this->output->discussionreport_discussion_activity_by_week($this->courseid,
                        $response->elements->discussionActivityByWeek);

                    $output .= $this->output->show_graph("wordcloud", $response->elements->wordcloud, $response->id);
                    $output .= $this->output->show_graph("avgWordPerPost", $response->elements->avgWordPerPost, $response->id);
                    $output .= $this->output->show_graph("socialStructure", $response->elements->socialStructure, $response->id);
                    $output .= $this->output->show_graph("socialStructureWordCount",
                        $response->elements->socialStructureWordCount, $response->id);
                    $output .= $this->output->show_graph("socialStructureWordContribution",
                        $response->elements->socialStructureWordContribution, $response->id);
                    $output .= $this->output->show_graph("socialStructureWordCTC",
                        $response->elements->socialStructureWordCTC, $response->id);
                }
            }

            // Show reports Discussion endogenic.
            if (has_capability("local/xray:discussionendogenicplagiarism_view", $ctx)) {
                $report = "discussionEndogenicPlagiarism";
                $response = \local_xray\local\api\wsapi::course($this->courseid, $report);
                if (!$response) {
                    $this->debugwebservice();
                    // Fail response of webservice.
                    \local_xray\local\api\xrayws::instance()->print_error();
                } else {
                    // Show graphs.
                    $output .= html_writer::tag("h2", get_string("discussionendogenicplagiarism", $this->component),
                        array("class" => "xray-report-page-title"));
                    $output .= $this->output->inforeport($response->reportdate);
                    $output .= $this->output->show_graph("endogenicPlagiarismStudentsHeatmap",
                        $response->elements->endogenicPlagiarismStudentsHeatmap, $response->id);
                    $output .= $this->output->show_graph("endogenicPlagiarismHeatmap",
                        $response->elements->endogenicPlagiarismHeatmap, $response->id);
                }
            }

            // Show reports discussion grading.
            if (has_capability("local/xray:discussiongrading_view", $ctx)) {

                $report = "discussionGrading";
                $response = \local_xray\local\api\wsapi::course($this->courseid, $report);
                if (!$response) {
                    // Fail response of webservice.
                    \local_xray\local\api\xrayws::instance()->print_error();
                } else {
                    // Show graphs.
                    $output .= html_writer::tag("h2",
                        get_string("discussiongrading", $this->component),
                        array("class" => "xray-report-page-title"));
                    $output .= $this->output->inforeport($response->reportdate);

                    // Its a table, I will get info with new call.
                    $datatable = new local_xray\datatables\datatables($response->elements->studentDiscussionGrades,
                        "rest.php?controller='discussionreport'&action='jsonstudentsgrades'&courseid=" . $this->courseid);
                    $output .= $this->output->standard_table((array)$datatable);
                    $output .= $this->output->show_graph("discussionSuggestedGrades",
                        $response->elements->discussionSuggestedGrades, $response->id);
                }
            }

        } catch (Exception $e) {
            get_report_failed::create_from_exception($e, $this->get_context(), $this->name)->trigger();
            $output = $this->print_error('error_xray', $e->getMessage());
        }
        return $output;
    }

    public function jsonparticipationdiscussion_action() {
        return $this->genericresponsejsonfordatatables("discussion",
            "discussionMetrics",
            "responseparticipationdiscussion");
    }

    /**
     * Json for provide data to participation_metrics table.
     */
    public function responseparticipationdiscussion($response) {

        global $PAGE;
        $data = array();
        $discussionreportind = get_string('discussionreportindividual', $this->component);
        foreach ($response->data as $row) {
            $r = new stdClass();
            $r->action = "";
            if (has_capability('local/xray:discussionreportindividual_view', $PAGE->context)) {
                // Url for discussionreportindividual.
                $url = new moodle_url("/local/xray/view.php",
                    array("controller" => "discussionreportindividual",
                        "courseid" => $this->courseid,
                        "userid" => $row->participantId->value
                    ));
                $r->action = html_writer::link($url, '', array("class" => "xray_icon_discussionreportindividual",
                    "title" => $discussionreportind,
                    "target" => "_blank"));
            }

            // Format of response for columns.
            if (!empty($response->columnOrder)) {
                foreach ($response->columnOrder as $column) {
                    $r->{$column} = '';
                    if (isset($row->{$column}->value)) {
                        $r->{$column} = $this->show_intuitive_value($row->{$column}->value, $response->elementName, $column);
                    }
                }
                $data[] = $r;
            }
        }
        return $data;
    }

    /**
     * Json for provide data to discussion_activity_by_week table.
     * We dont use genericresponsejsonfordatatables because this is a specific table with different format.
     */
    public function jsonweekdiscussion_action() {
        // Pager.
        // Count param with number of weeks.
        $count = optional_param('count', 10, PARAM_INT);
        $start = optional_param('iDisplayStart', 0, PARAM_INT);
        // Sortable
        $sortcol = optional_param('iSortCol_0', 0, PARAM_INT); // Number of column to sort.
        $sortorder = optional_param('sSortDir_0', "asc", PARAM_ALPHA); // Direction of sort.
        $sortfield = optional_param("mDataProp_{$sortcol}", "id", PARAM_TEXT); // Get column name.

        $return = "";

        try {
            $report = "discussion";
            $element = "discussionActivityByWeek";

            $response = \local_xray\local\api\wsapi::courseelement($this->courseid,
                $element,
                $report,
                null,
                '',
                '',
                $start,
                $count,
                $sortfield,
                $sortorder);

            if (!$response) {
                \local_xray\local\api\xrayws::instance()->print_error();
            } else {
                $data = array();
                if (!empty($response->data)) {
                    // This report has not specified columnOrder.
                    if (!empty($response->columnHeaders) && is_object($response->columnHeaders)) {
                        // Inverted table.
                        // Add column names to the first column.
                        $posts = array('weeks' => $response->columnHeaders->posts);
                        $avglag = array('weeks' => $response->columnHeaders->avgLag);
                        $avgwordcount = array('weeks' => $response->columnHeaders->avgWordCount);

                        // Add the remaining data. The number of each week will be the column name.
                        foreach ($response->data as $col) {
                            // Number of posts.
                            $posts[$col->week->value] = (isset($col->posts->value) ? $col->posts->value : '');
                            // Average time to respond (hours).
                            $avglag[$col->week->value] = '';
                            if (isset($col->avgLag->value)) {
                                // Set time to HH:MM.
                                $avglag[$col->week->value] = $this->show_time_hours_minutes($col->avgLag->value);
                            }
                            // Average word count.
                            $avgwordcount[$col->week->value] = '';
                            if (isset($col->avgWordCount->value)) {
                                // Round Value.
                                $avgwordcount[$col->week->value] = round(floatval($col->avgWordCount->value), 2);
                            }
                        }
                        $data[] = $posts;
                        $data[] = $avglag;
                        $data[] = $avgwordcount;
                    }
                }

                // Provide info to table.
                $return["recordsFiltered"] = $response->itemCount;
                $return["recordsTotal"] = $response->itemCount;
                $return["data"] = $data;
            }
        } catch (Exception $e) {
            get_report_failed::create_from_exception($e, $this->get_context(), $this->name)->trigger();
            // Error, return invalid data, and pluginjs will show error in table.
            $return["data"] = "-";
        }

        return json_encode($return);
    }

    /**
     * Json for provide data to students_grades_based_on_discussions table.
     */
    public function jsonstudentsgrades_action() {
        return $this->genericresponsejsonfordatatables("discussionGrading", "studentDiscussionGrades");
    }
}
