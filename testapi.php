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
 * Tests the global xray api settings.
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package   local_xray
 * @copyright Copyright (c) 2016 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


define('AJAX_SCRIPT', true);
define('NO_DEBUG_DISPLAY', true);

require_once(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

global $PAGE;

$result = [];

$checks = get_class_methods('local_xray\local\api\validationaws');
$prefix = 'check_';

foreach ($checks as $check) {
    $check_result = local_xray\local\api\validationaws::{$check}();
    $api_msg_key = substr($check, strlen($prefix));
    if ($check_result === true) {
        $result[$api_msg_key] = ['success' => $check_result];
    } else {
        $result[$api_msg_key] = [
            'success' => false,
            'reasons' => $check_result
        ];
    }
}

echo json_encode($result);