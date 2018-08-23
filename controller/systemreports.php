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
 * @copyright Copyright (c) 2016 Blackboard Inc. (http://www.blackboardopenlms.com)
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
 * @copyright Copyright (c) 2016 Blackboard Inc. (http://www.blackboardopenlms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_xray_controller_systemreports extends local_xray_controller_reports {

    // Risk Status.
    const XRAYRISKDISABLED = 0;
    const XRAYRISKENABLED = 1;

    public function view_action() {

        global $CFG, $PAGE, $SESSION;

        $PAGE->navbar->add(get_string("navigation_xray", $this->component));
        $PAGE->navbar->add(get_string('systemreports', $this->component), $PAGE->url);

        // The title will be displayed in the X-ray page.
        $this->heading->text = '';

        $output = '';
        try {
            // Get the URL.
            $systemreportsurl  = get_config('local_xray', 'systemreportsurl');
            if (($systemreportsurl === false) || ($systemreportsurl === '')) {
                print_error("error_systemreports_nourl", $this->component);
            }

            // Get the flag.
            $showsystemreports = get_config('local_xray', 'displaysystemreports');
            if (!$showsystemreports) {
                print_error("error_systemreports_disabled", $this->component);
            }

            // We allow cancel authentication in shiny server from config for the first version.
            if (isset($CFG->local_xray_system_reports_cancel_auth) && $CFG->local_xray_system_reports_cancel_auth) {
                // We need to send the clientid.
                $tokenparams = array("cid" => get_config("local_xray", 'xrayclientid'));
            } else {
                $tokenparams = \local_xray\local\api\jwthelper::get_token_params();
                if (!$tokenparams) {
                    // Error to get token for shiny server.
                    print_error("error_systemreports_gettoken", $this->component);
                }
            }

            // Check if Risk is enanled/disabled.
            require_once($CFG->dirroot.'/local/xray/locallib.php');
            $tokenparams["risk"] = self::XRAYRISKENABLED;
            if (local_xray_risk_disabled()) {
                $tokenparams["risk"] = self::XRAYRISKDISABLED;
            }

            /*
             * Check is exist cookie for xray  to use Safari browser.If not exist,we redirect to xray side with param
             * url for create cookie there and from xray side will redirect to Joule again.
             */
            if ((strpos($_SERVER['HTTP_USER_AGENT'], 'Safari')) && !isset($SESSION->xray_cookie_systemreport)) {
                $tokenparams["url"] = $this->url->out(false); // Add page to redirect from xray server.
                $url = new moodle_url($systemreportsurl."/auth", $tokenparams);
                $SESSION->xray_cookie_systemreport = true;
                redirect($url);
            }

            // The param jouleurl is required in shiny server to add link to each report of joule side.
            $tokenparams["jouleurl"] = $CFG->wwwroot;
            $url = new moodle_url($systemreportsurl, $tokenparams);
            $output .= $this->output->print_iframe_systemreport($url);

        } catch (Exception $e) {
            get_report_failed::create_from_exception($e, $this->get_context(), $this->name)->trigger();
            $output = $this->print_error('error_xray', $e->getMessage());
        }

        return $output;
    }
}
