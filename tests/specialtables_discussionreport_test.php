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
 * Discussion report implementation tests.
 *
 * @package   local_xray
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__.'/base.php');

/**
 * Test for check the correct creation of columns in special tables of discussion report:
 * Methods discussionreport_discussion_activity_by_week() and discussionreportindividual_discussion_activity_by_week().
 *
 * @author    Pablo Pagnone
 * @package   local_xray
 * @group local_xray
 * @group local_xray_discussionreport
 * @copyright Copyright (c) 2016 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_xray_specialtables_discussionreport_testcase extends local_xray_base_testcase {

    /**
     * Plugin name.
     */
    const PLUGIN_NAME = "local_xray";

    /**
     * Course created for test.
     */
    private $course;

    /**
     * User created for test.
     */
    private $user;

    /**
     * Renderer local_xray.
     */
    private $renderer;

    /**
     * Setup test data.
     */
    public function setUp() {

        global $PAGE;
        $this->resetAfterTest(true);
        $this->config_set_ok();
        $this->course = $this->getDataGenerator()->create_course();
        $this->user = $this->getDataGenerator()->create_user();

        // Set url of discussionreport.
        $PAGE->set_url('/local/xray/view.php', array('id' => $this->course->id, 'controller' => 'discussionreport', 'action' => 'view'));
        $this->renderer = $PAGE->get_renderer(self::PLUGIN_NAME);
    }

    /**
     * Method for test discussionreport_discussion_activity_by_week()
     * Check if data returned by method with json with data is correct.
     */
    public function test_discussionreport_column_creation_from_json_with_data() {

        // Tell the cache to load specific fixture for discussion report.
        $url = 'http://xrayserver.foo.com/demo/course/'.$this->course->id.'/discussion';
        /* @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        \local_xray\local\api\testhelper::push_pair($url,
            'course-report-discussion-final-withdiscussionActivityByWeekWithData.json');

        // Get response for discussion report.
        $response = \local_xray\local\api\wsapi::course($this->course->id, "discussion");
        $tableoutput = $this->renderer->discussionreport_discussion_activity_by_week($this->course->id,
            $response->elements->discussionActivityByWeek);

        $this->assertStringStartsWith('<h3 class="xray-table-title-link xray-reportsname">', $tableoutput);
    }

    /**
     * Method for test discussionreport_discussion_activity_by_week()
     * Check if data returned by method with json without data is correct.
     */
    public function test_discussionreport_column_creation_from_json_empty() {

        // Tell the cache to load specific fixture for discussion report.
        $url = 'http://xrayserver.foo.com/demo/course/'.$this->course->id.'/discussion';
        /* @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        \local_xray\local\api\testhelper::push_pair($url,
            'course-report-discussion-final-withdiscussionActivityByWeekEmpty.json');

        // Get response for discussion report.
        $response = \local_xray\local\api\wsapi::course($this->course->id, "discussion");
        $tableoutput = $this->renderer->discussionreport_discussion_activity_by_week($this->course->id,
            $response->elements->discussionActivityByWeek);

        $this->assertStringStartsWith('<h3 class="xray-table-title-link xray-reportsname">', $tableoutput);
    }

    /**
     * Method for test discussionreportindividual_discussion_activity_by_week()
     * Check if data returned by method with json with data is correct.
     */
    public function test_discussionreportindividual_column_creation_from_json_with_data() {

        // Tell the cache to load specific fixture for discussion report.
        $url = 'http://xrayserver.foo.com/demo/course/'.$this->course->id.'/discussion';
        /* @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        \local_xray\local\api\testhelper::push_pair($url,
            'course-report-discussion-final-withdiscussionActivityByWeekWithData.json');

        // Get response for discussion report.
        $response = \local_xray\local\api\wsapi::course($this->course->id, "discussion");
        $tableoutput = $this->renderer->discussionreportindividual_discussion_activity_by_week($this->course->id,
            $response->elements->discussionActivityByWeek);

        $this->assertStringStartsWith('<h3 class="xray-table-title-link xray-reportsname">', $tableoutput);
    }

    /**
     * Method for test discussionreportindividual_discussion_activity_by_week()
     * Check if data returned by method with json without data is correct.
     */
    public function test_discussionreportindividual_column_creation_from_json_empty() {

        // Tell the cache to load specific fixture for discussion report.
        $url = 'http://xrayserver.foo.com/demo/course/'.$this->course->id.'/discussion';
        /* @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        \local_xray\local\api\testhelper::push_pair($url,
            'course-report-discussion-final-withdiscussionActivityByWeekEmpty.json');

        // Get response for discussion report.
        $response = \local_xray\local\api\wsapi::course($this->course->id, "discussion");
        $tableoutput = $this->renderer->discussionreportindividual_discussion_activity_by_week($this->course->id,
            $response->elements->discussionActivityByWeek);

        $this->assertStringStartsWith('<h3 class="xray-table-title-link xray-reportsname">', $tableoutput);
    }
}