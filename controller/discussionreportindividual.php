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
