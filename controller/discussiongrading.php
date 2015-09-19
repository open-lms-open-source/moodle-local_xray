<?php
defined('MOODLE_INTERNAL') or die ();
require_once($CFG->dirroot . '/local/xray/controller/reports.php');

/**
 * Discussion Grading
 *
 * @author Pablo Pagnone
 * @package local_xray
 */
class local_xray_controller_discussiongrading extends local_xray_controller_reports {

    public function view_action() {

        global $PAGE, $DB;
        $title = get_string($this->name, $this->component);
        $PAGE->set_title($title);
        $this->heading->text = $title;

        // Add title to breadcrumb.
        $PAGE->navbar->add(get_string($this->name, $this->component));
        $output = "";

        try {
            $report = "discussionGrading";
            $response = \local_xray\api\wsapi::course($this->courseid, $report);
            if (!$response) {
                // Fail response of webservice.
                \local_xray\api\xrayws::instance()->print_error();
            } else {

                // Show graphs.
                $output .= $this->output->inforeport($response->reportdate,
                    null,
                    $DB->get_field('course', 'fullname', array("id" => $this->courseid)));
                $output .= $this->students_grades_based_on_discussions($response->elements->studentDiscussionGrades); // Its a table, I will get info with new call.
                $output .= $this->barplot_of_suggested_grades($response->elements->discussionSuggestedGrades);
            }
        } catch (exception $e) {
            print_error('error_xray', $this->component, '', null, $e->getMessage());
        }

        return $output;
    }

    /**
     * Report Student Grades Based on Discussions(table)
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
        global $PAGE;
        // Pager
        $count = optional_param('iDisplayLength', 10, PARAM_ALPHANUM);
        $start = optional_param('iDisplayStart', 0, PARAM_ALPHANUM);

        $return = "";

        // This renders the page correctly using standard Moodle ajax renderer
        $this->setajaxoutput();

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
        } catch (exception $e) {
            // Error, return invalid data, and pluginjs will show error in table.
            $return["data"] = "-";
        }

        return json_encode($return);
    }

    /**
     * Report Barplot of Suggested Grades
     */
    private function barplot_of_suggested_grades($element) {
        $output = "";
        $output .= $this->output->discussiongrading_barplot_of_suggested_grades($element);
        return $output;
    }
}
