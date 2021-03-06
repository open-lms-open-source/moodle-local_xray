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
 * Scheduled task declaration.
 *
 * @package   local_xray
 * @author    Darko Miletic
 * @copyright Copyright (c) 2015 Open LMS (https://www.openlms.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => 'local_xray\task\data_sync',
        'blocking'  => 0,
        'minute'    => '0',
        'hour'      => '*/12',
        'day'       => '*',
        'dayofweek' => '*',
        'month'     => '*'
    ],
    [
        'classname' => 'local_xray\task\data_prune_task',
        'blocking'  => 0,
        'minute'    => '0',
        'hour'      => '3',
        'day'       => '*',
        'dayofweek' => '6',
        'month'     => '*'
    ],
    [
        'classname' => 'local_xray\task\risk_sync',
        'blocking'  => 0,
        'minute'    => '1',
        'hour'      => '0',
        'day'       => '*',
        'dayofweek' => '*',
        'month'     => '*'
    ],
    [
        'classname' => 'local_xray\task\course_sync',
        'blocking'  => 0,
        'minute'    => '1',
        'hour'      => '0',
        'day'       => '*',
        'dayofweek' => '*',
        'month'     => '*'
    ],
    [
        'classname' => 'local_xray\task\send_emails',
        'blocking'  => 0,
        'minute'    => '1',
        'hour'      => '0',
        'day'       => '*',
        'dayofweek' => '*',
        'month'     => '*',
        'disabled'  => 1
    ]
];
