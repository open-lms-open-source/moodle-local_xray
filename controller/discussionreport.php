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
class local_xray_controller_discussionreport extends local_xray_controller_reports {

    /**
     * Require capabilities
     */
    public function require_capability() {
    	// TODO: To determinate.
    }
    
    public function view_action() {
    	
    	global $PAGE;
    	// Add title to breadcrumb.
    	$PAGE->navbar->add(get_string('discussionreport', 'local_xray'));
    	$output = "";

    	try {
    		$report = "discussion";
    		$response = \local_xray\api\wsapi::course(parent::XRAY_COURSEID, $report);
    		if(!$response) {
    			// Fail response of webservice.
    			throw new Exception(\local_xray\api\xrayws::instance()->geterrormsg());
    			
    		} else {

    			// Show graphs.
    			$output .= $this->participation_metrics(); // Its a table, I will get info with new call.
    			$output .= $this->average_words_weekly_by_post($response->elements[5]);
    			$output .= $this->social_structure($response->elements[9]);
    			$output .= $this->social_structure_with_words_count($response->elements[10]);
    			$output .= $this->social_structure_with_contributions_adjusted($response->elements[11]);
    			$output .= $this->social_structure_coefficient_of_critical_thinking($response->elements[12]);
    			//$output .= $this->first_login_non_starters();
    			//$output .= $this->first_login_to_course();
    			//$output .= $this->first_login_date_observed();
		    	
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
        $output .= $this->output->discussionreport_participation_metrics();
        return $output;
    }   
    
    /**
     * Json for provide data to participation_metrics table.
     */
    public function jsonparticipationdiscussion_action() {
        
        // TODO:: Review , implement search, sortable, pagination.
        $return = array();
        try {
            // Pager
            $count = optional_param('iDisplayLength', 10, PARAM_RAW);
            $start  = optional_param('iDisplayStart', 10, PARAM_RAW);
            
            $report = "discussion";
            $element = "element2";
            
            $response = \local_xray\api\wsapi::courseelement(parent::XRAY_DOMAIN, // TODO:: Hardcoded.
                    parent::XRAY_COURSEID, // TODO:: Hardcoded.
                    $element,
                    $report,
                    null,
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
                    //$activityreportind = get_string('activityreportindividual', $this->component);//TODO
                    $activityreportind = 'algo';
                    
                    foreach($response->data as $row) {
                
                        // Url for activityreportindividual.
                        $url = new moodle_url("/local/xray/view.php",
                                array("controller" => "activityreportindividual",
                                        "xraycourseid" => $row->courseId->value,
                                        "xrayuserid" => $row->participantId->value
                                ));
                
                        $r = new stdClass();
                        /*$r->action = html_writer::link($url, '', array("class" => "icon_activityreportindividual",//TODO
                                "title" => $activityreportind,
                                "target" => "_blank"));*/
                        $r->firstname = $row->firstname->value;
                        $r->lastname = $row->lastname->value;
                        /*$r->posts = $row->posts->value;
                        $r->contribution = $row->contribution->value;
                        $r->ctc = $row->ctc->value;
                        $r->regularityofcontributions = $row->regularityofcontributions->value;
                        $r->regularityofctc = $row->regularityofctc->value;*/
                        $data[] = $r;
                    }
                }
                // Provide info to table.
                $return["recordsFiltered"] = 100; // TODO:: Get from webservice.
                $return["data"] = $data;

                
            }
        } catch(exception $e) {
            // TODO:: Send message error to js.
            $return = "";
        }
        
        echo json_encode($return);
        exit();
    }
    
    /**
     * Report Average Words Weekly by Post.
     *
     */
    private function average_words_weekly_by_post($element) {
        
        $output = "";
        $output .= $this->output->discussionreport_average_words_weekly_by_post($element);
        return $output; 
    }
    
    /**
     * Report Social Structure.
     *
     */    
    private function social_structure($element) {

    	$output = "";
    	$output .= $this->output->discussionreport_social_structure($element);
    	return $output; 
    }
    
    /**
     * Report Social Structure With Words Count.
     * @param unknown $element
     */
    private function social_structure_with_words_count($element) {

    	$output = "";
    	$output .= $this->output->discussionreport_social_structure_with_words_count($element);
    	return $output;
    }
    
    /**
     * Report Social Structure With Contributions Adjusted
     */
    private function social_structure_with_contributions_adjusted($element) {
    
    	$output = "";
    	$output .= $this->output->discussionreport_social_structure_with_contributions_adjusted($element);
    	return $output;
    }   
    
    /**
     * Report Social Structure Coefficient of Critical Thinking
     */
    private function social_structure_coefficient_of_critical_thinking($element) {
    
    	$output = "";
    	$output .= $this->output->discussionreport_social_structure_coefficient_of_critical_thinking($element);
    	return $output;
    }   
}
