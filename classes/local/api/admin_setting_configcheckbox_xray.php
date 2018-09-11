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
 * Custom setting checkbox class.
 *
 * @package   local_xray
 * @author    Darko Miletic
 * @copyright Copyright (c) 2015 Blackboard Inc. (http://www.blackboard.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xray\local\api;

defined('MOODLE_INTERNAL') || die();

/**
 * Class admin_setting_configcheckbox_xray
 *
 * @package local_xray
 * @author    Darko Miletic
 * @copyright Copyright (c) 2015 Blackboard Inc. (http://www.blackboard.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_setting_configcheckbox_xray extends \admin_setting_configcheckbox {

    /**
     * @param $name
     * @param $visiblename
     * @param $description
     * @param $defaultsetting
     * @param string $yes
     * @param string $no
     */
    public function __construct($name, $visiblename, $description, $defaultsetting, $yes='1', $no='0') {
        parent::__construct($name, $visiblename, $description, $defaultsetting, $yes, $no);
        $configval = $this->get_setting();
        if ($configval == $this->yes) {
            $this->write_setting($this->no);
        }
        $this->nosave = $this->disablewrite();
    }

    /**
     * @return bool
     */
    protected function disablewrite() {
        global $PAGE;
        $result = false;
        if (!during_initial_install() && !CLI_SCRIPT && $PAGE->has_set_url()) {
            $url = new \moodle_url('/admin/settings.php', ['section' => 'local_xray_global']);
            $result = $PAGE->url->compare($url);
        }
        return $result;
    }

    /**
     * @param $data
     * @return mixed
     */
    public function write_setting($data) {
        if ($this->nosave && ((string)$data === $this->yes)) {
            data_export::delete_progress_settings();
        }
        return parent::write_setting($data);
    }
}
