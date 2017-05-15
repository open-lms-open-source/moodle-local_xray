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

        $timenow = time() + HOURSECS;
        $timepast = $timenow - DAYSECS;

        $forumtypedef = [
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'string' ],
            ['optional' => false, 'type' => 'string' ],
            ['optional' => false, 'type' => 'string' ],
            ['optional' => false, 'type' => 'string' ],
        ];

        $threadtypedef = [
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'string' ],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'string' ],
        ];

        $courses = $this->addcourses(5, $timepast);

        foreach ([5 => 10, 3 => 180] as $itemnumber => $timediff) {
            list($forumdata, $threaddata) = $this->addforums_validation(
                $itemnumber,
                $courses,
                $timepast + $timediff
            );

            $elemcount = $itemnumber * 5;
            $passdata = [
                'forums'  => ['typedef' => $forumtypedef , 'data' => $forumdata ],
                'threads' => ['typedef' => $threadtypedef, 'data' => $threaddata],
            ];
            foreach ($passdata as $item => $data) {
                $this->export_check($item, $data['typedef'], $timenow, false, $elemcount, $data['data']);
                $this->export_check($item, [], $timenow, false, 0);
            }
        }
    }

    /**
     * Add's 5 Moodlerooms forums with some content, exports them in csv and checks the export format.
     * @return void
     */
    public function test_hsuforums_export() {
        if (!$this->plugin_present('mod_hsuforum')) {
            $this->markTestSkipped('Moodlerooms Forum not present!');
        }

        $this->resetAfterTest();

        $timenow = time() + HOURSECS;
        $timepast = $timenow - DAYSECS;

        $forumtypedef = [
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'string' ],
            ['optional' => false, 'type' => 'string' ],
            ['optional' => false, 'type' => 'string' ],
            ['optional' => false, 'type' => 'string' ],
        ];

        $threadtypedef = [
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'string' ],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'string' ],
        ];

        $courses = $this->addcourses(5, $timepast);

        foreach ([5 => 10, 3 => 180] as $itemnumber => $timediff) {
            list($forumdata, $threaddata) = $this->addhsuforums_validation(
                $itemnumber,
                $courses,
                $timepast + $timediff
            );

            $elemcount = $itemnumber * 5;
            $passdata = [
                'hsuforums'  => ['typedef' => $forumtypedef , 'data' => $forumdata ],
                'hsuthreads' => ['typedef' => $threadtypedef, 'data' => $threaddata],
            ];
            foreach ($passdata as $item => $data) {
                $this->export_check($item, $data['typedef'], $timenow, false, $elemcount, $data['data']);
                $this->export_check($item, [], $timenow, false, 0);
            }
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

        $typedef = [
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'string' ],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'string' ],
        ];

        $courses = $this->addcourses(5, $timepast);
        foreach ([5, 3] as $itemnumber) {
            $data = $this->addquizzes_validation($itemnumber, $courses);
            $elemcount = $itemnumber * 5;
            $this->export_check('quiz', $typedef, $timenow, false, $elemcount, $data);
            $this->export_check('quiz', [], $timenow, false, 0);
        }
    }

    /**
     * Add's 5 quizzes with some grades, exports them in csv and checks the export format.
     * @return void
     */
    public function test_grades_export() {

        $this->resetAfterTest();

        $timenow = time() + HOURSECS;
        $timepast = $timenow - DAYSECS;
        $courses = $this->addcourses(5, $timepast);
        $this->addquizzes(5, $courses);
        $this->user_set($courses, 'quiz');
        $gradedata = $this->get_validategrades($courses);

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

        $this->export_check('grades', $typedef, $timenow, false, 30, $gradedata);
        $this->export_check('grades', [], $timenow, false, 0);
    }

    /**
     * Test export of grades history.
     */
    public function test_grades_history_export() {
        $this->resetAfterTest();

        $timenow = time() + HOURSECS;
        $timepast = $timenow - DAYSECS;
        $courses = $this->addcourses(5, $timepast);
        $this->addquizzes(5, $courses);
        $this->user_set($courses, 'quiz');
        $data = $this->get_validate_gradehistory();

        $typedef = [
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'string' ],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => true , 'type' => 'numeric'],
            ['optional' => true , 'type' => 'numeric'],
            ['optional' => true , 'type' => 'numeric'],
            ['optional' => true , 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
            ['optional' => false, 'type' => 'numeric'],
        ];

        $this->export_check('grades_history', $typedef, $timenow, false, 75, $data);
        $this->export_check('grades_history', [], $timenow, false, 0);
    }

}
