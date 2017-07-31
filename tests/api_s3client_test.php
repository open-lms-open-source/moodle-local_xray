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

require_once(__DIR__.'/api_data_export_base.php');

/**
 * Class local_xray_api_s3client_testcase
 * @group local_xray
 */
class local_xray_api_s3client_testcase extends local_xray_api_data_export_base_testcase {

    /**
     * @param $major
     * @param $minor
     */
    public function test_inits3() {
        if (!$this->plugin_present('local_aws_sdk')) {
            $this->markTestSkipped('Aws SDK not present!');
        }

        $this->assertNotNull(local_xray\local\api\s3client::create());
    }

}
