<?php
defined('MOODLE_INTERNAL') or die();
require_once($CFG->dirroot . '/local/xray/controller/reports.php');

/**
 * Activity Report Individual
 *
 * @author Pablo Pagnone
 * @package local_xray
 */
class local_xray_controller_activityreportindividual extends local_xray_controller_reports {

    public function init() {
        parent::init();
        $this->userid = (int)required_param('userid', PARAM_ALPHANUM);
    }

    public function view_action() {

        global $PAGE, $USER, $DB;

        $title = get_string($this->name, $this->component);
        $PAGE->set_title($title);
        $this->heading->text = $title;

        // Add nav to return to activityreport.
        $PAGE->navbar->add(get_string("activityreport", $this->component),
            new moodle_url('/local/xray/view.php',
                array("controller" => "activityreport",
                    "courseid" => $this->courseid)));
        $PAGE->navbar->add($title);
        $output = "";

        try {
            $report = "activity";
            $response = \local_xray\api\wsapi::course($this->courseid, $report, $this->userid);
            if (!$response) {
                // Fail response of webservice.
                \local_xray\api\xrayws::instance()->print_error();

            } else {

                // Show graphs.
                $output .= $this->output->inforeport($response->reportdate,
                    $DB->get_field('user', 'username', array("id" => $this->userid)),
                    $DB->get_field('course', 'fullname', array("id" => $this->courseid)));
                $output .= $this->activity_by_date($response->elements->activityLevelTimeline);
                $output .= $this->activity_last_two_weeks($response->elements->barplotOfActivityWholeWeek);
                $output .= $this->activity_last_two_weeks_byweekday($response->elements->barplotOfActivityByWeekday);

            }
        } catch (Exception $e) {
            print_error('error_xray', $this->component, '', null, $e->getMessage());
        }

        return $output;
    }

    /**
     * Report Students activity (table).
     *
     */
    private function activity_by_date($element) {

        $output = "";
        $output .= $this->output->activityreportindividual_activity_by_date($element);
        return $output;
    }

    /**
     * Report Activity of course by day.
     *
     */
    private function activity_last_two_weeks($element) {

        $output = "";
        $output .= $this->output->activityreportindividual_activity_last_two_weeks($element);
        return $output;
    }

    /**
     * Report Activity by time of day.
     *
     */
    private function activity_last_two_weeks_byweekday($element) {

        $output = "";
        $output .= $this->output->activityreportindividual_activity_last_two_weeks_byday($element);
        return $output;
    }
}
