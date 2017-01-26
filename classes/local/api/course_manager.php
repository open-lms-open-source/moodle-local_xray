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
     * @param string $cid Id of the category
     * @return coursecat[] Format $id=>$fullname
     * @throws \moodle_exception
     */
    public static function list_categories_as_simple_array($cid = 0) {
        $res = array();

        global $DB;

        $query = 'SELECT mdcat.id, mdcat.name,

                         COUNT(mdc.id) AS totcourses,
                         COUNT(lxc.id) AS xraycourses,

                         COUNT(mdsc.id) AS stotcourses,
                         COUNT(lxsc.id) AS sxraycourses

                    FROM {course_categories} mdcat
               LEFT JOIN {course} mdc ON mdc.category = mdcat.id
               LEFT JOIN {local_xray_selectedcourse} lxc ON mdc.id = lxc.cid

               LEFT JOIN {course_categories} mdscat ON mdscat.parent = mdcat.id
               LEFT JOIN {course} mdsc ON mdsc.category = mdscat.id
               LEFT JOIN {local_xray_selectedcourse} lxsc ON mdsc.id = lxsc.cid

                   WHERE mdcat.parent = ?
                GROUP BY mdcat.id, mdcat.name
                ORDER BY mdcat.name';

        $categories = $DB->get_records_sql($query, array($cid));

        foreach ($categories as $cat) {
            $checked = ($cat->xraycourses > 0 || $cat->sxraycourses > 0);
            $indeterminate = $checked && ($cat->totcourses > $cat->xraycourses || $cat->stotcourses > $cat->sxraycourses);
            $res[] = array(
                'id' => $cat->id,
                'name' => $cat->name,
                'checked' => $checked,
                'indeterminate' => $indeterminate
            );
        }

        return $res;
    }

    /**
     * Retrieve the available categories for usage with X-Ray as json encoded string
     *
     * @param string $$cid Id of the category
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
        if(!defined('XRAY_OMIT_CACHE')) {
            define('XRAY_OMIT_CACHE', true);
        }
        
        $res = array();
        
        if ($xraycourses = wsapi::get_analysis_filter()) {
            $res = $xraycourses->filtervalue;
        } else {
            throw new \moodle_exception('xray_check_global_settings', self::PLUGIN, '',self::generate_xray_settings_link());
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
        foreach ($cids as $cid) {
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
        if(!defined('XRAY_OMIT_CACHE')) {
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
                throw new \moodle_exception('xray_save_course_filter_error', self::PLUGIN, '',$error);
            }
        } else {
            throw new \moodle_exception(
                    'xray_save_course_filter_error', self::PLUGIN,
                    '',get_string('error_xray', self::PLUGIN));
        }
    }
    
    /**
     * Generates X-Ray connection error text
     * @return string Text with a connection error to X-Ray
     */
    public static function generate_xray_settings_link() {
        $globalseturl = new \moodle_url('/admin/settings.php',
                array('section' => self::PLUGIN.'_global'));

        $globalsetlink = '&nbsp;<a href="'.$globalseturl->out(false).'">'
                .get_string('xray_check_global_settings_link', self::PLUGIN)
                .'</a>';

        return $globalsetlink;
    }

}
