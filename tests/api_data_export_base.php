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
 * Class local_xray_api_data_export_base_testcase
 * @group local_xray
 */
abstract class local_xray_api_data_export_base_testcase extends advanced_testcase {

    /**
     * @param string $pattern
     * @param string $dir
     */
    protected function copy_dir($pattern, $dir) {
        mtrace('');
        mtrace($pattern);
        $realdir = realpath($dir);
        if ($realdir === false) {
            $realdir = realpath(make_writable_directory($dir));
        }
        foreach (glob($pattern) as $file) {
            $dest = $realdir.DIRECTORY_SEPARATOR.basename($file);
            mtrace($dest);
            mtrace($file);
            if (is_dir($file)) {
                make_writable_directory($dest);
            } else if (is_readable($file)) {
                copy($file, $dest);
            }
        }
    }

    /**
     * @param $id
     * @param $courseid
     * @param $cmid
     * @param $forumid
     * @throws coding_exception
     * @throws moodle_exception
     */
    protected function delete_discussion($id, $courseid, $cmid, $forumid) {
        global $DB;
        /* @noinspection PhpIncludeInspection */
        require_once(core_component::get_component_directory('mod_forum').'/lib.php');
        $discussion = $DB->get_record('forum_discussions', ['id' => $id]);
        $cm = (object)['id' => $cmid];
        $course = get_course($courseid);
        $forum = $DB->get_record('forum', ['id' => $forumid]);
        forum_delete_discussion($discussion, true, $course, $cm, $forum);

        // That is a great design. They did not place event code into forum_delete_discussion API.
        // So I have to do it.
        $modcontext = context_module::instance($cmid);
        $params = [
            'objectid' => $discussion->id,
            'context'  => $modcontext,
            'other'    => [
                'forumid' => $forum->id,
            ]
        ];

        $event = \mod_forum\event\discussion_deleted::create($params);
        $event->add_record_snapshot('forum_discussions', $discussion);
        $event->trigger();
    }

    /**
     * @param $id
     * @param $courseid
     * @param $cmid
     * @param $forumid
     * @throws coding_exception
     * @throws moodle_exception
     */
    protected function delete_post($id, $courseid, $cmid, $forumid) {
        global $DB;
        /* @noinspection PhpIncludeInspection */
        require_once(core_component::get_component_directory('mod_forum').'/lib.php');
        $post = forum_get_post_full($id);
        $cm = (object)['id' => $cmid];
        $course = get_course($courseid);
        $forum = $DB->get_record('forum', ['id' => $forumid]);
        forum_delete_post($post, true, $course, $cm, $forum);
    }

    /**
     * @param $id
     * @param $courseid
     * @param $cmid
     * @param $forumid
     * @throws coding_exception
     * @throws moodle_exception
     */
    protected function delete_hsudiscussion($id, $courseid, $cmid, $forumid) {
        global $DB;
        /* @noinspection PhpIncludeInspection */
        require_once(core_component::get_component_directory('mod_hsuforum').'/lib.php');
        $discussion = $DB->get_record('hsuforum_discussions', ['id' => $id]);
        $cm = (object)['id' => $cmid];
        $course = get_course($courseid);
        $forum = $DB->get_record('hsuforum', ['id' => $forumid]);
        hsuforum_delete_discussion($discussion, false, $course, $cm, $forum);

        $modcontext = context_module::instance($cmid);
        $params = [
            'objectid' => $discussion->id,
            'context'  => $modcontext,
            'other'    => [
                'forumid' => $forum->id,
            ]
        ];

        $event = \mod_hsuforum\event\discussion_deleted::create($params);
        $event->add_record_snapshot('hsuforum_discussions', $discussion);
        $event->trigger();
    }

    /**
     * @param $id
     * @param $courseid
     * @param $cmid
     * @param $forumid
     * @throws coding_exception
     * @throws moodle_exception
     */
    protected function delete_hsupost($id, $courseid, $cmid, $forumid) {
        global $DB;
        /* @noinspection PhpIncludeInspection */
        require_once(core_component::get_component_directory('mod_hsuforum').'/lib.php');
        $post = hsuforum_get_post_full($id);
        $cm = (object)['id' => $cmid];
        $course = get_course($courseid);
        $forum = $DB->get_record('hsuforum', ['id' => $forumid]);
        hsuforum_delete_post($post, true, $course, $cm, $forum);
    }

    /**
     * checks if specific plugin is present and enabled
     * @param $component - plugin component
     * @return bool
     */
    protected function plugin_present($component) {
        list($type, $plugin) = core_component::normalize_component($component);
        $plugins = \core_plugin_manager::instance()->get_enabled_plugins($type);
        return in_array($plugin, $plugins);
    }

    /**
     * @param int  $nr
     * @param int $timecreated
     * @return coursecat[]
     */
    protected function addcategories($nr, $timecreated = null) {
        global $DB;

        // Create categories.
        $datagen = $this->getDataGenerator();
        $categories = [];
        $count = 0;
        while ($count++ < $nr) {
            $cat = $datagen->create_category();
            if (!empty($timecreated)) {
                $catid = (int)$cat->id;
                $DB->update_record('course_categories', (object)['id' => $catid, 'timemodified' => $timecreated]);
                $cat = coursecat::get($catid);
            }
            $categories[] = $cat;
        }

        return $categories;
    }

    /**
     * Creates courses needed for test
     *
     * @param int $nr
     * @param int $timecreated
     * @return stdClass[]
     */
    protected function addcourses($nr, $timecreated = null) {
        global $CFG;
        /* @noinspection PhpIncludeInspection */
        require_once($CFG->libdir.'/gradelib.php');

        if (empty($timecreated)) {
            $timecreated = time();
        }

        $record = [
            'timecreated' => $timecreated,
            'startdate'   => $timecreated
        ];
        // Create course(s).
        $datagen = $this->getDataGenerator();
        $courses = [];
        $count = 0;
        while ($count++ < $nr) {
            $course = $datagen->create_course($record);
            grade_regrade_final_grades($course->id);
            $courses[] = $course;
        }

        return $courses;
    }

    /**
     * Creates forums and discussions needed for the test for specified type of forum
     *
     * @param  int         $nr
     * @param  stdClass[]  $courses
     * @param  string      $module
     * @param  int         $timepast
     * @return array
     * @throws coding_exception
     */
    protected function addforumsbase($nr, $courses, $module, $timepast = 0) {
        global $USER;
        if ($timepast == 0) {
            $timepast = time();
        }
        /** @var mod_forum_generator|mod_hsuforum_generator $forumgen */
        $forumgen = $this->getDataGenerator()->get_plugin_generator($module);
        $forums = [];
        $discussions = [];
        $posts = [];
        foreach ($courses as $course) {
            $count = 0;
            while ($count++ < $nr) {
                $instance = $forumgen->create_instance([
                    'type'         => 'general',
                    'course'       => $course,
                    'timemodified' => $timepast
                ]);
                $forums[] = $instance;
                $discussion = $forumgen->create_discussion([
                    'course'       => $course->id,
                    'forum'        => $instance->id,
                    'userid'       => $USER->id,
                    'timemodified' => $timepast
                ]);
                $discussions[] = $discussion;
                $post = $forumgen->create_post([
                    'discussion' => $discussion->id,
                    'userid'     => $USER->id,
                    'created'    => $timepast,
                    'modified'   => $timepast
                ]);
                $posts[] = $post;
            }
        }

        return [$forums, $discussions, $posts];
    }

    /**
     * @param  int        $nr
     * @param  stdClass[] $courses
     * @param  int        $timepast
     * @return array[]
     */
    protected function addhsuforums($nr, $courses, $timepast = 0) {
        return $this->addforumsbase($nr, $courses, 'mod_hsuforum', $timepast);
    }

    /**
     * @param  int        $nr
     * @param  stdClass[] $courses
     * @param  int        $timepast
     * @return array[]
     */
    protected function addforums($nr, $courses, $timepast = 0) {
        return $this->addforumsbase($nr, $courses, 'mod_forum', $timepast);
    }

    /**
     * @param  int        $nr
     * @param  stdClass[] $courses
     * @param  string     $module
     * @param  int        $timepast
     * @return array[]
     */
    protected function addforumsbase_validation($nr, $courses, $module, $timepast = 0) {
        list($forums, $discussions, $posts) = $this->addforumsbase($nr, $courses, $module, $timepast);
        $forumdata = [];
        foreach ($forums as $forum) {
            $forumdata[] = [
                $forum->id,
                null,
                $forum->course,
                $forum->type,
                $forum->name,
                $forum->intro,
                null
            ];
        }

        $discdata = [];
        foreach ($discussions as $dsc) {
            $discdata[] = [
                $dsc->id,
                $dsc->forum,
                $dsc->name,
                $dsc->userid,
                $dsc->groupid,
                null
            ];
        }

        $postdata = [];
        foreach ($posts as $post) {
            $postdata[] = [
                $post->id,
                $post->parent,
                $post->discussion,
                $post->userid,
                null,
                null,
                $post->subject,
                $post->message
            ];
        }

        return [$forumdata, $discdata, $postdata];
    }

    /**
     * @param  int $nr
     * @param  stdClass[] $courses
     * @param  int $timepast
     * @return array[]
     */
    protected function addhsuforums_validation($nr, $courses, $timepast = 0) {
        return $this->addforumsbase_validation($nr, $courses, 'mod_hsuforum', $timepast);
    }

    /**
     * @param  int        $nr
     * @param  stdClass[] $courses
     * @param  int        $timepast
     * @return array[]
     */
    protected function addforums_validation($nr, $courses, $timepast = 0) {
        return $this->addforumsbase_validation($nr, $courses, 'mod_forum', $timepast);
    }

    /**
     * Add specified ammount of quizzes
     *
     * @param int $nr
     * @param array $courses
     * @return array
     * @throws coding_exception
     */
    protected function addquizzes($nr, $courses) {
        /** @var mod_quiz_generator $quizgen */
        $quizgen = $this->getDataGenerator()->get_plugin_generator('mod_quiz');
        $instances = [];
        foreach ($courses as $course) {
            $lnr = $nr;
            while ($lnr-- > 0) {
                $instances[] = $quizgen->create_instance(['course' => $course]);
            }
        }
        return $instances;
    }

    /**
     * @param  int        $nr
     * @param  stdClass[] $courses
     * @return array
     */
    protected function addquizzes_validation($nr, $courses) {
        $instances = $this->addquizzes($nr, $courses);
        $validationdata = [];
        foreach ($instances as $quiz) {
            $validationdata[] = [$quiz->id, null, $quiz->course, $quiz->name, $quiz->attempts, $quiz->grade, null];
        }
        return $validationdata;
    }


    /**
     * Export data to csv files
     *
     * @param int    $timeend
     * @param string $dir
     * @param string $fn
     */
    protected function export($timeend, $dir, $fn = null) {
        if (empty($fn)) {
            local_xray\local\api\data_export::export_csv(0, $timeend, $dir);
        } else {
            local_xray\local\api\data_export::reset_counter_storage();
            call_user_func(['local_xray\local\api\data_export', $fn], 0, $timeend, $dir);
        }
        local_xray\local\api\data_export::store_counters();
    }

    /**
     * Set the student account
     *
     * @param array $courses
     * @param string $module
     * @return stdClass
     */
    protected function user_set($courses, $module) {
        global $DB, $CFG;
        /* @noinspection PhpIncludeInspection */
        require_once($CFG->libdir.'/gradelib.php');

        $datagen = $this->getDataGenerator();
        $student = $datagen->create_user(['username' => uniqid('supercalifragilisticoexpialidoso')]);
        $roleid = $DB->get_field('role', 'id', ['shortname' => 'student'], MUST_EXIST);
        $timenow = time();
        foreach ($courses as $course) {
            $datagen->enrol_user($student->id, $course->id, $roleid);
            /** @var grade_item[] $gitems */
            $gitems = grade_item::fetch_all(['itemmodule' => $module, 'courseid' => $course->id]);
            foreach ($gitems as $gitem) {
                $gitem->update_raw_grade($student->id, 80.0, null, false, FORMAT_MOODLE, null, $timenow, $timenow);
            }
            grade_regrade_final_grades($course->id);
        }

        return $student;
    }

    /**
     * @param  stdClass[] $courses
     * @return array[]
     * @throws coding_exception
     */
    protected function get_validategrades(array $courses) {
        global $CFG;
        /* @noinspection PhpIncludeInspection */
        require_once($CFG->libdir.'/gradelib.php');

        $grades = [];
        foreach ($courses as $course) {
            /** @var grade_grade[] $gradess */
            $gradess = grade_grade::fetch_all(['courseid' => $course->id]);
            foreach ($gradess as $gg) {
                $gg->load_grade_item();
                $gitem = $gg->grade_item;
                $cm = get_coursemodule_from_instance('quiz', $gitem->iteminstance, $gitem->courseid);
                $grades[] = [
                    $gg->id,
                    $gg->userid,
                    isset($cm->id) ? $cm->id : '',
                    $gitem->id,
                    $gitem->courseid,
                    $gitem->itemname,
                    ($gitem->itemtype == 'mod') ? $gitem->itemmodule : $gitem->itemtype,
                    $gg->rawgrademax,
                    $gg->rawgrademin,
                    $gg->rawgrade,
                    $gg->finalgrade,
                    $gg->locktime,
                    $gg->timecreated,
                    $gg->timemodified,
                ];
            }
        }

        return $grades;
    }

    /**
     * @return array[]
     * @throws coding_exception
     * @throws dml_exception
     */
    protected function get_validate_gradehistory() {
        global $DB;
        $grades = [];
        $actions = [1 => 'INSERT', 2 => 'UPDATE', 3 => 'DELETE'];
        list($sql, $params) = $DB->get_in_or_equal([], SQL_PARAMS_NAMED, 'param', false, true);
        $recs = $DB->get_records_select('grade_grades_history', 'finalgrade '.$sql, $params);
        foreach ($recs as $rec) {
            $grades[] = [
                $rec->id,
                $actions[$rec->action],
                $rec->itemid,
                $rec->userid,
                $rec->rawgrademax,
                $rec->rawgrademin,
                $rec->rawgrade,
                $rec->finalgrade,
                $rec->loggeduser,
                $rec->timemodified,
            ];
        }

        return $grades;
    }

    /**
     * @param  int $nr
     * @param  int $timecreated
     * @return stdClass[]
     */
    protected function addusers($nr, $timecreated = null) {
        $datagen = $this->getDataGenerator();
        if (empty($timecreated)) {
            $timecreated = time();
        }
        $users = [];
        for ($pos = 0; $pos < $nr; $pos++) {
            $users[] = $datagen->create_user([
                'username'    => 'testuser'.$pos,
                'deleted'     => 0,
                'timecreated' => $timecreated
            ]);
        }
        return $users;
    }

    /**
     * preset
     */
    protected function init_base() {
        // Reset any progress saved.
        local_xray\local\api\data_export::delete_progress_settings();
        set_config('fixedprefix', '_123', 'local_xray');
        $this->setAdminUser();
    }

    /**
     * @param  int $startpoint
     * @return int[]
     */
    protected function get_now_past($startpoint) {
        $pastdiff = HOURSECS;
        $now      = $startpoint - $pastdiff;
        $past     = $now - $pastdiff;
        return [$now, $past];
    }

    /**
     * @param  string $storagedir
     * @param  string $itemname
     * @return string
     */
    protected function get_export_file($storagedir, $itemname) {
        return $storagedir.
               DIRECTORY_SEPARATOR.
               \local_xray\local\api\data_export::exportpath($itemname) ."_00000001.csv";
    }

    /**
     * Generic export check method
     *
     * @param string $itemname
     * @param array  $typedef
     * @param int    $now
     * @param bool   $debug
     * @param int    $expectedcount - expected record count, if -1 no expectations are checked, if 0 we expect no data
     * @param array  $validate
     * @return void
     * @throws PHPUnit_Framework_ExpectationFailedException
     */
    protected function export_check($itemname, $typedef, $now, $debug = false, $expectedcount = -1, $validate = []) {
        global $DB;

        // Export.
        $storage = new local_xray\local\api\auto_clean();
        $storagedir = $storage->get_directory();
        $DB->set_debug($debug);
        $this->export($now, $storagedir, $itemname);
        if ($debug) {
            $storage->listdir();
            $DB->set_debug(false);
        }

        $exportfile = $this->get_export_file($storagedir, $itemname);
        if ($expectedcount == 0) {
            $this->assertFileNotExists($exportfile);
            return;
        }

        $this->assertFileExists($exportfile);
        if ($debug) {
            mtrace($exportfile);
        }

        $first = true;
        $count = count($typedef);
        $iterator = new csv_fileiterator($exportfile);
        $realexpectedcount = 0;
        $validator = null;
        foreach ($iterator as $item) {
            if ($first) {
                $first = false;
                continue;
            }
            // Expect N items.
            $this->assertEquals($count, count($item), var_export($item, true));

            $pos = 0;
            if (!empty($validate)) {
                $validator = isset($validate[$realexpectedcount]) ? $validate[$realexpectedcount] : null;
            }
            foreach ($item as $field) {
                if (($typedef[$pos]['optional'] and !empty($field)) or !$typedef[$pos]['optional']) {
                    $this->assertInternalType($typedef[$pos]['type'], $field);
                }
                if (!empty($validate)) {
                    if ($validator[$pos] !== null) {
                        $this->assertEquals(
                            $validator[$pos],
                            $field,
                            var_export($item, true) . var_export($validator, true)
                        );
                    }
                }
                $pos++;
            }
            $realexpectedcount++;
        }

        if ($expectedcount > 0) {
            $this->assertEquals($expectedcount, $realexpectedcount);
        }
    }

}
