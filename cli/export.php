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

if (isset($_SERVER['REMOTE_ADDR'])) {
    die; // no access from web!
}

define('CLI_SCRIPT', true);

use local_xray\local\api\data_export;

require_once(__DIR__.'/../../../config.php');
require_once($CFG->libdir.'/clilib.php');

try {
    $DB->set_debug(($CFG->debug == DEBUG_DEVELOPER) && $CFG->debugdisplay);
    $timeend = time() - (2 * HOURSECS);
    echo 'Starting export.', PHP_EOL;
    $base = uniqid('doexport');
    $outdir = make_temp_directory($base);
    data_export::export_csv(0, $timeend, $outdir);
    list($compfile, $destfile) = data_export::compress($CFG->tempdir, $base);
    $DB->set_debug(false);
    echo 'Finished.', PHP_EOL;
    echo 'Export file is: ', $compfile, PHP_EOL;
} catch (Exception $e) {
    cli_error($e->getMessage(), $e->getCode());
}
