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
 * Class local_xray_api_data_export_course_hideshow_testcase
 * @package local_xray
 */
class local_xray_api_data_export_course_hideshow_testcase extends local_xray_api_data_export_base_testcase {

    public function setUp(): void {
        $this->init_base();
    }

    public function test_course_category_hide_show_export() {
        global $DB, $CFG;

        $this->resetAfterTest(true);

        $cat = $this->addcategories(1);
        $cats = $this->addcategories(3, null, $cat[0]->id);
        foreach ($cats as $category) {
            $this->addcourses(3, null, ['category' => $category->id]);
        }

        $coursecount = $DB->count_records_select('course', 'category <> 0');

        $typedef = [
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'string' ],
            ['optional' => false, 'type' => 'string' ],
            ['optional' => true , 'type' => 'string' ],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'string' ],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'string' ],
            ['optional' => false, 'type' => 'string' ],
            ['optional' => false, 'type' => 'string' ],
        ];

        // Initial export.
        $this->export_check('courseinfo', $typedef, time(), false, $coursecount);
        $this->waitForSecond();
        $this->waitForSecond();
        // Disable our fix.
        $CFG->forced_plugin_settings['local_xray']['disablecoursecatevent'] = true;
        $cat[0]->hide();
        $this->waitForSecond();
        $this->waitForSecond();
        // We expect no data.
        $this->export_check('courseinfo', $typedef, time(), false, 0);
        $this->waitForSecond();
        $this->waitForSecond();
        $CFG->forced_plugin_settings['local_xray']['disablecoursecatevent'] = false;
        $cat[0]->show();
        $this->waitForSecond();
        $this->waitForSecond();
        $this->export_check('courseinfo', $typedef, time(), false, $coursecount);
    }

}
