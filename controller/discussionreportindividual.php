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
 * Xray integration Reports Controller
 *
 * @author Pablo Pagnone
 * @author German Vitale
 * @package local_xray
 */
class local_xray_controller_discussionreportindividual extends local_xray_controller_reports {

    public function init() {
        parent::init();
        $this->userid = (int)required_param('userid', PARAM_ALPHANUM);
    }

    public function view_action() {
        global $PAGE, $DB;

        // Add nav to return to discussionreport.
        $PAGE->navbar->add(get_string("discussionreport", $this->component),
                           new moodle_url('/local/xray/view.php',
                                          ["controller" => "discussionreport", "courseid" => $this->courseid]));
        $PAGE->navbar->add($PAGE->title);
        $output = "";
        try {
            $report = "discussion";
            $response = \local_xray\api\wsapi::course($this->courseid, $report, $this->userid);
            if (!$response) {
                // Fail response of webservice.
                throw new Exception(\local_xray\api\xrayws::instance()->geterrormsg());
            } else {
                // Show graphs.
                $output .= $this->output->inforeport($response->reportdate,
                                                     $DB->get_field('user', 'username', array("id" => $this->userid)),
                                                     $PAGE->course->fullname);
                // Its a table, I will get info with new call.
                $output .= $this->participation_metrics($response->elements->discussionMetrics);
                // Table with variable columns - Send data to create columns.
                $output .= $this->discussion_activity_by_week($response->elements->discussionActivityByWeek);
                $output .= $this->social_structure($response->elements->socialStructure);
                $output .= $this->main_terms($response->elements->wordcloud);
                $output .= $this->main_terms_histogram($response->elements->wordHistogram);
            }
        } catch (Exception $e) {
            print_error('error_xray', 'local_xray', '', null, $e->getMessage());
        }

        return $output;
    }

    /**
     * Report "A summary table to be added" (table).
     * @param object $element
     * @return string
     */
    private function participation_metrics($element) {
        $output = "";
        $output .= $this->output->discussionreportindividual_participation_metrics($this->courseid, $element, $this->userid);
        return $output;
    }

    /**
     * Json for provide data to participation_metrics table.
     * @return string
     */
    public function jsonparticipationdiscussionindividual_action() {

        // Pager.
        $count = (int)optional_param('iDisplayLength', 10, PARAM_ALPHANUM);
        $start = (int)optional_param('iDisplayStart', 0, PARAM_ALPHANUM);
        $return = "";
        try {
            $report = "discussion";
            $element = "discussionMetrics";

            $response = \local_xray\api\wsapi::courseelement($this->courseid,
                $element,
                $report,
                $this->userid,
                '',
                '',
                $start,
                $count);

            if (!$response) {
                // TODO:: Fail response of webservice.
                throw new Exception(\local_xray\api\xrayws::instance()->geterrormsg());
            } else {
                $data = array();
                if (!empty($response->data)) {
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

    /**
     * Report "Discussion Activity by Week" (table).
     * @param object $element
     * @return string
     */
    private function discussion_activity_by_week($element) {
        $output = "";
        $output .= $this->output->discussionreportindividual_discussion_activity_by_week($this->courseid, $this->userid, $element);
        return $output;
    }

    /**
     * Json for provide data to discussion_activity_by_week table.
     *
     * @return string
     */
    public function jsonweekdiscussionindividual_action() {
        // Pager
        $count = (int)optional_param('count', 10, PARAM_ALPHANUM); // Count param with number of weeks.
        $start = (int)optional_param('iDisplayStart', 0, PARAM_ALPHANUM);

        $return = "";

        try {
            $report = "discussion";
            $element = "discussionActivityByWeek";

            $response = \local_xray\api\wsapi::courseelement($this->courseid, // TODO:: Hardcoded.
                $element,
                $report,
                $this->userid,
                '',
                '',
                $start,
                $count);

            if (!$response) {
                // TODO:: Fail response of webservice.
                throw new Exception(\local_xray\api\xrayws::instance()->geterrormsg());
            } else {
                $data = array();
                $posts = array('weeks' => $response->columnHeaders->posts);
                $avgwordcount = array('weeks' => $response->columnHeaders->avgWordCount);
                if (!empty($response->data)) {
                    foreach ($response->data as $col) {
                        $posts[$col->week->value] = (isset($col->posts->value) ? $col->posts->value : '');
                        $avgwordcount[$col->week->value] = (isset($col->avgWordCount->value) ? $col->avgWordCount->value : '');
                    }
                    $data[] = $posts;
                    $data[] = $avgwordcount;
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

    /**
     * Report Social Structure.
     * @param object $element
     * @return string
     */
    private function social_structure($element) {
        $output = "";
        $output .= $this->output->discussionreportindividual_social_structure($element);
        return $output;
    }

    /**
     * Report Main Terms.
     * @param mixed $element
     * @return string
     */
    private function main_terms($element) {
        $output = "";
        $output .= $this->output->discussionreportindividual_main_terms($element);
        return $output;
    }

    /**
     * Report Main Terms Histogram.
     * @param object $element
     * @return string
     */
    private function main_terms_histogram($element) {
        $output = "";
        $output .= $this->output->discussionreportindividual_main_terms_histogram($element);
        return $output;
    }

}
