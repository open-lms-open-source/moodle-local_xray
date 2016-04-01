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
        $output .= html_writer::tag("p",
            get_string("reportdate", "local_xray") . ": " . $mreportdate , array('class' => 'inforeport'));
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
        } catch (Exception $e) {
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
     * @param array $columnsnames
     * @param array $rows
     * @param string $title
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
    public function standard_table(array $datatable, $hashelp = true) {

        global $OUTPUT, $PAGE;
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
        if ($hashelp) {
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

        // First get the help image icon.
        $src = $this->pix_url('help');
        $attributes = array('src' => $src, 'class' => 'iconhelp');
        $output = html_writer::empty_tag('img', $attributes);

        $attributes = array('href' => $url, 'title' => $title, 'aria-haspopup' => 'true', 'target' => '_blank');
        $output = html_writer::tag('a', $output, $attributes);
        return html_writer::tag('span', $output);
    }
    /************************** End General elements for Reports **************************/

    /************************** Elements for tabla Discussion activity by Week (Inversetable) **************************/

    /**
     * Table for:
     * Discussion Report - Activity by Week (TABLE) - Special case table.
     * Discussion Individual Report - Activity by Week (TABLE) - Special case table.
     *
     * @param $element
     * @param moodle_url $jsonurl
     * @return string
     */
    public function table_inverse_discussion_activity_by_week($element, moodle_url $jsonurl) {
        // Create standard table.
        $columns = array();
        $columns[] = new local_xray\datatables\datatablescolumns('weeks', get_string('week', 'local_xray'));
        foreach ($element->data as $column) {
            if (isset($column->week->value) && is_string($column->week->value)) {
                $columns[] = new local_xray\datatables\datatablescolumns($column->week->value, $column->week->value);
            }
        }

        $numberofweeks = count($columns) - 1; // Get number of weeks - we need to rest the "week" title column.
        $jsonurl->param("count", $numberofweeks);

        $datatable = new local_xray\datatables\datatables($element,
            $jsonurl->out(false),
            $columns,
            false,
            false, // We don't need pagination because we have only four rows.
            '<"xray_table_scrool"t>',
            array(10, 50, 100),
            false); // Without sortable.

        // Create standard table.This tables has not icon help.
        $output = $this->standard_table((array)$datatable, false);

        return $output;
    }
    /************************** End Elements for tabla Discussion activity by Week (Inversetable) **************************/

    /************************** Course Header **************************/

    /**
     * Snap Dashboard Xray
     *
     * @param local_xray\dashboard\dashboard_data $data
     * @return string
     * */
    private function dashboard_xray_output($data) {

        global $COURSE;
        $plugin = "local_xray";
        $output = "";

        // Number for risk.
        $a = new stdClass();
        $a->first = $data->usersinrisk;
        $a->second = $data->risktotal;
        $risknumber = get_string('headline_number_of', $plugin, $a);

        // Number of students at risk in the last 7 days.
        $a = new stdClass();
        $a->previous = $data->averagerisksevendaybefore;
        $a->total = $data->maximumtotalrisksevendaybefore;
        $textlink = get_string("averageofweek_integer", $plugin, $a);

        // To risk metrics.
        $url = new moodle_url("/local/xray/view.php",
            array("controller" => "risk", "courseid" => $COURSE->id, "header" => 1), "riskMeasures");
        // Calculate colour status.
        $statusclass = $this->headline_status_risk($data->usersinrisk, $data->averagerisksevendaybefore);
        $column1 = $this->headline_column($risknumber,
            get_string('headline_studentatrisk', $plugin),
            $url,
            $textlink,
            $statusclass);

        // Number for activity.
        $a = new stdClass();
        $a->first = $data->usersloggedinpreviousweek;
        $a->second = $data->usersactivitytotal;
        $activitynumber = get_string('headline_number_of', $plugin, $a);

        // Number of students logged in in last 7 days.
        $a = new stdClass();
        $a->current = $data->averageuserslastsevendays;
        $a->total = $data->userstotalprevioussevendays;
        $textlink = get_string("headline_lastweekwasof_activity", $plugin, $a);

        // To activity metrics.
        $url = new moodle_url("/local/xray/view.php",
            array("controller" => "activityreport", "courseid" => $COURSE->id, "header" => 1), "studentList");
        // Calculate colour status.
        $statusclass = $this->headline_status($data->usersloggedinpreviousweek, $data->averageuserslastsevendays);

        $column2 = $this->headline_column($activitynumber,
            get_string('headline_loggedstudents', 'local_xray'),
            $url,
            $textlink,
            $statusclass);

        // Number for gradebook.
        $gradebooknumber = get_string('headline_number_percentage', $plugin, $data->averagegradeslastsevendays);

        // Number of average grades in the last 7 days.
        $textlink = get_string("averageofweek_gradebook", $plugin, $data->averagegradeslastsevendayspreviousweek);
        // To students grades.
        $url = new moodle_url("/local/xray/view.php",
            array("controller" => "gradebookreport", "courseid" => $COURSE->id, "header" => 1), "element2");
        // Calculate colour status.
        $statusclass = $this->headline_status($data->averagegradeslastsevendays, $data->averagegradeslastsevendayspreviousweek);

        $column3 = $this->headline_column($gradebooknumber,
            get_string('headline_average', $plugin),
            $url,
            $textlink,
            $statusclass);

        // Number of posts in the last 7 days.
        $textlink = get_string("headline_lastweekwas_discussion", $plugin, $data->postslastsevendayspreviousweek);
        // To participation metrics.
        $url = new moodle_url("/local/xray/view.php",
            array("controller" => "discussionreport", "courseid" => $COURSE->id, "header" => 1), "discussionMetrics");
        // Calculate colour status.
        $statusclass = $this->headline_status($data->postslastsevendays, $data->postslastsevendayspreviousweek);

        $column4 = $this->headline_column($data->postslastsevendays,
            get_string('headline_posts', 'local_xray'),
            $url,
            $textlink,
            $statusclass);

        // Menu list.
        $list = html_writer::start_tag("ul", array("class" => "xray-headline"));
        $list .= html_writer::tag("li", $column1, array("id" => "xray-headline-risk", "tabindex" => 0));
        $list .= html_writer::tag("li", $column2, array("id" => "xray-headline-activity", "tabindex" => 0));
        $list .= html_writer::tag("li", $column3, array("id" => "xray-headline-gradebook", "tabindex" => 0));
        $list .= html_writer::tag("li", $column4, array("id" => "xray-headline-discussion", "tabindex" => 0));
        $list .= html_writer::end_tag("ul");

        $output .= html_writer::tag("nav", $list, array("id" => "xray-nav-headline"));
        return $output;
    }

    /**
     * Create column for headeline data.
     *
     * @param integer $number
     * @param string $text
     * @param string $linkurl
     * @param string $textweekbefore
     * @param array $stylestatus - Array with class and lang for status.
     * @return string
     */
    private function headline_column($number, $text, $linkurl, $textweekbefore, $stylestatus) {

        // Link with Number and icon.
        $icon = html_writer::span('', $stylestatus[0]."-icon xray-headline-icon");
        $number = html_writer::tag("p", $number.$icon, array("class" => "xray-headline-number"));
        $link = html_writer::link($linkurl,
            $number,
            array("tabindex" => -1,
                "class" => "xray-headline-link",
                "title" => get_string('link_gotoreport', 'local_xray')));

        // Text only for reader screens.
        $arrowreader = html_writer::tag("span", "", array("class" => "xray-headline-status-hide", "title" => $stylestatus[1]));
        // Text for description and text of week before.
        $textdesc = html_writer::tag("p", $text.$arrowreader, array("class" => "xray-headline-desc"));

        $textweekbefore = html_writer::tag("span",
            $textweekbefore,
            array("class" => "xray-headline-textweekbefore {$stylestatus[0]}"));

        return $link.$textdesc.$textweekbefore;
    }

    /**
     * Calculate colour and arrow for headline (compare current value and value in the previous week).
     * Return array with class and string for reader.
     *
     * Same value = return class for yellow colour.
     * Increment value = return class for green colour.
     * Decrement value = return class for red colour.
     *
     * @param $valuenow
     * @param $valuepreviousweek
     * @return array
     */
    private function headline_status($valuenow, $valuepreviousweek) {
        // Default, same value.
        $stylestatus = "xray-headline-yellow";
        $langstatus = get_string("arrow_same", "local_xray");

        if ($valuenow < $valuepreviousweek) {
            // Decrement.
            $stylestatus = "xray-headline-red";
            $langstatus = get_string("arrow_decrease", "local_xray");
        } else if ($valuenow > $valuepreviousweek) {
            // Increment.
            $stylestatus = "xray-headline-green";
            $langstatus = get_string("arrow_increase", "local_xray");
        }

        return array($stylestatus, $langstatus);
    }
    /**
     * Calculate colour and arrow for headline (compare current value and value in the previous week).
     * This case is only for RISK column.
     *
     * Same value = return class for yellow colour.
     * Decrement value = return class for green colour.
     * Increment value = return class for red colour.
     *
     * @param $valuenow
     * @param $valuepreviousweek
     * @return array
     */
    private function headline_status_risk($valuenow, $valuepreviousweek) {

        // Default, same value.
        $stylestatus = "xray-headline-yellow";
        $langstatus = get_string("arrow_same", "local_xray");

        if ($valuenow > $valuepreviousweek) {
            // Decrement.
            $stylestatus = "xray-headline-red-caserisk";
            $langstatus = get_string("arrow_decrease", "local_xray");
        } else if ($valuenow < $valuepreviousweek) {
            // Increment.
            $stylestatus = "xray-headline-green-caserisk";
            $langstatus = get_string("arrow_increase", "local_xray");
        }

        return array($stylestatus, $langstatus);
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

        global $PAGE, $COURSE, $OUTPUT;
        $displaymenu = get_config('local_xray', 'displaymenu');
        $menu = '';
        if ($displaymenu) {
            if (!empty($reports)) {
                $classes = 'clearfix';
                $menuitems = [];
                foreach ($reports as $nodename => $reportsublist) {
                    foreach ($reportsublist as $reportstring => $url) {
                        $class = $reportstring;
                        $class .= " xray-reports-links-image";

                        if ($reportstring == $reportcontroller) {
                            $class .= " xray-menu-item-active";
                        }
                        $menuitems[] = html_writer::link($url, get_string($reportstring, 'local_xray'), array('class' => $class));
                    }
                }
                $title = '';
                if (empty($reportcontroller)) {
                    $classes .= " block"; // Structure of headline in frontpage will be like block.
                    $pluginname = get_string('pluginname', 'local_xray');
                    $icon = $OUTPUT->pix_icon('xray-logo',
                        $pluginname,
                        'local_xray',
                        array("tabindex" => -1, "class" => "x-ray-icon-title"));
                    $title = html_writer::tag('h4', $icon.$pluginname);
                }
                $amenu = html_writer::alist($menuitems, array('class' => 'xray-reports-links'));
                $navmenu = html_writer::tag("nav", $amenu, array("id" => "xray-nav-reports"));

                // Check if show headerline in course frontpage.
                $headerdata = "";
                if (empty($reportcontroller) && has_capability('local/xray:dashboard_view', $PAGE->context)) {

                    $dashboarddata = local_xray\dashboard\dashboard::get($COURSE->id);
                    if ($dashboarddata instanceof local_xray\dashboard\dashboard_data) {
                        $headerdata .= $this->dashboard_xray_output($dashboarddata);
                    } else {
                        $headerdata .= html_writer::div(get_string('error_xray', 'local_xray'), 'xray-headline-errortoconnect');
                    }
                }

                $menu = html_writer::div($title . $navmenu. $headerdata,
                    $classes,
                    array('id' => 'js-xraymenu', 'role' => 'region'));

            }
        }

        return $menu;
    }
}
