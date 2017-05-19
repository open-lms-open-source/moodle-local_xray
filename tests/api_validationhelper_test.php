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
     * @return array
     */
    public function ws_schema_provider_ok() {
        return [
            '/user/login ok' => ['http://foo.com/user/login', 'user-login-schema.json', 'user-login-final.json', true ],
            '/user/login error' => ['http://foo.com/user/login', 'user-login-schema.json', 'user-accesstoken-final.json', false],
            '/user/accesstoken ok' => ['http://foo.com/user/accesstoken', 'user-accesstoken-schema.json',
                'user-accesstoken-final.json', true],
            '/user/accesstoken error' => ['http://foo.com/user/accesstoken', 'user-accesstoken-schema.json',
                'data-accessible-wordHistogram-final.json', false],
            '/somedomain ok' => ['http://foo.com/somedomain', 'domain-schema.json', 'domain-final.json', true],
            '/somedomain error' => ['http://foo.com/somedomain', 'domain-schema.json',
                'data-accessible-wordHistogram-final.json', false],
            '/somedomain/course ok' => ['http://foo.com/somedomain/course', 'courses-schema.json', 'courses-final.json', true],
            '/somedomain/course error' => [
                'http://foo.com/somedomain/course', 'courses-schema.json',
                'data-accessible-wordHistogram-final.json', false
            ],
            '/somedomain/course/123/activity ok' => [
                'http://foo.com/somedomain/course/123/activity',
                'course-report-activity-schema.json',
                'course-report-activity-final_v2.json', true
            ],
            '/somedomain/course/123/activity error' => [
                'http://foo.com/somedomain/course/123/activity',
                'course-report-activity-schema.json',
                'data-accessible-wordHistogram-final.json', false
            ],
            '/somedomain/course/123/firstLogin ok' => [
                'http://foo.com/somedomain/course/123/firstLogin',
                'course-report-firstLogin-schema.json',
                'course-report-firstLogin-final_v2.json', true
            ],
            '/somedomain/course/123/firstLogin error' => [
                'http://foo.com/somedomain/course/123/firstLogin',
                'course-report-firstLogin-schema.json',
                'data-accessible-wordHistogram-final.json', false
            ],
            '/somedomain/course/123/risk ok' => [
                'http://foo.com/somedomain/course/123/risk',
                'course-report-risk-schema.json',
                'course-report-risk-final_v2.json', true
            ],
            '/somedomain/course/123/risk error' => [
                'http://foo.com/somedomain/course/123/risk',
                'course-report-risk-schema.json',
                'data-accessible-wordHistogram-final.json', false
            ],
            // We place here two correct tests of different size to ensure validation is ok.
            '/somedomain/course/123/discussion ok' => [
                'http://foo.com/somedomain/course/123/discussion',
                'course-report-discussion-schema.json',
                'course-report-discussion-final_v2.json', true
            ],
            '/somedomain/course/123/discussion ok 2' => [
                'http://foo.com/somedomain/course/123/discussion',
                'course-report-discussion-schema.json',
                'course-report-discussion-final_v3.json', true
            ],
            '/somedomain/course/123/discussion error' => [
                'http://foo.com/somedomain/course/123/discussion',
                'course-report-discussion-schema.json',
                'data-accessible-wordHistogram-final.json', false
            ],
            '/somedomain/course/123/discussionEndogenicPlagiarism ok' => [
                'http://foo.com/somedomain/course/123/discussionEndogenicPlagiarism',
                'course-report-discussionEndogenicPlagiarism-schema.json',
                'course-report-discussionEndogenicPlagiarism-final_v2.json', true
            ],
            '/somedomain/course/123/discussionEndogenicPlagiarism error' => [
                'http://foo.com/somedomain/course/123/discussionEndogenicPlagiarism',
                'course-report-discussionEndogenicPlagiarism-schema.json',
                'data-accessible-wordHistogram-final.json', false
            ],
            '/somedomain/course/123/discussionGrading ok' => [
                'http://foo.com/somedomain/course/123/discussionGrading',
                'course-report-discussionGrading-schema.json',
                'course-report-discussionGrading-final_v2.json', true
            ],
            '/somedomain/course/123/discussionGrading error' => [
                'http://foo.com/somedomain/course/123/discussionGrading',
                'course-report-discussionGrading-schema.json',
                'data-accessible-wordHistogram-final.json', false
            ],
            '/somedomain/course/123/gradebook ok' => [
                'http://foo.com/somedomain/course/123/gradebook',
                'course-report-gradebook-schema.json',
                'course-report-gradebook-final.json', true
            ],
            '/somedomain/course/123/gradebook error' => [
                'http://foo.com/somedomain/course/123/gradebook',
                'course-report-gradebook-schema.json',
                'data-accessible-wordHistogram-final.json', false
            ],
            '/somedomain/course/123/133/activity ok' => [
                'http://foo.com/somedomain/course/123/133/activity',
                'course-report-activity-user-schema.json',
                'course-report-activity-user-final.json', true
            ],
            '/somedomain/course/123/133/activity error' => [
                'http://foo.com/somedomain/course/123/133/activity',
                'course-report-activity-user-schema.json',
                'data-accessible-wordHistogram-final.json', false
            ],
            '/somedomain/course/123/133/discussion ok' => [
                'http://foo.com/somedomain/course/123/133/discussion',
                'course-report-discussion-user-schema.json',
                'course-report-discussion-user-final.json', true
            ],
            '/somedomain/course/123/133/discussion error' => [
                'http://foo.com/somedomain/course/123/133/discussion',
                'course-report-discussion-user-schema.json',
                'data-accessible-wordHistogram-final.json', false
            ],
        ];
    }

    /**
     * Central method that tests all json validations for web service methods
     *
     * @param  string $url
     * @param  string $schemafile
     * @param  string $jsonfile
     * @param  bool   $noerror
     * @return void
     *
     * @dataProvider ws_schema_provider_ok
     */
    public function test_webservice_schemas($url, $schemafile, $jsonfile, $noerror) {
        // Check if url is correctly recognized and schema name checks.
        $schemafilegot = \local_xray\local\api\validationhelper::generate_schema_name($url);
        $this->assertEquals($schemafile, $schemafilegot, "No schema file pattern for {$url}!");

        // Check if schema file is present.
        $schemareal = \local_xray\local\api\validationhelper::get_schema($url);
        $this->assertNotEmpty($schemareal, "{$schemafile} does not exist!");

        // Check if fixture exists.
        $file = __DIR__.'/fixtures/'.$jsonfile;
        $this->assertFileExists($file);

        $json  = file_get_contents($file);
        $emsgs = \local_xray\local\api\validationhelper::validate_schema($json, $url, false);
        $msg   = \local_xray\local\api\validationhelper::generate_message($emsgs);
        if ($noerror) {
            $this->assertEmpty($emsgs, $msg);
        } else {
            $this->assertNotEmpty($emsgs, $msg);
        }
    }

}