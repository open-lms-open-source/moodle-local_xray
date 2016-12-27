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
 * Class auto_clean
 * @package local_xray
 * @author    Darko Miletic
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auto_clean {
    /**
     * @var string
     */
    private $directory = '';

    /**
     * @var string
     */
    private $dirbase = '';

    /**
     * @var string
     */
    private $dirname = '';

    /**
     * @throws \coding_exception
     * @throws \invalid_dataroot_permissions
     */
    public function __construct() {
        $dirbase  = data_export::get_dir();
        $dirname  = uniqid('export_', true);
        $transdir = $dirbase . DIRECTORY_SEPARATOR . $dirname;
        if (!file_exists($transdir)) {
            make_writable_directory($transdir);
        }
        $this->directory = $transdir;
        $this->dirbase   = $dirbase;
        $this->dirname   = $dirname;
    }

    /**
     * @return void
     */
    public function __destruct() {
        // We prevent deletion of attached directory for unit testing.
        // There are issues with vagrant image and NFS mounted data.
        // It will be deleted anyways after all tests are executed.
        if (!PHPUNIT_TEST) {
            remove_dir($this->directory);
        }
        $this->detach();
    }

    private function __clone() {
    }

    /**
     * @return string
     */
    public function get_directory() {
        return $this->directory;
    }

    /**
     * @return string
     */
    public function get_dirbase() {
        return $this->dirbase;
    }

    /**
     * @return string
     */
    public function get_dirname() {
        return $this->dirname;
    }

    /**
     * List contents of a directory.
     * Intended for debugging purporses.
     */
    public function listdir() {
        /** @var int $flags */
        $flags  = \FilesystemIterator::KEY_AS_PATHNAME;
        $flags |= \FilesystemIterator::CURRENT_AS_FILEINFO;
        $flags |= \FilesystemIterator::SKIP_DOTS;

        $objects = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->directory, $flags),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($objects as $name => $object) {
            mtrace($name);
        }
    }

    /**
     * @return void
     */
    public function detach() {
        $this->directory = '';
        $this->dirbase   = '';
        $this->dirname   = '';
    }
}
