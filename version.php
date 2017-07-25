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
 * Xray integration version file
 *
 * @package local_xray
 * @author Pablo Pagnone
 * @author German Vitale
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/** @var stdClass $plugin */
$plugin->version  = 2015070348;
$plugin->requires = 2015051100; // Moodle 2.9 .
$plugin->cron = 0;
$plugin->component = 'local_xray';
$plugin->maturity = MATURITY_STABLE;
$plugin->release = '2.0 (Build: 2015070348)';
$plugin->dependencies = [
        'local_mr'       => ANY_VERSION,
        'local_aws_sdk'  => ANY_VERSION
];
