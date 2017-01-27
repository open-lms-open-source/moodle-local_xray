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

namespace local_xray\local\api;

defined('MOODLE_INTERNAL') || die();

/* @var stdClass $CFG */

/**
 * Get the status of the courses for X-Ray.
 *
 * @package   local_xray
 * @author    German Vitale
 * @copyright Copyright (c) 2016 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class course_validation {

    const MAXSTUDENTS = 1000;
    const XRAYCOURSEDISABLED = 0;
    const XRAYCOURSEENABLED = 1;
    const XRAYCOURSEHIDDEN = 'hidden';
    const XRAYCOURSESTUDENTS = 'students';
    const XRAYCOURSESINGLEFORMAT = 'single';

    /**
     * Check if the course selected and available for X-ray.
     *
     * @param $courseid
     * @return bool
     */
    public static function is_xray_course($courseid) {
        if (defined('BEHAT_SITE_RUNNING')) {
            return true;
        }
        if (!self::validate_students($courseid)  || self::single_activity_course($courseid) ||
                !self::course_is_visible($courseid) || !self::selected_course($courseid)) {
            return false;
        }
        return true;
    }

    /**
     * Validate the status of the course.
     *
     * @param stdClass $course object.
     * @param bool $extrainfo.
     * @return array course status.
     */
    public static function validate_course($course, $extrainfo = false) {

        $response = array();
        $response['id'] = $course->id;
        $response['students'] = false;
        $response['single'] = false;
        $response['hidden'] = false;
        $response['checked'] = self::selected_course($course->id);

        $status = self::XRAYCOURSEENABLED;
        $description = '';
        if (!self::validate_students($course->id)) {
            $response[self::XRAYCOURSESTUDENTS] = true;
            //$description .= self::XRAYCOURSESTUDENTS;
            $status = self::XRAYCOURSEDISABLED;
        }
        if (self::single_activity_course($course->id)) {
            $response[self::XRAYCOURSESINGLEFORMAT] = true;
            //$description .= ($status == self::XRAYCOURSEDISABLED ? ', ' : '').self::XRAYCOURSESINGLEFORMAT;
            $status = self::XRAYCOURSEDISABLED;
        }
        if (!self::course_is_visible($course->id)) {
            $response[self::XRAYCOURSEDISABLED] = true;
            //$description .= ($status == self::XRAYCOURSEDISABLED ? ', ' : '').self::XRAYCOURSEHIDDEN;
            $status = self::XRAYCOURSEDISABLED;
        }
        $response['status'] = $status;

        if ($extrainfo) {
            $response['name'] = $course->fullname;
            $response['disabled'] = false;
            if (!self::validate_students($course->id)
                || self::single_activity_course($course->id) || !self::course_is_visible($course->id)) {
                $response['disabled'] = true;
                $response['checked'] = false;
            }
        }
        return $response;
    }

    /**
     * Get the status of the courses in a category.
     *
     * @param $categoryid
     * @return array
     */
    public static function get_courses($categoryid) {
        $response = array();
        if ($categoryid) {
            // Get courses in category.
            if ($courses = get_courses($categoryid, 'c.sortorder DESC', 'c.id, c.fullname, c.shortname')) {
                foreach ($courses as $course) {
                    $response[$course->id] = self::validate_course($course, true);
                }
            }
        }
        return $response;
    }

    /**
     * Validate if the students enrolled do not exceed the maximum allowed (By default 1000).
     *
     * @param $courseid
     * @return bool
     */
    public static function validate_students($courseid) {
        global $DB, $CFG;

        list($inoreqsql, $params) = $DB->get_in_or_equal(explode(',', $CFG->gradebookroles), SQL_PARAMS_NAMED, 'grbr0');
        $params['courseid'] = $courseid;
        $params['contextcourse'] = CONTEXT_COURSE;

        $sql = "SELECT count(u.id)
                FROM {user} u
                JOIN {user_enrolments} ue ON ue.userid = u.id
                JOIN {enrol} e ON e.id = ue.enrolid
                JOIN {role_assignments} ra ON ra.userid = u.id AND ra.roleid $inoreqsql
                JOIN {context} ct ON ct.id = ra.contextid AND ct.contextlevel = :contextcourse
                JOIN {course} c ON c.id = ct.instanceid AND e.courseid = c.id
                WHERE e.status = 0 AND u.suspended = 0 AND u.deleted = 0 AND c.id = :courseid";

        $maxstudents = get_config('local_xray', 'maxstudents');
        $max = (!empty($maxstudents) ? $maxstudents : self::MAXSTUDENTS);
        if ($DB->count_records_sql($sql, $params) > $max) {
            return false;
        }
        return true;
    }

    /**
     * Validate if the course is visible.
     *
     * @param $courseid
     * @return mixed
     */
    public static function course_is_visible($courseid) {
        global $DB;
        return $DB->get_field('course', 'visible', array('id' => $courseid), IGNORE_MULTIPLE);
    }

    /**
     * Check if the course has the Single Activity format.
     *
     * @return bool.
     */
    public static function selected_course($courseid) {
        if (PHPUNIT_TEST) {
            return true;
        }
        global $DB;
        return $DB->record_exists('local_xray_selectedcourse', array('cid' => $courseid));
    }

    /**
     * Check if the course has the Single Activity format.
     *
     * @return bool.
     */
    public static function single_activity_course($courseid) {
        global $DB;
        return $DB->record_exists('course', array('id' => $courseid, 'format' => 'singleactivity'));
    }
}