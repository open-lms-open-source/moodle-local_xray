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
class local_xray_controller_discussionreportindividual extends local_xray_controller_reports {

    public function init() {
        parent::init();
        $this->userid = required_param('userid', PARAM_INT);
    }

    public function view_action() {
        global $PAGE, $DB;

        // Add nav to return to discussionreport.
        $PAGE->navbar->add(get_string("discussionreport", $this->component),
            new moodle_url('/local/xray/view.php',
                array("controller" => "discussionreport", "courseid" => $this->courseid)));
        $PAGE->navbar->add($PAGE->title);
        $output = "";
        try {
            $report = "discussion";
            $response = \local_xray\local\api\wsapi::course($this->courseid, $report, $this->userid);
            if (!$response) {
                // Fail response of webservice.
                \local_xray\local\api\xrayws::instance()->print_error();
            } else {
                // Show graphs.
                $output .= $this->print_top();
                $output .= $this->output->inforeport($response->reportdate,
                    $DB->get_record('user', array("id" => $this->userid)));

                // Its a table, I will get info with new call.
                $datatable = new local_xray\datatables\datatables($response->elements->discussionMetrics,
                    "view.php?controller='discussionreportindividual'&action='jsonparticipationdiscussionindividual'&courseid=".
                    $this->courseid."&userid=".$this->userid);
                $output .= $this->output->standard_table((array)$datatable);

                // Special Table with variable columns.
                $output .= $this->output->discussionreportindividual_discussion_activity_by_week($this->courseid,
                    $this->userid,
                    $response->elements->discussionActivityByWeek);

                // Graphs.
                $output .= $this->output->show_on_lightbox("socialStructure", $response->elements->socialStructure);
                $output .= $this->output->show_on_lightbox("wordcloud", $response->elements->wordcloud);
                $output .= $this->output->show_on_lightbox("wordHistogram", $response->elements->wordHistogram);
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

        $return = "";

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
                        $avgwordcount = array('weeks' => $response->columnHeaders->avgWordCount);

                        // Add the remaining data. The number of each week will be the column name.
                        foreach ($response->data as $col) {
                            $posts[$col->week->value] = (isset($col->posts->value) ? $col->posts->value : '');
                            $avgwordcount[$col->week->value] = (isset($col->avgWordCount->value) ? $col->avgWordCount->value : '');
                        }
                        $data[] = $posts;
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
