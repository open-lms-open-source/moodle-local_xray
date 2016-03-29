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
 * Class local_xray_api_testhelper_testcase
 * @group local_xray
 */
class local_xray_api_validationhelper_testcase extends advanced_testcase {

    /**
     * @return void
     */
    public function test_schema_login_ok() {
        $this->resetAfterTest(true);

        $requesturl = 'http://foo.com/user/login';
        // Load correct fixture.
        $json = file_get_contents(__DIR__.'/fixtures/user-login-final.json');
        $emsgs = \local_xray\local\api\validationhelper::validate_schema($json, $requesturl);
        $this->assertEmpty($emsgs);
    }

    /**
     * @return void
     */
    public function test_schema_login_fail() {
        $this->resetAfterTest(true);

        $requesturl = 'http://foo.com/user/login';
        // Load unexpected fixture.
        $json = file_get_contents(__DIR__.'/fixtures/user-accesstoken-final.json');
        $emsgs = \local_xray\local\api\validationhelper::validate_schema($json, $requesturl);
        $this->assertNotEmpty($emsgs);
    }

    /**
     * @return void
     */
    public function test_schema_accesskey_ok() {
        $this->resetAfterTest(true);

        $requesturl = 'http://foo.com/user/accesstoken';
        $json = file_get_contents(__DIR__.'/fixtures/user-accesstoken-final.json');
        $emsgs = \local_xray\local\api\validationhelper::validate_schema($json, $requesturl);
        $this->assertEmpty($emsgs);
    }

    /**
     * @return void
     */
    public function test_schema_accesskey_fail() {
        $this->resetAfterTest(true);

        $requesturl = 'http://foo.com/user/accesstoken';
        $json = file_get_contents(__DIR__.'/fixtures/data-accessible-wordHistogram-final.json');
        $emsgs = \local_xray\local\api\validationhelper::validate_schema($json, $requesturl);
        $this->assertNotEmpty($emsgs);
    }

    /**
     * @return void
     */
    public function test_schema_domaininfo_ok() {
        $this->resetAfterTest(true);

        $requesturl = 'http://foo.com/somedomain';
        $json = file_get_contents(__DIR__.'/fixtures/domain-final.json');
        $emsgs = \local_xray\local\api\validationhelper::validate_schema($json, $requesturl);
        $this->assertEmpty($emsgs);
    }

    /**
     * @return void
     */
    public function test_schema_domaininfo_fail() {
        $this->resetAfterTest(true);

        $requesturl = 'http://foo.com/somedomain';

        // To ensure we actually have the correct schema filename.
        $schemafile = \local_xray\local\api\validationhelper::generate_schema_name($requesturl);
        $this->assertEquals('domain-schema.json', $schemafile);

        $json = file_get_contents(__DIR__.'/fixtures/data-accessible-wordHistogram-final.json');
        $emsgs = \local_xray\local\api\validationhelper::validate_schema($json, $requesturl);
        $this->assertNotEmpty($emsgs);
    }

}
