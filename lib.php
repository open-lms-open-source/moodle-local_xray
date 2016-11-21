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

/**
 * Convenient wrappers and helper for using the X-Ray web service API.
 *
 * @package local_xray
 * @author Pablo Pagnone
 * @author German Vitale
 * @author Darko MIletic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright Moodlerooms
 */

defined('MOODLE_INTERNAL') || die();

// Contants.

define('XRAYSUBSCRIBECOURSE', 0);
define('XRAYSUBSCRIBEON', 1);
define('XRAYSUBSCRIBEOFF', 2);

define('XRAYNEVER', 'never');
define('XRAYDAILY', 'daily');
define('XRAYWEEKLY', 'weekly');

/**
 * Generate list of report links according to the current page
 * Result is returned as associative array ($reportname => $reporturl)
 *
 * @param  moodle_page $page
 * @param  context $context
 * @return array
 * @throws coding_exception
 */
function local_xray_navigationlinks(moodle_page $page, context $context) {
    // Small caching to prevent double calculation call since we need the same information in both calls.
    // The forums in home page should not display the X-ray link.
    static $reports = null;
    if (!local_xray_is_course_enable() || ($reports !== null) || $page->course->format == "singleactivity" || ($context->contextlevel < CONTEXT_COURSE) ||
        ($page->course->id == SITEID) || !has_capability('local/xray:view', $context)) {
        return $reports;
    }

    $reports     = [];
    $extraparams = [];

    $reportlist  = [
        'courseadmin' => [
            'risk'             => 'local/xray:risk_view',
            'activityreport'   => 'local/xray:activityreport_view',
            'gradebookreport'  => 'local/xray:gradebookreport_view',
            'discussionreport' => 'local/xray:discussionreport_view'
        ]
    ];

    if (in_array($page->pagetype, ['mod-forum-view', 'mod-hsuforum-view',
        'mod-forum-discuss', 'mod-hsuforum-discuss'])) {
        $extraparams['cmid' ] = $context->instanceid;
        $extraparams['forum'] = $page->cm->instance;

        // Support for discussion of forum/hsforum.
        $d = $page->url->get_param('d');
        if (!empty($d)) {
            $extraparams['d'] = $d;
        }
        $reportlist = [
            'modulesettings' => [
                'discussionreportindividualforum' => 'local/xray:discussionreportindividualforum_view',
            ]
        ];
    }

    $baseurl = new moodle_url('/local/xray/view.php');
    foreach ($reportlist as $nodename => $reportsublist) {
        foreach ($reportsublist as $report => $capability) {
            if (has_capability($capability, $context)) {
                $reports[$nodename][$report] = $baseurl->out(false, ['controller' => $report,
                                                                     'courseid'   => $page->course->id,
                                                                     'action'     => 'view'] + $extraparams);
            }
        }
    }

    return $reports;
}

/**
 * Extend navigations block.
 *
 * @param  settings_navigation $settings
 * @param  context $context
 * @return void
 * @throws coding_exception
 */
function local_xray_extends_settings_navigation(settings_navigation $settings, context $context) {
    global $PAGE;

    $reports = local_xray_navigationlinks($PAGE, $context);
    if (empty($reports)) {
        return;
    }

    foreach ($reports as $nodename => $reportsublist) {
        $coursenode = $settings->get($nodename);
        if ($coursenode === false) {
            continue;
        }
        $extranavigation = $coursenode->add(get_string('navigation_xray', 'local_xray'));
        foreach ($reportsublist as $reportstring => $url) {
            $extranavigation->add(get_string($reportstring, 'local_xray'), $url);
        }
        $extranavigation = null;
        $coursenode = null;
    }
}

/**
 * This is the version of the JS that should be used up to Moodle 2.8
 * New one will be required for Moodle 2.9+
 *
 * @param  global_navigation $nav
 * @return void
 */
function local_xray_extends_navigation(global_navigation $nav) {
    global $PAGE;
    ($nav); // Just to remove unused param warning.

    static $search = [
        'topics' => '#region-main',
        'weeks' => '#region-main',
        'flexpage' => '#page-content',
        'folderview' => '#region-main',
        'onetopic' => '#region-main',
        'singleactivity' => '.notexist', // Not sure what to do here?
        'social' => '#region-main',
        'tabbedweek' => '#region-main',
        'topcoll' => '#region-main',
    ];

    $courseformat = $PAGE->course->format;
    if (!isset($search[$courseformat])) {
        $search[$courseformat] = '#region-main';
    }

    $reportview = ($PAGE->pagetype == 'local-xray-view');
    $courseview = false;
    if ($PAGE->has_set_url()) {
        $courseview = $PAGE->url->compare(new moodle_url('/course/view.php', ['id' => $PAGE->course->id]), URL_MATCH_BASE);
        if ($courseview) {
            $buieditid = (int)optional_param('bui_editid'  , 0, PARAM_INT);
            $buihideid = (int)optional_param('bui_hideid'  , 0, PARAM_INT);
            $buidelid  = (int)optional_param('bui_deleteid', 0, PARAM_INT);
            if (!empty($buidelid) || !empty($buihideid) || !empty($buieditid)) {
                $courseview = false;
            }
        }
    }
    if (!$reportview && !$courseview) {
        return;
    }

    $menu = '';
    if (!$reportview) {
        $reportcontroller = optional_param('controller', '', PARAM_ALPHA);
        $reports = local_xray_navigationlinks($PAGE, $PAGE->context);
        /* @var local_xray_renderer $renderer */
        $renderer = $PAGE->get_renderer('local_xray');
        $menu = $renderer->print_course_menu($reportcontroller, $reports);
    }

    if (!empty($menu)) {
        $menuappend = 0;
        // Easy way to force include on every page (provided that navigation block is present).
        $PAGE->requires->yui_module(['moodle-local_xray-custmenu'],
            'M.local_xray.custmenu.init',
            [[
                'menusearch' => $search[$courseformat],
                'menuappend' => $menuappend,
                'items'      => $menu,
            ]],
            null,
            false
        );
    }

}

/**
 * Moodle 2.9+ compliant method.
 *
 * @link  https://tracker.moodle.org/browse/MDL-49643 MDL-49643
 * @param settings_navigation $settings
 * @param context $context
 */
function local_xray_extend_settings_navigation(settings_navigation $settings, context $context) {
    local_xray_extends_settings_navigation($settings, $context);
}

/**
 * Moodle 2.9+ compliant method.
 *
 * @link  https://tracker.moodle.org/browse/MDL-49643 MDL-49643
 * @param global_navigation $nav
 */
function local_xray_extend_navigation(global_navigation $nav) {
    local_xray_extends_navigation($nav);
}

/**
 * Listener for course delete event
 *
 * @param \core\event\course_deleted $event
 */
function local_xray_course_deleted(\core\event\course_deleted $event) {
    global $DB;
    $data = [
        'course'      => $event->courseid,
        'timedeleted' => $event->timecreated
    ];
    $DB->insert_record_raw('local_xray_course', $data, false);
}

/**
 * Listener for course category delete
 *
 * @param \core\event\course_category_deleted $event
 */
function local_xray_course_category_deleted(\core\event\course_category_deleted $event) {
    global $DB;
    $data = [
        'category'    => $event->objectid,
        'timedeleted' => $event->timecreated
    ];
    $DB->insert_record_raw('local_xray_coursecat', $data, false);
}

/**
 * Listener for Advanced forum discussion delete
 *
 * @param \mod_hsuforum\event\discussion_deleted $event
 */
function local_xray_hsu_discussion_deleted(\mod_hsuforum\event\discussion_deleted $event) {
    global $DB;
    $data = [
        'discussion'  => $event->objectid,
        'cm'          => $event->contextinstanceid,
        'timedeleted' => $event->timecreated
    ];
    $DB->insert_record_raw('local_xray_hsudisc', $data, false);
}

/**
 * Listener for Advanced forum post delete
 *
 * @param \mod_hsuforum\event\post_deleted $event
 */
function local_xray_hsu_post_deleted(\mod_hsuforum\event\post_deleted $event) {
    global $DB;
    $data = [
        'post'        => $event->objectid,
        'discussion'  => $event->other['discussionid'],
        'cm'          => $event->contextinstanceid,
        'timedeleted' => $event->timecreated
    ];
    $DB->insert_record_raw('local_xray_hsupost', $data, false);
}

/**
 * Listener for forum discussion delete
 *
 * @param \mod_forum\event\discussion_deleted $event
 */
function local_xray_discussion_deleted(\mod_forum\event\discussion_deleted $event) {
    global $DB;
    $data = [
        'discussion'  => $event->objectid,
        'cm'          => $event->contextinstanceid,
        'timedeleted' => $event->timecreated
    ];
    $DB->insert_record_raw('local_xray_disc', $data, false);
}

/**
 * Listener for forum post delete
 *
 * @param \mod_forum\event\post_deleted $event
 */
function local_xray_post_deleted(\mod_forum\event\post_deleted $event) {
    global $DB;
    $data = [
        'post'        => $event->objectid,
        'discussion'  => $event->other['discussionid'],
        'cm'          => $event->contextinstanceid,
        'timedeleted' => $event->timecreated
    ];
    $DB->insert_record_raw('local_xray_post', $data, false);
}

/**
 * Listener for activity delete from course
 *
 * @param \core\event\course_module_deleted $event
 */
function local_xray_course_module_deleted(\core\event\course_module_deleted $event) {
    global $DB;
    // We handle only gradable activities.
    if (plugin_supports('mod', $event->other['modulename'], FEATURE_GRADE_HAS_GRADE, false)) {
        $data = [
            'cm'          => $event->objectid,
            'course'      => $event->courseid,
            'timedeleted' => $event->timecreated
        ];
        $DB->insert_record_raw('local_xray_cm', $data, false);
    }
}

/**
 * Listener for role unasignment on a course context ONLY!
 *
 * @param \core\event\role_unassigned $event
 * @throws coding_exception
 */
function local_xray_role_unassigned(\core\event\role_unassigned $event) {
    global $DB;
    // Strangely can not use course_context::instance_by_id since it throws exception...
    $courseid = $DB->get_field('context', 'instanceid', ['id' => $event->contextid, 'contextlevel' => CONTEXT_COURSE]);
    if ($courseid) {
        $data = [
            'role'        => $event->objectid,
            'userid'      => $event->relateduserid,
            'course'      => $courseid,
            'timedeleted' => $event->timecreated
        ];
        $DB->insert_record_raw('local_xray_roleunas', $data, false);
    }
}

/**
 * Listener for removal of user enrollment from a course
 * @param \core\event\user_enrolment_deleted $event
 * @throws coding_exception
 */
function local_xray_user_enrolment_deleted(\core\event\user_enrolment_deleted $event) {
    global $DB;
    $data = [
        'enrolid'     => $event->objectid,
        'userid'      => $event->relateduserid,
        'courseid'    => $event->courseid,
        'timedeleted' => $event->timecreated
    ];
    $DB->insert_record_raw('local_xray_enroldel', $data, false);
}

/**
 * Get headline data for the template.
 * @param $courseid
 * @return stdClass
 */
function local_xray_template_data($courseid, $userid){
    // Get headline data.
    $headlinedata = \local_xray\dashboard\dashboard::get($courseid, $userid);

    if ($headlinedata instanceof \local_xray\dashboard\dashboard_data) {
        // Add info in the template.
        $data = new stdClass();

        // Risk.
        // Number for risk.
        $a = new stdClass();
        $a->first = $headlinedata->usersinrisk;
        $a->second = $headlinedata->risktotal;
        $data->riskdata = get_string('headline_number_of', 'local_xray', $a);

        $data->studentsrisk = get_string('headline_studentatrisk', 'local_xray');

        // Number of students at risk in the last 7 days.
        $a = new stdClass();
        $a->previous = $headlinedata->averagerisksevendaybefore;
        $a->total = $headlinedata->maximumtotalrisksevendaybefore;
        $data->riskaverageweek = get_string("averageofweek_integer", 'local_xray', $a);

        // Activity.
        $a = new stdClass();
        $a->first = $headlinedata->usersloggedinpreviousweek;
        $a->second = $headlinedata->usersactivitytotal;
        $data->activitydata = get_string('headline_number_of', 'local_xray', $a);

        $data->activityloggedstudents = get_string('headline_loggedstudents', 'local_xray');

        // Number of students logged in in last 7 days.
        $a = new stdClass();
        $a->current = $headlinedata->averageuserslastsevendays;
        $a->total = $headlinedata->userstotalprevioussevendays;
        $data->activitylastweekwasof = get_string("headline_lastweekwasof_activity", 'local_xray', $a);

        // Gradebook.
        $data->gradebooknumber = get_string('headline_number_percentage', 'local_xray', $headlinedata->averagegradeslastsevendays);
        $data->gradebookheadline = get_string('headline_average', 'local_xray');
        $data->gradebookaverageofweek = get_string("averageofweek_gradebook", 'local_xray', $headlinedata->averagegradeslastsevendayspreviousweek);

        // Discussion.
        $data->discussiondata = $headlinedata->postslastsevendays;
        $data->discussionposts = get_string('headline_posts', 'local_xray');
        $data->discussionlastweekwas = get_string("headline_lastweekwas_discussion", 'local_xray', $headlinedata->postslastsevendayspreviousweek);

        // Recommended Actions.
        $data->recommendationslist = '';
        if ($headlinedata->countrecommendations) {
            $data->recommendations = true;
            $recommendationnumber = 1;
            foreach ($headlinedata->recommendations as $recommendation) {
                // Add the recommendation.
                // Title.
                $data->recommendationstitle = get_string('recommendedactions' , 'local_xray');
                // Number.
                $data->recommendationslist .= html_writer::tag('td', $recommendationnumber, array('align' => 'left',
                    'class' => 'MsoNormal',
                    'style' => "color:#777777;font-family:'Segoe UI',sans-serif,Arial,Helvetica,Lato;font-size:29px;line-height:24px;padding-right:5px;",
                    'valign' => 'top'));

                // Recommendation text.
                $data->recommendationslist .= html_writer::tag('td', $recommendation, array('align' => 'left',
                    'class' => 'MsoNormal',
                    'style' => "color:#777777;font-family:'Segoe UI',sans-serif,Arial,Helvetica,Lato;font-size:13px;line-height:24px;"));

                $data->recommendationslist .= html_writer::end_tag('tr');
                $data->recommendationslist .= html_writer::tag('tr', '', array('style' => "height:16px;"));
                $recommendationnumber++;
            }
        }

        $result = $data;
    } else {
        $result = false;
    }

    return $result;
}

/**
 * Check capabilities to send emails.
 *
 * @param $courseid
 * @param $userid
 * @return bool
 * @throws coding_exception
 */
function local_xray_email_capability($courseid, $userid) {
    if (has_capability("local/xray:view", context_course::instance($courseid), $userid)) {
        return true;
    }
    return false;
}

/**
 * Add the link in the profile user for subscriptions.
 */
function local_xray_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    // Validate user.
    if ((!has_capability("local/xray:globalsub_view", context_system::instance()) &&
            !local_xray_get_teacher_courses($user->id)) || !local_xray_email_enable()) {
        return false;
    }
    // Url for subscription.
    $subscriptionurl = new moodle_url("/local/xray/view.php",
        array("controller" => "globalsub"));

    $node = new core_user\output\myprofile\node('miscellaneous', 'local_xray', get_string('profilelink', 'local_xray'), null, $subscriptionurl);
    $tree->add_node($node);
    return true;
}

/**
 * Get all the courses where the user is enrolled as editingteacher or teacher.
 * 
 * @param int $userid
 * @return array of objects, or empty array if no records were found.
 */
function local_xray_get_teacher_courses($userid) {

    global $DB;

    $sql = "SELECT DISTINCT c.id AS courseid
                FROM {user} u
                JOIN {user_enrolments} ue ON ue.userid = u.id
                JOIN {enrol} e ON e.id = ue.enrolid
                JOIN {role_assignments} ra ON ra.userid = u.id
                JOIN {context} ct ON ct.id = ra.contextid AND ct.contextlevel = :contextcourse
                JOIN {course} c ON c.id = ct.instanceid AND e.courseid = c.id
                JOIN {role} r ON r.id = ra.roleid AND (r.shortname = 'editingteacher' OR r.shortname = 'teacher')
                WHERE u.id = :userid AND e.status = 0 AND u.suspended = 0 AND u.deleted = 0";

    $params = array('userid' => $userid, 'contextcourse' => CONTEXT_COURSE);
    return $DB->get_records_sql($sql, $params);
}

/**
 * Check if the user is enrolled as a teacher in a course.
 *
 * @param int $courseid
 * @param int $userid
 * @return bool.
 */
function local_xray_is_teacher_in_course ($courseid, $userid) {
    $usercourses = local_xray_get_teacher_courses($userid);
    if (array_key_exists($courseid, $usercourses)) {
        return true;
    }
    return false;
}

/**
 * This check if course is enable for xray and if we must to show links/report of xray.
 * By default all courses are enabled.
 * But if you add $CFG->xray_enable_courses to config, this will check there.
 *
 * Example:
 * $CFG->xray_enable_courses = array(2,3)
 * Xray will be enable only for courses 2 and 3.
 *
 * Example 2:
 * $CFG->xray_enabled_courses = array();
 * This will disabled all courses.
 *
 * @return bool
 */
function local_xray_is_course_enable() {

    global $CFG, $COURSE;
    $result = true;
    if (isset($CFG->xray_enabled_courses) && is_array($CFG->xray_enabled_courses)) {
        $result = false;
        if (in_array($COURSE->id, $CFG->xray_enabled_courses)) {
            $result = true;
        }
    }
    return $result;
}

/**
 * Check if the Email Report is enabled in control panel.
 *
 * @return bool
 */
function local_xray_email_enable() {
    $result = false;
    $cfgxray = get_config('local_xray');
    if (isset($cfgxray->emailreport) && $cfgxray->emailreport == 1) {
        $result = true;
    }
    return $result;
}

/**
 * Check if the user is subscribed.
 *
 * @param int $courseid
 * @param int $userid
 * @return bool
 */
function local_xray_is_subscribed($userid, $courseid) {
    global $DB;
    return $DB->record_exists('local_xray_subscribe', array('courseid' => $courseid, 'userid' => $userid));
}

/**
 * Get support user.
 *
 * @return stdClass user record.
 */
function local_xray_get_support_user() {
    return core_user::get_support_user();
}

/**
 * Check if the email should be sent today.
 *
 * @return bool.
 */
function local_xray_send_email_today() {
    // Check frequency.
    $frequency = get_config('local_xray', 'emailfrequency');
    // If the frequency is never, the email should not be sent.
    if ($frequency == XRAYNEVER) {
        return false;
    }
    // If the frequency is daily, the email should be sent.
    if ($frequency == XRAYDAILY) {
        return true;
    }
    // By default, the email should be sent weekly.
    // Days of the week.
    $day = array(0 => 'sunday', 1 => 'monday', 2 => 'tuesday', 3 => 'wednesday',
        4 => 'thursday', 5 => 'friday', 6 => 'saturday');
    // Get current day of week.
    $currentdayofweek = date("w");
    // By default, Sunday is the day to run.
    $daytorun = 0;
    // Check if the Control Panel variable weeklyday is set.
    $cfgxray = get_config('local_xray');
    if (isset($cfgxray->weeklyday) && isset($day[$cfgxray->weeklyday])) {
        $daytorun = $cfgxray->weeklyday;
    }
    if ($currentdayofweek == $daytorun) {
        return true;
    }
    return false;
}