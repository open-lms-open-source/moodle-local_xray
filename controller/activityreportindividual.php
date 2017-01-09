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
use local_xray\event\get_report_failed;

/**
 * Activity Report Individual
 *
 * @package   local_xray
 * @author    Pablo Pagnone
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_xray_controller_activityreportindividual extends local_xray_controller_reports {

    public function init() {
        $this->userid = required_param('userid', PARAM_INT);
        $this->url->param("userid", $this->userid);
    }

    public function view_action() {
        global $PAGE, $DB;

        $PAGE->navbar->add(get_string("navigation_xray", $this->component));
        // Add nav to return to activityreport.
        $PAGE->navbar->add(get_string("activityreport", $this->component),
            new moodle_url('/local/xray/view.php',
                array("controller" => "activityreport",
                    "courseid" => $this->courseid)));
        $PAGE->navbar->add($PAGE->title);

        $this->validate_course();
        $this->addiconhelp();

        $output = '';
        try {
            $report = "activity";
            $response = \local_xray\local\api\wsapi::course($this->courseid, $report, $this->userid);
            if (!$response) {
                // Fail response of webservice.
                \local_xray\local\api\xrayws::instance()->print_error();

            } else {
                // Always show menu.
                $output .= $this->print_top();

                // If report is empty, show only message. No graphs/tables empties.
                if (isset($response->elements->reportHeader->emptyReport) &&
                    $response->elements->reportHeader->emptyReport) {
                    $output .= $this->output->notification(get_string("xray_course_report_empty", $this->component));
                    return $output;
                }

                $output .= $this->output->inforeport($response->reportdate,
                    $DB->get_record('user', array('id' => $this->userid)));

                $extraparamaccessible = array("userid" => $this->userid);
                $output .= $this->output->show_graph("activityLevelTimeline",
                    $response->elements->activityLevelTimeline, $response->id, $extraparamaccessible);
                $output .= $this->output->show_graph("barplotOfActivityWholeWeek",
                    $response->elements->barplotOfActivityWholeWeek, $response->id, $extraparamaccessible);
                $output .= $this->output->show_graph("barplotOfActivityByWeekday",
                    $response->elements->barplotOfActivityByWeekday, $response->id, $extraparamaccessible);
            }
        } catch (Exception $e) {
            get_report_failed::create_from_exception($e, $this->get_context(), $this->name)->trigger();
            $output = $this->print_error('error_xray', $e->getMessage());
        }

        return $output;
    }
}
