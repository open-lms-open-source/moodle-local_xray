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
 * Class csv_file - for exporting data into CSV file
 *
 * @package   local_xray
 * @author    Darko Miletic
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class csv_file {
    const DELIMITER   = ',';
    const ENCLOSURE   = "\x1";
    const ESCAPE_CHAR = "\v";

    /**
     * @var null|resource
     */
    private $resource = null;

    /**
     * @var bool
     */
    private $header = false;

    /**
     * @param string $path
     */
    public function __construct($path) {
        if (file_exists($path)) {
            print_error('error_fexists', 'local_xray', '', $path);
        }
        $res = fopen($path, 'w');
        if ($res === false) {
            print_error('error_fnocreate', 'local_xray', '', $path);
        }
        $this->resource = $res;
    }

    public function __destruct() {
        $this->close();
    }

    /**
     * @return void
     */
    public function close() {
        if (is_resource($this->resource)) {
            fclose($this->resource);
            $this->resource = null;
            $this->header = false;
        }
    }

    /**
     * @param string $value
     * @param string $needle
     * @return bool
     */
    protected function endswith($value, $needle) {
        return (substr($value, -strlen($needle)) === $needle);
    }

    /**
     * @param  array $data - data to be stored
     * @return int|bool
     */
    protected function write($data) {
        return fputcsv($this->resource, $data, self::DELIMITER, self::ENCLOSURE, self::ESCAPE_CHAR);
    }

    /**
     * @param \stdClass $fields
     * @return int|bool
     */
    public function write_csv($fields) {
        $data = (array)$fields;
        if (!$this->header) {
            $result = $this->write(array_keys($data));
            if ($result === false) {
                return false;
            }
            $this->header = true;
        }

        $result = $this->write($data);
        $data = null;
        return $result;
    }

    /**
     * @return void
     */
    public function flush() {
        if (is_resource($this->resource)) {
            fflush($this->resource);
        }
    }
}
