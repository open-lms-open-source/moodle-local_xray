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
 * AWS validation helpers.
 *
 * @package   local_xray
 * @author    David Castro <david.castro@blackboard.com>
 * @copyright Copyright (c) 2016 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xray\local\api;

defined('MOODLE_INTERNAL') || die();

/**
 * Class course_manager
 * @package local_xray
 */
abstract class course_manager {

    const PLUGIN = 'local_xray';

    /**
     * Retrieve the available courses for usage with X-Ray
     *
     * @param string $cid Id of the category
     * @return string[] Format $id=>$fullname
     * @throws \moodle_exception
     */
    public static function list_courses_as_simple_array($cid = 'all') {
        if ($cid === 'all' || $cid === 0) {
            return array();
        }

        global $DB;

        $query = 'SELECT mdc.id, mdc.fullname, lxsc.id AS xray_id
                    FROM {course} mdc
               LEFT JOIN {local_xray_selectedcourse} lxsc ON (mdc.id = lxsc.cid)
                   WHERE mdc.category = ?
                ORDER BY mdc.fullname';

        $courses = $DB->get_records_sql($query, array($cid));

        $res = array();
        foreach ($courses as $course) {
            $res[] = array(
                'id' => $course->id,
                'name' => $course->fullname,
                'checked' => !is_null($course->xray_id), // TODO bring from database
                'disabled' => false // TODO bring from database.
            );
        }

        return $res;
    }

    /**
     * Retrieve the available courses for usage with X-Ray as json encoded string
     *
     * @param string $cid Id of the category
     * @return string[] Format $id=>$fullname
     * @throws \moodle_exception
     */
    public static function list_courses_as_json($cid = 'all') {
        return json_encode(self::list_courses_as_simple_array($cid));
    }

    /**
     * Retrieve the available categories for usage with X-Ray
     *
     * @param  int $cid Id of the category
     * @return array
     * @throws \moodle_exception
     */
    public static function list_categories_as_simple_array($cid = 0) {
        $res = array();

        global $DB;

        $query = 'SELECT mdcat.id, mdcat.name,

                         COUNT(mdc.id) AS totcourses,
                         COUNT(lxc.id) AS xraycourses

                    FROM {course_categories} mdcat
               LEFT JOIN {course} mdc ON mdc.category = mdcat.id
               LEFT JOIN {local_xray_selectedcourse} lxc ON mdc.id = lxc.cid

                   WHERE mdcat.parent = ?
                GROUP BY mdcat.id, mdcat.name
                ORDER BY mdcat.name';

        $categories = $DB->get_records_sql($query, array($cid));

        foreach ($categories as $cat) {
            $xraycourses = $cat->xraycourses;
            $totcourses = $cat->totcourses;

            $subcatcourses = self::query_categories_selected_courses($cat->id);
            $xraycourses += $subcatcourses->xraycourses;
            $totcourses += $subcatcourses->totcourses;

            $checkstatus = self::compute_check_status($xraycourses, $totcourses);

            $res[] = array(
                'id' => $cat->id,
                'name' => $cat->name,
                'checked' => $checkstatus->checked,
                'indeterminate' => $checkstatus->indeterminate,
                'disabled' => $checkstatus->disabled
            );
        }

        return $res;
    }

    /**
     * Counts number of selected courses and total courses in category and sub categories
     * @param string|int $cid Parent category id
     * @return \stdClass response with attributes: xraycourses, totcourses
     */
    private static function query_categories_selected_courses($cid = 0) {
        $res = new \stdClass();
        global $DB;

        $query = 'SELECT mdcat.id,

                         COUNT(mdc.id) AS totcourses,
                         COUNT(lxc.id) AS xraycourses

                    FROM {course_categories} mdcat
               LEFT JOIN {course} mdc ON mdc.category = mdcat.id
               LEFT JOIN {local_xray_selectedcourse} lxc ON mdc.id = lxc.cid

                   WHERE mdcat.parent = ?
                GROUP BY mdcat.id, mdcat.name
                ORDER BY mdcat.name';

        $categories = $DB->get_records_sql($query, array($cid));

        $res->xraycourses = 0;
        $res->totcourses = 0;
        foreach ($categories as $cat) {
            $res->xraycourses += $cat->xraycourses;
            $res->totcourses += $cat->totcourses;

            $subcatcourses = self::query_categories_selected_courses($cat->id);
            $res->xraycourses += $subcatcourses->xraycourses;
            $res->totcourses += $subcatcourses->totcourses;
        }

        return $res;
    }

    /**
     * Calculates the check status based on number of xray selected courses and total category courses
     * @param int $xraycourses X-Ray selected courses
     * @param int $totcourses Total courses for this category
     * @return \stdClass response with attribures: disabled, checked, indeterminate
     */
    public static function compute_check_status($xraycourses, $totcourses) {
        $res = new \stdClass();

        $res->disabled = $totcourses == 0;
        $res->checked = $xraycourses > 0;
        $res->indeterminate = $res->checked && $xraycourses < $totcourses;

        return $res;
    }

    /**
     * Retrieve the available categories for usage with X-Ray as json encoded string
     *
     * @param  int $cid Id of the category
     * @return string
     * @throws \moodle_exception
     */
    public static function list_categories_as_json($cid = 0) {
        return json_encode(self::list_categories_as_simple_array($cid));
    }

    /**
     * Checks if course is selected for X-Ray usage
     * @param int|string $cid Course id
     * @return boolean
     */
    public static function is_course_selected($cid) {
        if (defined('BEHAT_SITE_RUNNING')) {
            return true;
        }

        global $DB;
        $res = $DB->record_exists('local_xray_selectedcourse', array('cid' => $cid));
        return $res;
    }

    /**
     * Loads selected courses on X-Ray server
     * @return array
     * @throws \moodle_exception
     */
    public static function load_course_ids_from_xray() {
        if (!defined('XRAY_OMIT_CACHE')) {
            define('XRAY_OMIT_CACHE', true);
        }

        $res = array();

        if ($xraycourses = wsapi::get_analysis_filter()) {
            $res = $xraycourses->filtervalue;
        } else {
            throw new \moodle_exception(
                'xray_check_global_settings',
                self::PLUGIN,
                '',
                self::generate_xray_settings_link()
            );
        }

        return $res;
    }

    /**
     * Verifies if courses in X-Ray match courses selected in moodle
     * @return boolean
     * @throws \moodle_exception
     */
    public static function courses_match() {
        $cids = self::list_selected_course_ids();
        $cidcount = count($cids);
        $xraycids = self::load_course_ids_from_xray();

        return $cidcount === count(array_intersect($xraycids, $cids));
    }

    /**
     * Saves selected courses
     * @param array $cids
     */
    public static function save_selected_courses($cids = null) {
        if (is_null($cids)) {
            return;
        }

        global $DB;

        $selcourtable = 'local_xray_selectedcourse';

        // Clear the table from old selection.
        if ($DB->record_exists($selcourtable, array())) {
            $DB->delete_records($selcourtable);
        }

        // Create array of new records.
        $cidobjects = array();
        $uniquecids = array_unique($cids);
        foreach ($uniquecids as $cid) {
            $cidobject = new \stdClass();
            $cidobject->cid = $cid;
            $cidobjects[] = $cidobject;
        }

        // Save all records.
        $DB->insert_records($selcourtable, $cidobjects);

        // Save courses to X-Ray as well.
        self::save_courses_to_xray();
    }

    /**
     * Retrieve the selected courses for usage with X-Ray
     *
     * @return int[] course ids
     * @throws \moodle_exception
     */
    public static function list_selected_course_ids() {
        global $DB;

        $selcourtable = 'local_xray_selectedcourse';

        $courses = $DB->get_records($selcourtable);

        $res = array();
        foreach ($courses as $course) {
            $res[] = $course->cid;
        }

        return $res;
    }

    /**
     * Saves the enabled course list to the X-Ray server
     * @return boolean Success status
     * @throws \moodle_exception
     */
    private static function save_courses_to_xray() {
        if (!defined('XRAY_OMIT_CACHE')) {
            define('XRAY_OMIT_CACHE', true);
        }
        $cids = self::list_selected_course_ids();

        $wsapires = wsapi::save_analysis_filter($cids);
        if ($wsapires !== false) {
            if (!empty($wsapires->ok)) {
                return true;
            } else {
                $error = get_string('error_xray_unknown', self::PLUGIN);
                if (isset($wsapires->error)) {
                    $error = $wsapires->error;
                } else if (isset($wsapires)) {
                    $error = $wsapires;
                }
                throw new \moodle_exception('xray_save_course_filter_error', self::PLUGIN, '', $error);
            }
        } else {
            throw new \moodle_exception(
                    'xray_save_course_filter_error', self::PLUGIN,
                    '', get_string('error_xray', self::PLUGIN));
        }
    }

    /**
     * Generates X-Ray connection error text with a link to the global settings
     * @return string Text with a connection error to X-Ray and link to settings
     */
    public static function generate_xray_settings_link() {
        $globalseturl = new \moodle_url('/admin/settings.php',
                array('section' => self::PLUGIN.'_global'));

        $globalsetlink = '&nbsp;<a href="'.$globalseturl->out(false).'">'
                .get_string('xray_check_global_settings_link', self::PLUGIN)
                .'</a>';

        return $globalsetlink;
    }

    /**
     * Check if the Risk Status report is disabled on X-Ray side.
     * @return array
     * @throws \moodle_exception
     */
    public static function is_risk_disabled() {
        if (!defined('XRAY_OMIT_CACHE')) {
            define('XRAY_OMIT_CACHE', true);
        }
        $res = false;
        if ($xraycourses = wsapi::get_risk_configuration()) {
            if (isset($xraycourses->currentSettings->RISK_REPORT)
                    && $xraycourses->currentSettings->RISK_REPORT == "false") {
                $res = true;
            }
        } else {
            throw new \moodle_exception(
                'xray_check_global_settings',
                self::PLUGIN,
                '',
                self::generate_xray_settings_link()
            );
        }
        return $res;
    }

    /*
    * Check if the course is an X-Ray course and delete it.
    * @param int $courseid
    */
    public static function check_xray_course_to_delete($courseid) {
        global $DB;

        // If the course is in local_xray_selectedcourse table, it should be deleted and saved in X-Ray side.
        if ($DB->record_exists('local_xray_selectedcourse', array('cid' => $courseid))) {
            // Delete the course in local_xray_selectedcourse table.
            $DB->delete_records_select('local_xray_selectedcourse', "cid = :courseid", array('courseid' => $courseid));
            // Update X-Ray side.
            if (!PHPUNIT_TEST) {
                self::save_courses_to_xray();
            }
        }
    }
}
