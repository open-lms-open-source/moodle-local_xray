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
 * Convenient wrappers and helper for using the X-Ray web service API.
 *
 * @package local_xray
 * @author Darko Miletic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright Moodlerooms
 */

namespace local_xray\api;

class curlerror_exception extends \Exception {
    /**
     * @param resource $ch
     */
    public function __construct($ch) {
        $error = 'No error.';
        $errornr = 0;
        if (is_resource($ch)) {
            $errmsg = curl_error($ch);
            if (!empty($errmsg)) {
                $error = $errmsg;
                $errornr = curl_errno($ch);
            }
        }
        parent::__construct($error, $errornr);
    }
}
