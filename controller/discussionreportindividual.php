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
 * @copyright Copyright (c) 2015 Blackboard Inc. (http://www.blackboard.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/xray/controller/reports.php');
use local_xray\event\get_report_failed;
use local_xray\local\api\course_manager;

/**
 * Xray integration Reports Controller
 *
 * @package   local_xray
 * @author    Pablo Pagnone
 * @author    German Vitale
 * @copyright Copyright (c) 2015 Blackboard Inc. (http://www.blackboard.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_xray_controller_discussionreportindividual extends local_xray_controller_reports {

    public function init() {
        $this->userid = required_param('userid', PARAM_INT);
        $this->url->param("userid", $this->userid);
    }

    public function view_action() {
        global $PAGE, $DB;

        $PAGE->navbar->add(get_string("navigation_xray", $this->component));
        // Add nav to return to discussionreport.
        $PAGE->navbar->add(get_string("discussionreport", $this->component),
            new moodle_url('/local/xray/view.php',
                array("controller" => "discussionreport", "courseid" => $this->courseid)));
        $PAGE->navbar->add($PAGE->title);

        $this->message_reports_disabled();
        if (!course_manager::is_xray_course($this->courseid)) {
            return $this->output->notification(get_string('warn_course_disabled', 'local_xray'), 'notifymessage');
        }
        $this->addiconhelp();

        $output = "";
        try {
            $report = "discussion";
            $response = \local_xray\local\api\wsapi::course($this->courseid, $report, $this->userid);
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
                $output .= $this->output->inforeport($response->reportdate,
                    $DB->get_record('user', array("id" => $this->userid)));

                // Its a table, I will get info with new call.
                $datatable = new local_xray\datatables\datatables($response->elements->discussionMetrics,
                    "rest.php?controller='discussionreportindividual'&action='jsonparticipationdiscussionindividual'&courseid=".
                    $this->courseid."&userid=".$this->userid);
                $output .= $this->output->standard_table((array)$datatable);

                // Special Table with variable columns.
                $jsonurlresponse = new moodle_url("rest.php",
                    array("controller" => "discussionreportindividual",
                        "action" => "jsonweekdiscussionindividual",
                        "courseid" => $this->courseid,
                        "userid" => $this->userid));
                $output .= $this->output->table_inverse_discussion_activity_by_week($response->elements->discussionActivityByWeek,
                    $jsonurlresponse);

                // Graphs.
                $extraparamaccessible = array("userid" => $this->userid);
                $output .= $this->output->show_graph("socialStructure", $response->elements->socialStructure,
                    $response->id, $extraparamaccessible);
                $output .= $this->output->show_graph("wordcloud", $response->elements->wordcloud,
                    $response->id, $extraparamaccessible);
                $output .= $this->output->show_graph("wordHistogram", $response->elements->wordHistogram,
                    $response->id, $extraparamaccessible);
            }
        } catch (Exception $e) {
            get_report_failed::create_from_exception($e, $this->get_context(), $this->name)->trigger();
            $output = $this->print_error('error_xray', $e->getMessage());
        }

        return $output;
    }

    /**
     * Json for provide data to participation_metrics table.
     * @return string
     */
    public function jsonparticipationdiscussionindividual_action() {
        return $this->genericresponsejsonfordatatables("discussion", "discussionMetrics");
    }

    /**
     * Json for provide data to discussion_activity_by_week table.
     * This is special case, with different format, for this we dont use genericresponsejsonfordatatables.
     *
     * @return string
     */
    public function jsonweekdiscussionindividual_action() {
        $count = optional_param('count', 10, PARAM_INT); // Count param with number of weeks.
        $start = optional_param('iDisplayStart', 0, PARAM_INT);
        $sortcol = optional_param('iSortCol_0', 0, PARAM_INT); // Number of column to sort.
        $sortorder = optional_param('sSortDir_0', "asc", PARAM_ALPHA); // Direction of sort.
        $sortfield = optional_param("mDataProp_{$sortcol}", "id", PARAM_TEXT); // Get column name.

        $return = [];

        try {
            $report = "discussion";
            $element = "discussionActivityByWeek";

            $response = \local_xray\local\api\wsapi::courseelement($this->courseid,
                $element,
                $report,
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
                                // Check if timerange is defined.
                                $minutes = false;
                                if (isset($response->dataFormat->avgLag) &&
                                    $response->dataFormat->avgLag == self::XRAYTIMERANGEMINUTE) {
                                    $minutes = true;
                                }
                                // Set time to HH:MM.
                                $avglag[$col->week->value] = $this->show_time_hours_minutes($col->avgLag->value, $minutes);
                            }
                            // Average No of Words.
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
}
