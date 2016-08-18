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

require_once(__DIR__.'/base.php');

/**
 * Class local_xray_api_jwthelper_testcase
 * @group local_xray
 */
class local_xray_api_jwthelper_testcase extends local_xray_base_testcase {

    protected function config_set_ok() {
        parent::config_set_ok();
        set_config('awssecret', 'WAzk9ohDeK', parent::PLUGIN);
    }

    protected function config_cleanup() {
        parent::config_cleanup();
        unset_config('awssecret', parent::PLUGIN);
    }

    public function test_jwttoken_false() {
        $token = \local_xray\local\api\jwthelper::get_token();
        $this->assertFalse($token);
    }

    public function test_jwttoken_ok() {
        $this->resetAfterTest(true);
        $this->config_set_ok();

        $token = \local_xray\local\api\jwthelper::get_token();
        $this->assertNotFalse($token);
    }

    public function test_jwttoken_valid() {
        $this->resetAfterTest(true);
        $this->config_set_ok();

        $token = \local_xray\local\api\jwthelper::get_token();
        $valid = \local_xray\local\api\jwthelper::validate_token($token);
        $this->assertTrue($valid);
    }
}