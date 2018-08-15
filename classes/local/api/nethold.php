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
 * Convenient wrapper for curl resource handle.
 *
 * @package   local_xray
 * @author    Darko Miletic
 * @copyright Copyright (c) 2015 Blackboard Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xray\local\api;

defined('MOODLE_INTERNAL') || die();

/**
 * Class nethold
 *
 * @package   local_xray
 * @copyright Copyright (c) 2015 Blackboard Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class nethold {
    /**
     * @var null|resource
     */
    private $ch = null;

    /**
     * @param null|array $options
     * @throws \Exception
     */
    public function __construct($options = null) {
        $ch = curl_init();
        if (!is_resource($ch)) {
            throw new \Exception('Unable to initialize cURL!');
        }
        $this->ch = $ch;
        if (is_array($options)) {
            if (!$this->setopts($options)) {
                $emsg = $this->geterror();
                $eno = $this->geterrno();
                $this->reset();
                throw new \Exception($emsg, $eno);
            }
        }
    }

    public function __destruct() {
        $this->reset();
    }

    /**
     * @return null|resource
     */
    public function get() {
        return $this->ch;
    }

    /**
     * @return mixed
     */
    public function exec() {
        return curl_exec($this->ch);
    }

    /**
     * @param array $opts
     * @return bool
     */
    public function setopts($opts) {
        return curl_setopt_array($this->ch, $opts);
    }

    /**
     * @return mixed
     */
    public function getinfo() {
        return curl_getinfo($this->ch);
    }

    /**
     * @return string
     */
    public function geterror() {
        return curl_error($this->ch);
    }

    /**
     * @return int
     */
    public function geterrno() {
        return curl_errno($this->ch);
    }

    /**
     * Resets the curl object
     */
    protected function reset() {
        if (is_resource($this->ch)) {
            curl_close($this->ch);
            $this->ch = null;
        }
    }
}
