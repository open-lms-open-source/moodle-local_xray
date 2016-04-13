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
 * Class local_xray_api_data_export_delete_testcase
 * @group local_xray1
 */
class local_xray_api_data_export_delete_testcase extends local_xray_api_data_export_base_testcase {

    /**
     * preset
     */
    public function setUp() {
        $this->init_base();
    }

    /**
     * Test forum discussions and posts delete
     */
    public function test_forum_discussions_delete_export() {
        if (!$this->plugin_present('mod_forum')) {
            $this->markTestSkipped('Forum not present!');
        }

        $this->resetAfterTest();

        $timenow = time();
        $timepast = $timenow - DAYSECS;
        $courses = $this->addcourses(5, $timepast);
        list($forums, $discussions, $posts) = $this->addforums(5, $courses);
        ($forums); ($posts);
        $count = 0;
        foreach ($discussions as $discussion) {
            if ($count++ > 3) {
                break;
            }

            $cmid = null;
            foreach ($forums as $forum) {
                if ($forum->id == $discussion->forum) {
                    $cmid = $forum->cmid;
                }
            }

            // This removes complete discussion with posts inside.
            $this->delete_discussion($discussion->id, $discussion->course, $cmid, $discussion->forum);
        }

        // Export.
        $storage = new local_xray\local\api\auto_clean();
        $storagedir = $storage->get_directory();
        $this->export($timenow, $storagedir);

        $exportfile = $storagedir.DIRECTORY_SEPARATOR.'threads_delete_00000001.csv';
        $this->assertFileExists($exportfile);

        $first = true;
        $iterator = new csv_fileiterator($exportfile);
        foreach ($iterator as $item) {
            if ($first) {
                $first = false;
                continue;
            }
            // Expect 4 items.
            $this->assertEquals(4, count($item));
            $this->assertInternalType('numeric', $item[0]);
            $this->assertInternalType('numeric', $item[1]);
            $this->assertInternalType('numeric', $item[2]);
            $this->assertInternalType('string' , $item[3]);
        }

        $exportfile = $storagedir.DIRECTORY_SEPARATOR.'posts_delete_00000001.csv';
        $this->assertFileExists($exportfile);

        $first = true;
        $iterator = new csv_fileiterator($exportfile);
        foreach ($iterator as $item) {
            if ($first) {
                $first = false;
                continue;
            }
            // Expect 5 items.
            $this->assertEquals(5, count($item));
            $this->assertInternalType('numeric', $item[0]);
            $this->assertInternalType('numeric', $item[1]);
            $this->assertInternalType('numeric', $item[2]);
            $this->assertInternalType('numeric', $item[3]);
            $this->assertInternalType('string' , $item[4]);
        }

    }

    /**
     * Test forum discussions and posts delete
     */
    public function test_hsuforum_discussions_delete_export() {
        if (!$this->plugin_present('mod_hsuforum')) {
            $this->markTestSkipped('Advanced Forum not present!');
        }

        $this->resetAfterTest();

        $timenow = time();
        $timepast = $timenow - DAYSECS;
        $courses = $this->addcourses(5, $timepast);
        list($forums, $discussions, $posts) = $this->addhsuforums(5, $courses);
        ($forums); ($posts);
        $count = 0;
        foreach ($discussions as $discussion) {
            if ($count++ > 3) {
                break;
            }

            $cmid = null;
            foreach ($forums as $forum) {
                if ($forum->id == $discussion->forum) {
                    $cmid = $forum->cmid;
                }
            }

            // This removes complete discussion with posts inside.
            $this->delete_hsudiscussion($discussion->id, $discussion->course, $cmid, $discussion->forum);
        }

        // Export.
        $storage = new local_xray\local\api\auto_clean();
        $storagedir = $storage->get_directory();
        $this->export($timenow, $storagedir);

        $exportfile = $storagedir.DIRECTORY_SEPARATOR.'hsuthreads_delete_00000001.csv';
        $this->assertFileExists($exportfile);

        $first = true;
        $iterator = new csv_fileiterator($exportfile);
        foreach ($iterator as $item) {
            if ($first) {
                $first = false;
                continue;
            }
            // Expect 4 items.
            $this->assertEquals(4, count($item));
            $this->assertInternalType('numeric', $item[0]);
            $this->assertInternalType('numeric', $item[1]);
            $this->assertInternalType('numeric', $item[2]);
            $this->assertInternalType('string' , $item[3]);
        }

        $exportfile = $storagedir.DIRECTORY_SEPARATOR.'hsuposts_delete_00000001.csv';
        $this->assertFileExists($exportfile);

        $first = true;
        $iterator = new csv_fileiterator($exportfile);
        foreach ($iterator as $item) {
            if ($first) {
                $first = false;
                continue;
            }
            // Expect 5 items.
            $this->assertEquals(5, count($item));
            $this->assertInternalType('numeric', $item[0]);
            $this->assertInternalType('numeric', $item[1]);
            $this->assertInternalType('numeric', $item[2]);
            $this->assertInternalType('numeric', $item[3]);
            $this->assertInternalType('string' , $item[4]);
        }

    }

    public function test_course_delete_export() {
        $this->resetAfterTest();
        $this->assertNotEmpty(null);
    }

    public function test_coursecategory_delete_export() {
        $this->resetAfterTest();
        $this->assertNotEmpty(null);
    }

    public function test_user_delete_export() {
        $this->resetAfterTest();
        $this->assertNotEmpty(null);
    }

    public function test_activity_delete_export() {
        $this->resetAfterTest();
        $this->assertNotEmpty(null);
    }

    public function test_enrol_delete_export() {
        $this->resetAfterTest();
        $this->assertNotEmpty(null);
    }

}
