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
 * @copyright Copyright (c) 2016 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/* @var stdClass $CFG */
require_once($CFG->dirroot . '/local/xray/controller/reports.php');
use local_xray\event\get_report_failed;
/**
 * Xray integration Reports Controller
 *
 * @package   local_xray
 * @author    Pablo Pagnone
 * @author    German Vitale
 * @copyright Copyright (c) 2016 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_xray_controller_xrayreports extends local_xray_controller_reports {

    /**
     * Report name.
     * @var String
     */
    private $reportname;

    /**
     * Init
     */
    public function init() {
        $this->reportname = required_param("name", PARAM_ALPHANUMEXT);
        if ($this->reportname == 'discussionreportindividual' || $this->reportname == 'activityreportindividual') {
            $this->showid = required_param("showid", PARAM_INT);
        }
        if ($this->reportname == 'discussionreportindividualforum') {
            $this->forumid = required_param("forum", PARAM_INT);
        }
    }

    /**
     * Require capability.
     */
    public function require_capability() {
        if (!local_xray_is_course_enable()) {// TODO test
            $ctx = $this->get_context();
            throw new required_capability_exception($ctx, "{$this->plugin}:{$this->reportname}_view", 'nopermissions', '');
        }
        require_capability("{$this->plugin}:{$this->reportname}_view", $this->get_context());
    }

    public function view_action() {

        global $CFG, $COURSE, $PAGE, $SESSION, $USER, $OUTPUT;

        // Add title.
        $PAGE->set_title(get_string($this->reportname, $this->component));
        // Add params.
        $jouleparams = array('name' => $this->reportname);
        // Report name. Fix reportname for X-Ray params.
        $xrayparams = array('name' => str_replace('report', '', $this->reportname));
        // Forum Activity Report.
        if ($this->forumid) {
            $jouleparams["forum"] = $this->forumid;
            /*
             * In X-ray side, the param forum will be replaced with showid. In joule side, forum id and
             * show id will never be together.
             */
            $xrayparams["showid"] = $this->forumid;
        } else if ($this->showid) { // Student Activity Report or Student Discussion Report.
            $jouleparams["showid"] = $this->showid;
            $xrayparams["showid"] = $this->showid;
        }

        $this->url->params($jouleparams);

        $PAGE->navbar->add(get_string("navigation_xray", $this->component));
        $PAGE->navbar->add(get_string($this->reportname, $this->component), $this->url);

        // The title will be displayed in the X-ray page.
        $this->heading->text = '';

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
                print_error("error_xrayreports_nourl", $this->component);// TODO
            }
            // We allow cancel authentication in shiny server from config for the first version.
            if (isset($CFG->local_xray_system_reports_cancel_auth) && $CFG->local_xray_system_reports_cancel_auth) {
                // We need to send the clientid.
                $tokenparams = array("cid" => get_config("local_xray", 'xrayclientid'));
            } else {
                $tokenparams = \local_xray\local\api\jwthelper::get_token_params();
                if (!$tokenparams) {
                    // Error to get token for shiny server.
                    print_error("error_xrayreports_gettoken", $this->component);// TODO
                }
            }
            $xrayparams = array_merge($xrayparams,$tokenparams);

            /*
             * Check is exist cookie for xray  to use Safari browser.If not exist,we redirect to xray side with param
             * url for create cookie there and from xray side will redirect to Joule again.
             */
            if ((strpos($_SERVER['HTTP_USER_AGENT'], 'Safari')) && !isset($SESSION->xray_cookie_systemreport)) {
                $xrayparams["url"] = $this->url->out(false); // Add page to redirect from xray server.
                $url = new moodle_url($xrayreportsurl."/auth", $xrayparams);
                $SESSION->xray_cookie_systemreport = true;
                redirect($url);
            }
            // The param jouleurl is required in shiny server to add link to each report of joule side.
            $xrayparams["jouleurl"] = $CFG->wwwroot;
            // Course id.
            $xrayparams["courseid"] = $COURSE->id;
            // User id: Instructor/Admin who is seeing the report.
            $xrayparams["uid"] = $USER->id;

            $url = new moodle_url($xrayreportsurl, $xrayparams);
            $output .= $this->output->print_iframe_systemreport($url);
        } catch (Exception $e) {
            get_report_failed::create_from_exception($e, $this->get_context(), $this->name)->trigger();
            $output = $this->print_error('error_xray', $e->getMessage());
        }

        return $output;
    }
}