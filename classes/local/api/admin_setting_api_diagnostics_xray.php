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
 * Custom setting for running api diagnostics
 *
 * @package   local_xray
 * @author    David Castro
 * @copyright Copyright (c) 2016 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xray\local\api;

defined('MOODLE_INTERNAL') || die();

/**
 * Class admin_setting_api_diagnostics_xray
 *
 * @package local_xray
 * @author    David Castro
 * @copyright Copyright (c) 2016 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_setting_api_diagnostics_xray extends \admin_setting {

    /**
     * Builds a settings button for testing valid xray api, aws and compression params
     */
    public function __construct() {
        global $PAGE;

        if ($PAGE->pagetype === 'admin-setting-local_xray_global') {
            $this->require_js_libs();
        }

        parent::__construct('local_xray/apidiagnostics', get_string('test_api_label', 'local_xray'),
            get_string('test_api_description', 'local_xray'), '');
    }

    /**
     * REquires the JavaScript libraries
     * @global \moodle_page $PAGE
     * @global \stdClass $CFG
     */
    private function require_js_libs() {
        global $PAGE, $CFG;

        // Load Jquery.
        $PAGE->requires->jquery();
        $PAGE->requires->jquery_plugin('ui');
        $PAGE->requires->jquery_plugin('ui-css');
        // Load specific js for validation.
        $PAGE->requires->jquery_plugin('local_xray-validate_api_aws_compress', 'local_xray');

        // Build the check list.
        $checks = get_class_methods('local_xray\local\api\validationaws');
        $apimsgkeys = [];
        $prefix = 'check_';
        foreach ($checks as $check) {
            $apimsgkeys[] = substr($check, strlen($prefix));
        }

        $data = array(
            'lang_strs' => [ // Language specific strings.
                'connectionfailed' => get_string('connectionfailed', 'local_xray'),
                'connectionverified' => get_string('connectionverified', 'local_xray'),
                'verifyingapi' => get_string('verifyingapi', 'local_xray'),
                'connectionstatusunknown' => get_string('connectionstatusunknown', 'local_xray'),
                'test_api_ws_connect' => get_string('test_api_ws_connect', 'local_xray'),
                'test_api_s3_bucket' => get_string('test_api_s3_bucket', 'local_xray'),
                'test_api_compress' => get_string('test_api_compress', 'local_xray'),
                'validate_service_response' => get_string('validate_service_response', 'local_xray')
            ],
            'watch_fields' => [ // Fields to watch for changes.
                'xrayurl', 'xrayusername', 'xraypassword', 'xrayclientid',
                'enablesync', 'awskey', 'awssecret', 's3bucket', 's3bucketregion', 's3protocol', 's3uploadretry',
                'enablepacker', 'packertar', 'exportlocation'
            ],
            'www_root' => $CFG->wwwroot, // Root directory.
            'api_msg_keys' => $apimsgkeys // Check list.

        );

        $strdata = [json_encode($data)];
        // Initialize js.
        $PAGE->requires->js_init_call('validate_api_aws_compress', $strdata);
    }

    /**
     * Always returns true
     * @return bool Always returns true
     */
    public function get_setting() {
        return true;
    }

    /**
     * Always returns true
     * @return bool Always returns true
     */
    public function get_defaultsetting() {
        return true;
    }

    /**
     * Never write settings
     * @return string Always returns an empty string
     */
    public function write_setting($data) {
        // Do not write any setting.
        return '';
    }

    /**
     * Prints the button to be used and the dialog window where the information will be shown
     * @return string Returns an HTML string
     */
    public function output_html($data, $query='') {
        global $OUTPUT;

        $context = (object) [
            'id' => $this->get_id(),
            'name' => $this->get_full_name(),
            'problemnotif' => $OUTPUT->notification('', 'error'),
            'successnotif' => $OUTPUT->notification('', 'success'),
            'messagenotif' => $OUTPUT->notification('', 'warning'),
        ];
        $element = $OUTPUT->render_from_template('local_xray/setting_apidiagnostics', $context);

        return format_admin_setting($this, $this->visiblename, $element, $this->description, true, '', null, $query);
    }
}
