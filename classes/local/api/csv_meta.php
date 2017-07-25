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
 * CSV export support.
 *
 * @package   local_xray
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xray\local\api;

defined('MOODLE_INTERNAL') || die();

/**
 * Class csv_meta
 * @package local_xray
 */
class csv_meta {

    /**
     * @var bool
     */
    protected static $newformat = null;

    /**
     * @return bool
     */
    public static function checkformat() {
        if (self::$newformat === null) {
            self::$newformat = get_config('local_xray', 'newformat');
        }
        return self::$newformat;
    }

    /**
     * @return string
     */
    public static function get_delimiter() {
        return self::checkformat() ? '|' : ',';
    }

    /**
     * @return string
     */
    public static function get_enclosure() {
        return self::checkformat() ? '"' : "\x1";
    }

    /**
     * We are switching to exclusive use of
     * {@link https://github.com/ajgarlag/AjglCsvRfc csv-rfc} and hence
     * only one escape character is permitted.
     * @return string
     */
    public static function get_escape() {
        return '\\';
    }

}
