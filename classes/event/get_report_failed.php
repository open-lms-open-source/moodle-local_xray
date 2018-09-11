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
 * Define the error when get report fail event
 *
 * This event is fired when a exist a problem to get data for a report from xray side.
 *
 * @package   local_xray
 * @author    Pablo Pagnone
 * @copyright Copyright (c) 2015 Blackboard Inc. (http://www.blackboard.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xray\event;
use core\event\base;

defined('MOODLE_INTERNAL') || die();

/**
 * The class for the error to get report event
 *
 */
class get_report_failed extends \core\event\base {

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * @return string
     */
    public function get_description() {
        return get_string('unexperror', 'local_xray').$this->other['message'];
    }

    /**
     * Get the name of this event
     *
     * @return string the name of this event
     */
    public static function get_name() {
        return get_string('getreportfailed', 'local_xray');
    }

    /**
     * Get the URL related to this action
     * Note: Ve have not a controller with dashboard name. The dashboard is embebed in course
     * frontpage, if we have an error in dashboard we will add link to course fronpage.
     *
     * @return \moodle_url
     */
    public function get_url() {

        if ($this->other['controller'] == "dashboard") {
            $url = new \moodle_url('/course/view.php', array(
                'id' => $this->courseid,
            ));
        } else {
            $url = new \moodle_url('/local/xray/view.php', array(
                'controller'    => $this->other['controller'],
                'courseid' => $this->courseid,
            ));
        }

        return $url;
    }

    /**
     * @param \Exception $exception
     * @return self
     * @throws \coding_exception
     */
    public static function create_from_exception(\Exception $exception, $context, $controller) {
        return self::create([
            'other' => [
                'message' => $exception->getMessage(),
                'code'    => $exception->getCode(),
                'trace'   => $exception->getTraceAsString(),
                'controller' => $controller
            ],
            'context' => $context,
        ]);
    }

    /**
     * @return array
     */
    public static function get_other_mapping() {
        return ['controller' => base::NOT_MAPPED];
    }
}
