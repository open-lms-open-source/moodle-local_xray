<?php
defined('MOODLE_INTERNAL') or die();
require_once($CFG->dirroot.'/local/xray/controller/reports.php');

/**
 * Risk report
 *
 * @author Pablo Pagnone
 * @package local_xray
 */
class local_xray_controller_risk extends local_xray_controller_reports {
 
    public function init() {
        // This report will get data by courseid.
        $this->courseid = required_param('courseid', PARAM_STRINGID);
    }
    
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
            $response = \local_xray\api\wsapi::course(parent::XRAY_COURSEID, $report);
            if(!$response) {
                // Fail response of webservice.
                \local_xray\api\xrayws::instance()->print_error();

            } else {
                // Show graphs.
            	$output .= $this->output->inforeport($response->reportdate,
            			                             null,
            			                             $DB->get_field('course', 'fullname', array("id" => $this->courseid)));
                $output .= $this->risk_measures();
                $output .= $this->total_risk_profile($response->elements[1]);
                $output .= $this->academic_vs_social_risk($response->elements[2]);		    	
            }
        } catch(exception $e) {
            print_error('error_xray', $this->component,'',null, $e->getMessage());
        }
        
        return $output;
    }

    /**
     * Report Risk measures.(TABLE)
     */
    private function risk_measures() {
    
        $output = "";
        $output .= $this->output->risk_risk_measures($this->courseid);
        return $output;
    }
    
    public function jsonriskmeasures_action(){

        global $PAGE;
         
        // Pager
        $count = optional_param('iDisplayLength', 10, PARAM_RAW);
        $start  = optional_param('iDisplayStart', 0, PARAM_RAW);
         
        $return = "";
        
        try {
            $report = "risk";
            $element = "riskMeasures";
            $response = \local_xray\api\wsapi::courseelement(parent::XRAY_COURSEID,
                                                            $element,
                                                            $report,
                                                            null,
                                                            '',
                                                            '',
                                                            $start,
                                                            $count);
            if(!$response) {
                // Fail response of webservice.
                \local_xray\api\xrayws::instance()->print_error();
            } else {
 
                $data = array();
                if(!empty($response->data)){
                    foreach($response->data as $row) {

                        $r = new stdClass();					
                        $r->lastname = (isset($row->lastname->value) ? $row->lastname->value : '');    						
                        $r->firstname = (isset($row->firstname->value) ? $row->firstname->value : '');
                        $r->timespentincourse = (isset($row->timeOnTask->value) ? $row->timeOnTask->value : '');
                        $r->academicrisk = (isset($row->fail->value) ? $row->fail->value : '');
                        $r->socialrisk = (isset($row->DW->value) ? $row->DW->value : '');
                        $r->totalrisk = (isset($row->DWF->value) ? $row->DWF->value : '');
                        $data[] = $r;
                    }
                }
                // Provide count info to table.
                $return["recordsFiltered"] = $response->itemCount;
                $return["data"] = $data;

            }
        } catch(exception $e) {
            // Error, return invalid data, and pluginjs will show error in table.
            $return["data"] = "-";
        }

        echo json_encode($return);
        exit();
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
