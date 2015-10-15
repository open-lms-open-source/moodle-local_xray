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
 * File manager class.
 *
 * @package   local_xray
 * @author    Darko Miletic
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xray\local\api;

defined('MOODLE_INTERNAL') || die();

/**
 * Class autoclean
 * @package local_xray
 * @author    Darko Miletic
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class autoclean {
    /**
     * @var null|string
     */
    private $directory = null;

    /**
     * @var null|string
     */
    private $dirbase = null;

    /**
     * @var null|string
     */
    private $dirname = null;

    /**
     * @throws \coding_exception
     * @throws \invalid_dataroot_permissions
     */
    public function __construct() {
        $dirbase  = dataexport::getdir();
        $dirname  = uniqid('export_', true);
        $transdir = $dirbase . DIRECTORY_SEPARATOR . $dirname;
        if (!file_exists($transdir)) {
            make_writable_directory($transdir);
        }
        $this->directory = $transdir;
        $this->dirbase = $dirbase;
        $this->dirname = $dirname;
    }

    public function __destruct() {
        dataexport::deletedir($this->directory);
        $this->directory = null;
    }

    private function __clone() {
    }

    /**
     * @return string
     */
    public function getdirectory() {
        return $this->directory;
    }

    /**
     * @return string
     */
    public function getdirbase() {
        return $this->dirbase;
    }

    /**
     * @return string
     */
    public function getdirname() {
        return $this->dirname;
    }
}
