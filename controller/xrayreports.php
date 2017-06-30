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
 * Xray Reports controller.
 *
 * @package   local_xray
 * @copyright Copyright (c) 2017 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/xray/controller/reports.php');

use local_xray\event\get_report_failed;

/**
 * Xray integration Reports Controller
 *
 * @package   local_xray
 * @author    Pablo Pagnone
 * @author    German Vitale
 * @copyright Copyright (c) 2017 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_xray_controller_xrayreports extends local_xray_controller_reports {

    /**
     * Report name.
     * @var String
     */
    protected $reportname;

    /**
     * Old report name.
     * @var String
     */
    protected $oldreportname;

    /**
     * Forum id.
     * @var String
     */
    protected $forumid = 0;

    /**
     * Show id.
     * @var String
     */
    protected $showid = 0;

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

    /**
     * Init
     */
    public function init() {
        global $CFG;
        require_once($CFG->dirroot.'/local/xray/locallib.php');

        $this->reportname = required_param("name", PARAM_ALPHANUMEXT);
        $this->oldreportname = local_xray_name_conversion($this->reportname, true);

        if ($this->reportname == 'activityindividual' || $this->reportname == 'discussionindividual') {
            $this->showid = required_param("showid", PARAM_INT); // User id viewed.
        } else if ($this->reportname == 'discussionindividualforum') {
            $this->forumid = required_param("forumid", PARAM_INT); // Forum id viewed.
            $this->cmid = required_param('cmid', PARAM_INT); // Cmid of forum/hsuforum.
            $this->d = optional_param('d', null, PARAM_INT); // Id of discussion.
        }
    }

    /**
     * Require capability.
     */
    public function require_capability() {
        require_capability("{$this->plugin}:{$this->oldreportname}_view", $this->get_context());
    }

    public function view_action() {

        global $CFG, $COURSE, $PAGE, $SESSION, $USER, $OUTPUT, $DB;

        // Add title.
        $PAGE->set_title(get_string($this->oldreportname, $this->component));
        // Add params.
        $jouleparams = array('name' => $this->reportname);
        // Url in joule side.
        $this->url->params($jouleparams);
        // The title will be displayed in the X-ray page.
        $this->heading->text = '';

        require_once($CFG->dirroot.'/local/xray/locallib.php');
        if (!local_xray_reports()) {
            return $OUTPUT->notification(get_string("noaccessxrayreports", $this->component), 'error');
        }

        $output = '';
        try {
            // Menu (Always show menu).
            $output .= $this->print_top();
            // Get the URL.
            $xrayreportsurl  = get_config('local_xray', 'xrayreportsurl');
            if (($xrayreportsurl === false) || ($xrayreportsurl === '')) {
                print_error("error_xrayreports_nourl", $this->component);
            }
            // Get the Client Id.
            $domain = get_config('local_xray', 'xrayclientid');
            if (($domain === false) || ($domain === '')) {
                print_error("error_xrayclientid", $this->component);
            }

            // We allow cancel authentication in shiny server from config for the first version.
            if (isset($CFG->local_xray_course_reports_cancel_auth) && $CFG->local_xray_course_reports_cancel_auth) {
                // We need to send the clientid.
                $xrayparams = array("cid" => get_config("local_xray", 'xrayclientid'));
            } else {
                $xrayparams = \local_xray\local\api\jwthelper::get_token_params();
                if (!$xrayparams) {
                    // Error to get token for shiny server.
                    print_error("error_xrayreports_gettoken", $this->component);
                }
            }
            /*
             * Check if exist cookie for xray to use Safari browser.If not exist,we redirect to xray side with param
             * url for create cookie there and from xray side will redirect to Joule again.
             */
            if ((strpos($_SERVER['HTTP_USER_AGENT'], 'Safari')) && !isset($SESSION->xray_cookie_xrayreports)) {
                $xrayparams["url"] = $this->url->out(false); // Add page to redirect from xray server.
                $url = new moodle_url($xrayreportsurl."/auth", $xrayparams);
                $SESSION->xray_cookie_xrayreports = true;
                redirect($url);
            }
            // Report name. Fix reportname for X-Ray params.
            $xrayparams['name'] = $this->reportname;
            // The param jouleurl is required in shiny server to add link to each report of joule side.
            $xrayparams["jouleurl"] = $CFG->wwwroot;
            // Course id.
            $xrayparams["courseid"] = $COURSE->id;
            // User id: Instructor/Admin who is seeing the report.
            $xrayparams["uid"] = $USER->id;
            // Navbar and extra params.
            switch ($this->reportname) {
                case 'activityindividual':
                    // Params.
                    $xrayparams["name"] = "activity";
                    if ($this->showid) {
                        $jouleparams["showid"] = $this->showid;
                        $xrayparams["showid"] = $this->showid;
                    }
                    // Navbar.
                    $PAGE->navbar->add(get_string("navigation_xray", $this->component));
                    // Add nav to return to activityreport.
                    $PAGE->navbar->add(get_string("activityreport", $this->component),
                        new moodle_url('/local/xray/view.php',
                            array("controller" => "xrayreports", "name" => "activity",
                                "courseid" => $this->courseid)));
                    $PAGE->navbar->add($PAGE->title);
                    break;
                case 'discussionindividual':
                    // Params.
                    $xrayparams["name"] = "discussion";
                    if ($this->showid) {
                        $jouleparams["showid"] = $this->showid;
                        $xrayparams["showid"] = $this->showid;
                    }
                    // Navbar.
                    $PAGE->navbar->add(get_string("navigation_xray", $this->component));
                    // Add nav to return to discussionreport.
                    $PAGE->navbar->add(get_string("discussionreport", $this->component),
                        new moodle_url('/local/xray/view.php',
                            array("controller" => "xrayreports", "name" => "discussion",
                                "courseid" => $this->courseid)));
                    $PAGE->navbar->add($PAGE->title);
                    break;
                case 'discussionindividualforum':
                    // Params.
                    $xrayparams["name"] = "discussion";
                    if ($this->forumid) {
                        $jouleparams["forumid"] = $this->forumid;
                        $xrayparams["forumid"] = $this->forumid;
                    }
                    // Navbar.
                    // Add title to breadcrumb.
                    /** @var array $plugins */
                    $plugins = \core_plugin_manager::instance()->get_plugins_of_type('mod');
                    $modulename = 'forum';
                    if (array_key_exists('hsuforum', $plugins)) {
                        // Get module name and forum/hsuforum id.
                        $sqlmodule = "SELECT m.name
                            FROM {course_modules} cm
                            INNER JOIN {modules} m ON m.id = cm.module
                            WHERE cm.id = :cmid";
                        $params = array('cmid' => $this->cmid);
                        if ($module = $DB->get_record_sql($sqlmodule, $params)) {
                            $modulename = $module->name;
                            if ($modulename == 'forum') {
                                $xrayparams["forumtype"] = "classic";
                            } else if ($modulename == 'hsuforum') {
                                $xrayparams["forumtype"] = "hsu";
                            }
                        }
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
                    break;
                default:
                    $PAGE->navbar->add(get_string("navigation_xray", $this->component));
                    $PAGE->navbar->add(get_string($this->oldreportname, $this->component), $this->url);
            }
            $url = new moodle_url($xrayreportsurl, $xrayparams);
            $output .= $this->output->print_iframe_systemreport($url);
        } catch (Exception $e) {
            get_report_failed::create_from_exception($e, $this->get_context(), $this->name)->trigger();
            $output = $this->print_error('error_xray', $e->getMessage());
        }

        return $output;
    }
}