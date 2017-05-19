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
        header("HTTP/1.0 404 Not Found");
        parent::setup();
    }

    /**
     * Default screen
     */
    public function view_action() {
        global $OUTPUT;
        $pagenotfoundmsg = get_string('page_not_found', self::PLUGIN);
        return $this->output->box(
                $OUTPUT->notification(
                    $pagenotfoundmsg, 'notificationerror'));
    }
}