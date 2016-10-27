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
 * Class local_xray_api_data_exportv2_testcase
 * @group local_xray
 */
class local_xray_api_data_exportv2_testcase extends local_xray_api_data_export_base_testcase {

    /**
     * preset
     */
    public function setUp() {
        $this->init_base();
        set_config('newformat', true, 'local_xray');
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

        $timenow = time() + HOURSECS;
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
            $this->assertInternalType('numeric', $item[6]);
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

        $timenow = time() + HOURSECS;
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
            $this->assertInternalType('numeric', $item[6]);
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

        $timenow = time() + HOURSECS;
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
            $this->assertInternalType('numeric', $item[6]);
        }
    }
}
