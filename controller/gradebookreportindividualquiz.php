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
 * Report Gradebook Individual Quiz
 *
 * @package   local_xray
 * @author    Pablo Pagnone
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_xray_controller_gradebookreportindividualquiz extends local_xray_controller_reports {

    /**
     * Quiz id
     * @var int
     */
    private $quizid;

    /**
     * Course module id.
     * @var int
     */
    private $cmid;

    /**
     * @return void
     * @throws coding_exception
     */
    public function init() {
        parent::init();
        $this->cmid = required_param('cmid', PARAM_INT); // Cmid of quiz.
        $this->quizid = required_param('quiz', PARAM_INT);
    }

    public function view_action() {
        global $PAGE, $DB;

        // Add title to breadcrumb.
        $quizname = $DB->get_field('quiz', 'name', array("id" => $this->quizid));
        $PAGE->navbar->add($quizname, new moodle_url("/mod/quiz/view.php",
            array("id" => $this->cmid)));
        $PAGE->navbar->add($PAGE->title);
        $output = "";
        try {
            $report = "grades";
            $response = \local_xray\local\api\wsapi::course($this->courseid, $report, "quiz/" . $this->quizid);
            if (!$response) {
                // Fail response of webservice.
                \local_xray\local\api\xrayws::instance()->print_error();
            }
        } catch (Exception $e) {
            $output = $this->print_error('error_xray', $e->getMessage());
        }

        return $output;
    }
}
