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
 * Timer.
 *
 * @package local_xray
 * @author Darko Miletic
 * @copyright Copyright (c) 2015 Open LMS (https://www.openlms.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xray\local\api;

defined('MOODLE_INTERNAL') || die();

/**
 * Class timer
 * @package local_xray
 * @copyright Copyright (c) 2015 Open LMS (https://www.openlms.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class timer {
    /**
     * @var float
     */
    private static $timestart = 0.0;

    /**
     * @var int
     */
    private static $timeframe = 0;

    /**
     * @param int $timeframe
     * @return void
     */
    public static function start($timeframe = 0) {
        self::$timeframe = $timeframe;
        self::$timestart = microtime(true);
    }

    /**
     * @return float
     */
    public static function current() {
        return (microtime(true) - self::$timestart);
    }

    /**
     * @return float
     */
    public static function end() {
        $result = 0.0;
        if (!empty(self::$timestart)) {
            $result = self::current();
            self::$timestart = 0.0;
            self::$timeframe = 0;
        }
        return $result;
    }

    /**
     * @return bool
     */
    public static function within_time() {
        return (empty(self::$timeframe) || (self::current() < self::$timeframe));
    }

    /**
     * @return float
     */
    public static function get_start() {
        return self::$timestart;
    }
}
