<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
require_once($CFG->dirroot.'/local/xray/controller/reports.php');

/**
 * Xray integration Reports Controller
 *
 * @author Pablo Pagnone
 * @author German Vitale
 * @package local_xray
 */
class local_xray_controller_discussionreportindividual extends local_xray_controller_reports {

    /**
     * Require capabilities
     */
    public function require_capability() {
    	// TODO: To determinate.
    }
    
    public function view_action() {
        
        global $PAGE;
        // Add title to breadcrumb.
        $PAGE->navbar->add(get_string($this->name, $this->component));
        $output = "";

        try {
            $report = "discussion";
            $response = \local_xray\api\wsapi::course(parent::XRAY_DOMAIN, parent::XRAY_COURSEID, $report, parent::XRAY_USERID);
            if(!$response) {
                // Fail response of webservice.
                throw new Exception(\local_xray\api\xrayws::instance()->geterrormsg());
                
            } else {
                
                // Show graphs.
                //$output .= $this->participation_metrics(); // Its a table, I will get info with new call.
                $output .= $this->social_structure($response->elements[0]);//TODO number elements are not the same of page
                $output .= $this->main_terms($response->elements[1]);//TODO number elements are not the same of page
                $output .= $this->main_terms_histogram($response->elements[2]);//TODO number elements are not the same of page
        
            }		 
        } catch(exception $e) {
            print_error('error_xray', 'local_xray','',null, $e->getMessage());
        }
        
        return $output;
    }
    
    /**
     * Report "A summary table to be added" (table).
     *
     *//*
    private function participation_metrics() {
    
        $output = "";
        $output .= $this->output->discussionreport_participation_metrics();
        return $output;
    }   */
    
    /**
     * Json for provide data to participation_metrics table.
     *//*
    public function jsonparticipationdiscussion_action() {
        
        // TODO:: Review , implement search, sortable, pagination.
        $return = array();
        try {
            $report = "discussion";
            $element = "element2";//"element1";
            $response = \local_xray\api\wsapi::course(parent::XRAY_DOMAIN, parent::XRAY_COURSEID, $report);
            if(!$response) {
                // TODO:: Fail response of webservice.
                throw new Exception(\local_xray\api\xrayws::instance()->geterrormsg());
                 
            } else {
                if(!empty($response->elements[0]->data)){
                    foreach($response->elements[0]->data as $row) {
                        $r = new stdClass();
                        $r->firstname = $row->firstname->value;
                        $r->lastname = $row->lastname->value;
                        $r->posts = $row->posts->value;
                        $r->contribution = $row->contribution->value;
                        $r->ctc = $row->ctc->value;
                        $r->regularityofcontributions = $row->regularityofcontributions->value;
                        $r->regularityofctc = $row->regularityofctc->value;
                        $return[] = $r;
                    }
                }
                
            }
        } catch(exception $e) {
            // TODO:: Send message error to js.
            $return = "";
        }
        
        echo json_encode($return);
        exit();
    }*/
    
    
    /**
     * Report Social Structure.
     *
     */    
    private function social_structure($element) {

    	$output = "";
    	$output .= $this->output->discussionreportindividual_social_structure($element);
    	return $output; 
    }
    
    /**
     * Report Main Terms.
     * @param unknown $element
     */
    private function main_terms($element) {

    	$output = "";
    	$output .= $this->output->discussionreportindividual_main_terms($element);
    	return $output;
    }
    
    /**
     * Report Main Terms Histogram.
     */
    private function main_terms_histogram($element) {
    
    	$output = "";
    	$output .= $this->output->discussionreportindividual_main_terms_histogram($element);
    	return $output;
    }   
 
}
