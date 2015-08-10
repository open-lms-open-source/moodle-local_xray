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
class local_xray_controller_gradebookreport extends local_xray_controller_reports {

    public function init() {
        parent::init();
        // This report will get data by courseid.
        $this->courseid = required_param('courseid', PARAM_RAW);	
    }

    public function view_action() {
        
        global $PAGE;
        $title = get_string($this->name, $this->component);
        $PAGE->set_title($title);
        $this->heading->text = $title;
        
        // Add title to breadcrumb.
        $PAGE->navbar->add(get_string($this->name, $this->component));
        $output = "";

        try {
            $report = "grades";
            $response = \local_xray\api\wsapi::course(parent::XRAY_COURSEID_HISTORY_II, $report);//TODO we use other course id to test this
            
            if(!$response) {
                // Fail response of webservice.
                throw new Exception(\local_xray\api\xrayws::instance()->geterrormsg());
            } else {
                
                // Show graphs.
                $output .= $this->distribution_of_grades_in_course($response->elements[1]);
                $output .= $this->distribution_of_grades_completed_items($response->elements[4]);
            }
        } catch(exception $e) {
            print_error('error_xray', $this->component,'',null, $e->getMessage());
        }
        
        return $output;
    }

    /**
     * Report Distribution of grades in course.
     *
     */
    private function distribution_of_grades_in_course($element) {
        
        $output = "";
        $output .= $this->output->gradebookreport_distribution_of_grades_in_course($element);
        return $output; 
    }

    /**
     * Report Distribution of grades completed items.
     *
     */
    private function distribution_of_grades_completed_items($element) {
        
        $output = "";
        $output .= $this->output->gradebookreport_distribution_of_grades_completed_items($element);
        return $output; 
    }
}
