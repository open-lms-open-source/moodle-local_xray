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
 * Class local_xray_login_test
 *
 * All tests in this class will fail in case there is no appropriate fixture to be loaded.
 *
 * @group local_xray
 */
class local_xray_api_wsapi_testcase extends local_xray_base_testcase {

    /**
     * @return void
     */
    public function setUp() {
        $this->reset_ws();
    }

    /**
     * @return void
     */
    public function test_login_configured_ok() {
        $this->resetAfterTest(true);
        $this->config_set_ok();
        // Tell the cache to load specific fixture for login url.
        \local_xray\local\api\testhelper::push_pair('http://xrayserver.foo.com/user/login', 'user-login-final.json');
        $this->assertTrue( \local_xray\local\api\wsapi::login() );
    }

    /**
     * @return void
     */
    public function test_login_fail() {
        $this->resetAfterTest(true);
        $this->config_set_ok();

        // Tell the cache to load specific fixture for login url.
        \local_xray\local\api\testhelper::push_pair('http://xrayserver.foo.com/user/login', 'user-login-fail-final.json');

        $result = \local_xray\local\api\wsapi::login();
        $this->assertFalse($result);
    }

    /**
     * @return void
     */
    public function test_login_notconfigured_fail() {
        $this->resetAfterTest(true);
        $this->config_cleanup();
        $this->assertFalse( \local_xray\local\api\wsapi::login() );
    }

    /**
     * @return void
     */
    public function test_accesstoken_ok() {
        $this->resetAfterTest(true);
        $this->config_set_ok();

        \local_xray\local\api\testhelper::push_pair('http://xrayserver.foo.com/user/keylogin', 'user-login-final.json');
        \local_xray\local\api\testhelper::push_pair('http://xrayserver.foo.com/user/accesstoken', 'user-accesstoken-final.json');
        $this->assertEquals('z291TD', \local_xray\local\api\wsapi::accesstoken());
    }

    /**
     * @return void
     */
    public function test_accesstoken_fail() {
        $this->resetAfterTest(true);
        $this->config_cleanup();

        $this->assertFalse(\local_xray\local\api\wsapi::accesstoken());
    }

    /**
     * @return void
     */
    public function test_domaininfo_ok() {
        $this->resetAfterTest(true);
        $this->config_set_ok();

        \local_xray\local\api\testhelper::push_pair('http://xrayserver.foo.com/user/login', 'user-login-final.json');
        \local_xray\local\api\testhelper::push_pair('http://xrayserver.foo.com/demo', 'domain-final.json');
        $expected = json_decode(file_get_contents(__DIR__.'/fixtures/domain-final.json'));
        $this->assertEquals($expected, \local_xray\local\api\wsapi::domaininfo());
    }

    /**
     * @return void
     */
    public function test_domaininfo_fail() {
        $this->resetAfterTest(true);
        $this->config_set_ok();

        \local_xray\local\api\testhelper::push_pair('http://xrayserver.foo.com/user/login', 'user-login-final.json');
        \local_xray\local\api\testhelper::push_pair('http://xrayserver.foo.com/demo', 'user-accesstoken-final.json');
        $expected = json_decode(file_get_contents(__DIR__.'/fixtures/domain-final.json'));
        $this->assertNotEquals($expected, \local_xray\local\api\wsapi::domaininfo());
    }

    /**
     * @return void
     */
    public function test_courses_ok() {
        $this->resetAfterTest(true);
        $this->config_set_ok();

        \local_xray\local\api\testhelper::push_pair('http://xrayserver.foo.com/user/login', 'user-login-final.json');
        \local_xray\local\api\testhelper::push_pair('http://xrayserver.foo.com/demo/course', 'courses-final.json');
        $expected = json_decode(file_get_contents(__DIR__.'/fixtures/courses-final.json'));
        $this->assertEquals($expected, \local_xray\local\api\wsapi::courses());
    }

    /**
     * @return void
     */
    public function test_courses_fail() {
        $this->resetAfterTest(true);
        $this->config_set_ok();

        \local_xray\local\api\testhelper::push_pair('http://xrayserver.foo.com/user/login', 'user-login-final.json');
        \local_xray\local\api\testhelper::push_pair('http://xrayserver.foo.com/demo/course', 'domain-final.json');
        $expected = json_decode(file_get_contents(__DIR__.'/fixtures/courses-final.json'));
        $this->assertNotEquals($expected, \local_xray\local\api\wsapi::courses());
    }

}
