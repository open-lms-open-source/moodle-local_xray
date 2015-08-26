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
 * Event implementation.
 *
 * @package local_xray
 * @author Darko Miletic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright Moodlerooms
 */

namespace local_xray\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Class sync_log
 * @package local_xray
 */
class sync_log extends \core\event\base {

    /**
     * Override in subclass.
     *
     * Set all required data properties:
     *  1/ crud - letter [crud]
     *  2/ edulevel - using a constant self::LEVEL_*.
     *  3/ objecttable - name of database table if objectid specified
     *
     * Optionally it can set:
     * a/ fixed system context
     *
     * @return void
     */
    protected function init() {
        $this->data['crud']     = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;

        $this->context = \context_system::instance();
    }

    /**
     * @return string
     */
    public static function get_name() {
        return get_string('synclog', 'local_xray');
    }

    /**
     * @return string
     */
    public function get_description() {
        return $this->other['message'];
    }

    /**
     * @return string
     */
    public static function get_explanation() {
        return get_string('synclogexplanation', 'local_xray');
    }

    /**
     * Validate event data.
     *
     * @throws \coding_exception
     */
    protected function validate_data() {
        if (empty($this->other['message'])) {
            throw new \coding_exception('The error message must be set');
        }
        if (!array_key_exists('code', $this->other)) {
            throw new \coding_exception('The error code must be set');
        }
    }

    /**
     * @param \Exception $exception
     * @return self
     * @throws \coding_exception
     */
    public static function create_from_exception(\Exception $exception) {
        return self::create([
            'other' => [
                'message' => $exception->getMessage(),
                'code'    => $exception->getCode(),
                'trace'   => $exception->getTraceAsString(),
            ]
        ]);
    }

    /**
     * @param string $msg
     * @return \core\event\base
     * @throws \coding_exception
     */
    public static function create_msg($msg) {
        return self::create([
            'other' => [
                'message' => $msg,
                'code'    => 0,
            ]
        ]);
    }

}
