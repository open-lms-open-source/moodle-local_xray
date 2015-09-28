<?php
defined('MOODLE_INTERNAL') or die();
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
            $response = \local_xray\api\wsapi::course($this->courseid, $report);//TODO we use other course id to test this

            if (!$response) {
                // Fail response of webservice.
                throw new Exception(\local_xray\api\xrayws::instance()->geterrormsg());
            } else {

                //var_dump($response->elements->element2);
                $output .= $this->student_grades($response->elements->element2);// Its a table, I will get info with new call.
                $output .= $this->density_of_standardized_scores($response->elements->element3);
                $output .= $this->summary_of_quizzes($response->elements->element4);// Its a table, I will get info with new call.
                $output .= $this->boxplot_of_standardized_scores_per_quiz($response->elements->element5);
                $output .= $this->scores_assigned_by_xray_versus_results_from_quizzes($response->elements->element6);
                $output .= $this->comparison_of_scores_in_quizzes($response->elements->element7);

                // Show graphs.
                //$output .= $this->students_grades_for_course($response->elements->element0);// Its a table, I will get info with new call.
                //$output .= $this->students_grades_on_completed_items_course($response->elements[1]);// Its a table, I will get info with new call.
                //$output .= $this->distribution_of_grades_in_course($response->elements[2]);
                //$output .= $this->distribution_of_grades_completed_items($response->elements[3]);
                //$output .= $this->density_plot_all_items($response->elements[5]);
                //$output .= $this->density_plot_completed_items($response->elements[6]);
                
                
                //$output .= $this->test_for_normality_on_course_grades($response->elements[7]);
                //$output .= $this->test_for_normality_on_course_grades($response->elements[8]);//TODO repeated - waiting instructions
                
                
                //$output .= $this->heat_map_of_grade_distribution($response->elements[9]);
            }
        } catch (Exception $e) {
            print_error('error_xray', $this->component, '', null, $e->getMessage());
        }

        return $output;
    }

    /* New tables */ 
    
    /**
     * Report Students' Grades for course (table).
     *
     */
    private function student_grades($element) {
    
        $output = "";
        $output .= $this->output->gradebookreport_student_grades($this->courseid, $element);
        return $output;
    }
    
    /**
     * Json for provide data to students_grades_for_course table.
     */
    public function jsonstudentgrades_action() {
        // Pager
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
     *
     */
    private function summary_of_quizzes($element) {
    
        $output = "";
        $output .= $this->output->gradebookreport_summary_of_quizzes($this->courseid, $element);
        return $output;
    }
    
    /**
     * Json for provide data to students_grades_for_course table.
     */
    public function jsonsummaryquizzes_action() {
        // Pager
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
                $return["data"] = $data;
    
            }
        } catch (Exception $e) {
            // Error, return invalid data, and pluginjs will show error in table.
            $return["data"] = "-";
        }
    
        return json_encode($return);
    }
    
    /* End of new tables */ 

    /**
     * Report Students' Grades for course (table).
     *
     */
    private function students_grades_for_course($element) {

        $output = "";
        $output .= $this->output->gradebookreport_students_grades_for_course($this->courseid, $element);
        return $output;
    }

    /**
     * Json for provide data to students_grades_for_course table.
     */
    public function jsonstudentsgradesforcourse_action() {
        // Pager
        $count = (int)optional_param('iDisplayLength', 10, PARAM_ALPHANUM);
        $start = (int)optional_param('iDisplayStart', 0, PARAM_ALPHANUM);

        $return = "";

        try {
            $report = "grades";
            $element = "grades1";
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
                $return["data"] = $data;

            }
        } catch (Exception $e) {
            // Error, return invalid data, and pluginjs will show error in table.
            $return["data"] = "-";
        }

        return json_encode($return);
    }


    /**
     * Report Students' Grades on completed items course (table).
     *
     */
    private function students_grades_on_completed_items_course($element) {

        $output = "";
        $output .= $this->output->gradebookreport_students_grades_on_completed_items_course($this->courseid, $element);
        return $output;
    }

    /**
     * Json for provide data to Students' Grades on completed items course table.
     */
    public function jsonstudentsgradesoncompleteditemscourse_action() {
        // Pager
        $count = (int)optional_param('iDisplayLength', 10, PARAM_ALPHANUM);
        $start = (int)optional_param('iDisplayStart', 0, PARAM_ALPHANUM);

        $return = "";

        try {
            $report = "grades";
            $element = "grades2";
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
                $return["data"] = $data;

            }
        } catch (Exception $e) {
            // Error, return invalid data, and pluginjs will show error in table.
            $return["data"] = "-";
        }

        return json_encode($return);
    }

    /*New reports*/
    /**
     * Report Density of Standardized Scores.
     *
     */
    private function density_of_standardized_scores($element) {
    
        $output = "";
        $output .= $this->output->gradebookreport_density_of_standardized_scores($element);
        return $output;
    }
    
    /**
     * Report Boxplot of Standardized Scores per Quiz.
     *
     */
    private function boxplot_of_standardized_scores_per_quiz($element) {
    
        $output = "";
        $output .= $this->output->gradebookreport_boxplot_of_standardized_scores_per_quiz($element);
        return $output;
    }
    
    /**
     * Report Distribution of grades in course.
     *
     */
    private function scores_assigned_by_xray_versus_results_from_quizzes($element) {
    
        $output = "";
        $output .= $this->output->gradebookreport_scores_assigned_by_xray_versus_results_from_quizzes($element);
        return $output;
    }
    
    /**
     * Report Distribution of grades in course.
     *
     */
    private function comparison_of_scores_in_quizzes($element) {
    
        $output = "";
        $output .= $this->output->gradebookreport_comparison_of_scores_in_quizzes($element);
        return $output;
    }
    
    /*End of new reports */

    /**
     * Report Distribution of grades in course.
     *
     */
    private function distribution_of_grades_in_course($element) {

        $output = "";
        $output .= $this->output->gradebookreport_distribution_of_grades_in_course($element);
        return $output;
    }

    /**
     * Report Distribution of grades completed items.
     *
     */
    private function distribution_of_grades_completed_items($element) {

        $output = "";
        $output .= $this->output->gradebookreport_distribution_of_grades_completed_items($element);
        return $output;
    }

    /**
     * Report Density plot: all items.
     *
     */
    private function density_plot_all_items($element) {

        $output = "";
        $output .= $this->output->gradebookreport_density_plot_all_items($element);
        return $output;
    }

    /**
     * Report Density plot: completed items.
     *
     */
    private function density_plot_completed_items($element) {

        $output = "";
        $output .= $this->output->gradebookreport_density_plot_completed_items($element);
        return $output;
    }

    /**
     * Report Test for normality on course grades.
     *
     */
    private function test_for_normality_on_course_grades($element) {

        $output = "";
        $output .= $this->output->gradebookreport_test_for_normality_on_course_grades($element);
        return $output;
    }

    /**
     * Report Test for normality on course grades.
     *
     *//*
    private function test_for_normality_on_course_grades($element) {//TODO repeated - waiting instructions
    
        $output = "";
        $output .= $this->output->gradebookreport_test_for_normality_on_course_grades($element);
        return $output;
    }*/

    /**
     * Report Heat map of grade distribution.
     *
     */
    private function heat_map_of_grade_distribution($element) {

        $output = "";
        $output .= $this->output->gradebookreport_heat_map_of_grade_distribution($element);
        return $output;
    }
}
