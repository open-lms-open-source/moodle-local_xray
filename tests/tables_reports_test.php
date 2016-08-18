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
 * Test correct creation of tables for reports.
 *
 * @author    Pablo Pagnone
 * @package   local_xray
 * @group local_xray
 * @group local_xray_tables_reports
 * @copyright Copyright (c) 2016 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_xray_tables_reports_testcase extends local_xray_base_testcase {

    /**
     * Plugin name.
     */
    const PLUGIN_NAME = "local_xray";

    /**
     * Course created for test.
     * @var stdClass
     */
    private $course;

    /**
     * @var local_xray_renderer
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

        // Set url of controller discussionreport. (We need to have the controller seted to create table).
        $PAGE->set_url('/local/xray/view.php',
            array('id' => $this->course->id, 'controller' => 'discussionreport', 'action' => 'view'));
        $this->renderer = $PAGE->get_renderer(self::PLUGIN_NAME);
    }

    /**
     * Method for test correct structure when table is created with data of json response.
     */
    public function test_creation_table() {

        // Tell the cache to load specific fixture for login.
        \local_xray\local\api\testhelper::push_pair('http://xrayserver.foo.com/user/login', 'user-login-final.json');
        \local_xray\local\api\testhelper::push_pair('http://xrayserver.foo.com/demo', 'domain-final.json');

        // Tell the cache to load specific fixture for discussion report.
        $url = 'http://xrayserver.foo.com/demo/course/'.$this->course->id.'/discussionGrading';
        /* @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        \local_xray\local\api\testhelper::push_pair($url,
            'course-report-discussionGrading-final.json');

        // Get response for discussion report.
        $response = \local_xray\local\api\wsapi::course($this->course->id, "discussionGrading");

        // Create object datatables for table "studentDiscussionGrades".
        $datatable = new local_xray\datatables\datatables($response->elements->studentDiscussionGrades,
            "rest.php?controller='discussionreport'&action='jsonstudentsgrades'&courseid=" . $this->course->id);

        // Create table "studentDiscussionGrades".
        $tableoutput = $this->renderer->standard_table((array) $datatable);

        // Check if h3 element contains class "xray-table-title-link xray-reportsname".
        $this->assertContains('<h3 class="xray-reportsname"', $tableoutput);

        // Check if exist div with class toggletable.
        $this->assertContains('<div id="studentDiscussionGrades" class="xray-toggleable-table"', $tableoutput);

        // Check if id start with "xray-js-table-".
        $this->assertContains('<table id="xray-js-table-studentDiscussionGrades', $tableoutput);

        // Check if exist button for close the table.
        $this->assertContains('<div class="xray-closetable">'.
                              '<a href="#studentDiscussionGrades-toggle">Close table</a></div>', $tableoutput);

    }
}