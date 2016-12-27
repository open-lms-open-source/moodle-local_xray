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

defined('MOODLE_INTERNAL') || die();

/**
 * Class csv_fileiterator
 */
class csv_fileiterator implements Iterator {

    /**
     * @var resource - file handle
     */
    protected $file;

    /**
     * @var int
     */
    protected $key = 0;

    /**
     * @var array
     */
    protected $current;

    /**
     * @var null|string
     */
    private $delimiter = null;

    /**
     * @var null
     */
    private $enclosure = null;

    /**
     * @var null
     */
    private $escape = null;

    /**
     * csv_fileiterator constructor.
     *
     * @param string $file
     */
    public function __construct($file) {
        $filehandle = fopen($file, 'r');
        if ($filehandle === false) {
            print_error('cannotopenfile', 'core_error', '', $file);
        }
        $this->file      = $filehandle;
        $this->delimiter = \local_xray\local\api\csv_meta::get_delimiter();
        $this->enclosure = \local_xray\local\api\csv_meta::get_enclosure();
        $this->escape    = \local_xray\local\api\csv_meta::get_escape();
    }

    public function __destruct() {
        fclose($this->file);
        $this->file = null;
    }

    /**
     * Get element
     */
    public function read() {
        if (\local_xray\local\api\csv_meta::checkformat()) {
            global $CFG;

            /* @noinspection PhpIncludeInspection */
            require_once($CFG->dirroot . '/local/xray/vendor/autoload.php');

            $this->current = \Ajgl\Csv\Rfc\fgetcsv(
                $this->file,
                0,
                $this->delimiter,
                $this->enclosure,
                $this->enclosure // It is a MUST to have this same as enclosure.
            );
        } else {
            $this->current = fgetcsv(
                $this->file,
                0,
                $this->delimiter,
                $this->enclosure,
                $this->escape
            );
        }
    }

    /**
     * @return void
     */
    public function rewind() {
        rewind($this->file);
        $this->read();
        $this->key = 0;
    }

    /**
     * @return bool
     */
    public function valid() {
        return !feof($this->file);
    }

    /**
     * @return int
     */
    public function key() {
        return $this->key;
    }

    /**
     * @return array
     */
    public function current() {
        return $this->current;
    }

    /**
     * @return void
     */
    public function next() {
        $this->read();
        $this->key++;
    }
}
