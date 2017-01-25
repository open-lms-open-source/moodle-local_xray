<?php
/**
 * Moodlerooms Framework
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://opensource.org/licenses/gpl-3.0.html.
 *
 * @copyright Copyright (c) 2017 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @package local_xray
 * @author David Castro
 */

defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');

/**
 * Default controller
 *
 * @author David Castro
 * @package local_xray
 */
class local_xray_controller_default extends mr_controller_block {
    
    /**
     * Plugin identifier.
     */
    const PLUGIN = 'local_xray';

    /**
     * Special setup for docs page
     */
    public function setup() {
        parent::setup();
    }

    /**
     * Default screen
     */
    public function view_action() {
        global $OUTPUT;
        $xraydefpagemsg = get_string('xraydefaultpage', self::PLUGIN);
        $message = get_string('nopermissions', 'error', $xraydefpagemsg);
        return $this->output->box(
                $OUTPUT->notification(
                        $message, 'notificationerror'));
    }
}