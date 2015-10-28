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

/**
 * X-Ray plugin renderer
 *
 * @package   local_xray
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') or die();

/* @var stdClass $CFG */
use local_xray\datatables\datatablescolumns;

/**
 * Renderer
 *
 * @package   local_xray
 * @author    Pablo Pagnone
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_xray_renderer extends plugin_renderer_base {

    /************************** General elements for Reports **************************/

    /**
     * Show data about report
     *
     * @param  string   $reportdate - Report date in ISO8601 format
     * @param  stdClass $user - User object
     * @return string
     */
    public function inforeport($reportdate, $user = null) {
        $date = new DateTime($reportdate);
        $mreportdate = userdate($date->getTimestamp(), get_string('strftimedayshort', 'langconfig'));

        $output  = html_writer::start_div('inforeport');
        $output .= html_writer::tag("p", get_string("reportdate", "local_xray") . ": " . $mreportdate);
        if (!empty($user)) {
            $output .= html_writer::tag("p", get_string(("user")) . ": " . format_string(fullname($user)));
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
     * @param  string $name
     * @param  stdClass $element
     * @return string
     */
    public function show_on_lightbox($name, $element) {
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
        $accesstoken = local_xray\local\api\wsapi::accesstoken();
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
     * @param array $datatable - Array containing object DataTable.
     * @return string
     */
    public function standard_table(array $datatable) {
        global $PAGE;
        // Load Jquery.
        $PAGE->requires->jquery();
        $PAGE->requires->jquery_plugin('ui');
        // Load specific js for tables.
        $PAGE->requires->jquery_plugin("local_xray-show_on_table", "local_xray");
        $output = "";
        $output .= html_writer::start_tag('div', array("id" => $datatable['id'], "class" => "xray_element xray_element_table"));
        $output .= html_writer::tag('h3', $datatable['title'], array("class" => "reportsname eventtoogletable"));
        
        // Table jquery datatables for show reports.
        $output .= html_writer::start_tag("table", 
                array("id" => "table_{$datatable['id']}",
                "class" => "xraydatatable display"));
        
        $output .= html_writer::start_tag("thead");
        $output .= html_writer::start_tag("tr");        
                
        
        foreach ($datatable['columns'] as $c) {
            $output .= html_writer::tag("th", $c->text);
        }
        
        $output .= html_writer::end_tag("tr");
        $output .= html_writer::end_tag("thead");
        $output .= html_writer::end_tag("table");
        $output .= html_writer::end_tag('div');

        // Load table with data.
        $PAGE->requires->js_init_call("local_xray_show_on_table", array($datatable));

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
     * Set Category
     *
     * @param  float $value
     * @return string
     */
    public function set_category($value) {
        $size = 'high';
        if ($value < 0.2) {
            $size = 'low';
        } else if (($value > 0.2) && ($value < 0.3)) {
            $size = 'medium';
        }

        return get_string($size, 'local_xray') . ' ' . $value;
    }

    /**
     * Set Category Regularly
     *
     * @param int $value
     * @return string
     */
    public function set_category_regularly($value) {
        $string = 'irregular';
        if ($value < 1) {
            $string = 'highlyregularity';
        } else if ($value < 2) {
            $string = 'somewhatregularity';
        }

        return get_string($string, 'local_xray') . ' ' . $value;
    }
    /************************** End General elements for Reports **************************/

    /************************** Elements for Report Discussion **************************/
    /**
     * Graphic Discussion Activity by Week (TABLE) - Special case table.
     * @param int $courseid
     * @param stdClass $element
     * @return string
     */
    public function discussionreport_discussion_activity_by_week($courseid, $element) {
        // Create standard table.
        $columns = array();
        $columns[] = new local_xray\datatables\datatablescolumns('weeks', get_string('weeks', 'local_xray'), false, false);
        foreach ($element->data as $column) {
            $columns[] = new local_xray\datatables\datatablescolumns($column->week->value, $column->week->value, false, false);
        }

        $numberofweeks = count($columns) - 1; // Get number of weeks - we need to rest the "week" title column.

        $datatable = new local_xray\datatables\datatables($element,
            "view.php?controller='discussionreport'&action='jsonweekdiscussion'&courseid=" . $courseid . "&count=" . $numberofweeks,
            $columns,
            false,
            false, // We don't need pagination because we have only four rows.
            '<"xray_table_scrool"t>', // Only the table.
            array(10, 50, 100),
            false); // This table has not sortable.

        // Create standard table.
        $output = $this->standard_table((array)$datatable);

        return $output;
    }

    /************************** End Elements for Report Discussion **************************/


    /************************** Elements for Report Discussion for an individual **************************/

    /**
     * Graphic Discussion Activity by Week (TABLE) - Special case table.
     * @param int $courseid
     * @param int $userid
     * @param object $element
     * @return string
     */
    public function discussionreportindividual_discussion_activity_by_week($courseid, $userid, $element) {
        // Create standard table.
        $columns = array();
        $columns[] = new local_xray\datatables\datatablescolumns('weeks', get_string('week', 'local_xray'));
        foreach ($element->data as $column) {
            $columns[] = new local_xray\datatables\datatablescolumns($column->week->value, $column->week->value);
        }

        $numberofweeks = count($columns) - 1; // Get number of weeks - we need to rest the "week" title column.

        $datatable = new local_xray\datatables\datatables($element,
            "view.php?controller='discussionreportindividual'&action='jsonweekdiscussionindividual'&courseid=" .
            $courseid . "&userid=" . $userid . "&count=" . $numberofweeks,
            $columns,
            false,
            false, // We don't need pagination because we have only four rows.
            '<"xray_table_scrool"t>',
            array(10, 50, 100),
            false); // without sortable.

        // Create standard table.
        $output = $this->standard_table((array)$datatable);

        return $output;
    }
    /************************** End Elements for Report Discussion for an individual **************************/

    /************************** Course Header **************************/

    /**
     * Snap Dashboard Xray
     */
    public function snap_dashboard_xray() {
        global $PAGE, $COURSE;

        $output = "";

        if (has_capability('local/xray:dashboard_view', $PAGE->context)) {
            $dashboarddata = local_xray\dashboard\dashboard::get($COURSE->id);
            if($dashboarddata instanceof local_xray\dashboard\dashboarddata) {
                $output .= $this->snap_dashboard_xray_output($dashboarddata);
            } else {
                $output .= get_string('error_xray', 'local_xray');
            }
        }

        return $output;
    }

    /**
     * Snap Dashboard Xray
     *
     * @param local_xray\dashboard\dashboard $data
     * @return string
     * */
    private function snap_dashboard_xray_output($data) {

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
        $studentsrisk = html_writer::tag("div", 
                html_writer::tag("span", $data->studentsrisk, array("class" => "xray-headline-number h1"))."${of} {$data->studentsenrolled}",
                array("class" => "xray-headline"));
                
        $studentatrisktext = html_writer::div(get_string('studentatrisk', 'local_xray'), 'xray-headline-description');
        // Bootstrap classes for positive/negative data.
        $comparitorclass = "xray-comparitor";
        $comparitorbsclass = " text-muted";
        if ($data->visitorsfromlastweek < 0) {
            $comparitorbsclass = " text-success";
        }
        if ($data->visitorsfromlastweek > 0) {
            $comparitorbsclass = " text-danger";
        }
        $comparitorclass .= $comparitorbsclass;
        $riskfromlastweekth = html_writer::div(get_string('fromlastweek', 'local_xray', $data->riskfromlastweek), $comparitorclass);

        $usersprofile = ""; // Six firsts users.
        $usersprofilehidden = ""; // Rest of users will be hidden.
        $countusers = 1;
        if (!empty($data->usersinrisk)) {
            foreach ($data->usersinrisk as $key => $id) {
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
        if (count($data->usersinrisk) > 6) {
            $showall = html_writer::div('Show all', 'btn btn-default btn-sm xray_dashboard_seeall');
        }

        $riskcolumn = html_writer::div($xraydashboardjquery . $studentsrisk . $studentatrisktext .
            $riskfromlastweekth . $usersprofilebox . $usersprofileboxhidden .
            $showall, 'xray-risk col-sm-6 span6');

        // Students Visitors.  
        $studentsvisitors = html_writer::div(html_writer::tag("span", 
                                            $data->studentsvisitslastsevendays,
                                            array("class" => "xray-headline-number h1"))."{$of} {$data->studentsenrolled}",
                array("class" => "xray-headline"));
        
        $studentvisitslastdaystext = html_writer::div(get_string('studentvisitslastdays', 'local_xray'),
            'xray-headline-description');
        // Bootstrap classes for positive/negative data.
        $comparitorclass = "xray-comparitor";
        $comparitorbsclass = " text-muted";
        if ($data->visitorsfromlastweek < 0) {
            $comparitorbsclass = " text-danger";
        }
        if ($data->visitorsfromlastweek > 0) {
            $comparitorbsclass = " text-success";
        }
        $comparitorclass .= $comparitorbsclass;

        $visitorsfromlastweektr = html_writer::div(get_string('fromlastweek', 'local_xray', $data->visitorsfromlastweek),
            $comparitorclass);

        // Students visits by Week Day.
        $studentsvisitsperday = "";
        if ($data->studentsvisitslastsevendays) {
            $studentsvisitsperday = html_writer::start_div("xray-student-visits-lastsevendays");
            foreach ($data->studentsvisitsbyweekday as $key => $value) {
                $visitsperday = $value->number_of_visits->value;
                $percent = ceil(($visitsperday / $data->studentsvisitslastsevendays) * 100);
                $day = substr($value->day_of_week->value, 0, 3);
                $studentsvisitsperday .= html_writer::start_div("xray-visits-unit");
                $studentsvisitsperday .= html_writer::div($day, array("class" => "xray-visits-per-day"));             
                $studentsvisitsperday .= html_writer::div($visitsperday, 
                        array("class" => "xray-visits-per-day-line", "style" => "height:{$percent}%"));             
                    
                $studentsvisitsperday .= html_writer::end_div();
            }
            $studentsvisitsperday .= html_writer::end_div();
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
    private function print_student_profile($user) {
        global $CFG;

        $userpicture = new user_picture($user);
        $userpicture->link = false;
        $userpicture->alttext = false;
        $userpicture->size = 30;
        $picture = $this->render($userpicture);
        $fullname = html_writer::tag("a", 
                format_string(fullname($user)), 
                array("href" => $CFG->wwwroot . '/user/profile.php?id=' . $user->id));
        return html_writer::div("{$picture} {$fullname}", array("class" => "dashboard_xray_users_profile"));
    }

    /************************** End Course Header **************************/

    /**
     * Print menu html
     *
     * @param  string $reportcontroller
     * @param  array  $reports
     * @return string
     */
    public function print_course_menu($reportcontroller, $reports) {
        $displaymenu = get_config('local_xray', 'displaymenu');
        $menu = '';
        if ($displaymenu) {
            if (!empty($reports)) {
                $menuitems = [];
                foreach ($reports as $nodename => $reportsublist) {
                    foreach ($reportsublist as $reportstring => $url) {
                        $class = $reportstring;
                        if (!empty($reportcontroller)) {
                            $class .= " xray-reports-links-bk-image-small";
                        } else {
                            $class .= " xray-reports-links-bk-image-large";
                        }
                        if ($reportstring == $reportcontroller) {
                            $class .= " xray-menu-item-active";
                        }
                        $menuitems[] = \html_writer::link($url, get_string($reportstring, 'local_xray'), array('class' => $class));
                    }
                }
                $title = '';
                if (empty($reportcontroller)) {
                    $title = \html_writer::tag('h4', get_string('reports', 'local_xray'));
                }
                $amenu = \html_writer::alist($menuitems, array('style' => 'list-style-type: none;',
                                                               'class' => 'xray-reports-links'));
                $menu = \html_writer::div($title . $amenu, 'clearfix', array('id' => 'js-xraymenu', 'role' => 'region'));
            }
        }

        return $menu;
    } // End print_course_header_menu.

    /**
     * Print course header data html
     *
     * @return string
     */
    public function print_course_header_data() {
        $displayheaderdata = get_config('local_xray', 'displayheaderdata');

        $headerdata = '';
        if ($displayheaderdata) {
            $headerdata = $this->snap_dashboard_xray();
            if (!empty($headerdata)) {
                $title = \html_writer::tag('h2', get_string('navigation_xray', 'local_xray') .
                                           get_string('analytics', 'local_xray'));
                $subc = $title . $headerdata;
                $headerdata = \html_writer::div($subc, '', ['id' => 'js-headerdata', 'class' => 'clearfix']);
            }
        }

        return $headerdata;
    } // End print_course_header_data.

}
