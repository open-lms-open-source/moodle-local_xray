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
    $plugin = 'local_xray';
    $settings = new admin_settingpage($plugin, new lang_string('pluginname', $plugin));

    // Xray url webservice
    $settings->add( new admin_setting_configtext("{$plugin}/xrayurl",
                                              new lang_string("xrayurl", $plugin),
                                              new lang_string("xrayurl_desc", $plugin),
                                              '', PARAM_URL));

    // Xray user webservice
    $settings->add( new admin_setting_configtext("{$plugin}/xrayusername",
                                              new lang_string("xrayusername", $plugin),
                                              new lang_string("xrayusername_desc", $plugin),
                                              '', PARAM_TEXT));
    // Xray password webservice
    $settings->add( new admin_setting_configtext("{$plugin}/xraypassword",
                                              new lang_string("xraypassword", $plugin),
                                              new lang_string("xraypassword_desc", $plugin),
                                              '', PARAM_TEXT));

    // Xray client identifier webservice
    $settings->add( new admin_setting_configtext("{$plugin}/xrayclientid",
                                              new lang_string("xrayclientid", $plugin),
                                              new lang_string("xrayclientid_desc", $plugin),
                                              '', PARAM_TEXT));

    // Configuration and credentials for accessing Xray S3 bucket
    $settings->add( new admin_setting_heading("{$plugin}/xrayawsheading",
                                              new lang_string("xrayawsheading", $plugin),
                                              new lang_string("xrayawsheading_desc", $plugin)));

    $settings->add( new admin_setting_configcheckbox("{$plugin}/enablesync",
                                                new lang_string("enablesync", $plugin),
                                                new lang_string("enablesync_desc", $plugin),
                                                '0'));

    $settings->add( new admin_setting_configtext("{$plugin}/awskey",
                                                 new lang_string("awskey", $plugin),
                                                 new lang_string("awskey_desc", $plugin),
                                                 '', PARAM_TEXT));

    $settings->add( new admin_setting_configtext("{$plugin}/awssecret",
                                                 new lang_string("awssecret", $plugin),
                                                 new lang_string("awssecret_desc", $plugin),
                                                 '', PARAM_TEXT));

    $settings->add( new admin_setting_configtext("{$plugin}/s3bucket",
                                                 new lang_string("s3bucket", $plugin),
                                                 new lang_string("s3bucket_desc", $plugin),
                                                 '', PARAM_TEXT));

    $ADMIN->add('localplugins', $settings);
}
