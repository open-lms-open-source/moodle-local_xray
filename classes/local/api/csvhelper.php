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
 * CSV export support - helper.
 *
 * @package   local_xray
 * @copyright Copyright (c) 2017 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xray\local\api;

defined('MOODLE_INTERNAL') || die();

/**
 * Class csvhelper
 * @package local_xray
 */
abstract class csvhelper {
    /**
     * @param  resource $resource
     * @param  array    $ndata
     * @param  string   $delimiter
     * @param  string   $enclosure
     * @param  string   $escape
     * @return void
     */
    public static function fputcsv($resource, $ndata, $delimiter, $enclosure, $escape = null) {
        global $CFG;

        /* @noinspection PhpIncludeInspection */
        require_once($CFG->dirroot.'/local/xray/vendor/autoload.php');

        /* @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        /* @noinspection PhpUndefinedClassInspection */
        \Ajgl\Csv\Rfc\fputcsv($resource, $ndata, $delimiter, $enclosure, $escape);
    }
}
