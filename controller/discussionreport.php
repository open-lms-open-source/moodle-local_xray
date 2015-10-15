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
        global $PAGE;
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
                    		"view.php?controller='discussionreport'&action='jsonparticipationdiscussion'&courseid=" . $this->courseid,
                    		array(),
				            true, // Add column action.
				            true,
				            "lftipr",
				            array(10, 50, 100),
				            true,
				            1); // Sort by first column "Lastname".Because table has action column.      
                    $output .= $this->output->standard_table((array)$datatable);
                    
                    // Special Table with variable columns.
                    $output .= $this->output->discussionreport_discussion_activity_by_week($this->courseid, 
                    		$response->elements->discussionActivityByWeek);
                    
                    $output .= $this->output->show_on_lightbox("wordcloud", $response->elements->wordcloud);
                    $output .= $this->output->show_on_lightbox("avgWordPerPost", $response->elements->avgWordPerPost);
                    $output .= $this->output->show_on_lightbox("socialStructure", $response->elements->socialStructure);
                    $output .= $this->output->show_on_lightbox("socialStructureWordCount", $response->elements->socialStructureWordCount);
                    $output .= $this->output->show_on_lightbox("socialStructureWordContribution", $response->elements->socialStructureWordContribution);
                    $output .= $this->output->show_on_lightbox("socialStructureWordCTC", $response->elements->socialStructureWordCTC);
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
                    $output .= html_writer::tag("div",
                        html_writer::tag("h2", get_string("discussionendogenicplagiarism", $this->component),
                            array("class" => "main")),
                        array("class" => "mr_html_heading"));
                    $output .= $this->output->inforeport($response->reportdate);
                    $output .= $this->output->show_on_lightbox("endogenicPlagiarismStudentsHeatmap", $response->elements->endogenicPlagiarismStudentsHeatmap);
                    $output .= $this->output->show_on_lightbox("endogenicPlagiarismHeatmap", $response->elements->endogenicPlagiarismHeatmap);
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
                    $output .= html_writer::tag("div",
                        html_writer::tag("h2", get_string("discussiongrading", $this->component), array("class" => "main")),
                        array("class" => "mr_html_heading"));
                    $output .= $this->output->inforeport($response->reportdate);
                    
                    // Its a table, I will get info with new call.
                    $datatable = new local_xray\datatables\datatables($response->elements->studentDiscussionGrades,
                    		"view.php?controller='discussionreport'&action='jsonstudentsgrades'&courseid=" . $this->courseid);
                    $output .= $this->output->standard_table((array)$datatable);
                    
                    $output .= $this->output->show_on_lightbox("discussionSuggestedGrades", $response->elements->discussionSuggestedGrades);;
                }
            }

        } catch (Exception $e) {
            $output = $this->print_error('error_xray', $e->getMessage());
        }
        return $output;
    }

    /**
     * Json for provide data to participation_metrics table.
     */
    public function jsonparticipationdiscussion_action() {
        global $PAGE;

        // Pager.
        $count = optional_param('iDisplayLength', 10, PARAM_INT);
        $start = optional_param('iDisplayStart', 0, PARAM_INT);
        // Sortable
        $sortcol = optional_param('iSortCol_0', 0, PARAM_INT); // Number of column to sort.
        $sortorder = optional_param('sSortDir_0', "asc", PARAM_ALPHA); // Direction of sort.
        $sortfield = optional_param("mDataProp_{$sortcol}", "id", PARAM_TEXT); // Get column name.

        $return = "";

        try {
            $report = "discussion";
            $element = "discussionMetrics";

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
                            $r->action = html_writer::link($url, '', array("class" => "icon_discussionreportindividual",
                                "title" => $discussionreportind,
                                "target" => "_blank"));
                        }

                        // Format of response for columns.
                        if (!empty($response->columnOrder)) {
                            foreach ($response->columnOrder as $column) {
                                $r->{$column} = (isset($row->{$column}->value) ? $row->{$column}->value : '');
                            }
                            $data[] = $r;
                        }
                    }
                }

                // Provide info to table.
                $return["recordsFiltered"] = $response->itemCount;
                $return["recordsTotal"] = $response->itemCount;
                $return["data"] = $data;
            }
        } catch (Exception $e) {
            // Error, return invalid data, and pluginjs will show error in table.
            $return["data"] = "-";
        }

        return json_encode($return);
    }

    /**
     * Json for provide data to discussion_activity_by_week table.
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
                            $posts[$col->week->value] = (isset($col->posts->value) ? $col->posts->value : '');
                            $avglag[$col->week->value] = (isset($col->avgLag->value) ? $col->avgLag->value : '');
                            $avgwordcount[$col->week->value] = (isset($col->avgWordCount->value) ? $col->avgWordCount->value : '');
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
            // Error, return invalid data, and pluginjs will show error in table.
            $return["data"] = "-";
        }

        return json_encode($return);
    }

    /**
     * Json for provide data to students_grades_based_on_discussions table.
     */
    public function jsonstudentsgrades_action() {
        // Pager.
        $count = optional_param('iDisplayLength', 10, PARAM_INT);
        $start = optional_param('iDisplayStart', 0, PARAM_INT);
        // Sortable
        $sortcol = optional_param('iSortCol_0', 0, PARAM_INT); // Number of column to sort.
        $sortorder = optional_param('sSortDir_0', "asc", PARAM_ALPHA); // Direction of sort.
        $sortfield = optional_param("mDataProp_{$sortcol}", "id", PARAM_TEXT); // Get column name.

        $return = "";

        try {
            $report = "discussionGrading";
            $element = "studentDiscussionGrades";
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
                if (!empty ($response->data)) {
                    foreach ($response->data as $row) {
                        // Format of response for columns.
                        if (!empty($response->columnOrder)) {
                            $r = new stdClass();
                            foreach ($response->columnOrder as $column) {
                                $r->{$column} = (isset($row->{$column}->value) ? $row->{$column}->value : '');
                            }
                            $data[] = $r;
                        }
                    }
                }
                // Provide count info to table.
                $return ["recordsFiltered"] = $response->itemCount;
                $return ["recordsTotal"] = $response->itemCount;
                $return ["data"] = $data;
            }
        } catch (Exception $e) {
            // Error, return invalid data, and pluginjs will show error in table.
            $return["data"] = "-";
        }

        return json_encode($return);
    }
}
