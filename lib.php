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
    if (($reports !== null) || $page->course->format == "singleactivity" || ($context->contextlevel < CONTEXT_COURSE) ||
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
 * Get headline data for the template.
 * @param $courseid
 * @return stdClass
 */
function local_xray_template_data($courseid){
    // Get headline data.
    $headlinedata = \local_xray\dashboard\dashboard::get($courseid);
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