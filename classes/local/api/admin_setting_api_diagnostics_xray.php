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
class admin_setting_api_diagnostics_xray extends \admin_setting_heading {

    /**
     * Builds a settings button for testing valid xray api, aws and compression params
     */
    public function __construct() {
        global $PAGE;
        
        if($PAGE->pagetype === 'admin-setting-local_xray') {
            $this->require_js_libs();
            $output = $this->print_html_output();
            parent::__construct('apidiagnostics', '', $output, '');
        } else {
            parent::__construct('apidiagnostics', '', '', '');
        }
        
    }
    
    /**
     * REquires the JavaScript libraries
     * @global type $PAGE
     * @global type $CFG
     */
    private function require_js_libs() {
        global $PAGE, $CFG;
        
        // Load Jquery.
        $PAGE->requires->jquery();
        $PAGE->requires->jquery_plugin('ui');
        $PAGE->requires->jquery_plugin('ui-css');
        // Load specific js for validation.
        $PAGE->requires->jquery_plugin('local_xray-validate_api_aws_compress', 'local_xray');
        
        // Build the check list
        $checks = get_class_methods('local_xray\local\api\validationaws');
        $api_msg_keys = [];
        $prefix = 'check_';
        foreach ($checks as $check) {
            $api_msg_keys[] = substr($check, strlen($prefix));
        }
        
        $data = array(
            'lang_strs' => [ // Language specific strings
                'connectionfailed' => get_string('connectionfailed', 'local_xray'),
                'connectionverified' => get_string('connectionverified', 'local_xray'),
                'verifyingapi' => get_string('verifyingapi', 'local_xray'),
                'connectionstatusunknown' => get_string('connectionstatusunknown', 'local_xray'),
                'test_api_ws_connect' => get_string('test_api_ws_connect', 'local_xray'),
                'test_api_s3_bucket' => get_string('test_api_s3_bucket', 'local_xray'),
                'test_api_compress' => get_string('test_api_compress', 'local_xray'),
            ],
            'watch_fields' => [ // Fields to watch for changes
                'xrayurl', 'xrayusername', 'xraypassword', 'xrayclientid',
                'enablesync', 'awskey', 'awssecret', 's3bucket', 's3bucketregion', 's3protocol', 's3uploadretry',
                'enablepacker', 'packertar', 'exportlocation'
            ],
            'www_root' => $CFG->wwwroot, // Root directory
            'api_msg_keys' => $api_msg_keys // Check list
            
        );
        
        $strdata = [json_encode($data)];
        // Initialize js
        $PAGE->requires->js_init_call('validate_api_aws_compress', $strdata);
    }
    
    /**
     * Prints the button to be used and the dialog window where the information will be shown
     * @global type $OUTPUT
     * @return string
     */
    private function print_html_output() {
        global $OUTPUT;

        $o = '<div id="api_diag" title="'.get_string('test_api_action', 'local_xray').'">';
        $o .= '<div class="noticetemplate_problem">'.$OUTPUT->notification('', 'error').'</div>';
        $o .= '<div class="noticetemplate_success">'.$OUTPUT->notification('', 'success').'</div>';
        $o .= '<div class="noticetemplate_message">'.$OUTPUT->notification('', 'warning').'</div>';
        $o .= '<div id="ws_connect-status" class="api-connection-status"></div>';
        $o .= '<div id="s3_bucket-status" class="api-connection-status"></div>';
        $o .= '<div id="compress-status" class="api-connection-status"></div>';
        $o .= '</div>';
        $o .= '<div class="form-item">';
        $o .= '<div class="form-label">';
        $o .= '<label>'.get_string('test_api_label', 'local_xray').'</label>';
        $o .= '</div>';
        $o .= '<div class="form-setting"><input class="form-submit api_diag_btn" type="submit" value="'.get_string('test_api_action', 'local_xray').'"></div>';
        $o .= '<div class="form-description"><p>'.get_string('test_api_description', 'local_xray').'</p></div>';
        $o .= '</div>';
        
        return $o;
    }
}
