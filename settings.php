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

defined('MOODLE_INTERNAL') || die;

/**
 * Local xray settings
 *
 * @author Pablo Pagnone
 * @author German Vitale
 * @package local_xray
 */

/* @var $ADMIN admin_root */
if ($hassiteconfig) { // needs this condition or there is error on login page

    $settings = new admin_settingpage('local_xray', new lang_string('pluginname', 'local_xray'));

    // Xray url webservice
    $settings->add(new admin_setting_configtext("xrayurl",
        new lang_string("xrayurl", "local_xray"),
        new lang_string("xrayurl_desc", "local_xray"),
        ""));

    // Xray user webservice
    $settings->add(new admin_setting_configtext("xrayusername",
        new lang_string("xrayusername", "local_xray"),
        new lang_string("xrayusername_desc", "local_xray"),
        ""));
    // Xray password webservice
    $settings->add(new admin_setting_password_unmask_encrypted("xraypassword",
        new lang_string("xraypassword", "local_xray"),
        new lang_string("xraypassword_desc", "local_xray"),
        ""));

    // Xray client identifier webservice
    $settings->add(new admin_setting_configtext("xrayclientid",
        new lang_string("xrayclientid", "local_xray"),
        new lang_string("xrayclientid_desc", "local_xray"),
        ""));

    $reports = local_xray_reports_utils::list_reports();
    if (!empty($reports)) {
        $r = array();
        foreach ($reports as $reportid => $reportname) {
            $r[$reportid] = $reportname;
        }

        $settings->add(new admin_setting_configmulticheckbox("enabledreports",
            new lang_string("enabledreports", "local_xray"),
            new lang_string("enabledreports_desc", "local_xray"),
            "",
            $r));
    }

    $ADMIN->add('localplugins', $settings);
}
