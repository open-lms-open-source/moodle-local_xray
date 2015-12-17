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
 * X-Ray global settings page
 *
 * @package   local_xray
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') or die();

/* @var admin_root $ADMIN */
if ($hassiteconfig) {
    $plugin = 'local_xray';
    $settings = new admin_settingpage($plugin, new lang_string('pluginname', $plugin));

    // Xray url webservice.
    $settings->add( new admin_setting_configtext("{$plugin}/xrayurl",
                                                 new lang_string("xrayurl", $plugin),
                                                 new lang_string("xrayurl_desc", $plugin),
                                                 '', PARAM_URL));

    // Xray user webservice.
    $settings->add( new admin_setting_configtext("{$plugin}/xrayusername",
                                                 new lang_string("xrayusername", $plugin),
                                                 new lang_string("xrayusername_desc", $plugin),
                                                 '', PARAM_TEXT));
    // Xray password webservice.
    $settings->add( new admin_setting_configpasswordunmask("{$plugin}/xraypassword",
                                                 new lang_string("xraypassword", $plugin),
                                                 new lang_string("xraypassword_desc", $plugin),
                                                 ''));

    // Xray client identifier webservice.
    $settings->add( new admin_setting_configtext("{$plugin}/xrayclientid",
                                                 new lang_string("xrayclientid", $plugin),
                                                 new lang_string("xrayclientid_desc", $plugin),
                                                 '', PARAM_TEXT));

    // Default time set to 1h.
    $settings->add( new admin_setting_configtime("{$plugin}/curlcache",
                                                 "curlcache_minutes",
                                                 new lang_string("curlcache", $plugin),
                                                 new lang_string("curlcache_desc", $plugin),
                                                 ['h' => 1, 'm' => 0]));

    // Settings for displaying content inline course front page.
    $settings->add( new admin_setting_heading("{$plugin}/xraydisplayheading",
                                              new lang_string("xraydisplayheading", $plugin),
                                              new lang_string("xraydisplayheading_desc", $plugin)));

    $settings->add( new admin_setting_configcheckbox("{$plugin}/displaymenu",
                                                     new lang_string("displaymenu", $plugin),
                                                     new lang_string("displaymenu_desc", $plugin),
                                                     '0'));

    // Configuration and credentials for accessing Xray S3 bucket.
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

    $settings->add( new admin_setting_configpasswordunmask("{$plugin}/xrayadminkey",
                                                 new lang_string("xrayadminkey", $plugin),
                                                 new lang_string("xrayadminkey_desc", $plugin),
                                                 ''));

    $settings->add( new admin_setting_configpasswordunmask("{$plugin}/awskey",
                                                 new lang_string("awskey", $plugin),
                                                 new lang_string("awskey_desc", $plugin),
                                                 ''));

    $settings->add( new admin_setting_configpasswordunmask("{$plugin}/awssecret",
                                                 new lang_string("awssecret", $plugin),
                                                 new lang_string("awssecret_desc", $plugin),
                                                 ''));

    $settings->add( new admin_setting_configtext("{$plugin}/s3bucket",
                                                 new lang_string("s3bucket", $plugin),
                                                 new lang_string("s3bucket_desc", $plugin),
                                                 '', PARAM_TEXT));

    // List of regions taken from http://docs.aws.amazon.com/general/latest/gr/rande.html#s3_region.
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

    // Offers a choice of protocol to use for uploading data. HTTP is faster but HTTPS is safer. The default is HTTPS.
    $protocols = array(
        'http'  => new lang_string('http' , $plugin),
        'https' => new lang_string('https', $plugin)
    );

    $settings->add( new admin_setting_configselect("{$plugin}/s3protocol",
                                                    new lang_string("s3protocol", $plugin),
                                                    new lang_string("s3protocol_desc", $plugin),
                                                    'https', $protocols));

    // How many tries shall we retry upload when it fails?
    $retries = array(
        '1'  => '1',
        '2'  => '2',
        '3'  => '3',
        '4'  => '4',
        '5'  => '5',
        '6'  => '6',
        '7'  => '7',
        '8'  => '8',
        '9'  => '9',
        '10' => '10',
    );
    $settings->add( new admin_setting_configselect("{$plugin}/s3uploadretry",
                                                    new lang_string("s3uploadretry", $plugin),
                                                    new lang_string("s3uploadretry_desc", $plugin),
                                                    '3', $retries));

    // Should we use OS native packer or not?
    $settings->add( new admin_setting_configcheckbox("{$plugin}/enablepacker",
                                                     new lang_string("enablepacker", $plugin),
                                                     new lang_string("enablepacker_desc", $plugin),
                                                     '0'));

    $settings->add( new admin_setting_configexecutable("{$plugin}/packertar",
                                                       new lang_string("packertar", $plugin),
                                                       new lang_string("packertar_desc", $plugin),
                                                       ''));

    $settings->add( new admin_setting_configtext("{$plugin}/exportlocation",
                                                 new lang_string("exportlocation", $plugin),
                                                 new lang_string("exportlocation_desc", $plugin),
                                                 '', PARAM_TEXT));

    // Default time set to 1h.
    $settings->add( new admin_setting_configtime("{$plugin}/exporttime_hours",
                                                 "exporttime_minutes",
                                                 new lang_string("exporttime", $plugin),
                                                 new lang_string("exporttime_desc", $plugin),
                                                 ['h' => 1, 'm' => 30]));


    $settings->add( new \local_xray\local\api\admin_setting_configcheckbox_xray("{$plugin}/export_progress",
                                                new lang_string("export_progress", $plugin),
                                                new lang_string("export_progress_desc", $plugin),
                                                '0'));

    // Configuration for cut-off ranges.
    $settings->add( new admin_setting_heading("{$plugin}/xraycutoffs",
        new lang_string("cutoff_title", $plugin),
        new lang_string("cutoff_desc", $plugin)));
    // Define cut-off values for Table Risk Measures from Risk Report.
    // Columns: Academic risk, Social risk and Total risk.
    $risk_values = array();
    for ($i = 0; $i <= 10; $i = $i + 0.1) {
        $risk_values[] = $i;
    }
    $settings->add( new admin_setting_configselect("{$plugin}/risk1",  new lang_string("risk1_name", $plugin),
        new lang_string("risk1_desc", $plugin), 2, $risk_values));
    $settings->add( new admin_setting_configselect("{$plugin}/risk2", new lang_string("risk2_name", $plugin),
        new lang_string("risk2_desc", $plugin), 3, $risk_values));
    // Define cut-off values for Activity table in Activity Report.
    // Column Visit regularity (weekly).
    $regularity_values = array();
    for ($i = 0; $i <= 10; $i++) {
        $regularity_values[$i] = $i;
    }
    $settings->add( new admin_setting_configselect("{$plugin}/visitreg1", new lang_string("visitreg1_name", $plugin),
        new lang_string("visitreg1_desc", $plugin), 1, $regularity_values));
    $settings->add( new admin_setting_configselect("{$plugin}/visitreg2", new lang_string("visitreg2_name", $plugin),
        new lang_string("visitreg2_desc", $plugin), 2, $regularity_values));
    // Define cut-off values for Participation Metrics table in Discussion Report.
    // Columns Regularity of contributions and Regularity of CTC.
    // Define cut-off for Student Grades Based on Discussions in Discussion Report.
    // Column Regularity of contributions.
    $regularity_values = array();
    for ($i = 10; $i >= 0; $i--) {
        $regularity_values[$i] = $i;
    }
    $settings->add( new admin_setting_configselect("{$plugin}/partreg1", new lang_string("partreg1_name", $plugin),
        new lang_string("partreg1_desc", $plugin), 2, $regularity_values));
    $settings->add( new admin_setting_configselect("{$plugin}/partreg2", new lang_string("partreg2_name", $plugin),
        new lang_string("partreg2_desc", $plugin), 4, $regularity_values));
    // Define cut-off for Participation Metrics table in Discussion Report.
    // Columns Contribution and CTC.
    // Define cut-off for Student Grades Based on Discussions in Discussion Report.
    // Column CTC.
    $regularity_values = array();
    for ($i = 1; $i <= 100; $i++) {
        $regularity_values[$i] = $i;
    }
    $settings->add( new admin_setting_configselect("{$plugin}/partc1", new lang_string("partc1_name", $plugin),
        new lang_string("partc1_desc", $plugin), 33, $regularity_values));
    $settings->add( new admin_setting_configselect("{$plugin}/partc2", new lang_string("partc2_name", $plugin),
        new lang_string("partc2_desc", $plugin), 66, $regularity_values));

    $ADMIN->add('localplugins', $settings);
}
