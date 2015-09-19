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

        global $PAGE, $DB;

        $title = get_string($this->name, $this->component);
        $PAGE->set_title($title);
        $this->heading->text = $title;

        // Add title to breadcrumb.	
        $PAGE->navbar->add($title);
        $output = "";

        try {
            $report = "risk";
            $response = \local_xray\api\wsapi::course($this->courseid, $report);
            if (!$response) {
                // Fail response of webservice.
                \local_xray\api\xrayws::instance()->print_error();

            } else {
                // Show graphs.
                $output .= $this->output->inforeport($response->reportdate,
                    null,
                    $DB->get_field('course', 'fullname', array("id" => $this->courseid)));
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
     */
    private function risk_measures($element) {

        $output = "";
        $output .= $this->output->risk_risk_measures($this->courseid, $element);
        return $output;
    }

    public function jsonriskmeasures_action() {

        global $PAGE;

        // Pager
        $count = optional_param('iDisplayLength', 10, PARAM_ALPHANUM);
        $start = optional_param('iDisplayStart', 0, PARAM_ALPHANUM);

        $return = "";

        // This renders the page correctly using standard Moodle ajax renderer
        $this->setajaxoutput();

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
