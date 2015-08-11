<?php
defined('MOODLE_INTERNAL') or die();
require_once($CFG->dirroot.'/local/xray/controller/reports.php');

/**
 * Report Gradebook Individual Quiz
 *
 * @author Pablo Pagnone
 * @package local_xray
 */
class local_xray_controller_gradebookreportindividualquiz extends local_xray_controller_reports {
		
	/**
	 * Forum id
	 * @var integer
	 */
	private $forumid;
	
	/**
	 * Course module id.
	 * @var integer
	 */
	private $id;
	
	public function init() {
		parent::init();
		global $DB;
		$this->courseid = required_param('courseid', PARAM_STRINGID);
		$this->cmid = required_param('cmid', PARAM_RAW); // Cmid of forum.
		$this->quizid = required_param('forum', PARAM_RAW);
	}
	
    public function view_action() {
    	
    	global $PAGE, $USER, $DB;
    	
    	$title = get_string($this->name, $this->component);
    	$PAGE->set_title($title);
    	$this->heading->text = $title;
    	
    	// Add title to breadcrumb.
    	$quizname = $DB->get_field('quiz', 'name', array("id" => $this->quizid));
    	$PAGE->navbar->add($quizname, new moodle_url("/mod/quiz/view.php", 
    			                                      array("id" => $this->cmid))); 
    	
    	$PAGE->navbar->add($title);
    	$output = "";
    	
    	try {
    		$report = "grades";
    		//TODO:: Temp Hardcoded id.
    		$response = \local_xray\api\wsapi::course(parent::XRAY_COURSEID_HISTORY_II, $report, "forum/".parent::XRAY_QUIZID);
    		if(!$response) {
    			// Fail response of webservice.
    			throw new Exception(\local_xray\api\xrayws::instance()->geterrormsg());
    			
    		} else {
    			// Show graphs.
    			/*$output .= $this->output->inforeport($response->reportdate, 
    					                             null,
    					                             $DB->get_field('course', 'fullname', array("id" => $this->courseid)));
    			
    			$output .= $this->wordshistogram($response->elements[2]);
    			$output .= $this->socialstructure($response->elements[0]);
    			$output .= $this->wordcloud($response->elements[1]);*/
		    	
    		}		 
    	} catch(exception $e) {
    		print_error('error_xray', $this->component,'',null, $e->getMessage());
    	}
    	
    	return $output;
    }
    
    /**
     * Words Histogram
     *
     *//*
    private function wordshistogram($element) {
    
    	$output = "";
    	$output .= $this->output->discussionreportindividualforum_wordshistogram($element);
    	return $output;
    }   */
    
    /**
     * Social Structure
     *
     *//*
    private function socialstructure($element) {
    	
    	$output = "";
    	$output .= $this->output->discussionreportindividualforum_socialstructure($element);
    	return $output; 
    }*/
    
    /**
     * Wordcloud
     *
     *//*    
    private function wordcloud($element) {

    	$output = "";
    	$output .= $this->output->discussionreportindividualforum_wordcloud($element);
    	return $output; 
    }*/
}
