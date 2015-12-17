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
use local_xray\event\get_report_failed;

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
        $output = '';
        if (!empty($user)) {
            $output .= html_writer::tag("p", get_string(("user")) . ": " . format_string(fullname($user)));
        }
        $date = new DateTime($reportdate);
        $mreportdate = userdate($date->getTimestamp(), get_string('strftimedayshort', 'langconfig'));
        $output .= html_writer::tag("p", get_string("reportdate", "local_xray") . ": " . $mreportdate , array('class' => 'inforeport'));
        return $output;
    }

    /**
     * Show Graph.
     *
     * @param  string $name
     * @param  stdClass $element
     * @param  integer $reportid - Id of report, we need this to get accessible data from webservice.
     * @return string
     */

    public function show_graph($name, $element, $reportid) {

        global $PAGE, $COURSE;
        $plugin = "local_xray";
        $cfgxray = get_config('local_xray');
        $imgurl = sprintf('%s/%s/%s', $cfgxray->xrayurl, $cfgxray->xrayclientid, $element->uuid);
        // Access Token.
        $accesstoken = local_xray\local\api\wsapi::accesstoken();
        $imgurl = new moodle_url($imgurl, array('accesstoken' => $accesstoken));
        $output = "";
        // List Graph.
        $output .= html_writer::start_tag('div', array('class' => 'xray-col-4 '.$element->elementName));
        $output .= html_writer::tag('h3', $element->title, array("class" => "reportsname"));

        // Validate if exist and is available image in xray side.
        $existimg = false;
        try {
            $ch = new curl(['debug' => false]);
            $ch->head($imgurl, ['CURLOPT_CONNECTTIMEOUT' => 2, 'CURLOPT_TIMEOUT' => 2]);
            if (!empty($ch->get_errno())) {
                print_error('xrayws_error_curl', 'local_xray', '', $ch->response);
            }
            if (!empty($ch->info['content_type']) && preg_match('#^image/.*#', $ch->info['content_type'])) {
                $existimg = true;
            }
        }
        catch (Exception $e) {
            get_report_failed::create_from_exception($e, $PAGE->context, "renderer_show_graph")->trigger();
        }

        // Link to accessible version.
        if($existimg) {
            $urlaccessible = new moodle_url("view.php",
                array("controller" => "accessibledata",
                    "origincontroller" => $PAGE->url->get_param("controller"),
                    "graphname" => rawurlencode($element->title),
                    "reportid" => $reportid,
                    "elementname" => $element->elementName,
                    "courseid" => $COURSE->id));

            $linkaccessibleversion = html_writer::link($urlaccessible, get_string("accessible_view_data", $plugin),
                array("target" => "_blank",
                    "class" => "xray-accessible-view-data"));
            $output .= html_writer::tag('span', $linkaccessibleversion);
        }

        // Show image.
        if ($existimg) {
            // Show image.
            $output .= html_writer::start_tag('a', array('href' => '#'.$element->elementName , 'class' => 'xray-graph-box-link'));
            $output .= html_writer::start_tag('span',
                array('class' => 'xray-graph-small-image',
                    'style' => 'background-image: url('.$imgurl.');'));
            $output .= html_writer::end_tag('span');
            $output .= html_writer::end_tag('a');
        } else {
            // Incorrect url img. Show error message.
            $output .= html_writer::tag("div",
                get_string('error_loadimg', $plugin), array("class" => "error_loadmsg"));
        }

        $output .= html_writer::end_tag('div');

        // Show Graph.
        // Get Tooltip.
        if ($existimg) {
            $output .= html_writer::start_tag('div', array('id' => $element->elementName, 'class' => 'xray-graph-background'));
            $output .= html_writer::start_tag('div', array('class' => 'xray-graph-view'));
            $output .= html_writer::tag('h6', $element->title, array('class' => 'xray-graph-caption-text'));
            if (isset($element->tooltip) && !empty($element->tooltip)) {
                $output .= html_writer::tag('p', $element->tooltip, array('class' => 'xray-graph-description'));
            }
            $output .= html_writer::img($imgurl, '', array('class' => 'xray-graph-image'));
            $output .= html_writer::end_tag('div');
            $output .= html_writer::tag('a', '' , array(
                'href' => '#',
                'class' => 'xray-close-link',
                'title' => get_string('close', 'local_xray')));
            $output .= html_writer::end_tag('div');
        }
        return $output;
    }

    /**
     * Show accessibledata in table.
     * @param Array $data
     * @param Array $rows
     * @param String $title
     * @return string
     */
    public function accessibledata(array $columnsnames, array $rows, $title = "") {

        $output = "";
        // Create table.
        $table = new html_table();
        $table->attributes = array("title" => $title);
        $table->head  = $columnsnames;
        $table->caption = $title;
        $table->captionhide = true;
        $table->data  = $rows;
        $table->summary  = $title;
        $output .= html_writer::table($table);
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
        // Table Title with link to open it.
        $output .= "<h3 class='xray-table-title-link reportsname'>
        <a href='#".$datatable['id']."'>".$datatable['title']."</a>
        </h3>";
        /*$link = html_writer::start_tag('a', array(
            'id' => "title_{$datatable['id']}",
            'href' => "#{$datatable['id']}",
            'class' => 'xray-table-title-link'));
        $link .= html_writer::end_tag('a');
        $output .= html_writer::tag('h3', $datatable['title'], array('class' => 'reportsname'));
        */
        // Table.
        $output .= html_writer::start_tag('div', array(
            'id' => "{$datatable['id']}",
            'class' => 'xray-toggleable-table',
            'tabindex' => '0'));
        // Table jquery datatables for show reports. //TODO clean styles.
        $output .= html_writer::start_tag("table",
            array("id" => "table_{$datatable['id']}",
                "class" => "xraydatatable display"));
        $output .= html_writer::tag("caption", $datatable['title']);
        $output .= html_writer::start_tag("thead");
        $output .= html_writer::start_tag("tr");
        foreach ($datatable['columns'] as $c) {
            $output .= html_writer::tag("th", $c->text);
        }
        $output .= html_writer::end_tag("tr");
        $output .= html_writer::end_tag("thead");
        $output .= html_writer::end_tag("table");
        // Close Table button.
        $output .= html_writer::tag('a', get_string('closetable', 'local_xray'), array('href' => "#"));
        $output .= html_writer::end_tag('div');
        // End Table.
        // Load table with data.
        $PAGE->requires->js_init_call("local_xray_show_on_table", array($datatable));
        return $output;
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
            "rest.php?controller='discussionreport'&action='jsonweekdiscussion'&courseid=" . $courseid . "&count=" . $numberofweeks,
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
            "rest.php?controller='discussionreportindividual'&action='jsonweekdiscussionindividual'&courseid=" .
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
            if($dashboarddata instanceof local_xray\dashboard\dashboard_data) {
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
     * @param local_xray\dashboard\dashboard_data $data
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
            "xray-headline");

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
                $navmenu = html_writer::tag("nav", $amenu);
                $menu = \html_writer::div($title . $navmenu, 'clearfix', array('id' => 'js-xraymenu'));
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
