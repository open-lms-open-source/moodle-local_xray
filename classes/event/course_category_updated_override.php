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
 * Class course_category_updated_override.
 *
 * Used only for being able to access legacy log data.
 *
 * @package   local_xray
 * @author    Darko Miletic
 * @copyright Copyright (c) 2017 Open LMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xray\event;
use core\event\base;
use core\event\course_category_updated;

defined('MOODLE_INTERNAL') || die();

/**
 * Class course_category_updated_override
 * @package local_xray
 */
class course_category_updated_override extends course_category_updated {

    /**
     * @param course_category_updated $event
     * @return course_category_updated_override
     */
    public static function createfrom(course_category_updated $event) {
        $fdata = ['objectid' => $event->objectid, 'context' => $event->context];
        $result = self::create($fdata);
        $result->set_legacy_logdata($event->get_legacy_logdata());
        return $result;
    }

    /**
     * @param  string $action
     * @return bool
     */
    protected function is_action($action) {
        $result = false;
        $legacy = $this->get_legacy_logdata();
        return ($legacy[2] === $action);
    }

    /**
     * @return bool
     * @see \coursecat::hide()
     */
    public function is_hide() {
        return $this->is_action('hide');
    }

    /**
     * @return bool
     * @see \coursecat::show()
     */
    public function is_show() {
        return $this->is_action('show');
    }

    /**
     * @return bool
     * @see \coursecat::change_parent()
     */
    public function is_move() {
        return $this->is_action('move');
    }

}
