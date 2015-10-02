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
class local_xray_controller_discussionreport extends local_xray_controller_reports {

    public function view_action() {
        $output = '';

        try {
            $report = "discussion";
            $response = \local_xray\api\wsapi::course($this->courseid, $report);
            if (!$response) {
                // Fail response of webservice.
                throw new Exception(\local_xray\api\xrayws::instance()->geterrormsg());
            } else {
                // Show graphs.
                // Its a table, I will get info with new call.
                $output .= $this->participation_metrics($response->elements->discussionMetrics);
                // Table with variable columns - Send data to create columns.
                $output .= $this->discussion_activity_by_week($response->elements->discussionActivityByWeek);
                $output .= $this->main_terms($response->elements->wordcloud);
                $output .= $this->average_words_weekly_by_post($response->elements->avgWordPerPost);
                $output .= $this->social_structure($response->elements->socialStructure);
                $output .= $this->social_structure_with_word_count($response->elements->socialStructureWordCount);
                $output .= $this->social_structure_with_contributions_adjusted(
                    $response->elements->socialStructureWordContribution
                );
                $output .= $this->social_structure_coefficient_of_critical_thinking($response->elements->socialStructureWordCTC);
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
        $output .= $this->output->discussionreport_participation_metrics($this->courseid, $element);
        return $output;
    }

    /**
     * Report "Discussion Activity by Week" (table).
     * @param object $element
     * @return string
     */
    private function discussion_activity_by_week($element) {
        $output = "";
        $output .= $this->output->discussionreport_discussion_activity_by_week($this->courseid, $element);
        return $output;
    }

    /**
     * Json for provide data to participation_metrics table.
     */
    public function jsonparticipationdiscussion_action() {
        global $PAGE;

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
                null,
                '',
                '',
                $start,
                $count);

            if (!$response) {
                throw new Exception(\local_xray\api\xrayws::instance()->geterrormsg());
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
        $count = (int)optional_param('count', 10, PARAM_ALPHANUM);
        $start = (int)optional_param('iDisplayStart', 0, PARAM_ALPHANUM);

        $return = "";

        try {
            $report = "discussion";
            $element = "discussionActivityByWeek";

            $response = \local_xray\api\wsapi::courseelement($this->courseid,
                $element,
                $report,
                null,
                '',
                '',
                $start,
                $count);

            if (!$response) {
                throw new Exception(\local_xray\api\xrayws::instance()->geterrormsg());
            } else {
                $data = array();
                if (!empty($response->data)) {
                    // This report has not specified columnOrder.
                    if (!empty($response->columnHeaders) && is_object($response->columnHeaders)) {
                        $posts = array('weeks' => $response->columnHeaders->posts);
                        $avglag = array('weeks' => $response->columnHeaders->avgLag);
                        $avgwordcount = array('weeks' => $response->columnHeaders->avgWordCount);

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
                $return["data"] = $data;
            }
        } catch (Exception $e) {
            // Error, return invalid data, and pluginjs will show error in table.
            $return["data"] = "-";
        }

        return json_encode($return);
    }

    /**
     * Report Average Words Weekly by Post.
     * @param object $element
     * @return string
     */
    private function average_words_weekly_by_post($element) {
        $output = "";
        $output .= $this->output->discussionreport_average_words_weekly_by_post($element);
        return $output;
    }

    /**
     * Report Social Structure.
     * @param object $element
     * @return string
     */
    private function social_structure($element) {
        $output = "";
        $output .= $this->output->discussionreport_social_structure($element);
        return $output;
    }

    /**
     * Report Social Structure with word count.
     * @param object $element
     * @return string
     *
     */
    private function social_structure_with_word_count($element) {
        $output = "";
        $output .= $this->output->discussionreport_social_structure_with_word_count($element);
        return $output;
    }

    /**
     * Report Social Structure With Contributions Adjusted
     * @param object $element
     * @return string
     */
    private function social_structure_with_contributions_adjusted($element) {

        $output = "";
        $output .= $this->output->discussionreport_social_structure_with_contributions_adjusted($element);
        return $output;
    }

    /**
     * Report Social Structure Coefficient of Critical Thinking
     * @param object $element
     * @return string
     */
    private function social_structure_coefficient_of_critical_thinking($element) {

        $output = "";
        $output .= $this->output->discussionreport_social_structure_coefficient_of_critical_thinking($element);
        return $output;
    }

    /**
     * Report Main Terms
     * @param object $element
     * @return string
     */
    private function main_terms($element) {

        $output = "";
        $output .= $this->output->discussionreport_main_terms($element);
        return $output;
    }
}
