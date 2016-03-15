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
            $output .= html_writer::tag("h4",
                get_string(("user")) . ": " . format_string(fullname($user)),
                array('class' => 'xray-inforeport-user'));
        }
        $date = new DateTime($reportdate);
        $mreportdate = userdate($date->getTimestamp(), get_string('strftimedayshort', 'langconfig'));
        $output .= html_writer::tag("p", get_string("reportdate", "local_xray") . ": " . $mreportdate , array('class' => 'inforeport'));
        return $output;
    }

    /**
     * Show Graph.
     *
     * @param $name
     * @param $element
     * @param $reportid - Id of report, we need this to get accessible data from webservice.
     * @param array $extraparamurlaccessible
     * @param bool|true $hashelp - Show help for graph or not.
     * @return string
     */
    public function show_graph($name, $element, $reportid, $extraparamurlaccessible = array(), $hashelp = true) {

        global $PAGE, $COURSE, $OUTPUT;
        $plugin = "local_xray";

        $output = "";
        // List Graph.
        $title = get_string($PAGE->url->get_param("controller")."_".$element->elementName, $plugin);
        $output .= html_writer::start_tag('div', array('class' => 'xray-col-4 '.$element->elementName));
        $output .= html_writer::tag('h3', $title, array("class" => "xray-reportsname"));

        $imgurl = false;
        try {
            // Validate if exist and is available image in xray side.
            $imgurl = local_xray\local\api\wsapi::get_imgurl_xray($element->uuid);
        }
        catch (Exception $e) {
            get_report_failed::create_from_exception($e, $PAGE->context, "renderer_show_graph")->trigger();
        }

        // Link to accessible version.
        if (!empty($imgurl)) {

            $paramsurl = array("controller" => "accessibledata",
                "origincontroller" => $PAGE->url->get_param("controller"),
                "graphname" => rawurlencode($element->title),
                "reportid" => $reportid,
                "elementname" => $element->elementName,
                "courseid" => $COURSE->id);
            if (!empty($extraparamurlaccessible)) {
                $paramsurl = array_merge($paramsurl, $extraparamurlaccessible);
            }
            $urlaccessible = new moodle_url("/local/xray/view.php", $paramsurl);

            $linkaccessibleversion = html_writer::link($urlaccessible, get_string("accessible_view_data", $plugin),
                array("target" => "_accessibledata",
                    "class" => "xray-accessible-view-data"));
            $output .= html_writer::tag('span', $linkaccessibleversion);
        }

        // Show image.
        if (!empty($imgurl)) {
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
                get_string('error_loadimg', $plugin), array("class" => "xray_error_loadmsg"));
        }

        $output .= html_writer::end_tag('div');

        // Show Graph.
        // Get Tooltip.
        if (!empty($imgurl)) {
            $output .= html_writer::start_tag('div', array('id' => $element->elementName, 'class' => 'xray-graph-background'));
            $output .= html_writer::start_tag('div', array('class' => 'xray-graph-view'));

            $helpicon = "";
            if ($hashelp) {
                $helpicon = $OUTPUT->help_icon($PAGE->url->get_param("controller")."_".$element->elementName, $plugin);
            }
            $output .= html_writer::tag('h6', $title.$helpicon, array('class' => 'xray-graph-caption-text'));

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
     * @param  boolean - Show help for table or not.
     * @return string
     */
    public function standard_table(array $datatable, $has_help = true) {

        global $PAGE, $OUTPUT, $PAGE;
        // Load Jquery.
        $PAGE->requires->jquery();
        $PAGE->requires->jquery_plugin('ui');
        // Load specific js for tables.
        $PAGE->requires->jquery_plugin("local_xray-show_on_table", "local_xray");

        $output = "";

        // Table Title with link to open it.
        $title = get_string($PAGE->url->get_param("controller")."_".$datatable['id'], 'local_xray');
        $link = html_writer::tag("a", $title, array('href' => "#{$datatable['id']}"));
        $output .= html_writer::tag('h3', $link, array('class' => 'xray-table-title-link xray-reportsname'));

        // Table.
        $output .= html_writer::start_tag('div', array(
            'id' => "{$datatable['id']}",
            'class' => 'xray-toggleable-table',
            'tabindex' => '0'));
        // Table jquery datatables for show reports.
        $output .= html_writer::start_tag("table",
            array("id" => "table_{$datatable['id']}",
                "class" => "xraydatatable display"));

        // Help icon for tables.
        $helpicon = "";
        if($has_help) {
            $helpicon = $OUTPUT->help_icon($PAGE->url->get_param("controller")."_".$datatable['id'], 'local_xray');
        }

        $output .= html_writer::tag("caption", $title.$helpicon);
        $output .= html_writer::start_tag("thead");
        $output .= html_writer::start_tag("tr");
        foreach ($datatable['columns'] as $c) {
            $output .= html_writer::tag("th", $c->text);
        }
        $output .= html_writer::end_tag("tr");
        $output .= html_writer::end_tag("thead");
        $output .= html_writer::end_tag("table");
        // Close Table button.
        $output .= html_writer::start_tag('div', array('class' => 'xray-closetable'));
        $output .= html_writer::tag('a', get_string('closetable', 'local_xray'), array('href' => "#"));
        $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('div');
        // End Table.
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

    /**
     * Similar to render_help_icon but redirect to external url in a new page.
     *
     * @param $title
     * @param $url
     * @return string
     */
    public function help_icon_external_url($title, $url) {
        global $CFG;

        // first get the help image icon
        $src = $this->pix_url('help');
        $attributes = array('src'=>$src, 'class'=>'iconhelp');
        $output = html_writer::empty_tag('img', $attributes);

        $attributes = array('href' => $url, 'title' => $title, 'aria-haspopup' => 'true', 'target'=>'_blank');
        $output = html_writer::tag('a', $output, $attributes);
        return html_writer::tag('span', $output);
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

        // Create standard table. This table has not icon.
        $output = $this->standard_table((array)$datatable, false);

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

        // Create standard table.This tables has not icon help.
        $output = $this->standard_table((array)$datatable, false);

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
                            $class .= " xray-reports-links-bk-image-frontpage";
                        }
                        if ($reportstring == $reportcontroller) {
                            $class .= " xray-menu-item-active";
                        }
                        $menuitems[] = html_writer::link($url, get_string($reportstring, 'local_xray'), array('class' => $class));
                    }
                }
                $title = '';
                $class_nav = '';
                if (empty($reportcontroller)) {
                    $title = html_writer::tag('h4', get_string('pluginname', 'local_xray'));
                    $class_nav = 'xray-logo-in-nav';
                }
                $amenu = html_writer::alist($menuitems, array('style' => 'list-style-type: none;',
                    'class' => 'xray-reports-links'));
                $navmenu = html_writer::tag("nav", $amenu, array("class" => $class_nav));
                $menu = html_writer::div($title . $navmenu, 'clearfix', array('id' => 'js-xraymenu', 'role' => 'region'));
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
