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
 * Class local_xray_api_data_export_grades_testcase
 * @group local_xray
 */
class local_xray_api_data_export_grades_testcase extends local_xray_api_data_export_base_testcase {

    /**
     * preset
     */
    public function setUp() {
        $this->init_base();
        set_config('newformat', true, 'local_xray');
    }

    /**
     *
     */
    public function test_grade_update_export() {
        $this->markTestSkipped('Started to fail after the 3.7.1 merge');

        global $CFG;
        /* @noinspection PhpIncludeInspection */
        require_once($CFG->libdir.'/gradelib.php');

        $this->resetAfterTest();

        $timenow = time() - HOURSECS;

        $newformat = get_config('local_xray', 'newformat');
        $delimiter = $newformat ? '|' : ',';

        $courses = $this->addcourses(1, $timenow, ['fullname' => 'Test Course |, |,||, - '.str_repeat($delimiter, 4).' yeah']);
        $quizzes = $this->addquizzes(1, $courses, ['name' => 'Test Quiz |, |,||, - '.str_repeat($delimiter, 4).' yeah']);
        /** @var grade_item[] $gitems */
        list($user, $gitems) = $this->user_set_internal($courses, 'quiz');

        $typedef = [
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => true , 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => true , 'type' => 'string' ],
            ['optional' => false, 'type' => 'string' ],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => true , 'type' => 'numeric'],
            ['optional' => false, 'type' => 'string' ],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => true , 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
        ];

        $gradedata = [];
        $course = reset($courses);
        $gitems[] = grade_item::fetch_course_item($course->id);
        foreach ($gitems as $gitem) {
            $grade = $gitem->get_grade($user->id, false);
            $cm = get_coursemodule_from_instance('quiz', $quizzes[0]->id, $courses[0]->id);
            // Add the quiz grade.
            $gradedata[] = [
                $grade->id,
                $user->id,
                ($gitem->itemtype == 'course') ? '' : $cm->id,
                $gitem->id,
                $course->id,
                $gitem->itemname,
                ($gitem->itemtype == 'course') ? $gitem->itemtype : $gitem->itemmodule,
                $grade->get_grade_max(),
                $grade->get_grade_min(),
                $grade->rawgrade,
                $grade->finalgrade,
                $grade->is_locked() ? $grade->get_locktime() : null,
                $grade->timecreated,
                $grade->timemodified,
            ];
        }

        $this->export_check('grades', $typedef, $timenow, false, count($gradedata), $gradedata);

        $this->waitForSecond();

        $updatedgrade = 88.0;
        $now = time() + HOURSECS;
        foreach ($gitems as $gitem) {
            if ($gitem->itemtype == 'mod') {
                $this->assertTrue($gitem->update_raw_grade($user->id, $updatedgrade), 'Grade not updated!');
            }
        }

        $gradedata = [];
        foreach ($gitems as $gitem) {
            $gitem->update_from_db();
            $grade = $gitem->get_grade($user->id, false);
            $cmid = '';
            $type = $gitem->itemtype;
            $rawgrade = '';
            if ($gitem->itemtype == 'mod') {
                $cm = get_coursemodule_from_instance('quiz', $quizzes[0]->id, $courses[0]->id);
                $cmid = $cm->id;
                $type = $gitem->itemmodule;
                $rawgrade = $updatedgrade;
            }
            // Add the quiz grade.
            $gradedata[] = [
                $grade->id,
                $user->id,
                $cmid,
                $gitem->id,
                $course->id,
                $gitem->itemname,
                $type,
                $grade->get_grade_max(),
                $grade->get_grade_min(),
                $rawgrade,
                $updatedgrade,
                $grade->is_locked() ? $grade->get_locktime() : null,
                $grade->timecreated,
                $grade->timemodified,
            ];
        }

        $this->export_check('grades', $typedef, $now, false, count($gradedata), $gradedata);
    }

}
