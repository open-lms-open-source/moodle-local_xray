<?php
// @codingStandardsIgnoreFile
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

if (isset($_SERVER['REMOTE_ADDR'])) {
    die; // No access from web.
}

/**
 * X-Ray Exports On Demand - xEOD
 *
 * @package   local_xray
 * @subpackage cli
 * @author    David Castro <david.castro@blackboard.com>
 * @copyright Copyright (c) 2017 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

use local_xray\local\api\data_export;
use local_xray\task\data_sync;

require_once(__DIR__.'/../../../config.php');
require_once($CFG->libdir.'/clilib.php');

// Add export execution flag.
define('XRAY_RUNNING_CLI_EXPORT', true);

// Utility functions.
/**
 * Performs X-Ray exports on an array of configurations
 * @param array $xrayexportsconfig Array of export configurations to execute.
 */
function process_xray_exports($xrayexportsconfig) {
    array_walk($xrayexportsconfig, 'process_xray_export');
}

/**
 * Performs X-Ray exports on a specific configutation
 * @param array $xrayexportconfig Export configurations to execute.
 * @param $key array key
 */
function process_xray_export($xrayexportconfig, $key) {
    global $CFG;
    // Required keys.
    $reqkeys = array(
        "xrayclientid",
        "awskey",
        "awssecret",
        "s3bucket",
        "s3bucketregion",
        "s3protocol",
        "s3uploadretry",
        "newformat"
    );
    mtrace('Processing export number '.($key + 1));
    // Check if the required values exist.
    if (count(array_intersect($reqkeys, array_keys($xrayexportconfig))) !== count($reqkeys)) {
        cli_problem('Finished export number '.($key + 1).' with errors:');
        cli_problem('Export '.($key + 1).' is missing required attributes.');
        cli_problem('Please check that the following attributes exist:');
        foreach ($reqkeys as $reqkey) {
            cli_problem(' * '.$reqkey);
        }
        cli_separator();
        die(1);
    }
    // Apply config forcefully to X-Ray plugin.
    foreach ($xrayexportconfig as $cfgkey => $value) {
        $CFG->forced_plugin_settings['local_xray'][$cfgkey] = $value;
        mtrace(sprintf('Set X-Ray config: %s = %s', $cfgkey, $value));
    }
    // Synchronization must be enabled and counter increase disabled.
    $CFG->forced_plugin_settings['local_xray']['enablesync'] = true;
    $CFG->forced_plugin_settings['local_xray']['disablecounterincrease'] = true;
    // Perform export.
    $datasync = new data_sync();
    try {
        $datasync->execute();
        mtrace('Finished export number '.($key + 1));
    } catch (Exception $e) {
        cli_problem('Finished export: '.($key + 1).' with errors:');
        cli_error($e->getMessage(), $e->getCode());
    }
    cli_separator();
}

// Create var for config path.
$cfgfilelocation = sprintf('%s/xeod_cfg.json', $CFG->dataroot);

// Read arguments.
list($options, $unrecognized) = cli_get_params(
        array(
            'help' => false,
            'disable-counter-increase' => false
        ), array(
            'h' => 'help',
            'd' => 'disable-counter-increase'
        )
    );

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized), 2);
}

if ($options['help']) {
    $help = "Performs X-Ray Exports On Demand - xEOD.

This script assumes that there is a config file with the exports in your moodledata folder. ($cfgfilelocation).

Options:
-h, --help                             Prints out this help
-d, --disable-counter-increase         Disables counter increase after exports have been executed

Example:
\$ sudo -u www-data /usr/bin/php local/xray/cli/xeod.php
";
    mtrace($help);
    exit(0);
}
// Look for config file.
$jsoncfgstr = file_get_contents($cfgfilelocation);
// If config file not found, print error.
if ($jsoncfgstr === false) {
    $filenotfounderr = "Config file was not found or could not be read in $cfgfilelocation.
Export can't be performed without config file.";
    cli_error($filenotfounderr, 3);
}

// Parse json file.
$exportcfgarray = json_decode($jsoncfgstr, true);
if (is_null($exportcfgarray)) {
    $filenotparsederr = "Config file could not be parsed $cfgfilelocation.
Export can't be performed without config file being correctly formed.";
    cli_error($filenotparsederr, 4);
}

// Else, process array.
process_xray_exports($exportcfgarray);

// Store counters after all exports have executed.
if (!$options['disable-counter-increase']) {
    data_export::store_counters();
}
mtrace($options['disable-counter-increase'] ? 'Counters remain unmodified.' : 'Counters were stored.');
mtrace('Exports finished.');
