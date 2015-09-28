<?php
defined('MOODLE_INTERNAL') or die();
require_once($CFG->dirroot . '/local/xray/controller/reports.php');

/**
 * Risk report
 *
 * @author Pablo Pagnone
 * @package local_xray
 */
class local_xray_controller_risk extends local_xray_controller_reports {

    public function view_action() {
        global $PAGE;

        $output = '';

        try {
            $report = "risk";
            $response = \local_xray\api\wsapi::course($this->courseid, $report);
            if (!$response) {
                // Fail response of webservice.
                \local_xray\api\xrayws::instance()->print_error();

            } else {
                // Show graphs.
                $output .= $this->output->inforeport($response->reportdate, null, $PAGE->course->fullname);
                $output .= $this->risk_measures($response->elements->riskMeasures); //TABLE.
                $output .= $this->total_risk_profile($response->elements->riskDensity);
                $output .= $this->academic_vs_social_risk($response->elements->riskScatterPlot);
            }
        } catch (Exception $e) {
            print_error('error_xray', $this->component, '', null, $e->getMessage());
        }

        return $output;
    }

    /**
     * Report Risk measures.(TABLE)
     * @param mixed $element
     * @return string
     */
    private function risk_measures($element) {

        $output = "";
        $output .= $this->output->risk_risk_measures($this->courseid, $element);
        return $output;
    }

    public function jsonriskmeasures_action() {

        // Pager
        $count = (int)optional_param('iDisplayLength', 10, PARAM_ALPHANUM);
        $start = (int)optional_param('iDisplayStart', 0, PARAM_ALPHANUM);

        $return = "";

        try {
            $report = "risk";
            $element = "riskMeasures";
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
     * Report total risk profile
     */
    private function total_risk_profile($element) {

        $output = "";
        $output .= $this->output->risk_total_risk_profile($element);
        return $output;
    }

    /**
     * Report total risk profile
     */
    private function academic_vs_social_risk($element) {

        $output = "";
        $output .= $this->output->risk_academic_vs_social_risk($element);
        return $output;
    }
}
