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

$observers = [
    [
        'eventname'   => '\core\event\course_deleted',
        'callback'    => 'local_xray_course_deleted',
        'includefile' => '/local/xray/lib.php'
    ],
    [
        'eventname'   => '\core\event\course_category_deleted',
        'callback'    => 'local_xray_course_category_deleted',
        'includefile' => '/local/xray/lib.php'
    ],
    [
        'eventname'   => '\mod_hsuforum\event\discussion_deleted',
        'callback'    => 'local_xray_hsu_discussion_deleted',
        'includefile' => '/local/xray/lib.php'
    ],
    [
        'eventname'   => '\mod_hsuforum\event\post_deleted',
        'callback'    => 'local_xray_hsu_post_deleted',
        'includefile' => '/local/xray/lib.php'
    ],
    [
        'eventname'   => '\mod_forum\event\discussion_deleted',
        'callback'    => 'local_xray_discussion_deleted',
        'includefile' => '/local/xray/lib.php'
    ],
    [
        'eventname'   => '\mod_forum\event\post_deleted',
        'callback'    => 'local_xray_post_deleted',
        'includefile' => '/local/xray/lib.php'
    ],
    [
        'eventname'   => '\core\event\course_module_deleted',
        'callback'    => 'local_xray_course_module_deleted',
        'includefile' => '/local/xray/lib.php'
    ],
    [
        'eventname'   => '\core\event\role_unassigned',
        'callback'    => 'local_xray_role_unassigned',
        'includefile' => '/local/xray/lib.php'
    ],
    [
        'eventname'   => '\core\event\user_enrolment_deleted',
        'callback'    => 'local_xray_user_enrolment_deleted',
        'includefile' => '/local/xray/lib.php'
    ],
    [
        'eventname'   => '\local_xray\event\sync_failed',
        'callback'    => 'local_xray_sync_failed',
        'includefile' => '/local/xray/lib.php'
    ],
    [
        'eventname'   => '\core\event\group_deleted',
        'callback'    => 'local_xray_group_deleted',
        'includefile' => '/local/xray/lib.php'
    ],
    [
        'eventname'   => '\core\event\group_member_removed',
        'callback'    => 'local_xray_group_member_removed',
        'includefile' => '/local/xray/lib.php'
    ],
];
