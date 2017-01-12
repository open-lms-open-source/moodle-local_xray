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

namespace local_xray\coursestatus;

use local_xray\local\api\course_manager;

defined('MOODLE_INTERNAL') || die();
/* @var stdClass $CFG */
require_once($CFG->dirroot.'/lib/externallib.php');

/**
 * Web Service Course Status
 * @author    David Castro
 * @copyright Copyright (c) 2016 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ws_course_selection extends \external_api {
    /**
     * @return \external_function_parameters
     */
    public static function list_categories_parameters() {
        return new \external_function_parameters(
            array(
                'categoryid' => new \external_value(PARAM_INT, 'Parent Category ID', VALUE_DEFAULT, 0),
            )
        );
    }

    /**
     * @return \external_multiple_structure
     */
    public static function list_categories_returns() {
        return new \external_multiple_structure(
            new \external_single_structure(
                array(
                    'id' => new \external_value(PARAM_INT, 'Category id'),
                    'name' => new \external_value(PARAM_TEXT, 'Category name'),
                    'checked' => new \external_value(PARAM_BOOL, 'Is Category selected'),
                    'indeterminate' => new \external_value(PARAM_BOOL, 'Is Category selection indeterminate')
                )
            )
        );
    }

    /**
     * @param int $categoryid
     * @return array
     */
    public static function list_categories($categoryid) {
        return course_manager::list_categories_as_simple_array($categoryid);
    }
    
    /**
     * @return \external_function_parameters
     */
    public static function list_courses_parameters() {
        return new \external_function_parameters(
            array(
                'categoryid' => new \external_value(PARAM_INT, 'Parent Category ID', VALUE_DEFAULT, 'all'),
            )
        );
    }

    /**
     * @return \external_multiple_structure
     */
    public static function list_courses_returns() {
        return new \external_multiple_structure(
            new \external_single_structure(
                array(
                    'id' => new \external_value(PARAM_INT, 'Course id'),
                    'name' => new \external_value(PARAM_TEXT, 'Course name'),
                    'checked' => new \external_value(PARAM_BOOL, 'Is Course selected'),
                    'disabled' => new \external_value(PARAM_BOOL, 'Is Course disabled for selection')
                )
            )
        );
    }

    /**
     * @param int $categoryid
     * @return array
     */
    public static function list_courses($categoryid) {
        return course_manager::list_courses_as_simple_array($categoryid);
    }
}
