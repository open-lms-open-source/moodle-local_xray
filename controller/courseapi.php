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
 * Xray course listing API controller
 *
 * @package   local_xray
 * @copyright Copyright (c) 2016 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/* @var stdClass $CFG */
require_once($CFG->dirroot.'/local/mr/framework/controller.php');

/**
 * Xray course listing API controller
 *
 * @author    David Castro
 * @package   local_xray
 * @copyright Copyright (c) 2016 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_xray_controller_courseapi extends mr_controller {

    public function view_action() {
        $this->ajax_err_response('404 Unauthorized');
    }

    private function define_json_headers() {
        if (!defined('AJAX_CRIPT') && !defined('NO_DEBUG_DISPLAY')) {
            define('AJAX_SCRIPT', true);
            define('NO_DEBUG_DISPLAY', true);
        }
    }

    public function listcategories_action() {
        $this->define_json_headers();

        $catid = optional_param('categoryid', 0, PARAM_INT);

        echo \local_xray\local\api\course_manager::list_categories_as_json($catid);
    }

    public function listcourses_action() {
        $this->define_json_headers();

        $catid = optional_param('categoryid', 'all', PARAM_INT);

        echo \local_xray\local\api\course_manager::list_courses_as_json($catid);
    }

    /**
     * Generate ajax error
     *
     * @param $errstr
     */
    protected function ajax_err_response($errstr) {
        header("HTTP/1.0 401 Not Authorized");
        echo $errstr;
        die();
    }
}