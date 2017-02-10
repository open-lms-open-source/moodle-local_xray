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
use local_xray\local\api\course_manager;

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

    /**
     * Discussion id.
     * @var integer
     */
    private $d;

    public function init() {
        $this->cmid = required_param('cmid', PARAM_INT); // Cmid of forum/hsuforum.
        $this->forumid = required_param('forum', PARAM_INT); // Id of forum/hsuforum.
        $this->d = optional_param('d', null, PARAM_INT); // Id of discussion.
        $this->url->param("cmid", $this->cmid);
        $this->url->param("forum", $this->forumid);
        if (!empty($this->d)) {
            $this->url->param("d", $this->d);
        }

    }

    /**
     * View Discussion individual forum.
     * We have support for forum an hsuforum in same controller.
     * In xray side, they have different url for each type(forum and advancedforum).
     * This controller identify forum type and get correct report.
     */
    public function view_action() {
        global $CFG, $PAGE, $DB;

        // Add title to breadcrumb.
        $plugins = \core_plugin_manager::instance()->get_plugins_of_type('mod');
        $modulename = 'forum';
        if (array_key_exists('hsuforum', $plugins)) {
            // Get module name and forum/hsuforum id.
            $sqlmodule = "SELECT m.name
                            FROM {course_modules} cm
                            INNER JOIN {modules} m ON m.id = cm.module
                            WHERE cm.id = :cmid";
            $params = array('cmid' => $this->cmid);
            $module = $DB->get_record_sql($sqlmodule, $params);
            $modulename = $module->name;
        }

        // Get and show forun name in navbar.
        $forumname = $DB->get_field($modulename, 'name', array("id" => $this->forumid));
        $PAGE->navbar->add(format_string($forumname), new moodle_url("/mod/".$modulename."/view.php",
            array("id" => $this->cmid)));

        if (!empty($this->d)) {
            // Get discussion name.
            $discussion = $DB->get_field($modulename."_discussions", 'name', array("id" => $this->d));
            $PAGE->navbar->add(format_string($discussion), new moodle_url("/mod/".$modulename."/discuss.php",
                array("d" => $this->d)));
        }
        $PAGE->navbar->add(get_string("navigation_xray", $this->component));
        $PAGE->navbar->add($PAGE->title);

        $this->message_reports_disabled();
        $this->validate_course_status();
        $this->addiconhelp();
        $output = "";

        // If the forum has no posts, display a message.
        $forumdiscussions = $modulename.'_discussions';
        $forumposts = $modulename.'_posts';
        $sqlposts = "SELECT fp.id
                      FROM {{$forumdiscussions}} fd
	                  INNER JOIN {{$forumposts}} fp ON fd.id = fp.discussion
		              WHERE fd.forum = :forumid";
        $params = array('forumid' => $this->forumid);
        if (!$DB->get_records_sql($sqlposts, $params)) {
            $output .= $this->output->notification(get_string("xray_course_report_empty", $this->component));
            return $output;
        }

        try {
            $report = "discussion";
            $forumnameinxray = "forum";
            if ($modulename == "hsuforum") {
                // Hsforum is called advancedforum in xray side.
                $forumnameinxray = "advancedforum";
            }

            $response = \local_xray\local\api\wsapi::course($this->courseid, $report, "{$forumnameinxray}/{$this->forumid}");
            if (!$response) {
                // Fail response of webservice.
                \local_xray\local\api\xrayws::instance()->print_error();
            } else {

                $output .= $this->print_top();

                // If report is empty, show only message. No graphs/tables empties.
                if (isset($response->elements->reportHeader->emptyReport) &&
                    $response->elements->reportHeader->emptyReport) {
                    $output .= $this->output->notification(get_string("xray_course_report_empty", $this->component));
                    return $output;
                }

                // Show graphs.
                $paramsaccessible = array("cmid" => $this->cmid, "forum" => $this->forumid);
                if (!empty($this->d)) {
                    $paramsaccessible["d"] = $this->d;
                }

                $output .= $this->output->inforeport($response->reportdate);
                $output .= $this->output->show_graph("wordHistogram",
                    $response->elements->wordHistogram,
                    $response->id,
                    $paramsaccessible);
                $output .= $this->output->show_graph("socialStructure",
                    $response->elements->socialStructure,
                    $response->id,
                    $paramsaccessible);
                $output .= $this->output->show_graph("wordcloud",
                    $response->elements->wordcloud,
                    $response->id,
                    $paramsaccessible);
            }
        } catch (Exception $e) {
            get_report_failed::create_from_exception($e, $this->get_context(), $this->name)->trigger();
            $output = $this->print_error('error_xray', $e->getMessage());
        }

        return $output;
    }

    /**
     * Print Footer.
     */

    public function print_footer() {
        parent::print_footer();
        // Call Report Viewed Event.
        $data = array(
            'context' => $this->get_context(), 'relateduserid' => $this->userid,
            'other' => array(
                'reportname' => $this->name,
                'cmid' => $this->cmid,
                'forumid' => $this->forumid
            )
        );
        $this->trigger_report_viewed_event($data);
    }
}
