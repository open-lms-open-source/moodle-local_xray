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

    $settings->add( new admin_setting_configtext("{$plugin}/xrayadminserver",
                                                 new lang_string("xrayadminserver", $plugin),
                                                 new lang_string("xrayadminserver_desc", $plugin),
                                                 '', PARAM_URL));

    $settings->add( new admin_setting_configtext("{$plugin}/xrayadmin",
                                                 new lang_string("xrayadmin", $plugin),
                                                 new lang_string("xrayadmin_desc", $plugin),
                                                 '', PARAM_TEXT));

    $settings->add( new admin_setting_configtext("{$plugin}/xrayadminkey",
                                                 new lang_string("xrayadminkey", $plugin),
                                                 new lang_string("xrayadminkey_desc", $plugin),
                                                 '', PARAM_TEXT));

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

    // List of regions taken from http://docs.aws.amazon.com/general/latest/gr/rande.html#s3_region
    $choices = array(
        'us-east-1'      => new lang_string('useast1'     , $plugin),
        'us-west-2'      => new lang_string('uswest2'     , $plugin),
        'us-west-1'      => new lang_string('uswest1'     , $plugin),
        'eu-west-1'      => new lang_string('euwest1'     , $plugin),
        'eu-central-1'   => new lang_string('eucentral1'  , $plugin),
        'ap-southeast-1' => new lang_string('apsoutheast1', $plugin),
        'ap-southeast-2' => new lang_string('apsoutheast2', $plugin),
        'ap-northeast-1' => new lang_string('apnortheast1', $plugin),
        'sa-east-1'      => new lang_string('saeast1'     , $plugin),
    );

    $settings->add( new admin_setting_configselect("{$plugin}/s3bucketregion",
                                                   new lang_string("s3bucketregion", $plugin),
                                                   new lang_string("s3bucketregion_desc", $plugin),
                                                   'us-east-1', $choices));


    $settings->add( new admin_setting_configexecutable("{$plugin}/packertar",
                                                       new lang_string("packertar", $plugin),
                                                       new lang_string("packertar_desc", $plugin),
                                                       ''));

    $settings->add( new admin_setting_configexecutable("{$plugin}/packerzip",
                                                       new lang_string("packerzip", $plugin),
                                                       new lang_string("packerzip_desc", $plugin),
                                                       ''));

    $settings->add( new admin_setting_configtext("{$plugin}/exportlocation",
                                                 new lang_string("exportlocation", $plugin),
                                                 new lang_string("exportlocation_desc", $plugin),
                                                 '', PARAM_TEXT));

    $ADMIN->add('localplugins', $settings);
}
