<?php
defined ( 'MOODLE_INTERNAL' ) or die ();
require_once ($CFG->dirroot . '/local/xray/controller/reports.php');

/**
 * Discussion Grading
 *
 * @author Pablo Pagnone
 * @package local_xray
 */
class local_xray_controller_discussiongrading extends local_xray_controller_reports {
	
	public function init() {
		parent::init ();
		// This report will get data by courseid.
		$this->courseid = required_param ( 'courseid', PARAM_RAW );
	}
	
	public function view_action() {
		global $PAGE;
		$title = get_string ( $this->name, $this->component );
		$PAGE->set_title ( $title );
		$this->heading->text = $title;
		
		// Add title to breadcrumb.
		$PAGE->navbar->add ( get_string ( $this->name, $this->component ) );
		$output = "";
		
		try {
			$report = "discussionGrading";
			$response = \local_xray\api\wsapi::course ( parent::XRAY_COURSEID, $report );
			if (! $response) {
				// Fail response of webservice.
				throw new Exception ( \local_xray\api\xrayws::instance ()->geterrormsg () );
			} else {
				
				// Show graphs.
				$output .= $this->students_grades_based_on_discussions (); // Its a table, I will get info with new call.
				$output .= $this->barplot_of_suggested_grades ( $response->elements [1] );
			}
		} catch ( exception $e ) {
			print_error ( 'error_xray', $this->component, '', null, $e->getMessage () );
		}
		
		return $output;
	}
	
	/**
	 * Report Student Grades Based on Discussions(table)
	 */
	private function students_grades_based_on_discussions() {
		$output = "";
		$output .= $this->output->discussiongrading_students_grades_based_on_discussions ( $this->courseid );
		return $output;
	}
	
	/**
	 * Json for provide data to students_grades_based_on_discussions table.
	 */
	public function jsonstudentsgrades_action() {
		global $PAGE;
		// Pager
		$count = optional_param ( 'iDisplayLength', 10, PARAM_RAW );
		$start = optional_param ( 'iDisplayStart', 0, PARAM_RAW );
		
		$return = "";
		
		try {
			$report = "discussionGrading";
			$element = "element2";
			$response = \local_xray\api\wsapi::courseelement ( parent::XRAY_COURSEID, $element, $report, null, '', '', $start, $count );
			
			if (! $response) {
				throw new Exception ( \local_xray\api\xrayws::instance ()->geterrormsg () );
			} else {
				
				$data = array ();
				if (! empty ( $response->data )) {
					foreach ( $response->data as $row ) {
						
						$r = new stdClass ();
						$r->lastname = $row->lastname->value;
						$r->firstname = $row->firstname->value;
						$r->numposts = $row->posts->value;
						$r->wordcount = $row->wc->value;
						$r->regularity_contributions = 0; // $row->regularityContrib->value;
						                                  // TODO: Bug in webservice, sometimes not return value property.
						$r->critical_thinking_coefficient = $row->ctc->value;
						$r->grade = $row->letterGrade->value;
						$data [] = $r;
					}
				}
				// Provide count info to table.
				$return ["recordsFiltered"] = $response->itemCount;
				$return ["data"] = $data;
			}
		} catch ( exception $e ) {
			// TODO:: Send message error to js.
			$return = "";
		}
		
		echo json_encode ( $return );
		exit ();
	}
	
	/**
	 * Report Barplot of Suggested Grades
	 */
	private function barplot_of_suggested_grades($element) {
		$output = "";
		$output .= $this->output->discussiongrading_barplot_of_suggested_grades ( $element );
		return $output;
	}
}
