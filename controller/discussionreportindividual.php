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
     * Course id
     */
    private $xraycourseid;
    
    /**
     * User id
     * @var unknown
     */
    private $xrayuserid;

    public function init() {
        parent::init();
        $this->xraycourseid = required_param('xraycourseid', PARAM_RAW);
        $this->xrayuserid = required_param('xrayuserid', PARAM_RAW);	
    }

    public function view_action() {

        global $PAGE, $USER, $DB;

        // Add title to breadcrumb.
        $title = get_string($this->name, $this->component);
        $PAGE->set_title($title);
        // Add nav to return to discussionreport.
        $PAGE->navbar->add(get_string("discussionreport", $this->component), new moodle_url('/local/xray/view.php', array("controller" => "discussionreport")));
        $PAGE->navbar->add($title);
        $output = "";

        try {
            $report = "discussion";
            $response = \local_xray\api\wsapi::course($this->xraycourseid, $report, $this->xrayuserid);
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
