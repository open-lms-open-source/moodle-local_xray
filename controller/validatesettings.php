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
 * Xray course listing API controller
 *
 * @package   local_xray
 * @copyright Copyright (c) 2016 Open LMS (https://www.openlms.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use local_xray\local\api\validationaws;

require_once($CFG->dirroot.'/local/mr/framework/controller.php');

/**
 * Xray course listing API controller
 *
 * @author    David Castro
 * @package   local_xray
 * @copyright Copyright (c) 2016 Open LMS (https://www.openlms.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_xray_controller_validatesettings extends mr_controller {

    /**
     *
     */
    public function view_action() {
        echo $this->ajax_notfound_response();
    }

    /**
     * Defines Json reponse headers
     */
    private function define_json_headers() {
        if (!defined('AJAX_CRIPT') && !defined('NO_DEBUG_DISPLAY')) {
            define('AJAX_SCRIPT', true);
            define('NO_DEBUG_DISPLAY', true);
        }
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
    }

    /**
     * Execute the validation check
     */
    public function check_action() {
        $this->define_json_headers();

        $checkkey = required_param('check', PARAM_ALPHANUMEXT);
        $checks = get_class_methods('local_xray\local\api\validationaws');
        $prefix = 'check_';

        $checkmethod = $prefix.$checkkey;
        if (in_array($checkmethod, $checks)) {
            $checkresult = validationaws::{$checkmethod}();
            echo $this->process_response($checkresult);
        } else {
            echo $this->ajax_notfound_response();
        }
    }

    /**
     * Processes a response for this service
     * @param mixed $res
     * @return string
     */
    private function process_response($res) {
        $result = array();
        if ($res->is_successful()) {
            $result['success'] = true;
        } else {
            $result['success'] = false;
            $result['reasons'] = $res->get_result();
        }
        return json_encode($result);
    }

    /**
     * Generate ajax notfound error
     */
    protected function ajax_notfound_response() {
        header("HTTP/1.0 404 Not Found");
        return $this->process_response(array(get_string('validation_check_not_filled', 'local_xray')));
    }
}
