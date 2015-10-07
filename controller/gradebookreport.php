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
class local_xray_controller_gradebookreport extends local_xray_controller_reports {

    public function view_action() {
        $output = '';
        try {
            $report = "gradebook";
            $response = \local_xray\api\wsapi::course($this->courseid, $report);
            if (!$response) {
                // Fail response of webservice.
                \local_xray\api\xrayws::instance()->print_error();
            } else {
                // Its a table, I will get info with new call.
                $output .= $this->student_grades($response->elements->element2);
                $output .= $this->density_of_standardized_scores($response->elements->element3);
                // Its a table, I will get info with new call.
                $output .= $this->summary_of_quizzes($response->elements->element4);
                $output .= $this->boxplot_of_standardized_scores_per_quiz($response->elements->element5);
                $output .= $this->scores_assigned_by_xray_versus_results_from_quizzes($response->elements->element6);
                $output .= $this->comparison_of_scores_in_quizzes($response->elements->element7);
            }
        } catch (Exception $e) {
            $output = $this->print_error('error_xray', $e->getMessage());
        }

        return $output;
    }

    /**
     * Report Students' Grades for course (table).
     * @param object $element
     * @return string
     *
     */
    private function student_grades($element) {
        $output = "";
        $output .= $this->output->gradebookreport_student_grades($this->courseid, $element);
        return $output;
    }

    /**
     * Json for provide data to students_grades_for_course table.
     *
     * @return string
     */
    public function jsonstudentgrades_action() {
        // Pager.
        $count = (int)optional_param('iDisplayLength', 10, PARAM_ALPHANUM);
        $start = (int)optional_param('iDisplayStart', 0, PARAM_ALPHANUM);

        $return = "";

        try {
            $report = "gradebook";
            $element = "element2";
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
     * Report Students' Grades for course (table).
     * @param object $element
     * @return string
     */
    private function summary_of_quizzes($element) {
        $output = "";
        $output .= $this->output->gradebookreport_summary_of_quizzes($this->courseid, $element);
        return $output;
    }

    /**
     * Json for provide data to students_grades_for_course table.
     * @return string
     */
    public function jsonsummaryquizzes_action() {
        // Pager.
        $count = (int)optional_param('iDisplayLength', 10, PARAM_ALPHANUM);
        $start = (int)optional_param('iDisplayStart', 0, PARAM_ALPHANUM);
        $return = "";
        try {
            $report = "gradebook";
            $element = "element4";
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
     * Report Density of Standardized Scores.
     * @param object $element
     * @return string
     */
    private function density_of_standardized_scores($element) {
        $output = "";
        $output .= $this->output->gradebookreport_density_of_standardized_scores($element);
        return $output;
    }

    /**
     * Report Boxplot of Standardized Scores per Quiz.
     * @param object $element
     * @return string
     */
    private function boxplot_of_standardized_scores_per_quiz($element) {
        $output = "";
        $output .= $this->output->gradebookreport_boxplot_of_standardized_scores_per_quiz($element);
        return $output;
    }

    /**
     * Report Distribution of grades in course.
     * @param object $element
     * @return string
     */
    private function scores_assigned_by_xray_versus_results_from_quizzes($element) {
        $output = "";
        $output .= $this->output->gradebookreport_scores_assigned_by_xray_versus_results_from_quizzes($element);
        return $output;
    }

    /**
     * Report Distribution of grades in course.
     * @param object $element
     * @return string
     */
    private function comparison_of_scores_in_quizzes($element) {
        $output = "";
        $output .= $this->output->gradebookreport_comparison_of_scores_in_quizzes($element);
        return $output;
    }
}
