<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') or die();

/* @var object $CFG */
require_once($CFG->dirroot . '/local/xray/controller/reports.php');
require_once($CFG->dirroot . '/local/xray/classes/local_xray_datatables.php');

/**
 * Renderer
 *
 * @author Pablo Pagnone
 * @package local_xray
 */
class local_xray_renderer extends plugin_renderer_base {

    /************************** General elements for Reports **************************/

    /**
     * Show data about report
     * @param String $reportdate
     * @param String $user
     * @param String $course
     * @return string
     */
    public function inforeport($reportdate, $user = null, $course = null) {
        ($course); // Disable unused param warning.
        $date = new DateTime($reportdate);
        $mreportdate = userdate($date->getTimestamp(), get_string('strftimedatetimeshort', 'langconfig'));

        $output = "";
        $output .= html_writer::start_div('inforeport');
        $output .= html_writer::tag("p", get_string("reportdate", "local_xray") . ": " . $mreportdate);
        if (!empty($user)) {
            $output .= html_writer::tag("p", get_string(("username")) . ": " . $user);
        }
        $output .= html_writer::end_div();
        return $output;
    }

    /**
     * Generate img with lightbox.
     * General template for show graph with lightbox.
     *
     * Structure:
     * <div id=$name">
     * <h3 class='reportsname'>$name</h3>
     * <a><img class='xray_graph'></a>
     * <div class='xray_graph_legend'>legend element</div>
     * </div>
     *
     * Important: Link to image will have id fancybox + "name of report".
     *
     * @param string $name
     * @param object $element
     * @return string
     */
    private function show_on_lightbox($name, $element) {

        global $PAGE;
        $plugin = "local_xray";

        // Load Jquery.
        $PAGE->requires->jquery();
        $PAGE->requires->jquery_plugin('ui');
        $PAGE->requires->jquery_plugin('local_xray-show_on_lightbox', $plugin); // Js for show on lightbox.
        $PAGE->requires->jquery_plugin('local_xray-create_thumb', $plugin); // Js for dynamic thumbnails.

        $cfgxray = get_config('local_xray');
        $imgurl = sprintf('%s/%s/%s', $cfgxray->xrayurl, $cfgxray->xrayclientid, $element->uuid);

        // Access Token.
        $accesstoken = local_xray\api\wsapi::accesstoken();
        $imgurl = new moodle_url($imgurl, array('accesstoken' => $accesstoken));

        $output = "";
        $output .= html_writer::start_tag('div', array("id" => $name, "class" => "xray_element xray_element_graph"));

        /* Graph Name */
        $output .= html_writer::tag('h3', $element->title, array("class" => "reportsname"));
        /* End Graph Name */

        /* Img */
        $tooltip = '';
        if (isset($element->tooltip) && !empty($element->tooltip)) {
            $tooltip = $element->tooltip;
        }
        $output .= html_writer::start_tag('div', array("class" => "xray_element_img"));

        // Validate if url of image is valid. Prohibited to use @.
        if (fopen($imgurl, "r")) {
            $idimg = "fancybox_" . $name;
            $output .= html_writer::start_tag('a', array("id" => $idimg, "href" => $imgurl));
            $output .= html_writer::empty_tag('img', array("title" => $tooltip,
                "src" => $imgurl,
                "class" => "thumb") // Used by dynamic thumbnails.
            );

            $output .= html_writer::end_tag('a');
            /* End Img */

            // Send data to js.
            $PAGE->requires->js_init_call("local_xray_show_on_lightbox", array($idimg, $element));
        } else {
            // Incorrect url img. Show error message.
            $output .= html_writer::tag("div", get_string('error_loadimg', $plugin), array("class" => "error_loadmsg"));
        }

        $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('div');

        return $output;
    }

    /**
     * Standard table Theme with Jquery datatables.
     *
     * @param array $data - Array containing object DataTable.
     * @param string $classes - Classes for table.
     * @param string $width
     * @return string
     */
    private function standard_table($data, $classes = '', $width = '100%') {
        global $PAGE;
        // Load Jquery.
        $PAGE->requires->jquery();
        $PAGE->requires->jquery_plugin('ui');
        // Load specific js for tables.
        $PAGE->requires->jquery_plugin("local_xray-show_on_table", "local_xray");
        $output = "";
        $output .= html_writer::start_tag('div', array("id" => $data['id'], "class" => "xray_element xray_element_table"));
        $output .= html_writer::tag('h3', $data['title'], array("class" => "reportsname eventtoogletable"));
        // Table jquery datatables for show reports.
        $output .= "<table id='table_{$data['id']}' class='display {$classes}' cellspacing='0' width='{$width}'> <thead><tr>";
        foreach ($data['columns'] as $c) {
            $output .= "<th>" . $c->text . "</th>";
        }
        $output .= " </tr> </thead> </table>";
        $output .= html_writer::end_tag('div');

        // Load table with data.
        $PAGE->requires->js_init_call("local_xray_show_on_table", array($data));

        return $output;
    }
    /**
     * Show minutes in format hours:minutes
     * @param int $minutes
     * @return string
     */
    public function minutes_to_hours($minutes) {
        return date('H:i', mktime(0, $minutes));
    }
    /**
     * Show minutes in format hours:minutes
     * @param int $minutes
     * @return string
     */
    public function set_category($value) {
        
        $category = '';
        if ($value < 0.2){
            $category = get_string('low', 'local_xray');
        }elseif($value > 0.2 && $value < 0.3){
            $category = get_string('medium', 'local_xray');
        }else{
            $category = get_string('high', 'local_xray');
        }
        
        return html_writer::link('#', $category, array('title' => $value/*, 'class' => 'xray-tooltip'*/));//TODO class
    }
    /************************** End General elements for Reports **************************/

    /************************** Elements for Report Activity **************************/

    /**
     * Graphic students activity (TABLE)
     * @param int $courseid
     * @param object $element
     * @return string
     */
    public function activityreport_students_activity($courseid, $element) {

        $columns = array(new local_xray_datatableColumn('action', ''));
        if (!empty($element->columnOrder) && is_array($element->columnOrder)) {
            foreach ($element->columnOrder as $c) {
                $columns[] = new local_xray_datatableColumn($c, $element->columnHeaders->{$c});
            }
        }

        $datatable = new local_xray_datatable(__FUNCTION__,
            $element->title,
            "view.php?controller='activityreport'&action='jsonstudentsactivity'&courseid=" . $courseid,
            $columns);

        // Create standard table.
        $output = $this->standard_table((array)$datatable);

        return $output;
    }

    /**
     * Graphic activity of course by day.(Graph)
     * @param object $element
     * @return string
     */
    public function activityreport_activity_of_course_by_day($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }

    /**
     * Graphic activity by time of day.(Graph)
     * @param object $element
     * @return string
     */
    public function activityreport_activity_by_time_of_day($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }

    /**
     * Graphic activity last two weeks.(Graph)
     * @param object $element
     * @return string
     */
    public function activityreport_activity_last_two_weeks($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }

    /**
     * Graphic activity last two weeks BY weekday.(Graph)
     * @param object $element
     * @return string
     */
    public function activityreport_activity_last_two_weeks_by_weekday($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }

    /**
     * Graphic activity by participant 1
     * @param object $element
     * @return string
     */
    public function activityreport_activity_by_participant1($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }

    /**
     * Graphic activity by participant 2
     * @param object $element
     * @return string
     */
    public function activityreport_activity_by_participant2($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }

    /**
     * Graphic first login non startes (TABLE)
     * @param int $courseid
     * @param object $element
     * @return string
     */
    public function activityreport_first_login_non_starters($courseid, $element) {
        $columns = array();
        // This report has not specified columnOrder.
        if (!empty($element->columnHeaders) && is_object($element->columnHeaders)) {
            $c = get_object_vars($element->columnHeaders);
            foreach ($c as $id => $name) {
                $columns[] = new local_xray_datatableColumn($id, $name);
            }
        }

        $datatable = new local_xray_datatable(__FUNCTION__,
            $element->title,
            "view.php?controller='activityreport'&action='jsonfirstloginnonstarters'&courseid=" . $courseid,
            $columns);

        // Create standard table.
        $output = $this->standard_table((array)$datatable);
        return $output;
    }

    /**
     * Graphic frist login to course
     * @param object $element
     * @return string
     */
    public function activityreport_first_login_to_course($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }

    /**
     * Graphic first login date observed
     * @param object $element
     * @return string
     */
    public function activityreport_first_login_date_observed($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }
    /************************** End Elements for Report Activity **************************/

    /************************** Elements for Report Activity Individual **************************/

    /**
     * Graphic activity individual by date
     * @param stdClass $element
     * @return string
     */
    public function activityreportindividual_activity_by_date($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }

    /**
     * Graphic activity individual last two week
     * @param stdClass $element
     * @return string
     */
    public function activityreportindividual_activity_last_two_weeks($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }

    /**
     * Graphic activity individual last two week by weekday
     * @param stdClass $element
     * @return string
     */
    public function activityreportindividual_activity_last_two_weeks_byday($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }

    /************************** End Elements for Report Activity Individual **************************/

    /************************** Elements for Report Discussion **************************/

    /**
     * Graphic Participation Metrics (TABLE)
     * @param int $courseid
     * @param object $element
     * @return string
     */
    public function discussionreport_participation_metrics($courseid, $element) {
        $columns = array(new local_xray_datatableColumn('action', ''));
        if (!empty($element->columnOrder) && is_array($element->columnOrder)) {
            foreach ($element->columnOrder as $c) {
                $columns[] = new local_xray_datatableColumn($c, $element->columnHeaders->{$c});
            }
        }

        $datatable = new local_xray_datatable(__FUNCTION__,
            $element->title,
            "view.php?controller='discussionreport'&action='jsonparticipationdiscussion'&courseid=" . $courseid,
            $columns);

        // Create standard table.
        $output = $this->standard_table((array)$datatable);

        return $output;
    }

    /**
     * Graphic Discussion Activity by Week (TABLE)
     * @param int $courseid
     * @param stdClass $element
     * @return string
     */
    public function discussionreport_discussion_activity_by_week($courseid, $element) {
        // Create standard table.
        $columns = array();
        $columns[] = new local_xray_datatableColumn('weeks', get_string('weeks', 'local_xray'));
        foreach ($element->data as $column) {
            $columns[] = new local_xray_datatableColumn($column->week->value, $column->week->value);
        }

        $numberofweeks = count($columns) - 1; // Get number of weeks - we need to rest the "week" title column.

        $datatable = new local_xray_datatable(__FUNCTION__,
            $element->title,
            "view.php?controller='discussionreport'&action='jsonweekdiscussion'&courseid=" . $courseid . "&count=" . $numberofweeks,
            $columns,
            false,
            false, // We don't need pagination because we have only four rows.
            '<"xray_table_scrool"t>'); // Only the table.

        // Create standard table.
        $output = $this->standard_table((array)$datatable);

        return $output;
    }

    /**
     * Average words weekly by post. (Graph)
     * @param stdClass $element
     * @return string
     */
    public function discussionreport_average_words_weekly_by_post($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }

    /**
     * Social structure.(Graph)
     * @param stdClass $element
     * @return string
     */
    public function discussionreport_social_structure($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }

    /**
     * Social structure with contributions adjusted.(Graph)
     * @param stdClass $element
     * @return string
     */
    public function discussionreport_social_structure_with_contributions_adjusted($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }

    /**
     * Social structure coefficient of critical thinking
     * @param stdClass $element
     * @return string
     */
    public function discussionreport_social_structure_coefficient_of_critical_thinking($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }

    /**
     * Main Terms
     * @param stdClass $element
     * @return string
     */
    public function discussionreport_main_terms($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }

    /************************** End Elements for Report Discussion **************************/


    /************************** Elements for Report Discussion for an individual **************************/

    /**
     * Graphic Participation Metrics (TABLE)
     * @param int $courseid
     * @param object $element
     * @param int $userid
     * @return string
     */
    public function discussionreportindividual_participation_metrics($courseid, $element, $userid) {

        $columns = array();
        if (!empty($element->columnOrder) && is_array($element->columnOrder)) {
            foreach ($element->columnOrder as $c) {
                $columns[] = new local_xray_datatableColumn($c, $element->columnHeaders->{$c});
            }
        }

        $datatable = new local_xray_datatable(__FUNCTION__,
            $element->title,
            "view.php?controller='discussionreportindividual'&action='jsonparticipationdiscussionindividual'&courseid=" .
            $courseid . "&userid=" . $userid,
            $columns);

        // Create standard table.
        $output = $this->standard_table((array)$datatable);

        return $output;
    }

    /**
     * Graphic Discussion Activity by Week (TABLE)
     * @param int $courseid
     * @param int $userid
     * @param object $element
     * @return string
     */
    public function discussionreportindividual_discussion_activity_by_week($courseid, $userid, $element) {
        // Create standard table.
        $columns = array();
        $columns[] = new local_xray_datatableColumn('weeks', get_string('week', 'local_xray'));
        foreach ($element->data as $column) {
            $columns[] = new local_xray_datatableColumn($column->week->value, $column->week->value);
        }

        $numberofweeks = count($columns) - 1; // Get number of weeks - we need to rest the "week" title column.

        $datatable = new local_xray_datatable(__FUNCTION__,
            $element->title,
            "view.php?controller='discussionreportindividual'&action='jsonweekdiscussionindividual'&courseid=" .
            $courseid . "&userid=" . $userid . "&count=" . $numberofweeks,
            $columns,
            false,
            false, // We don't need pagination because we have only four rows.
            '<"xray_table_scrool"t>'); // Only the table.

        // Create standard table.
        $output = $this->standard_table((array)$datatable);

        return $output;
    }

    /**
     * Social structure.(Graph)
     * @param stdClass $element
     * @return string
     */
    public function discussionreportindividual_social_structure($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }

    /**
     * Social structure with word count.(Graph)
     * @param stdClass $element
     * @return string
     */
    public function discussionreport_social_structure_with_word_count($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }

    /**
     * Main terms.(Graph)
     * @param stdClass $element
     * @return string
     */
    public function discussionreportindividual_main_terms($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }

    /**
     * Main terms histogram.(Graph)
     * @param stdClass $element
     * @return string
     */
    public function discussionreportindividual_main_terms_histogram($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }

    /************************** End Elements for Report Discussion for an individual **************************/

    /************************** Elements for Report Discussion individual forum **************************/


    /**
     * Social structure.(Graph)
     * @param stdClass $element
     * @return string
     */
    public function discussionreportindividualforum_wordshistogram($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }

    /**
     * Main terms.(Graph)
     * @param stdClass $element
     * @return string
     */
    public function discussionreportindividualforum_socialstructure($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }

    /**
     * Main terms histogram.(Graph)
     * @param stdClass $element
     * @return string
     */
    public function discussionreportindividualforum_wordcloud($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }

    /************************** End Elements for Report Discussion Endogenic Plagiarism **************************/

    /**
     * Heatmap endogenic plagiarism student
     * @param stdClass $element
     * @return string
     */
    public function discussionendogenicplagiarism_heatmap_endogenic_plagiarism_students($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }

    /**
     * Heatmap endogenic plagiarism instructor
     * @param stdClass $element
     * @return string
     */
    public function discussionendogenicplagiarism_heatmap_endogenic_plagiarism_instructors($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }
    /************************** End Elements for Report Discussion Endogenic Plagiarism **************************/

    /************************** Elements for Report Risk **************************/

    /**
     * Graphic first login non startes (TABLE)
     * @param int $courseid
     * @param object $element
     * @return string
     */
    public function risk_first_login_non_starters($courseid, $element) {
        $columns = array();
        // This report has not specified columnOrder.
        if (!empty($element->columnHeaders) && is_object($element->columnHeaders)) {
            $c = get_object_vars($element->columnHeaders);
            foreach ($c as $id => $name) {
                $columns[] = new local_xray_datatableColumn($id, $name);
            }
        }

        $datatable = new local_xray_datatable(__FUNCTION__,
            $element->title,
            "view.php?controller='risk'&action='jsonfirstloginnonstarters'&courseid=" . $courseid,
            $columns);

        // Create standard table.
        $output = $this->standard_table((array)$datatable);
        return $output;
    }

    /**
     * Risk Measures(TABLE)
     * @param int $courseid
     * @param object $element
     * @return string
     */
    public function risk_risk_measures($courseid, $element) {
        $columns = array();
        if (!empty($element->columnOrder) && is_array($element->columnOrder)) {
            foreach ($element->columnOrder as $c) {
                $columns[] = new local_xray_datatableColumn($c, $element->columnHeaders->{$c});
            }
        }

        $datatable = new local_xray_datatable(__FUNCTION__,
            $element->title,
            "view.php?controller='risk'&action='jsonriskmeasures'&courseid=" . $courseid,
            $columns);

        // Create standard table.
        $output = $this->standard_table((array)$datatable);

        return $output;
    }

    /**
     * Total risk profile
     * @param stdClass $element
     * @return string
     */
    public function risk_total_risk_profile($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }

    /**
     * Academic vs social risk
     * @param stdClass $element
     * @return string
     */
    public function risk_academic_vs_social_risk($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }

    /************************** End Elements for Report Risk **************************/
    /**************************  Elements for Report Discussion grading **************************/

    /**
     * Discussion grading students grades (TABLE)
     * @param int $courseid
     * @param object $element
     * @return string
     */
    public function discussiongrading_students_grades_based_on_discussions($courseid, $element) {
        $columns = array();
        if (!empty($element->columnOrder) && is_array($element->columnOrder)) {
            foreach ($element->columnOrder as $c) {
                $columns[] = new local_xray_datatableColumn($c, $element->columnHeaders->{$c});
            }
        }
        $datatable = new local_xray_datatable(__FUNCTION__,
            $element->title,
            // INT-8194 , this report is moved to discussionreport.
            "view.php?controller='discussionreport'&action='jsonstudentsgrades'&courseid=" . $courseid,
            $columns);
        // Create standard table.
        $output = $this->standard_table((array)$datatable);

        return $output;
    }

    /**
     * Discussion grading barplot
     * @param stdClass $element
     * @return string
     */
    public function discussiongrading_barplot_of_suggested_grades($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }

    /************************** End Elements for Report Discussion grading **************************/
    /**************************  Elements for Gradebook Report **************************/

    /**
     * Students' Grades for course (TABLE)
     * @param int $courseid
     * @param object $element
     * @return string
     */
    public function gradebookreport_student_grades($courseid, $element) {

        $columns = array();
        if (!empty($element->columnOrder) && is_array($element->columnOrder)) {
            foreach ($element->columnOrder as $c) {
                $columns[] = new local_xray_datatableColumn($c, $element->columnHeaders->{$c});
            }
        }

        $datatable = new local_xray_datatable(__FUNCTION__,
            $element->title,
            "view.php?controller='gradebookreport'&action='jsonstudentgrades'&courseid=" . $courseid,
            $columns);

        // Create standard table.
        $output = $this->standard_table((array)$datatable);

        return $output;
    }

    /**
     * Summary of Quizzes (TABLE)
     * @param int $courseid
     * @param object $element
     * @return string
     */
    public function gradebookreport_summary_of_quizzes($courseid, $element) {

        $columns = array();
        if (!empty($element->columnOrder) && is_array($element->columnOrder)) {
            foreach ($element->columnOrder as $c) {
                $columns[] = new local_xray_datatableColumn($c, $element->columnHeaders->{$c});
            }
        }

        $datatable = new local_xray_datatable(__FUNCTION__,
            $element->title,
            "view.php?controller='gradebookreport'&action='jsonsummaryquizzes'&courseid=" . $courseid,
            $columns);

        // Create standard table.
        $output = $this->standard_table((array)$datatable);

        return $output;
    }

    /**
     * Heat map of grade distribution.
     * @param stdClass $element
     * @return string
     */
    public function gradebookreport_density_of_standardized_scores($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }

    /**
     * Heat map of grade distribution.
     * @param stdClass $element
     * @return string
     */
    public function gradebookreport_boxplot_of_standardized_scores_per_quiz($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }

    /**
     * Heat map of grade distribution.
     * @param stdClass $element
     * @return string
     */
    public function gradebookreport_scores_assigned_by_xray_versus_results_from_quizzes($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }

    /**
     * Heat map of grade distribution.
     * @param stdClass $element
     * @return string
     */
    public function gradebookreport_comparison_of_scores_in_quizzes($element) {
        return $this->show_on_lightbox(__FUNCTION__, $element);
    }

    /************************** End Elements for Gradebook Report **************************/

    /************************** Course Header **************************/

    /**
     * Snap Dashboard Xray
     */
    public function snap_dashboard_xray() {
        global $PAGE, $COURSE;

        $output = "";

        if (has_capability('local/xray:dashboard_view', $PAGE->context)) {
            try {
                $report = "dashboard";
                $response = \local_xray\api\wsapi::course($COURSE->id, $report);

                if (!$response) {
                    // Fail response of webservice.
                    \local_xray\api\xrayws::instance()->print_error();

                } else {

                    // Get users in risk.
                    $usersinrisk = array();

                    if (isset($response->elements->element3->data) && !empty($response->elements->element3->data)) {
                        foreach ($response->elements->element3->data as $key => $obj) {
                            if ($obj->severity->value == "high") {
                                $usersinrisk[] = $obj->participantId->value;
                            }
                        }
                    }
                    // Student ins risk.
                    $countstudentsrisk = (isset($response->elements->element6->items[5]->value) ?
                                                $response->elements->element6->items[5]->value : "-");
                    // Students enrolled.
                    $countstudentsenrolled = (isset($response->elements->element6->items[2]->value) ?
                                                    $response->elements->element6->items[2]->value : "-");
                    // Visits last 7 days.
                    $countstudentsvisitslastsevendays = (isset($response->elements->element6->items[0]->value) ?
                                                               $response->elements->element6->items[0]->value : "-");
                    // Risk previous 7 days.
                    $countstudentsriskprev = (isset($response->elements->element6->items[6]->value) ?
                                                    $response->elements->element6->items[6]->value : "-");
                    // Visits previous 7 days.
                    $countstudentsvisitsprev = (isset($response->elements->element6->items[1]->value) ?
                                                      $response->elements->element6->items[1]->value : "-");

                    // Calculate percentajes from last weeks.
                    $precentajevalueperstudent = 100 / $countstudentsenrolled;

                    // Diff risk.
                    $percentajestudentsriskprev = $precentajevalueperstudent * $countstudentsriskprev;
                    $percentajestudentsrisk = $precentajevalueperstudent * $countstudentsrisk;
                    $diffrisk = round($percentajestudentsrisk - $percentajestudentsriskprev);

                    // Diff visits.
                    $percentajestudentsvisitsprev = $precentajevalueperstudent * $countstudentsvisitsprev;
                    $percentajestudentsvisitslastsevendays = $precentajevalueperstudent * $countstudentsvisitslastsevendays;
                    $diffvisits = round($percentajestudentsvisitslastsevendays - $percentajestudentsvisitsprev);

                    // Students visits by week day.
                    $studentsvisitsbyweekday = (isset($response->elements->activity_level->data) ?
                                                      $response->elements->activity_level->data : "-");

                    $output .= $this->snap_dashboard_xray_output($usersinrisk,
                        $countstudentsenrolled,
                        $countstudentsrisk,
                        $countstudentsvisitslastsevendays,
                        $diffrisk,
                        $diffvisits,
                        $studentsvisitsbyweekday);
                }
            } catch (exception $e) {
                $output .= get_string('error_xray', 'local_xray');
            }
        }

        return $output;
    }

    /**
     * Snap Dashboard Xray
     *
     * @param mixed $usersinrisk
     * @param int $studentsenrolled
     * @param int $studentsrisk
     * @param int $studentsvisitslastsevendays
     * @param float $riskfromlastweek
     * @param float $visitorsfromlastweek
     * @param Array $studentsvisitsbyweekday
     * @return string
     * */
    public function snap_dashboard_xray_output($usersinrisk,
                                               $studentsenrolled,
                                               $studentsrisk,
                                               $studentsvisitslastsevendays,
                                               $riskfromlastweek,
                                               $visitorsfromlastweek,
                                               $studentsvisitsbyweekday) {

        global $DB;

        // JQuery to show all students.
        $jscode = "$(function(){
            $('.xray_dashboard_seeall').click(function()
                {
                    var div = $('.xray_dashboard_users_risk_hidden');
                    startAnimation();
                    function startAnimation(){
                        div.slideToggle('slow');
                    }
                });
            });";

        $xraydashboardjquery = html_writer::script($jscode);

        $of = html_writer::tag('small', get_string('of', 'local_xray'));

        // Students at risk.
        $studentsrisk = "<div class='xray-headline'><span class='xray-headline-number h1'>$studentsrisk</span>".
                        "$of $studentsenrolled</div>";
        $studentatrisktext = html_writer::div(get_string('studentatrisk', 'local_xray'), 'xray-headline-description');
        // Bootstrap classes for positive/negative data.
        $comparitorclass = "xray-comparitor";
        $comparitorbsclass = " text-muted";
        if ($visitorsfromlastweek < 0) {
            $comparitorbsclass = " text-success";
        }
        if ($visitorsfromlastweek > 0) {
            $comparitorbsclass = " text-danger";
        }
        $comparitorclass .= $comparitorbsclass;
        $riskfromlastweekth = html_writer::div(get_string('fromlastweek', 'local_xray', $riskfromlastweek), $comparitorclass);

        $usersprofile = ""; // Six firsts users.
        $usersprofilehidden = ""; // Rest of users will be hidden.
        $countusers = 1;
        if (!empty($usersinrisk)) {
            foreach ($usersinrisk as $key => $id) {
                if ($countusers > 6) {
                    $usersprofilehidden .= $this->print_student_profile($DB->get_record('user', array("id" => $id)));
                } else {
                    $usersprofile .= $this->print_student_profile($DB->get_record('user', array("id" => $id)));
                }
                $countusers++;
            }
        }

        $usersprofilebox = html_writer::div($usersprofile);
        $usersprofileboxhidden = html_writer::div($usersprofilehidden, 'xray_dashboard_users_risk_hidden');

        $showall = '';
        if (count($usersinrisk) > 6) {
            $showall = html_writer::div('Show all', 'btn btn-default btn-sm xray_dashboard_seeall');
        }

        $riskcolumn = html_writer::div($xraydashboardjquery . $studentsrisk . $studentatrisktext .
                                        $riskfromlastweekth . $usersprofilebox . $usersprofileboxhidden .
                                        $showall, 'xray-risk col-sm-6 span6');

        // Students Visitors.
        $studentsvisitors = "<div class='xray-headline'><span class='xray-headline-number h1'>$studentsvisitslastsevendays</span>".
                            "$of $studentsenrolled</small></div>";
        $studentvisitslastdaystext = html_writer::div(get_string('studentvisitslastdays', 'local_xray'),
                                                      'xray-headline-description');
        // Bootstrap classes for positive/negative data.
        $comparitorclass = "xray-comparitor";
        $comparitorbsclass = " text-muted";
        if ($visitorsfromlastweek < 0) {
            $comparitorbsclass = " text-danger";
        }
        if ($visitorsfromlastweek > 0) {
            $comparitorbsclass = " text-success";
        }
        $comparitorclass .= $comparitorbsclass;

        $visitorsfromlastweektr = html_writer::div(get_string('fromlastweek', 'local_xray', $visitorsfromlastweek),
                                                   $comparitorclass);

        // Students visits by Week Day.
        $studentsvisitsperday = "";
        // TODO - Test data, remove.
        if ($studentsvisitslastsevendays) {
            $studentsvisitsperday = "<div class='xray-student-visits-lastsevendays'>";
            foreach ($studentsvisitsbyweekday as $key => $value) {
                $visitsperday = $value->number_of_visits->value;
                $percent = ceil(($visitsperday / $studentsvisitslastsevendays) * 100);
                $day = substr($value->day_of_week->value, 0, 3);
                $studentsvisitsperday .= "<div class='xray-visits-unit'>";
                $studentsvisitsperday .= "<div class='xray-visits-per-day'>$day</div>";
                $studentsvisitsperday .= "<div class='xray-visits-per-day-line' style='height:".$percent."%'>$visitsperday</div>";
                $studentsvisitsperday .= "</div>";
            }
            $studentsvisitsperday .= "</div>";
        }
        $visitorscolumn = html_writer::div($studentsvisitors . $studentvisitslastdaystext . $visitorsfromlastweektr .
                                           $studentsvisitsperday, 'xray-visitors col-sm-6 span6');

        return html_writer::div($riskcolumn . $visitorscolumn, 'row row-fluid container-fluid');
    }

    /**
     * Renderer (copy of print_teacher_profile in renderer.php of snap theme).
     * @param stdClass $user
     * @return string
     */
    public function print_student_profile($user) {
        global $CFG;

        $userpicture = new user_picture($user);
        $userpicture->link = false;
        $userpicture->alttext = false;
        $userpicture->size = 30;
        $picture = $this->render($userpicture);
        $fullname = '<a href="' . $CFG->wwwroot . '/user/profile.php?id='.$user->id.'">' . format_string(fullname($user)) . '</a>';
        return "<div class='dashboard_xray_users_profile'>
                $picture $fullname </div>";
    }

    /************************** End Course Header **************************/

}
