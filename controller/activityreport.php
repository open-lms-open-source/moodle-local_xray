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
 * Report controller.
 *
 * @package   local_xray
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/* @var stdClass $CFG */
require_once($CFG->dirroot . '/local/xray/controller/reports.php');

/**
 * Xray integration Reports Controller
 *
 * @package   local_xray
 * @author    Pablo Pagnone
 * @author    German Vitale
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_xray_controller_activityreport extends local_xray_controller_reports {

    public function view_action() {
        global $PAGE;

        $output = '';

        try {

            if (has_capability("local/xray:activityreport_view", $this->get_context())) {

                $report = "firstLogin";
                $responsefirstlogin = \local_xray\local\api\wsapi::course($this->courseid, $report);
                if (!$responsefirstlogin) {
                    // Fail response of webservice.
                    \local_xray\local\api\xrayws::instance()->print_error();
                } else {
                    $output .= $this->print_top();
                    $output .= $this->output->inforeport($responsefirstlogin->reportdate);
                    
                    // We need show table first in activity report.(INT-8186).
                    $datatable = new local_xray\datatables\datatables($responsefirstlogin->elements->nonStarters,
                    		"view.php?controller='activityreport'&action='jsonfirstloginnonstarters'&courseid=" . $this->courseid);                 
                    $output .= $this->output->standard_table((array)$datatable);
                }

                $report = "activity";
                $response = \local_xray\local\api\wsapi::course($this->courseid, $report);
                if (!$response) {
                    // Fail response of webservice.
                    \local_xray\local\api\xrayws::instance()->print_error();
                } else {
                    
                	// Show table Activity report.
                	$datatable = new local_xray\datatables\datatables($response->elements->studentList,
                			"view.php?controller='activityreport'&action='jsonstudentsactivity'&courseid=" . $this->courseid,
                			array(),
				            true, // add column action.
				            true,
				            "lftipr",
				            array(10, 50, 100),
				            true,
				            1); // Sort by first column "Lastname".Because table has action column);
                	$output .= $this->output->standard_table((array)$datatable);
                	
                	// Show graphs Activity report.
                    $output .= $this->output->show_on_lightbox("activityLevelTimeline", $response->elements->activityLevelTimeline);
                    $output .= $this->output->show_on_lightbox("compassTimeDiagram", $response->elements->compassTimeDiagram);             
                    $output .= $this->output->show_on_lightbox("barplotOfActivityByWeekday", $response->elements->barplotOfActivityByWeekday);
                    $output .= $this->output->show_on_lightbox("barplotOfActivityWholeWeek", $response->elements->barplotOfActivityWholeWeek);
                    $output .= $this->output->show_on_lightbox("activityByWeekAsFractionOfTotal", $response->elements->activityByWeekAsFractionOfTotal);
                    $output .= $this->output->show_on_lightbox("activityByWeekAsFractionOfOwn", $response->elements->activityByWeekAsFractionOfOwn);
                    $output .= $this->output->show_on_lightbox("firstloginPiechartAdjusted", $responsefirstlogin->elements->firstloginPiechartAdjusted);
                }
            }

        } catch (Exception $e) {
            $output = $this->print_error('error_xray', $e->getMessage());
        }

        return $output;
    }

    /**
     * Json for provide data to students_activity table.
     * @return string
     */
    public function jsonstudentsactivity_action() {
        global $PAGE;

        // Pager.
        $count = optional_param('iDisplayLength', 10, PARAM_INT);
        $start = optional_param('iDisplayStart', 0, PARAM_INT);
        // Sortable
        $sortcol = optional_param('iSortCol_0', 0, PARAM_INT); // Number of column to sort.
        $sortorder = optional_param('sSortDir_0', 'asc', PARAM_ALPHA); // Direction of sort.
        $sortfield = optional_param("mDataProp_{$sortcol}", 'id', PARAM_TEXT); // Get column name.

        $return = "";
        try {
            $report = "activity";
            $element = "studentList";
            $response = \local_xray\local\api\wsapi::courseelement($this->courseid,
                $element,
                $report,
                null,
                '',
                '',
                $start,
                $count,
                $sortfield,
                $sortorder);

            if (!$response) {
                // Fail response of webservice.
                \local_xray\local\api\xrayws::instance()->print_error();
            } else {
                $data = array();
                if (!empty($response->data)) {
                    $activityreportind = get_string('activityreportindividual', $this->component);
                    foreach ($response->data as $row) {

                        $r = new stdClass();
                        $r->action = "";
                        if (has_capability('local/xray:activityreportindividual_view', $PAGE->context)) {
                            // Url for activityreportindividual.
                            $url = new moodle_url("/local/xray/view.php",
                                array("controller" => "activityreportindividual",
                                    "courseid" => $this->courseid,
                                    "userid" => $row->participantId->value
                                ));
                            $r->action = html_writer::link($url, '', array("class" => "icon_activityreportindividual",
                                "title" => $activityreportind,
                                "target" => "_blank"));
                        }
                        // Format of response for columns.
                        if (!empty($response->columnOrder)) {
                            foreach ($response->columnOrder as $column) {
                                $r->{$column} = '';
                                if (isset($row->{$column}->value)) {
                                    /* @var local_xray_renderer $localxrayrenderer */
                                    $localxrayrenderer = $PAGE->get_renderer('local_xray');
                                    switch ($column) {
                                        case 'timeOnTask':
                                            $r->{$column} = $localxrayrenderer->minutes_to_hours($row->{$column}->value);
                                            break;
                                        case 'weeklyRegularity':
                                            $r->{$column} = $localxrayrenderer->set_category_regularly($row->{$column}->value);
                                            break;
                                        default:
                                            $r->{$column} = $row->{$column}->value;
                                    }
                                }
                            }
                        }
                        $data[] = $r;
                    }
                }
                // Provide count info to table.
                $return["recordsFiltered"] = $response->itemCount;
                $return["recordsTotal"] = $response->itemCount;
                $return["data"] = $data;
            }
        } catch (Exception $e) {
            // Error, return invalid data, and pluginjs will show error in table.
            $return["data"] = "-";
        }

        return json_encode($return);
    }

    /**
     * Json for table first login non starters.
     *
     */
    public function jsonfirstloginnonstarters_action() {
        // Pager.
        $count = optional_param('iDisplayLength', 10, PARAM_INT);
        $start = optional_param('iDisplayStart', 0, PARAM_INT);
        // Sortable
        $sortcol   = optional_param('iSortCol_0', 0, PARAM_INT); // Number of column to sort.
        $sortorder = optional_param('sSortDir_0', 'asc', PARAM_ALPHA); // Direction of sort.
        $sortfield = optional_param("mDataProp_{$sortcol}", 'id', PARAM_TEXT); // Get column name.

        $return = "";

        try {
            $report = "firstLogin";
            $element = "nonStarters";
            $response = \local_xray\local\api\wsapi::courseelement($this->courseid,
                $element,
                $report,
                null,
                '',
                '',
                $start,
                $count,
                $sortfield,
                $sortorder);
            if (!$response) {
                // Fail response of webservice.
                \local_xray\local\api\xrayws::instance()->print_error();

            } else {
                $data = array();
                if (!empty($response->data)) {
                    // Format of response for columns.
                    foreach ($response->data as $row) {

                        // This report has not specified columnOrder.
                        if (!empty($response->columnHeaders) && is_object($response->columnHeaders)) {
                            $r = new stdClass();
                            $c = get_object_vars($response->columnHeaders);
                            foreach ($c as $id => $name) {
                                $r->{$id} = (isset($row->{$id}->value) ? $row->{$id}->value : '');
                            }
                            $data[] = $r;
                        }
                    }
                }

                // Provide info to table.
                $return["recordsFiltered"] = $response->itemCount;
                $return["recordsTotal"] = $response->itemCount;
                $return["data"] = $data;
            }
        } catch (Exception $e) {
            // Error, return invalid data, and pluginjs will show error in table.
            $return["data"] = "-";
        }

        return json_encode($return);
    }
}