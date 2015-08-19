<?php
defined('MOODLE_INTERNAL') or die();
require_once($CFG->dirroot.'/local/xray/controller/reports.php');

/**
 * Xray integration Reports Controller
 *
 * @author Pablo Pagnone
 * @author German Vitale
 * @package local_xray
 */
class local_xray_controller_discussionreportindividual extends local_xray_controller_reports {

    public function init() {
        parent::init();
        //$this->courseid = required_param('courseid', PARAM_RAW);
        // TODO:: Hardcodeid by test.
        $this->courseid = required_param('xraycourseid', PARAM_RAW);
        $this->xrayuserid = required_param('xrayuserid', PARAM_RAW);
    }

    public function view_action() {

        global $PAGE, $USER, $DB;

        // Add title to breadcrumb.
        $title = get_string($this->name, $this->component);
        $PAGE->set_title($title);
        // Add nav to return to discussionreport.
        $PAGE->navbar->add(get_string("discussionreport", $this->component), new moodle_url('/local/xray/view.php', array("controller" => "discussionreport", "courseid" => $this->courseid)));
        $PAGE->navbar->add($title);
        $output = "";

        try {
            $report = "discussion";
            $response = \local_xray\api\wsapi::course(parent::XRAY_COURSEID, $report, $this->xrayuserid);
            if(!$response) {
                // Fail response of webservice.
                throw new Exception(\local_xray\api\xrayws::instance()->geterrormsg());
                
            } else {
                // Show graphs.
                $output .= $this->output->inforeport($response->reportdate,
                                                     $DB->get_field('user', 'username', array("id" => $this->xrayuserid)),
                                                     $DB->get_field('course', 'fullname', array("id" => $this->courseid)));
                $output .= $this->participation_metrics(); // Its a table, I will get info with new call.
                $output .= $this->social_structure($response->elements[2]);//TODO number elements are not the same of page
                $output .= $this->main_terms($response->elements[3]);//TODO number elements are not the same of page
                $output .= $this->main_terms_histogram($response->elements[4]);//TODO number elements are not the same of page
            }
        } catch(exception $e) {
            print_error('error_xray', 'local_xray','',null, $e->getMessage());
        }
        
        return $output;
    }
    
    /**
     * Report "A summary table to be added" (table).
     *
     */
    private function participation_metrics() {
        $output = "";
        $output .= $this->output->discussionreportindividual_participation_metrics($this->courseid, $this->xrayuserid);
        return $output;
    }
    
    /**
     * Json for provide data to participation_metrics table.
     */
    public function jsonparticipationdiscussionindividual_action() {
    
        global $PAGE;
    
        // Pager
        $count = optional_param('iDisplayLength', 10, PARAM_RAW);
        $start  = optional_param('iDisplayStart', 0, PARAM_RAW);
    
        $return = "";
    
        try {
            $report = "discussion";
            $element = "discussionMetrics";
    
            $response = \local_xray\api\wsapi::courseelement(parent::XRAY_COURSEID,
                    $element,
                    $report,
                    parent::XRAY_USERID,
                    '',
                    '',
                    $start,
                    $count);
             
            if(!$response) {
                // TODO:: Fail response of webservice.
                throw new Exception(\local_xray\api\xrayws::instance()->geterrormsg());
            } else {
    
                $data = array();
                if(!empty($response->data)){
                    $discussionreportind = get_string('discussionreportindividual', $this->component);//TODO
    
                    foreach($response->data as $row) {
    
                        $r = new stdClass();
                        $r->lastname = (isset($row->lastname->value) ? $row->lastname->value : '');
                        $r->firstname = (isset($row->firstname->value) ? $row->firstname->value : '');
                        $r->posts = (isset($row->posts->value) ? $row->posts->value : '');
                        $r->contribution = (isset($row->contrib->value) ? $row->contrib->value : '');
                        $r->ctc = (isset($row->ctc->value) ? $row->ctc->value : '');
                        $r->regularityofcontributions = (isset($row->regularityContrib->value) ? $row->regularityContrib->value : '');//TODO No value in this object, notify Shani - $row->regularityContrib->value
                        $r->regularityofctc = (isset($row->regularityCTC->value) ? $row->regularityCTC->value : '');//TODO No value in this object, notify Shani - $row->regularityCTC->value
                        $data[] = $r;
                    }
                }
    
                // Provide info to table.
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
