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
 * Class local_xray_api_data_export_extrav2_testcase
 * @group local_xray
 */
class local_xray_api_data_export_extrav2_testcase extends local_xray_api_data_export_base_testcase {

    /**
     * preset
     */
    public function setUp() {
        $this->init_base();
        set_config('newformat', true, 'local_xray');
    }

    /**
     * Course category test
     *
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_coursecategories_export() {
        global $DB;

        $this->resetAfterTest(false);
        $now = time();
        $startnow = $now - (12 * HOURSECS);
        list($exportuntil, $timecreated) = $this->get_now_past($startnow);
        // Ensure all existing categories are set to correct creation date.
        $DB->execute(
            'UPDATE {course_categories} SET timemodified = :tm',
            ['tm' => ($timecreated - HOURSECS)]
        );

        $categorynr = 10;
        $categories = $this->addcategories($categorynr, $timecreated);

        $typedef = [
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'string' ],
            ['optional' => false, 'type' => 'string' ],
            ['optional' => false, 'type' => 'numeric'],
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
        $DB->execute(
            "UPDATE {course_categories} SET timemodified = :timemodified WHERE id {$insql}",
            $params
        );
        $this->assertEquals(
            $categorynr,
            $DB->count_records('course_categories', ['timemodified' => $timemodified])
        );

        // Update export.
        $this->export_check('coursecategories', $typedef, $exportuntil, false, $categorynr);
    }

    /**
     * Course info export test with updates
     *
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_courseinfo_export() {
        global $DB;

        $this->resetAfterTest(false);
        $now = time();
        $startnow = $now - (8 * HOURSECS);
        list($exportuntil, $timecreated) = $this->get_now_past($startnow);
        $coursecount = $DB->count_records_select('course', 'category <> 0');
        // Ensure all existing courses are set to correct creation date.
        $DB->execute(
            'UPDATE {course} SET timemodified = :tm',
            ['tm' => ($timecreated - HOURSECS)]
        );

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
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
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
        $DB->execute(
            "UPDATE {course} SET timemodified = :timemodified WHERE id {$insql}",
            $params
        );
        $this->assertEquals(
            $nr,
            $DB->count_records('course', ['timemodified' => $timemodified])
        );
        // Update export.
        $this->export_check('courseinfo', $typedef, $exportuntil, false, $nr);
    }

    /**
     * User export with updates
     *
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_userlist_export() {
        global $DB;

        $this->resetAfterTest(false);
        $now = time();
        $startnow = $now - (8 * HOURSECS);
        list($exportuntil, $timecreated) = $this->get_now_past($startnow);
        $usercount = $DB->count_records('user', ['deleted' => false]);
        $DB->execute(
            "UPDATE {user} SET timemodified = :tm WHERE deleted = :deleted",
            ['tm' => ($timecreated - HOURSECS), 'deleted' => false]
        );

        $nr = 10;
        $elements = $this->addusers($nr, $timecreated);

        $typedef = [
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'string' ],
            ['optional' => false, 'type' => 'string' ],
            ['optional' => true , 'type' => 'string' ],
            ['optional' => true , 'type' => 'string' ],
            ['optional' => false, 'type' => 'string' ],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
        ];

        // Initial export.
        $this->export_check('userlistv2', $typedef, $exportuntil, false, ($nr + $usercount));

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
        $DB->execute(
            "UPDATE {user} SET timemodified = :timemodified WHERE id {$insql}",
            $params
        );
        $this->assertEquals(
            $nr,
            $DB->count_records('user', ['timemodified' => $timemodified])
        );

        // Update export.
        $this->export_check('userlistv2', $typedef, $exportuntil, false, $nr);
    }

    /**
     * Enrollment export
     *
     * @throws coding_exception
     */
    public function test_roles_export() {
        global $DB;

        $this->resetAfterTest(false);
        $now = time();
        $startnow = $now - (8 * HOURSECS);
        list($exportuntil, $timecreated) = $this->get_now_past($startnow);
        $rcount = $DB->count_records_sql("
            SELECT COUNT(r.id) AS count
              FROM {role_assignments} r
              JOIN {context}          ctx ON r.contextid = ctx.id AND ctx.contextlevel= :ctxlevel
             WHERE
                   EXISTS (SELECT u.id FROM {user} u WHERE r.userid = u.id AND u.deleted = :deleted)
                   AND
                   EXISTS (SELECT c.id FROM {course} c WHERE ctx.instanceid = c.id AND c.category <> 0)
        ", ['ctxlevel' => CONTEXT_COURSE, 'deleted' => false]);
        $nr = 10;
        $courses = $this->addcourses($nr, $timecreated);
        $this->addquizzes($nr, $courses);
        $this->user_set($courses, 'quiz');

        $typedef = [
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
        ];

        // Initial export.
        $this->export_check('roles', $typedef, $exportuntil, false, ($nr + $rcount));
    }

}
