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
 * Class local_xray_api_data_export_course_testcase
 * @group local_xray
 */
class local_xray_api_data_export_course_testcase extends local_xray_api_data_export_base_testcase {
    /**
     * preset
     */
    public function setUp() {
        $this->init_base();
    }

    /**
     * Test export of courses
     *
     * @throws moodle_exception
     */
    public function test_course_nosingleactivity() {
        $this->resetAfterTest();

        $timenow = time() + HOURSECS;
        $timepast = $timenow - DAYSECS;
        $courses = $this->addcourses(5, $timepast);
        $count = 0;
        foreach ($courses as $course) {
            if ($count == 2) {
                break;
            }
            $course->format = 'singleactivity';
            update_course($course);
            $count++;
        }

        // Export.
        $storage = new local_xray\local\api\auto_clean();
        $storagedir = $storage->get_directory();
        $this->export($timenow, $storagedir);

        $exportfile = $storagedir.DIRECTORY_SEPARATOR.'courseinfo_00000001.csv';
        $this->assertFileExists($exportfile);

        $first = true;
        $iterator = new csv_fileiterator($exportfile);
        foreach ($iterator as $item) {
            if ($first) {
                $first = false;
                continue;
            }
            // Expect 10 items.
            // Verify format.
            $this->assertEquals(10, count($item));
            $this->assertInternalType('numeric', $item[0]);
            $this->assertInternalType('string' , $item[1]);
            $this->assertInternalType('string' , $item[2]);
            $this->assertInternalType('string' , $item[3]);
            $this->assertInternalType('numeric', $item[4]);
            $this->assertInternalType('string' , $item[5]);
            $this->assertInternalType('numeric', $item[6]);
            $this->assertInternalType('string' , $item[7]);
            $this->assertInternalType('string' , $item[8]);
            $this->assertInternalType('string' , $item[9]);
            // Confirm that no single activity course was exported.
            $this->assertNotEquals('singleactivity'   , $item[5]);
        }
    }

    /**
     * Test export of courses
     *
     * @throws moodle_exception
     */
    public function test_course_singleactivity() {
        $this->resetAfterTest();

        set_config('sacenabled', true, 'local_xray');

        $timenow = time() + HOURSECS;
        $timepast = $timenow - DAYSECS;
        $courses = $this->addcourses(5, $timepast);
        foreach ($courses as $course) {
            $course->format = 'singleactivity';
            update_course($course);
        }

        // Export.
        $storage = new local_xray\local\api\auto_clean();
        $storagedir = $storage->get_directory();
        $this->export($timenow, $storagedir);

        $exportfile = $storagedir.DIRECTORY_SEPARATOR.'courseinfo_00000001.csv';
        $this->assertFileExists($exportfile);

        $first = true;
        $iterator = new csv_fileiterator($exportfile);
        foreach ($iterator as $item) {
            if ($first) {
                $first = false;
                continue;
            }
            // Expect 10 items.
            // Verify format.
            $this->assertEquals(10, count($item));
            $this->assertInternalType('numeric', $item[0]);
            $this->assertInternalType('string' , $item[1]);
            $this->assertInternalType('string' , $item[2]);
            $this->assertInternalType('string' , $item[3]);
            $this->assertInternalType('numeric', $item[4]);
            $this->assertInternalType('string' , $item[5]);
            $this->assertInternalType('numeric', $item[6]);
            $this->assertInternalType('string' , $item[7]);
            $this->assertInternalType('string' , $item[8]);
            $this->assertInternalType('string' , $item[9]);
            // Confirm that no single activity course was exported.
            $this->assertEquals('singleactivity'   , $item[5]);
        }

        unset_config('sacenabled', 'local_xray');
    }

}