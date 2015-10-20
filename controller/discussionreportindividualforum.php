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
 * Report Discussion Individual forum.
 *
 * @package   local_xray
 * @author    Pablo Pagnone
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_xray_controller_discussionreportindividualforum extends local_xray_controller_reports {

    /**
     * Forum id
     * @var integer
     */
    private $forumid;

    /**
     * Course module id.
     * @var integer
     */
    private $cmid;

    public function init() {
        parent::init();
        $this->cmid = required_param('cmid', PARAM_INT); // Cmid of forum.
        $this->forumid = required_param('forum', PARAM_INT);
    }

    public function view_action() {
        global $PAGE, $DB;

        // Add title to breadcrumb.
        $forumname = $DB->get_field('forum', 'name', array("id" => $this->forumid));
        $PAGE->navbar->add(format_string($forumname), new moodle_url("/mod/forum/view.php",
            array("id" => $this->cmid)));
        $PAGE->navbar->add($PAGE->title);
        $output = "";

        try {
            $report = "discussion";
            $response = \local_xray\local\api\wsapi::course($this->courseid, $report, "forum/" . $this->forumid);
            if (!$response) {
                // Fail response of webservice.
                \local_xray\local\api\xrayws::instance()->print_error();
            } else {
                // Show graphs.
                $output .= $this->print_top();
                $output .= $this->output->inforeport($response->reportdate);
                $output .= $this->output->show_on_lightbox("wordHistogram", $response->elements->wordHistogram);
                $output .= $this->output->show_on_lightbox("socialStructure", $response->elements->socialStructure);
                $output .= $this->output->show_on_lightbox("wordcloud", $response->elements->wordcloud);
            }
        } catch (Exception $e) {
            $output = $this->print_error('error_xray', $e->getMessage());
        }

        return $output;
    }
}
