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

/**
 * Class local_xray_api_data_export_testcase
 * @group local_xray
 */
class local_xray_api_data_export_testcase extends advanced_testcase {

    /**
     * checks if specific plugin is present and enabled
     * @param $component - plugin component
     * @return bool
     */
    protected function plugin_present($component) {
        list($type, $plugin) = core_component::normalize_component($component);
        $plugins = \core_plugin_manager::instance()->get_enabled_plugins($type);
        return in_array($plugin, $plugins);
    }

    /**
     * Creates courses needed for test
     *
     * @param int $nr
     * @param int $timecreated
     * @return array
     */
    protected function addcourses($nr, $timecreated = null) {
        if (empty($timecreated)) {
            $timecreated = time();
        }

        $record = [
            'timecreated' => $timecreated,
            'startdate'   => $timecreated
        ];
        // Create course(s).
        $datagen = $this->getDataGenerator();
        $courses = [];
        $count = 0;
        while ($count++ < $nr) {
            $courses[] = $datagen->create_course($record);
        }

        return $courses;
    }

    /**
     * Creates forums and discussions needed for the test for specified type of forum
     *
     * @param int $nr
     * @param array $courses
     * @param string $module
     * @throws coding_exception
     */
    protected function addforumsbase($nr, $courses, $module) {
        global $USER;
        /* @var mod_forum_generator|mod_hsuforum_generator $forumgen */
        $forumgen = $this->getDataGenerator()->get_plugin_generator($module);
        foreach ($courses as $course) {
            $count = 0;
            while ($count++ < $nr) {
                $instance = $forumgen->create_instance(['type' => 'general', 'course' => $course]);
                $discussion = $forumgen->create_discussion(['course' => $course->id,
                                                            'forum'  => $instance->id,
                                                            'userid' => $USER->id]);
                $forumgen->create_post(['discussion' => $discussion->id, 'userid' => $USER->id]);
            }
        }

    }

    /**
     * @param int $nr
     * @param array $courses
     */
    protected function addhsuforums($nr, $courses) {
        $this->addforumsbase($nr, $courses, 'mod_hsuforum');
    }

    /**
     * @param int $nr
     * @param array $courses
     */
    protected function addforums($nr, $courses) {
        $this->addforumsbase($nr, $courses, 'mod_forum');
    }

    /**
     * Add specified ammount of quizzes
     *
     * @param int $nr
     * @param array $courses
     * @return array
     * @throws coding_exception
     */
    protected function addquizzes($nr, $courses) {
        ($nr);
        /* @var mod_quiz_generator $quizgen */
        $quizgen = $this->getDataGenerator()->get_plugin_generator('mod_quiz');
        $instances = [];
        foreach ($courses as $course) {
            $instances[] = $quizgen->create_instance(['course' => $course]);
        }
        return $instances;
    }

    /**
     * Export data to csv files
     *
     * @param $timeend
     * @param $dir
     */
    protected function export($timeend, $dir) {
        local_xray\local\api\data_export::export_csv(0, $timeend, $dir);
        local_xray\local\api\data_export::store_counters();
    }

    /**
     * Set the student account
     * 
     * @param array $courses
     * @param string $module
     */
    protected function user_set($courses, $module) {
        global $DB;
        $datagen = $this->getDataGenerator();
        $student = $datagen->create_user(['username' => 'supercalifragilisticoexpialidoso']);
        $roleid = $DB->get_field('role', 'id', ['shortname' => 'student'], MUST_EXIST);
        $timenow = time();
        foreach ($courses as $course) {
            $datagen->enrol_user($student->id, $course->id, $roleid);
            /* @var grade_item[] $gitems */
            $gitems = grade_item::fetch_all(['itemmodule' => $module, 'courseid' => $course->id]);
            foreach ($gitems as $gitem) {
                $gitem->update_raw_grade($student->id, 80.0, null, false, FORMAT_MOODLE, null, $timenow, $timenow);
            }
        }
    }

    /**
     * preset
     */
    public function setUp() {
        // Reset any progress saved.
        local_xray\local\api\data_export::delete_progress_settings();
        $this->setAdminUser();

        // Check is required since many hosting companies disable this function.
        if (function_exists('sys_get_temp_dir')) {
            // This is unfortunate workaround for issues with nfs locking when using Vagrant.
            // If returned directory does not offer sufficient permissions the default is used.
            set_config('exportlocation', sys_get_temp_dir(), 'local_xray');
        }
    }

    /**
     * Add's 5 forums with some content, exports them in csv and checks the export format.
     *
     * @return void
     */
    public function test_forums_export() {
        if (!$this->plugin_present('mod_forum')) {
            $this->markTestSkipped('Forum not present!');
        }

        $this->resetAfterTest();

        $timenow = time();
        $timepast = $timenow - DAYSECS;
        $courses = $this->addcourses(5, $timepast);
        $this->addforums(5, $courses);

        // Export.
        $storage = new local_xray\local\api\auto_clean();
        $storagedir = $storage->get_directory();
        $this->export($timenow, $storagedir);

        $exportfile = $storagedir.DIRECTORY_SEPARATOR.'forums_00000001.csv';
        $this->assertFileExists($exportfile);

        $first = true;
        $iterator = new csv_fileiterator($exportfile);
        foreach ($iterator as $item) {
            if ($first) {
                $first = false;
                continue;
            }
            // Expect 7 items.
            $this->assertEquals(7, count($item));
            $this->assertInternalType('numeric', $item[0]);
            $this->assertInternalType('numeric', $item[1]);
            $this->assertInternalType('numeric', $item[2]);
            $this->assertInternalType('string' , $item[3]);
            $this->assertInternalType('string' , $item[4]);
            $this->assertInternalType('string' , $item[5]);
            $this->assertInternalType('string' , $item[6]);
        }

    }

    /**
     * Add's 5 advanced forums with some content, exports them in csv and checks the export format.
     * @return void
     */
    public function test_hsuforums_export() {
        if (!$this->plugin_present('mod_hsuforum')) {
            $this->markTestSkipped('Advanced Forum not present!');
        }

        $this->resetAfterTest();

        $timenow = time();
        $timepast = $timenow - DAYSECS;
        $courses = $this->addcourses(5, $timepast);
        $this->addhsuforums(5, $courses);

        // Export.
        $storage = new local_xray\local\api\auto_clean();
        $storagedir = $storage->get_directory();
        $this->export($timenow, $storagedir);

        $exportfile = $storagedir.DIRECTORY_SEPARATOR.'hsuforums_00000001.csv';
        $this->assertFileExists($exportfile);

        $first = true;
        $iterator = new csv_fileiterator($exportfile);
        foreach ($iterator as $item) {
            if ($first) {
                $first = false;
                continue;
            }
            // Expect 7 items.
            $this->assertEquals(7, count($item));
            $this->assertInternalType('numeric', $item[0]);
            $this->assertInternalType('numeric', $item[1]);
            $this->assertInternalType('numeric', $item[2]);
            $this->assertInternalType('string' , $item[3]);
            $this->assertInternalType('string' , $item[4]);
            $this->assertInternalType('string' , $item[5]);
            $this->assertInternalType('string' , $item[6]);
        }

    }

    /**
     * Add's 5 quizes with some content, exports them in csv and checks the export format.
     * @return void
     */
    public function test_quiz_export() {
        if (!$this->plugin_present('mod_quiz')) {
            $this->markTestSkipped('Quiz not present!');
        }

        $this->resetAfterTest();

        $timenow = time();
        $timepast = $timenow - DAYSECS;
        $courses = $this->addcourses(5, $timepast);
        $this->addquizzes(5, $courses);

        // Export.
        $storage = new local_xray\local\api\auto_clean();
        $storagedir = $storage->get_directory();
        $this->export($timenow, $storagedir);

        $exportfile = $storagedir.DIRECTORY_SEPARATOR.'quiz_00000001.csv';
        $this->assertFileExists($exportfile);

        $first = true;
        $iterator = new csv_fileiterator($exportfile);
        foreach ($iterator as $item) {
            if ($first) {
                $first = false;
                continue;
            }
            // Expect 7 items.
            $this->assertEquals(7, count($item));
            $this->assertInternalType('numeric', $item[0]);
            $this->assertInternalType('numeric', $item[1]);
            $this->assertInternalType('numeric', $item[2]);
            $this->assertInternalType('string' , $item[3]);
            $this->assertInternalType('numeric', $item[4]);
            $this->assertInternalType('numeric', $item[5]);
            $this->assertInternalType('string' , $item[6]);
        }
    }

    /**
     * Add's 5 quizzes with some grades, exports them in csv and checks the export format.
     * @return void
     */
    public function test_grades_export() {

        $this->resetAfterTest();

        $timenow = time();
        $timepast = $timenow - DAYSECS;
        $courses = $this->addcourses(5, $timepast);
        $this->addquizzes(5, $courses);
        $this->user_set($courses, 'quiz');

        // Export.
        $storage = new local_xray\local\api\auto_clean();
        $storagedir = $storage->get_directory();
        $this->export($timenow, $storagedir);

        $exportfile = $storagedir.DIRECTORY_SEPARATOR.'grades_00000001.csv';
        $this->assertFileExists($exportfile);

        $first = true;
        $iterator = new csv_fileiterator($exportfile);
        foreach ($iterator as $item) {
            if ($first) {
                $first = false;
                continue;
            }
            // Expect 7 items.
            $this->assertEquals(13, count($item));
            $this->assertInternalType('numeric', $item[0]);
            $this->assertInternalType('numeric', $item[1]);
            if (!empty($item[2])) {
                $this->assertInternalType('numeric', $item[2]);
            }
            $this->assertInternalType('numeric', $item[3]);
            if (!empty($item[4])) {
                $this->assertInternalType('string', $item[4]);
            }
            $this->assertInternalType('string' , $item[5]);
            $this->assertInternalType('numeric', $item[6]);
            $this->assertInternalType('numeric', $item[7]);
            if (!empty($item[8])) {
                $this->assertInternalType('numeric', $item[8]);
            }
            $this->assertInternalType('numeric', $item[9]);
            $this->assertInternalType('numeric', $item[10]);
            if (!empty($item[11])) {
                $this->assertInternalType('numeric', $item[11]);
            }
            $this->assertInternalType('numeric', $item[12]);
            $this->assertRegExp('/^(quiz|course)$/', $item[5]);
            $this->assertEquals(100.0 , $item[6]);
            $this->assertEquals(0.0   , $item[7]);
            if (!empty($item[8])) {
                $this->assertEquals(80.0, $item[8]);
            }
            $this->assertEquals(80.0  , $item[9]);
            $this->assertEquals(0     , $item[10]);
        }
    }

}
