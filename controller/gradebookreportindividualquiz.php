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
     * Quiz id
     * @var integer
     */
    private $quizid;
    
    /**
     * Course module id.
     * @var integer
     */
    private $id;
    
    public function init() {
        parent::init();
        $this->cmid = (int)required_param('cmid', PARAM_ALPHANUM); // Cmid of quiz.
        $this->quizid = (int)required_param('quiz', PARAM_ALPHANUM);
    }
    
    public function view_action() {
        global $PAGE, $DB;
        
        // Add title to breadcrumb.
        $quizname = $DB->get_field('quiz', 'name', array("id" => $this->quizid));
        $PAGE->navbar->add($quizname, new moodle_url("/mod/quiz/view.php", 
                                                  array("id" => $this->cmid)));
        $PAGE->navbar->add($PAGE->title);

        $output = "";
        
        try {
            $report = "grades";
            //TODO:: Temp Hardcoded id.
            $response = \local_xray\api\wsapi::course($this->courseid, $report, "quiz/".$this->quizid);
            if(!$response) {
                // Fail response of webservice.
                throw new Exception(\local_xray\api\xrayws::instance()->geterrormsg());
            } else {
                // Show graphs.
            }
        } catch(Exception $e) {
            print_error('error_xray', $this->component,'',null, $e->getMessage());
        }
        
        return $output;
    }
}
