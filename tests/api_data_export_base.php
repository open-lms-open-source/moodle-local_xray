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

/**
 * Class local_xray_api_data_export_base_testcase
 * @group local_xray
 */
abstract class local_xray_api_data_export_base_testcase extends advanced_testcase {

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
        $params = array(
            'objectid' => $discussion->id,
            'context' => $modcontext,
            'other' => array(
                'forumid' => $forum->id,
            )
        );

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
        $params = array(
            'objectid' => $discussion->id,
            'context' => $modcontext,
            'other' => array(
                'forumid' => $forum->id,
            )
        );

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
        if (empty($timecreated)) {
            $timecreated = time();
        }

        $record = [
            'timecreated' => $timecreated,
            'startdate'   => $timecreated
        ];
        // Create course(s).
        $datagen = $this->getDataGenerator();
        $categories = [];
        $count = 0;
        while ($count++ < $nr) {
            $categories[] = $datagen->create_category($record);
        }

        return $categories;
    }

    /**
     * Creates courses needed for test
     *
     * @param int $nr
     * @param int $timecreated
     * @return array
     */
    protected function addcourses($nr, $timecreated = null) {
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
            $courses[] = $datagen->create_course($record);
        }

        return $courses;
    }

    /**
     * Creates forums and discussions needed for the test for specified type of forum
     *
     * @param int $nr
     * @param array $courses
     * @param string $module
     * @return array
     * @throws coding_exception
     */
    protected function addforumsbase($nr, $courses, $module) {
        global $USER;
        /* @var mod_forum_generator|mod_hsuforum_generator $forumgen */
        $forumgen = $this->getDataGenerator()->get_plugin_generator($module);
        $forums = [];
        $discussions = [];
        $posts = [];
        foreach ($courses as $course) {
            $count = 0;
            while ($count++ < $nr) {
                $instance = $forumgen->create_instance(['type' => 'general', 'course' => $course]);
                $forums[] = $instance;
                $discussion = $forumgen->create_discussion(['course' => $course->id,
                    'forum'  => $instance->id,
                    'userid' => $USER->id]);
                $discussions[] = $discussion;
                $post = $forumgen->create_post(['discussion' => $discussion->id, 'userid' => $USER->id]);
                $posts[] = $post;
            }
        }

        return [$forums, $discussions, $posts];
    }

    /**
     * @param int $nr
     * @param array $courses
     * @return array
     */
    protected function addhsuforums($nr, $courses) {
        return $this->addforumsbase($nr, $courses, 'mod_hsuforum');
    }

    /**
     * @param int $nr
     * @param array $courses
     * @return array
     */
    protected function addforums($nr, $courses) {
        return $this->addforumsbase($nr, $courses, 'mod_forum');
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
        /* @var mod_quiz_generator $quizgen */
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
     * Export data to csv files
     *
     * @param $timeend
     * @param $dir
     */
    protected function export($timeend, $dir) {
        local_xray\local\api\data_export::export_csv(0, $timeend, $dir);
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
        global $DB;
        $datagen = $this->getDataGenerator();
        $student = $datagen->create_user(['username' => 'supercalifragilisticoexpialidoso']);
        $roleid = $DB->get_field('role', 'id', ['shortname' => 'student'], MUST_EXIST);
        $timenow = time();
        foreach ($courses as $course) {
            $datagen->enrol_user($student->id, $course->id, $roleid);
            /* @var grade_item[] $gitems */
            $gitems = grade_item::fetch_all(['itemmodule' => $module, 'courseid' => $course->id]);
            foreach ($gitems as $gitem) {
                $gitem->update_raw_grade($student->id, 80.0, null, false, FORMAT_MOODLE, null, $timenow, $timenow);
            }
        }

        return $student;
    }

    /**
     * @param  int $nr
     * @param  int $timemodified
     * @return stdClass[]
     */
    protected function addusers($nr) {
        $datagen = $this->getDataGenerator();
        $users = [];
        for ($pos = 0; $pos < $nr; $pos++) {
            $users[] = $datagen->create_user(['username' => 'testuser'.$pos, 'deleted' => 0]);
        }
        return $users;
    }

    /**
     * preset
     */
    protected function init_base() {
        // Reset any progress saved.
        local_xray\local\api\data_export::delete_progress_settings();
        $this->setAdminUser();

        // Check is required since many hosting companies disable this function.
        if (function_exists('sys_get_temp_dir')) {
            // This is unfortunate workaround for issues with nfs locking when using Vagrant.
            // If returned directory does not offer sufficient permissions the default is used.
            set_config('exportlocation', sys_get_temp_dir(), 'local_xray');
        }
    }

}
