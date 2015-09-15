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
            $report = "discussion";
            $response = \local_xray\api\wsapi::course($this->courseid, $report);
            if(!$response) {
                // Fail response of webservice.
                throw new Exception(\local_xray\api\xrayws::instance()->geterrormsg());
            } else {
                
                // Show graphs.
                $output .= $this->participation_metrics($response->elements[0]); // Its a table, I will get info with new call.
                $output .= $this->discussion_activity_by_week($response->elements[1]); // Table with variable columns - Send data to create columns
                $output .= $this->average_words_weekly_by_post($response->elements[3]);
                $output .= $this->social_structure($response->elements[7]);
                $output .= $this->social_structure_with_contributions_adjusted($response->elements[9]);
                $output .= $this->social_structure_coefficient_of_critical_thinking($response->elements[10]);
                $output .= $this->main_terms($response->elements[11]);
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
    private function participation_metrics($element) {
        $output = "";
        $output .= $this->output->discussionreport_participation_metrics($this->courseid, $element);
        return $output;
    }   
    
    /**
     * Report "Discussion Activity by Week" (table).
     *
     */
    private function discussion_activity_by_week($element) {
        $output = "";
        $output .= $this->output->discussionreport_discussion_activity_by_week($this->courseid, $element);
        return $output;
    }

    /**
     * Json for provide data to participation_metrics table.
     */
    public function jsonparticipationdiscussion_action() {

        global $PAGE;

        // Pager
        $count = optional_param('iDisplayLength', 10, PARAM_RAW);
        $start  = optional_param('iDisplayStart', 0, PARAM_RAW);
        
        $return = "";
        
        try {
            $report = "discussion";
            $element = "discussionMetrics";
            
            $response = \local_xray\api\wsapi::courseelement($this->courseid,
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
                    $discussionreportind = get_string('discussionreportindividual', $this->component);//TODO
                    
                    foreach($response->data as $row) {
                        
                        $r = new stdClass();
                        $r->action = "";
                        if(has_capability('local/xray:discussionreportindividual_view', $PAGE->context)) {
                            // Url for discussionreportindividual.
                            $url = new moodle_url("/local/xray/view.php",
                                    array("controller" => "discussionreportindividual",
                                          "courseid" => $this->courseid,
                                          "userid" => $row->participantId->value
                                    ));
                            $r->action = html_writer::link($url, '', array("class" => "icon_discussionreportindividual",
                                    "title" => $discussionreportind,
                                    "target" => "_blank"));
                        }
                        
                        
                        // Format of response for columns.
                        if(!empty($response->columnOrder)) {
                        	foreach($response->columnOrder as $column) {
                        		$r->{$column} = (isset($row->{$column}->value) ? $row->{$column}->value : '');
                        	}
                        	$data[] = $r;
                        }
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
     * Json for provide data to discussion_activity_by_week table.
     */
    public function jsonweekdiscussion_action() {
    
        global $PAGE;

        // Pager
        $count  = optional_param('count', 10, PARAM_RAW);//count param with number of weeks
        $start  = optional_param('iDisplayStart', 0, PARAM_RAW);

        $return = "";

        try {
            $report = "discussion";
            $element = "discussionActivityByWeek";

            $response = \local_xray\api\wsapi::courseelement($this->courseid,
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
                	// This report has not specified columnOrder.
                	if(!empty($response->columnHeaders) && is_object($response->columnHeaders)) {
                		$r = new stdClass();
                	
                		$posts = array('weeks' => $response->columnHeaders->posts);
                		$avglag = array('weeks' => $response->columnHeaders->avgLag);
                		$avgwordcount = array('weeks' => $response->columnHeaders->avgWordCount);
                		
                		foreach($response->data as $col) {
                			$posts[$col->week->value] = (isset($col->posts->value) ? $col->posts->value : '');
                			$avglag[$col->week->value] = (isset($col->avgLag->value) ? $col->avgLag->value : '');
                			$avgwordcount[$col->week->value] = (isset($col->avgWordCount->value) ? $col->avgWordCount->value : '');
                		}
                		$data[] = $posts;
                		$data[] = $avglag;
                		$data[] = $avgwordcount;
                		 
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
    
    /**
     * Report Main Terms 
     */
    private function main_terms($element) {
    
        $output = "";
        $output .= $this->output->discussionreport_main_terms($element);
        return $output;
    }
}
