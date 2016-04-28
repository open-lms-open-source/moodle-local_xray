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
 * Class local_xray_api_data_export_testcase
 * @group local_xray
 */
class local_xray_api_data_export_testcase extends local_xray_api_data_export_base_testcase {

    /**
     * preset
     */
    public function setUp() {
        $this->init_base();
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
            // Expect 14 items.
            $this->assertEquals(14, count($item));
            $this->assertInternalType('numeric', $item[0]);
            $this->assertInternalType('numeric', $item[1]);
            if (!empty($item[2])) {
                $this->assertInternalType('numeric', $item[2]);
            }
            $this->assertInternalType('numeric', $item[3]);
            $this->assertInternalType('numeric', $item[4]);
            if (!empty($item[5])) {
                $this->assertInternalType('string', $item[5]);
            }
            $this->assertInternalType('string' , $item[6]);
            $this->assertInternalType('numeric', $item[7]);
            $this->assertInternalType('numeric', $item[8]);
            if (!empty($item[9])) {
                $this->assertInternalType('numeric', $item[9]);
            }
            $this->assertInternalType('numeric', $item[10]);
            $this->assertInternalType('numeric', $item[11]);
            if (!empty($item[12])) {
                $this->assertInternalType('numeric', $item[12]);
            }
            $this->assertInternalType('numeric', $item[13]);
            $this->assertRegExp('/^(quiz|course)$/', $item[6]);
            $this->assertEquals(($item[6] == 'course') ? 500.0 : 100.0 , (float)$item[7]);
            $this->assertEquals(0.0   , (float)$item[8]);
            if (!empty($item[9])) {
                $this->assertEquals(80.0, $item[9]);
            }
            $this->assertEquals(($item[6] == 'course') ? 400.0 : 80.0 , (float)$item[10]);
            $this->assertEquals(0     , $item[11]);
        }
    }

}
