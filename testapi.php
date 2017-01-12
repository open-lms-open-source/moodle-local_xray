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

$check_key = optional_param('check', null, PARAM_ALPHANUMEXT);

if(!empty($check_key)){
    $check_method = $prefix.$check_key;
    if(in_array($check_method, $checks)) {
        $check_result = local_xray\local\api\validationaws::{$check_method}();
        if ($check_result === true) {
            $result['success'] = $check_result;
        } else {
            $result['success'] = false;
            $result['reasons'] = $check_result;
        }
    } else {
        header("HTTP/1.0 404 Not Found");
        $result['success'] = false;
        $result['reasons'] = array(get_string('validation_check_not_found', 'local_xray', $check_key));
    }
} else {
    header("HTTP/1.0 400 Bad Request");
    $result['success'] = false;
    $result['reasons'] = array(get_string('validation_check_not_filled', 'local_xray'));
}

echo json_encode($result);