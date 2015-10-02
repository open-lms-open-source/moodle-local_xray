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
require_once($CFG->dirroot.'/local/xray/controller/reports.php');

/**
 * Xray integration Reports Controller
 *
 * @author Pablo Pagnone
 * @author German Vitale
 * @package local_xray
 */
class local_xray_controller_activityreport extends local_xray_controller_reports {

    /**
     * Require capabilities
     */
    public function require_capability() {
        // Change INT-8194 , this report show 3 differents reports.
        $ctx = $this->get_context();
        if (!has_capability("local/xray:activityreport_view", $ctx) &&
            !has_capability("local/xray:discussionendogenicplagiarism_view", $ctx) &&
            !has_capability("local/xray:discussiongrading_view", $ctx)
        ) {

            throw new required_capability_exception($ctx, "local/xray:activityreport_view", 'nopermissions', '');
        }
    }

    public function view_action() {
        global $PAGE;

        $output = '';
        $ctx = $this->get_context();

        try {

            if (has_capability("local/xray:activityreport_view", $ctx)) {

                $report = "firstLogin";
                $responsefirstlogin = \local_xray\api\wsapi::course($this->courseid, $report);
                if (!$responsefirstlogin) {
                    // Fail response of webservice.
                    \local_xray\api\xrayws::instance()->print_error();

                } else {

                    $output .= $this->output->inforeport($responsefirstlogin->reportdate, null, $PAGE->course->fullname);
                    // Show graphs. We need show table first in activity report.(INT-8186).
                    $output .= $this->first_login_non_starters($responsefirstlogin->elements->nonStarters);
                }

                $report = "activity";
                $response = \local_xray\api\wsapi::course($this->courseid, $report);
                if (!$response) {
                    // Fail response of webservice.
                    \local_xray\api\xrayws::instance()->print_error();
                } else {
                    // Show graphs Activity report.
                    $output .= $this->students_activity($response->elements->studentList);
                    $output .= $this->activity_of_course_by_day($response->elements->activityLevelTimeline);
                    $output .= $this->activity_by_time_of_day($response->elements->compassTimeDiagram);
                    $output .= $this->activity_last_two_weeks_by_weekday($response->elements->barplotOfActivityByWeekday);
                    $output .= $this->activity_last_two_weeks($response->elements->barplotOfActivityWholeWeek);
                    $output .= $this->activity_by_participant1($response->elements->activityByWeekAsFractionOfTotal);
                    $output .= $this->activity_by_participant2($response->elements->activityByWeekAsFractionOfOwn);
                    $output .= $this->first_login_to_course($responsefirstlogin->elements->firstloginPiechartAdjusted);
                }
            }
            // Show reports Discussion endogenic (INT-8194).
            if (has_capability("local/xray:discussionendogenicplagiarism_view", $ctx)) {
                $report = "discussionEndogenicPlagiarism";
                $response = \local_xray\api\wsapi::course($this->courseid, $report);
                if (!$response) {
                    $this->debugwebservice();
                    // Fail response of webservice.
                    \local_xray\api\xrayws::instance()->print_error();
                } else {
                    // Show graphs.
                    $output .= html_writer::tag("div",
                        html_writer::tag("h2", get_string("discussionendogenicplagiarism", $this->component),
                                         array("class" => "main")),
                                         array("class" => "mr_html_heading"));
                    $output .= $this->output->inforeport($response->reportdate, null, $PAGE->course->fullname);
                    $output .= $this->heatmap_endogenic_plagiarism_students(
                                      $response->elements->endogenicPlagiarismStudentsHeatmap
                               );
                    $output .= $this->heatmap_endogenic_plagiarism_instructors($response->elements->endogenicPlagiarismHeatmap);
                }
            }

            // Show reports discussion grading. (INT-8194).
            if (has_capability("local/xray:discussiongrading_view", $ctx)) {

                $report = "discussionGrading";
                $response = \local_xray\api\wsapi::course($this->courseid, $report);
                if (!$response) {
                    // Fail response of webservice.
                    \local_xray\api\xrayws::instance()->print_error();
                } else {

                    // Show graphs.
                    $output .= html_writer::tag("div",
                        html_writer::tag("h2", get_string("discussiongrading", $this->component), array("class" => "main")),
                        array("class" => "mr_html_heading"));
                    $output .= $this->output->inforeport($response->reportdate, null, $PAGE->course->fullname);
                    // Its a table, I will get info with new call.
                    $output .= $this->students_grades_based_on_discussions($response->elements->studentDiscussionGrades);
                    $output .= $this->barplot_of_suggested_grades($response->elements->discussionSuggestedGrades);
                }
            }

        } catch (Exception $e) {
            print_error('error_xray', $this->component, '', null, $e->getMessage() . ' ' . $PAGE->pagetype);
        }

        return $output;
    }

    /**
     * Report Students activity (table).
     * @param mixed $element
     * @return string
     */
    private function students_activity($element) {

        $output = "";
        $output .= $this->output->activityreport_students_activity($this->courseid, $element);
        return $output;
    }

    /**
     * Json for provide data to students_activity table.
     * @return string
     */
    public function jsonstudentsactivity_action() {
        global $PAGE;

        // Pager.
        $count = (int)optional_param('iDisplayLength', 10, PARAM_ALPHANUM);
        $start = (int)optional_param('iDisplayStart', 0, PARAM_ALPHANUM);

        $return = "";
        try {
            $report = "activity";
            $element = "studentList";
            $response = \local_xray\api\wsapi::courseelement($this->courseid,
                $element,
                $report,
                null,
                '',
                '',
                $start,
                $count);

            if (!$response) {
                // Fail response of webservice.
                \local_xray\api\xrayws::instance()->print_error();
            } else {
                $data = array();
                if (!empty($response->data)) {
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
                            $r->action = html_writer::link($url, '', array("class" => "icon_activityreportindividual",
                                "title" => $activityreportind,
                                "target" => "_blank"));
                        }
                        // Format of response for columns.
                        if (!empty($response->columnOrder)) {
                            foreach ($response->columnOrder as $column) {
                                $r->{$column} = (isset($row->{$column}->value) ? $row->{$column}->value : '');
                            }
                        }
                        $data[] = $r;
                    }
                }
                // Provide count info to table.
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
     * Report Activity of course by day.
     * @param mixed $element
     * @return string
     */
    private function activity_of_course_by_day($element) {

        $output = "";
        $output .= $this->output->activityreport_activity_of_course_by_day($element);
        return $output;
    }

    /**
     * Report Activity by time of day.
     * @param object $element
     * @return string
     */
    private function activity_by_time_of_day($element) {

        $output = "";
        $output .= $this->output->activityreport_activity_by_time_of_day($element);
        return $output;
    }

    /**
     * Report Activity last two weeks.
     * @param mixed $element
     * @return string
     */
    private function activity_last_two_weeks($element) {

        $output = "";
        $output .= $this->output->activityreport_activity_last_two_weeks($element);
        return $output;
    }

    /**
     * Report Activity Last Two Weeks by Weekday
     * @param mixed $element
     * @return string
     */
    private function activity_last_two_weeks_by_weekday($element) {

        $output = "";
        $output .= $this->output->activityreport_activity_last_two_weeks_by_weekday($element);
        return $output;
    }

    /**
     * Report Activity by Participant 1
     * @param mixed $element
     * @return string
     */
    private function activity_by_participant1($element) {

        $output = "";
        $output .= $this->output->activityreport_activity_by_participant1($element);
        return $output;
    }

    /**
     * Report Activity by Participant 2
     * @param mixed $element
     * @return string
     */
    private function activity_by_participant2($element) {

        $output = "";
        $output .= $this->output->activityreport_activity_by_participant2($element);
        return $output;
    }

    /**
     * Report First login
     * - Element to show: table users not starters in course.
     * @param object $element
     * @return string
     */
    private function first_login_non_starters($element) {
        $output = "";
        $output .= $this->output->activityreport_first_login_non_starters($this->courseid, $element);
        return $output;
    }

    /**
     * Json for table non starters.
     *
     */
    public function jsonfirstloginnonstarters_action() {
        // Pager.
        $count = (int)optional_param('iDisplayLength', 10, PARAM_ALPHANUM);
        $start = (int)optional_param('iDisplayStart', 0, PARAM_ALPHANUM);

        $return = "";

        try {
            $report = "firstLogin";
            $element = "nonStarters";
            $response = \local_xray\api\wsapi::courseelement($this->courseid,
                $element,
                $report,
                null,
                '',
                '',
                $start,
                $count);
            if (!$response) {
                // Fail response of webservice.
                \local_xray\api\xrayws::instance()->print_error();

            } else {
                $data = array();
                if (!empty($response->data)) {
                    // Format of response for columns.
                    foreach ($response->data as $row) {

                        // This report has not specified columnOrder.
                        if (!empty($response->columnHeaders) && is_object($response->columnHeaders)) {
                            $r = new stdClass();
                            $c = get_object_vars($response->columnHeaders);
                            foreach ($c as $id => $name) {
                                $r->{$id} = (isset($row->{$id}->value) ? $row->{$id}->value : '');
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
     * Report First login
     * - Element to show: 5 , first login to course.
     * @param object $element
     * @return string
     */
    private function first_login_to_course($element) {
        $output = "";
        $output .= $this->output->activityreport_first_login_to_course($element);
        return $output;
    }

    /**
     * Report First login
     * - Element to show: 9 , first login in date observed.
     * @param object $element
     * @return string
     */
    private function first_login_date_observed($element) {
        $output = "";
        $output .= $this->output->activityreport_first_login_date_observed($element);
        return $output;
    }

    /**
     * Report Heatmap for students.
     * @param object $element
     * @return string
     */
    private function heatmap_endogenic_plagiarism_students($element) {

        $output = "";
        $output .= $this->output->discussionendogenicplagiarism_heatmap_endogenic_plagiarism_students($element);
        return $output;
    }

    /**
     * Report Heatmap for instructors.
     * @param $element
     * @return string
     */
    private function heatmap_endogenic_plagiarism_instructors($element) {

        $output = "";
        $output .= $this->output->discussionendogenicplagiarism_heatmap_endogenic_plagiarism_instructors($element);
        return $output;
    }

    /**
     * Report Student Grades Based on Discussions(table)
     * @param object $element
     * @return string
     */
    private function students_grades_based_on_discussions($element) {
        $output = "";
        $output .= $this->output->discussiongrading_students_grades_based_on_discussions($this->courseid, $element);
        return $output;
    }

    /**
     * Json for provide data to students_grades_based_on_discussions table.
     */
    public function jsonstudentsgrades_action() {
        // Pager.
        $count = (int)optional_param('iDisplayLength', 10, PARAM_ALPHANUM);
        $start = (int)optional_param('iDisplayStart', 0, PARAM_ALPHANUM);

        $return = "";

        try {
            $report = "discussionGrading";
            $element = "studentDiscussionGrades";
            $response = \local_xray\api\wsapi::courseelement($this->courseid, $element, $report, null, '', '', $start, $count);

            if (!$response) {
                throw new Exception (\local_xray\api\xrayws::instance()->geterrormsg());
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
                $return ["data"] = $data;
            }
        } catch (Exception $e) {
            // Error, return invalid data, and pluginjs will show error in table.
            $return["data"] = "-";
        }

        return json_encode($return);
    }

    /**
     * Report Barplot of Suggested Grades
     * @param object $element
     * @return string
     */
    private function barplot_of_suggested_grades($element) {
        $output = "";
        $output .= $this->output->discussiongrading_barplot_of_suggested_grades($element);
        return $output;
    }

}
