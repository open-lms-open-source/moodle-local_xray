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
        $PAGE->navbar->add($forumname, new moodle_url("/mod/forum/view.php",
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
                $reportcontroller = $this->url->get_param('controller');
                $reports = local_xray_navigationlinks($PAGE, $PAGE->context);
                $output .= $this->output->inforeport($response->reportdate, $reportcontroller, $reports);
                $output .= $this->wordshistogram($response->elements->wordHistogram);
                $output .= $this->socialstructure($response->elements->socialStructure);
                $output .= $this->wordcloud($response->elements->wordcloud);
            }
        } catch (Exception $e) {
            $output = $this->print_error('error_xray', $e->getMessage());
        }

        return $output;
    }

    /**
     * Words Histogram
     * @param object $element
     * @return string
     */
    private function wordshistogram($element) {
        $output = "";
        $output .= $this->output->discussionreportindividualforum_wordshistogram($element);
        return $output;
    }

    /**
     * Social Structure
     * @param object $element
     * @return string
     */
    private function socialstructure($element) {
        $output = "";
        $output .= $this->output->discussionreportindividualforum_socialstructure($element);
        return $output;
    }

    /**
     * Wordcloud
     * @param object $element
     * @return string
     */
    private function wordcloud($element) {
        $output = "";
        $output .= $this->output->discussionreportindividualforum_wordcloud($element);
        return $output;
    }
}
