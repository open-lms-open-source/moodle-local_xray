<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
require_once($CFG->dirroot.'/local/xray/controller/reports.php');

/**
 * Report Discussion Individual forum.
 *
 * @author Pablo Pagnone
 * @package local_xray
 */
class local_xray_controller_discussionreportindividualforum extends local_xray_controller_reports {
		
	/**
	 * Forum id
	 * @var integer
	 */
	private $forumid;
	
	public function init() {
		parent::init();
		//$this->xraycourseid = required_param('xraycourseid', PARAM_RAW);
		$this->forumid = required_param('forum', PARAM_RAW);
		$this->xraycourseid = parent::XRAY_COURSEID;
		
	}
	
    public function view_action() {
    	
    	global $PAGE, $USER, $DB;
    	
    	// Add title to breadcrumb.
    	$title = get_string($this->name, $this->component);
    	$PAGE->set_title($title);   	
    	$PAGE->navbar->add($title);
    	$output = "";
    	
    	// TODO:: This report is returning bad. Check in x-ray side.

    	try {
    		$report = "discussionForum";
    		$response = \local_xray\api\wsapi::course($this->xraycourseid, $report);
    		if(!$response) {
    			// Fail response of webservice.
    			throw new Exception(\local_xray\api\xrayws::instance()->geterrormsg());
    			
    		} else {
    			// Show graphs.
    			$output .= $this->output->inforeport($response->reportdate, 
    					                             null,
    					                             $DB->get_field('course', 'fullname', array("id" => $this->xraycourseid)));
    			
    			$output .= $this->wordshistogram($response->elements[1]);
    			$output .= $this->socialstructure($response->elements[3]);
    			$output .= $this->wordcloud($response->elements[4]);
		    	
    		}		 
    	} catch(exception $e) {
    		print_error('error_xray', $this->component,'',null, $e->getMessage());
    	}
    	
    	return $output;
    }
    
    /**
     * Words Histogram
     *
     */
    private function wordshistogram($element) {
    
    	$output = "";
    	$output .= $this->output->discussionreportindividualforum_wordshistogram($element);
    	return $output;
    }   
    
    /**
     * Social Structure
     *
     */
    private function socialstructure($element) {
    	
    	$output = "";
    	$output .= $this->output->discussionreportindividualforum_socialstructure($element);
    	return $output; 
    }
    
    /**
     * Wordcloud
     *
     */    
    private function wordcloud($element) {

    	$output = "";
    	$output .= $this->output->discussionreportindividualforum_wordcloud($element);
    	return $output; 
    }
}
