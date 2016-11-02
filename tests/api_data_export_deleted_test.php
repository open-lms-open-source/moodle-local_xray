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
 * @group local_xray
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
        global $DB;

        $this->resetAfterTest();

        $timenow = time() + HOURSECS;
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

        // Check data pruning.
        $task = new \local_xray\task\data_prune_task();
        $task->execute();
        $this->assertEquals(0, $DB->count_records('local_xray_disc'));
    }

    /**
     * Test forum discussions and posts delete
     */
    public function test_hsuforum_discussions_delete_export() {
        if (!$this->plugin_present('mod_hsuforum')) {
            $this->markTestSkipped('Advanced Forum not present!');
        }

        global $DB;

        $this->resetAfterTest();

        $timenow = time() + HOURSECS;
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

        // Check data pruning.
        $task = new \local_xray\task\data_prune_task();
        $task->execute();
        $this->assertEquals(0, $DB->count_records('local_xray_hsudisc'));
    }

    /**
     * test course delete export
     */
    public function test_course_delete_export() {
        global $DB;

        $this->resetAfterTest();

        $timenow = time() + HOURSECS;
        $timepast = $timenow - DAYSECS;
        $courses = $this->addcourses(5, $timepast);

        for ($count = 0; $count < 3; $count++) {
            delete_course($courses[$count], false);
        }

        // Export.
        $storage = new local_xray\local\api\auto_clean();
        $storagedir = $storage->get_directory();
        $this->export($timenow, $storagedir);

        $exportfile = $storagedir.DIRECTORY_SEPARATOR.'courseinfo_delete_00000001.csv';
        $this->assertFileExists($exportfile);

        $counter = 0;
        $first = true;
        $iterator = new csv_fileiterator($exportfile);
        foreach ($iterator as $item) {
            if ($first) {
                $first = false;
                continue;
            }
            // Expect 4 items.
            $this->assertEquals(3, count($item));
            $this->assertInternalType('numeric', $item[0]);
            $this->assertInternalType('numeric', $item[1]);
            $this->assertEquals($courses[$counter]->id, $item[1]);
            $this->assertInternalType('string' , $item[2]);
            $counter++;
        }

        // Check data pruning.
        $task = new \local_xray\task\data_prune_task();
        $task->execute();
        $this->assertEquals(0, $DB->count_records('local_xray_course'));
    }

    /**
     * test course category deletion
     *
     * @throws moodle_exception
     */
    public function test_coursecategory_delete_export() {
        global $DB;
        $this->resetAfterTest();

        $timenow = time() + HOURSECS;
        $timepast = $timenow - DAYSECS;
        $categories = $this->addcategories(5, $timepast);

        for ($count = 0; $count < 3; $count++) {
            $categories[$count]->delete_full(false);
        }

        // Export.
        $storage = new local_xray\local\api\auto_clean();
        $storagedir = $storage->get_directory();
        $this->export($timenow, $storagedir);

        $exportfile = $storagedir.DIRECTORY_SEPARATOR.'coursecategories_delete_00000001.csv';
        $this->assertFileExists($exportfile);

        $counter = 0;
        $first = true;
        $iterator = new csv_fileiterator($exportfile);
        foreach ($iterator as $item) {
            if ($first) {
                $first = false;
                continue;
            }
            // Expect 4 items.
            $this->assertEquals(3, count($item));
            $this->assertInternalType('numeric', $item[0]);
            $this->assertInternalType('numeric', $item[1]);
            $this->assertEquals($categories[$counter]->id, $item[1]);
            $this->assertInternalType('string' , $item[2]);
            $counter++;
        }

        // Check data pruning.
        $task = new \local_xray\task\data_prune_task();
        $task->execute();
        $this->assertEquals(0, $DB->count_records('local_xray_coursecat'));
    }

    /**
     * test user deletion
     */
    public function test_user_delete_export() {
        $this->resetAfterTest();

        $timenow = time() + HOURSECS;

        $users = $this->addusers(5);
        for ($count = 0; $count < 3; $count++) {
            delete_user($users[$count]);
        }

        // Export.
        $storage = new local_xray\local\api\auto_clean();
        $storagedir = $storage->get_directory();
        $this->export($timenow, $storagedir);

        $exportfile = $storagedir.DIRECTORY_SEPARATOR.'userlist_00000001.csv';
        $this->assertFileExists($exportfile);

        $first = true;
        $iterator = new csv_fileiterator($exportfile);
        foreach ($iterator as $item) {
            if ($first) {
                $first = false;
                continue;
            }
            // Expect 11 items.
            $this->assertEquals(11, count($item));
            $this->assertInternalType('numeric', $item[0]);
            $this->assertInternalType('string' , $item[1]);
            $this->assertInternalType('string' , $item[2]);
            if (!empty($item[3])) {
                $this->assertInternalType('string', $item[3]);
            }
            $this->assertInternalType('string' , $item[4]);
            $this->assertInternalType('numeric', $item[5]);
            $this->assertInternalType('numeric', $item[6]);
            $this->assertInternalType('string' , $item[7]);
            $this->assertInternalType('string' , $item[8]);
            $this->assertInternalType('string' , $item[9]);
            $this->assertInternalType('string' , $item[10]);
        }

    }

    /**
     * test course module delete
     * @throws moodle_exception
     */
    public function test_activity_delete_export() {
        if (!$this->plugin_present('mod_quiz')) {
            $this->markTestSkipped('Quiz not present!');
        }

        global $DB;

        $this->resetAfterTest();

        $timenow = time() + HOURSECS;
        $timepast = $timenow - DAYSECS;
        $courses = $this->addcourses(5, $timepast);
        $quizes = $this->addquizzes(5, $courses);

        /* @noinspection PhpIncludeInspection */
        require_once(core_component::get_component_directory('core_course').'/lib.php');

        $deleted = [];
        for ($count = 0; $count < 10; $count++) {
            course_delete_module($quizes[$count]->cmid);
            $deleted[] = (int)$quizes[$count]->cmid;
        }

        // Export.
        $storage = new local_xray\local\api\auto_clean();
        $storagedir = $storage->get_directory();
        $this->export($timenow, $storagedir);

        $exportfile = $storagedir.DIRECTORY_SEPARATOR.'activity_delete_00000001.csv';
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
            $this->assertTrue(in_array($item[1], $deleted));
            $this->assertInternalType('numeric', $item[2]);
            $this->assertInternalType('string' , $item[3]);
        }

        // Check data pruning.
        $task = new \local_xray\task\data_prune_task();
        $task->execute();
        $this->assertEquals(0, $DB->count_records('local_xray_cm'));
    }

    /**
     * test role assignment removal on course context
     * @throws coding_exception
     */
    public function test_enrol_delete_export() {
        global $DB;

        $this->resetAfterTest();

        $timenow = time() + HOURSECS;
        $timepast = $timenow - DAYSECS;
        $courses = $this->addcourses(5, $timepast);
        $this->addquizzes(5, $courses);
        $user = $this->user_set($courses, 'quiz');
        $authmanual = enrol_get_plugin('manual');
        for ($pos = 0; $pos < 3; $pos++) {
            $instance = null;
            $instances = enrol_get_instances($courses[$pos]->id, true);
            foreach ($instances as $inst) {
                if ($inst->enrol == 'manual') {
                    $instance = $inst;
                    break;
                }
            }
            $authmanual->unenrol_user($instance, $user->id);
        }

        // Export.
        $storage = new local_xray\local\api\auto_clean();
        $storagedir = $storage->get_directory();
        $this->export($timenow, $storagedir);

        $exportfile = $storagedir.DIRECTORY_SEPARATOR.'enrolment_deletev2_00000001.csv';
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
            $this->assertInternalType('numeric', $item[4]);

        }

        // Check data pruning.
        $task = new \local_xray\task\data_prune_task();
        $task->execute();
        $this->assertEquals(0, $DB->count_records('local_xray_enroldel'));
    }

    /**
     * Helper function for unenrolling the users from courses
     *
     * @param int        $start
     * @param int        $end
     * @param stdClass[] $courses
     * @param stdClass   $user
     */
    protected function unenrol_users($start, $end, $courses, $user) {
        $authmanual = enrol_get_plugin('manual');
        for ($pos = $start; $pos < $end; $pos++) {
            $instance = null;
            $instances = enrol_get_instances($courses[$pos]->id, true);
            foreach ($instances as $inst) {
                if ($inst->enrol == 'manual') {
                    $instance = $inst;
                    break;
                }
            }
            $authmanual->unenrol_user($instance, $user->id);
        }
    }

    /**
     * @param  string $exportfile
     * @return int[]
     */
    protected function get_export_data_keys($exportfile) {
        $first = true;
        $iterator = new csv_fileiterator($exportfile);
        $result = [];
        foreach ($iterator as $item) {
            if ($first) {
                $first = false;
                continue;
            }

            $result[] = (int)$item[0];

        }

        return $result;
    }

    /**
     * Additional export test (dedicated to Leeloo)
     */
    public function test_enrol_delete_export_multipass() {
        $this->resetAfterTest();

        $timenow = time() + HOURSECS;
        $timepast = $timenow - DAYSECS;
        $courses = $this->addcourses(5, $timepast);
        $this->addquizzes(5, $courses);
        $user = $this->user_set($courses, 'quiz');
        $this->unenrol_users(0, 3, $courses, $user);

        // Export.
        $storage = new local_xray\local\api\auto_clean();
        $storagedir = $storage->get_directory();
        $this->export($timenow, $storagedir);

        $exportfile = $storagedir.DIRECTORY_SEPARATOR.'enrolment_deletev2_00000001.csv';
        $this->assertFileExists($exportfile);
        $exportkeys1 = $this->get_export_data_keys($exportfile);

        $this->unenrol_users(3, 5, $courses, $user);
        $storage2 = new local_xray\local\api\auto_clean();
        $storagedir2 = $storage2->get_directory();
        $this->export($timenow, $storagedir2);

        $exportfile = $storagedir2.DIRECTORY_SEPARATOR.'enrolment_deletev2_00000001.csv';
        $this->assertFileExists($exportfile);
        $exportkeys2 = $this->get_export_data_keys($exportfile);

        $testset = array_merge($exportkeys1, $exportkeys2);
        $final = array_count_values($testset);
        foreach ($final as $key => $count) {
            $this->assertEquals(1, $count, "Key $key has duplicate(s)!");
        }
    }
}
