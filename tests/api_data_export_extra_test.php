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
 * Class local_xray_api_data_export_extra_testcase
 * @group local_xray4
 */
class local_xray_api_data_export_extra_testcase extends local_xray_api_data_export_base_testcase {

    /**
     * preset
     */
    public function setUp() {
        $this->init_base();
    }

    /**
     * Course category test
     *
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_coursecategories_export() {
        global $DB;

        $this->resetAfterTest();
        $now = time();
        $startnow = $now - (8 * HOURSECS);
        list($exportuntil, $timecreated) = $this->get_now_past($startnow);
        $categorynr = 10;
        $categories = $this->addcategories($categorynr, $timecreated);

        $typedef = [
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'string' ],
            ['optional' => false, 'type' => 'string' ],
            ['optional' => false, 'type' => 'string' ],
        ];

        // Initial export.
        $this->export_check('coursecategories', $typedef, $exportuntil, false, coursecat::count_all());

        // Check export of modified categories.
        $newnow = $now - (4 * HOURSECS);
        list($exportuntil, $timemodified) = $this->get_now_past($newnow);
        $cats = [];
        foreach ($categories as $category) {
            $cats[] = (int)$category->id;
        }
        $params = ['timemodified' => $timemodified];
        list ($insql, $tparams) = $DB->get_in_or_equal($cats, SQL_PARAMS_NAMED);
        $params += $tparams;
        $DB->execute("UPDATE {course_categories} SET timemodified = :timemodified WHERE id {$insql}", $params);
        // Update export.
        $this->export_check('coursecategories', $typedef, $exportuntil, false, $categorynr);
    }

    public function test_courseinfo_export() {
        global $DB;

        $this->resetAfterTest();
        $now = time();
        $startnow = $now - (8 * HOURSECS);
        list($exportuntil, $timecreated) = $this->get_now_past($startnow);
        $coursecount = $DB->count_records_select('course', 'category <> 0');
        $nr = 10;
        $elements = $this->addcourses($nr, $timecreated);

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
        $this->export_check('courseinfo', $typedef, $exportuntil, false, ($nr + $coursecount));

        // Check export of modified categories.
        $newnow = $now - (4 * HOURSECS);
        list($exportuntil, $timemodified) = $this->get_now_past($newnow);
        $cats = [];
        foreach ($elements as $elem) {
            $cats[] = (int)$elem->id;
        }
        $params = ['timemodified' => $timemodified];
        list ($insql, $tparams) = $DB->get_in_or_equal($cats, SQL_PARAMS_NAMED);
        $params += $tparams;
        $DB->execute("UPDATE {course} SET timemodified = :timemodified WHERE id {$insql}", $params);
        // Update export.
        $this->export_check('courseinfo', $typedef, $exportuntil, false, $nr);
    }

    public function test_userlist_export() {
        global $DB;

        $this->resetAfterTest();
        $now = time();
        $startnow = $now - (8 * HOURSECS);
        list($exportuntil, $timecreated) = $this->get_now_past($startnow);
        $usercount = $DB->count_records('user', ['deleted' => false]);
        $nr = 10;
        $elements = $this->addusers($nr, $timecreated);

        $typedef = [
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'string' ],
            ['optional' => true , 'type' => 'string' ],
            ['optional' => true , 'type' => 'string' ],
            ['optional' => false, 'type' => 'string' ],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'string' ],
            ['optional' => false, 'type' => 'string' ],
            ['optional' => false, 'type' => 'string' ],
            ['optional' => false, 'type' => 'string' ],
        ];

        // Initial export.
        $this->export_check('userlist', $typedef, $exportuntil, false, ($nr + $usercount));

        // Check export of modified categories.
        $newnow = $now - (4 * HOURSECS);
        list($exportuntil, $timemodified) = $this->get_now_past($newnow);
        $cats = [];
        foreach ($elements as $elem) {
            $cats[] = (int)$elem->id;
        }
        $params = ['timemodified' => $timemodified];
        list ($insql, $tparams) = $DB->get_in_or_equal($cats, SQL_PARAMS_NAMED);
        $params += $tparams;
        $DB->execute("UPDATE {user} SET timemodified = :timemodified WHERE id {$insql}", $params);
        // Update export.
        $this->export_check('userlist', $typedef, $exportuntil, false, $nr);
    }

    public function test_enrolment_export() {
        $this->resetAfterTest();
        $this->markTestSkipped('Not Implemented.');

        // TODO: to be implemented.
    }

}
