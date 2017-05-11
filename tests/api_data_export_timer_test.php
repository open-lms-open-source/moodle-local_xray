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
 * Class local_xray_api_data_export_timer_testcase
 */
class local_xray_api_data_export_timer_testcase extends local_xray_api_data_export_base_testcase {

    /**
     * test
     */
    public function test_timer_process() {
        global $CFG;
        $this->resetAfterTest(true);
        $CFG->forced_plugin_settings['local_xray']['exporttime_hours'  ] = 0;
        $CFG->forced_plugin_settings['local_xray']['exporttime_minutes'] = 0.0334; // Set to 2sec.
        $this->assertEquals(2, local_xray\local\api\data_export::executiontime());
        $CFG->forced_plugin_settings['local_xray']['exporttime_minutes'] = 10; // Set to 600sec.
        $this->assertEquals(600, local_xray\local\api\data_export::executiontime());
    }
}