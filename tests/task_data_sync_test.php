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

require_once(__DIR__.'/csviterator.php');

/**
 * Class local_xray_task_data_sync_testcase
 * @group local_xray
 */
class local_xray_task_data_sync_testcase extends advanced_testcase {

    /**
     * We need to ensure that database uses correct timezone (UTC)
     * MS SQL does not offer way of configuring session timezone
     * SQLite already uses UTC by default
     */
    protected function timezone() {
        global $DB;
        switch($DB->get_dbfamily()) {
            case 'mysql':
                $DB->execute("SET time_zone='+00:00'");
                break;
            case 'postgres':
                $DB->execute("SET SESSION TIME ZONE 'UTC'");
                break;
            case 'oracle':
                $DB->execute("ALTER SESSION SET TIME_ZONE='+00:00'");
                break;
        }
    }

    /**
     * @param int $nr
     * @param int $timecreated
     */
    protected function addcourses($nr, $timecreated = null) {
        if (empty($timecreated)) {
            $timecreated = time();
        }

        $record['timecreated'] = $timecreated;
        $record['startdate'] = $timecreated;

        // Create 5 courses.
        $datagen = $this->getDataGenerator();
        for ($count = 0; $count < $nr; $count++) {
            $datagen->create_course($record);
        }
    }

    protected function export($timeend, $dir) {
        local_xray\local\api\data_export::export_csv(0, $timeend, $dir);
        local_xray\local\api\data_export::store_counters();
    }

    public function test_export() {
        $this->timezone();
        $this->resetAfterTest();

        // Reset any progress saved.
        local_xray\local\api\data_export::delete_progress_settings();

        // Add five fresh courses.
        $this->addcourses(5);

        // Export.
        $storage = new local_xray\local\api\auto_clean();
        $storagedir = $storage->get_directory();
        $timenow = time();
        $timepast = $timenow - (5 * DAYSECS);
        $this->export($timenow, $storagedir);

        $exportfile = $storagedir.DIRECTORY_SEPARATOR.'courseinfo_00000001.csv';
        $this->assertFileExists($exportfile);

        // Add 2 courses created in past.
        $this->addcourses(2, $timepast);

        // Export again.
        $storage2 = new local_xray\local\api\auto_clean();
        $storagedir = $storage2->get_directory();
        $this->export($timenow, $storagedir);

        $exportfile = $storagedir.DIRECTORY_SEPARATOR.'courseinfo_00000001.csv';
        $this->assertFileExists($exportfile);

        $expected = gmdate('Y-m-d H:i:s', $timepast);
        $count = 5;
        $first = true;
        $iterator = new csv_fileiterator($exportfile);
        foreach ($iterator as $item) {
            if ($first) {
                $first = false;
                continue;
            }
            $this->assertEquals('tc_'.++$count, $item[2]);
            $this->assertEquals($expected, $item[7]);
            $this->assertEquals($expected, $item[8]);
            $this->assertEquals($expected, $item[9]);
        }

        // One additional course.
        $this->addcourses(1);

        // Export again.
        $storage3 = new local_xray\local\api\auto_clean();
        $storagedir = $storage3->get_directory();
        $this->export($timenow, $storagedir);

        $exportfile = $storagedir.DIRECTORY_SEPARATOR.'courseinfo_00000001.csv';
        $this->assertFileExists($exportfile);
    }

}
