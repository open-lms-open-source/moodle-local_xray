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
                $output .= $this->students_grades_for_course();// Its a table, I will get info with new call.
                $output .= $this->students_grades_on_completed_items_course();// Its a table, I will get info with new call.
                $output .= $this->distribution_of_grades_in_course($response->elements[2]);
                $output .= $this->distribution_of_grades_completed_items($response->elements[3]);
                $output .= $this->density_plot_all_items($response->elements[5]);
                $output .= $this->density_plot_completed_items($response->elements[6]);
            }
        } catch(exception $e) {
            print_error('error_xray', $this->component,'',null, $e->getMessage());
        }
        
        return $output;
    }

    /**
    * Report Students' Grades for course (table).
    *
    */
    private function students_grades_for_course() {
    
        $output = "";
        $output .= $this->output->gradebookreport_students_grades_for_course($this->courseid);
        return $output;
    }
    
    /**
     * Json for provide data to students_grades_for_course table.
     */
    public function jsonstudentsgradesforcourse_action() {
         
        global $PAGE;
        // Pager
        $count = optional_param('iDisplayLength', 10, PARAM_RAW);
        $start  = optional_param('iDisplayStart', 0, PARAM_RAW);
    
        $return = "";
    
        try {
            $report = "grades";
            $element = "grades1";
            $response = \local_xray\api\wsapi::courseelement(parent::XRAY_COURSEID_HISTORY_II,
                    $element,
                    $report,
                    null,
                    '',
                    '',
                    $start,
                    $count);
            
            if(!$response) {
                throw new Exception(\local_xray\api\xrayws::instance()->geterrormsg());
            } else {
                $data = array();
                if(!empty($response->data)){
                    foreach($response->data as $row) {
                        $r = new stdClass();
                        $r->lastname = $row->lastname->value;
                        $r->firstname = $row->firstname->value;
                        $r->grade = $row->letterGradeCourse->value;
                        $r->percentage  = $row->course_grade_percent->value;
                        $data[] = $r;
                    }
                }
                // Provide count info to table.
                $return["recordsFiltered"] = $response->itemCount;
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
     * Report Students' Grades on completed items course (table).
     *
     */
    private function students_grades_on_completed_items_course() {
    
        $output = "";
        $output .= $this->output->gradebookreport_students_grades_on_completed_items_course($this->courseid);
        return $output;
    }
    
    /**
     * Json for provide data to Students' Grades on completed items course table.
     */
    public function jsonstudentsgradesoncompleteditemscourse_action() {
         
        global $PAGE;
        // Pager
        $count = optional_param('iDisplayLength', 10, PARAM_RAW);
        $start  = optional_param('iDisplayStart', 0, PARAM_RAW);
    
        $return = "";
    
        try {
            $report = "grades";
            $element = "grades2";
            $response = \local_xray\api\wsapi::courseelement(parent::XRAY_COURSEID_HISTORY_II,
                    $element,
                    $report,
                    null,
                    '',
                    '',
                    $start,
                    $count);
    
            if(!$response) {
                throw new Exception(\local_xray\api\xrayws::instance()->geterrormsg());
            } else {
                $data = array();
                if(!empty($response->data)){
                    foreach($response->data as $row) {
                        $r = new stdClass();
                        $r->lastname = $row->lastname->value;
                        $r->firstname = $row->firstname->value;
                        /*$r->completed = $row->letterGradeCourse->value;
                        $r->percentage  = $row->course_grade_percent->value;
                        $r->grade  = $row->course_grade_percent->value;*/
                        $data[] = $r;
                    }
                }
                // Provide count info to table.
                $return["recordsFiltered"] = $response->itemCount;
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
    
    /**
     * Report Density plot: all items.
     *
     */
    private function density_plot_all_items($element) {
    
        $output = "";
        $output .= $this->output->gradebookreport_density_plot_all_items($element);
        return $output;
    }
    
    /**
     * Report Density plot: completed items.
     *
     */
    private function density_plot_completed_items($element) {
    
        $output = "";
        $output .= $this->output->gradebookreport_density_plot_completed_items($element);
        return $output;
    }
}
