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
 * @package local_xray
 * @author Darko Miletic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright Moodlerooms
 */

namespace local_xray\api;

class csvfile {
    /**
     * @var null|resource
     */
    private $resource = null;

    /**
     * @param string $path
     */
    public function __construct($path) {
        $res = fopen($path, 'w');
        if ($res === false) {
            throw new \RuntimeException('Unable to create file!');
        }
        $this->resource = $res;
    }

    public function __destruct() {
        $this->close();
    }

    public function close() {
        if (is_resource($this->resource)) {
            fclose($this->resource);
            $this->resource = null;
        }
    }

    /**
     * @param \stdClass $fields
     * @return int|bool
     */
    public function writecsv($fields) {
        $result = fputcsv($this->resource, (array)$fields);
        return $result;
    }

    public function writecsvheader($fields) {
        $result = fputcsv($this->resource, array_keys((array)$fields));
        return $result;
    }

}
