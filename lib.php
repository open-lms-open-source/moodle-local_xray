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
    static $reports = null;
    // Course selection check.
    if (!\local_xray\local\api\course_manager::is_course_selected($page->course->id)) {
        return $reports;
    }

    // Small caching to prevent double calculation call since we need the same information in both calls.
    // The forums in home page should not display the X-ray link.
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
 * Listener for send email to admin/s when X-Ray Learning Analytics data sync failed.
 * @param \local_xray\event\sync_failed $event
 * @throws coding_exception
 */
function local_xray_sync_failed(\local_xray\event\sync_failed $event) {

    $error = $event->get_description();
    $subject = get_string('syncfailed', 'local_xray');
    // We will send email to each administrator.
    $userfrom = get_admin();
    $admins = get_admins();
    foreach ($admins as $admin) {
        $eventdata = new \stdClass();
        $eventdata->component         = 'moodle';
        $eventdata->name              = 'errors';
        $eventdata->userfrom          = $userfrom;
        $eventdata->userto            = $admin;
        $eventdata->subject           = $subject;
        $eventdata->fullmessage       = $error;
        $eventdata->fullmessageformat = FORMAT_PLAIN;
        $eventdata->fullmessagehtml   = '';
        $eventdata->smallmessage      = '';
        message_send($eventdata);
    }
}

/**
 * Get headline data for the template.
 * @param $courseid
 * @return stdClass
 */
function local_xray_template_data($courseid, $userid){
    global $OUTPUT;
    // Get headline data.
    $headlinedata = \local_xray\dashboard\dashboard::get($courseid, $userid);

    if ($headlinedata instanceof \local_xray\dashboard\dashboard_data) {
        // Add info in the template.
        $data = new stdClass();

        // Styles.
        $linksnum = array('title' => get_string('link_gotoreport', 'local_xray'), 'style' => 'text-decoration: none; color: #777777; font-weight: bolder;');
        $gotoreport = array('title' => get_string('link_gotoreport', 'local_xray'));
        $reporticonpdf = array('width' => '37px');
        $reporticon = array('width' => '39px');
        $reporticonarrowpdf = array('width' => '25px');
        $reporticonarrow = array('width' => '31px');
        $reportdata = array('style' => 'color:#777777;font-weight:bolder;font-size:20px;');

        // Add report date.
        $data->reportdate = $headlinedata->reportdate;

        // Risk.
        // Icon and link.
        $xrayriskicon = local_xray_get_email_icons('xray-risk');
        $data->riskiconpdf = html_writer::img($xrayriskicon, get_string('risk', 'local_xray'), $reporticonpdf);
        $data->riskicon = html_writer::img($xrayriskicon, get_string('risk', 'local_xray'), $reporticon);

        $riskurl = new moodle_url("/local/xray/view.php",
            array("controller" => "risk", "courseid" => $courseid, "action" => "view"));

        $data->risklink = html_writer::link($riskurl, get_string('risk', 'local_xray'), $gotoreport);

        $riskarrowurl = new moodle_url("/local/xray/view.php",
            array("controller" => "risk", "courseid" => $courseid, "header" => 1), "riskMeasures");

        // Calculate colour status.
        $statusclassrisk = local_xray\dashboard\dashboard_data::get_status_with_average($headlinedata->usersinrisk,
            $headlinedata->risktotal,
            $headlinedata->averagerisksevendaybefore,
            $headlinedata->maximumtotalrisksevendaybefore,
            true,
            true); // This arrow will be inverse to all.

        $xrayriskarrow = local_xray_get_email_icons($statusclassrisk[2]);
        $data->riskarrowpdf = html_writer::img($xrayriskarrow, $statusclassrisk[1], $reporticonarrowpdf);
        $riskarrow = html_writer::img($xrayriskarrow, $statusclassrisk[1], $reporticonarrow);
        $data->riskarrow = html_writer::link($riskarrowurl, $riskarrow, $gotoreport);

        // Number for risk.
        $a = new stdClass();
        $a->first = $headlinedata->usersinrisk;
        $a->second = $headlinedata->risktotal;
        $data->riskdatapdf = html_writer::span(get_string('headline_number_of', 'local_xray', $a), '', $reportdata);
        $data->riskdata = html_writer::link($riskarrowurl, get_string('headline_number_of', 'local_xray', $a),
            $linksnum);

        $data->studentsrisk = get_string('headline_studentatrisk', 'local_xray');

        // Number of students at risk in the last 7 days.
        $a = new stdClass();
        $a->previous = $headlinedata->averagerisksevendaybefore;
        $a->total = $headlinedata->maximumtotalrisksevendaybefore;
        $data->riskaverageweek = get_string("averageofweek_integer", 'local_xray', $a);

        // Activity.
        // Icon and link.
        $xrayactivityicon = local_xray_get_email_icons('xray-activity');
        $data->activityiconpdf = html_writer::img($xrayactivityicon, get_string('activityreport', 'local_xray'),
            $reporticonpdf);
        $data->activityicon = html_writer::img($xrayactivityicon, get_string('activityreport', 'local_xray'),
            $reporticon);

        $activityurl = new moodle_url("/local/xray/view.php",
            array("controller" => "activityreport", "courseid" => $courseid, "action" => "view"));

        $data->activitylink = html_writer::link($activityurl, get_string('activityreport', 'local_xray'), $gotoreport);

        $activityarrowurl = new moodle_url("/local/xray/view.php",
            array("controller" => "activityreport", "courseid" => $courseid, "header" => 1), "studentList");

        // Calculate colour status.
        $statusclassactivity = local_xray\dashboard\dashboard_data::get_status_with_average($headlinedata->usersloggedinpreviousweek,
            $headlinedata->usersactivitytotal,
            $headlinedata->averageuserslastsevendays,
            $headlinedata->userstotalprevioussevendays,
            false,
            true);

        $xrayactivityarrow = local_xray_get_email_icons($statusclassactivity[2]);
        $data->activityarrowpdf = html_writer::img($xrayactivityarrow, $statusclassactivity[1], $reporticonarrowpdf);
        $activityarrow = html_writer::img($xrayactivityarrow, $statusclassactivity[1], $reporticonarrow);
        $data->activityarrow = html_writer::link($activityarrowurl, $activityarrow, $gotoreport);

        $a = new stdClass();
        $a->first = $headlinedata->usersloggedinpreviousweek;
        $a->second = $headlinedata->usersactivitytotal;
        $data->activitydatapdf = html_writer::span(get_string('headline_number_of', 'local_xray', $a), '', $reportdata);
        $data->activitydata = html_writer::link($activityarrowurl, get_string('headline_number_of', 'local_xray', $a),
            $linksnum);

        $data->activityloggedstudents = get_string('headline_loggedstudents', 'local_xray');

        // Number of students logged in in last 7 days.
        $a = new stdClass();
        $a->current = $headlinedata->averageuserslastsevendays;
        $a->total = $headlinedata->userstotalprevioussevendays;
        $data->activitylastweekwasof = get_string("headline_lastweekwasof_activity", 'local_xray', $a);

        // Gradebook.
        // Icon and link.
        $xraygradeicon = local_xray_get_email_icons('xray-grade');
        $data->gradebookiconpdf = html_writer::img($xraygradeicon, get_string('gradebookreport', 'local_xray'),
            $reporticonpdf);
        $data->gradebookicon = html_writer::img($xraygradeicon, get_string('gradebookreport', 'local_xray'), $reporticon);

        $gradebookurl = new moodle_url("/local/xray/view.php",
            array("controller" => "gradebookreport", "courseid" => $courseid, "action" => "view"));

        $data->gradebooklink = html_writer::link($gradebookurl, get_string('gradebookreport', 'local_xray'), $gotoreport);

        $gradebookarrowurl = new moodle_url("/local/xray/view.php",
            array("controller" => "gradebookreport", "courseid" => $courseid, "header" => 1), "courseGradeTable");

        // Calculate colour status.
        $statusclass = local_xray\dashboard\dashboard_data::get_status_simple($headlinedata->averagegradeslastsevendays,
            $headlinedata->averagegradeslastsevendayspreviousweek,
            true);

        $xraygradebookarrow = local_xray_get_email_icons($statusclass[2]);
        $data->gradebookarrowpdf = html_writer::img($xraygradebookarrow, $statusclass[1], $reporticonarrowpdf);
        $gradebookarrow = html_writer::img($xraygradebookarrow, $statusclass[1], $reporticonarrow);
        $data->gradebookarrow = html_writer::link($gradebookarrowurl, $gradebookarrow, $gotoreport);

        $data->gradebooknumberpdf = html_writer::span(get_string('headline_number_percentage', 'local_xray',
            $headlinedata->averagegradeslastsevendays), '', $reportdata);
        $data->gradebooknumber = html_writer::link($gradebookarrowurl, get_string('headline_number_percentage', 'local_xray',
            $headlinedata->averagegradeslastsevendays), $linksnum);
        $data->gradebookheadline = get_string('headline_average', 'local_xray');
        $data->gradebookaverageofweek = get_string("averageofweek_gradebook", 'local_xray', $headlinedata->averagegradeslastsevendayspreviousweek);

        // Discussion.
        // Icon and link.
        $xraydiscussionsicon = local_xray_get_email_icons('xray-discussions');
        $data->discussioniconpdf = html_writer::img($xraydiscussionsicon, get_string('discussionreport', 'local_xray'),
            $reporticonpdf);
        $data->discussionicon = html_writer::img($xraydiscussionsicon, get_string('discussionreport', 'local_xray'),
            $reporticon);

        $discussionurl = new moodle_url("/local/xray/view.php",
            array("controller" => "discussionreport", "courseid" => $courseid, "action" => "view"));

        $data->discussionlink = html_writer::link($discussionurl, get_string('discussionreport', 'local_xray'), $gotoreport);

        $discussionarrowurl = new moodle_url("/local/xray/view.php",
            array("controller" => "discussionreport", "courseid" => $courseid, "header" => 1), "discussionMetrics");

        // Calculate colour status.
        $statusclassdiscussion = local_xray\dashboard\dashboard_data::get_status_simple($headlinedata->postslastsevendays,
            $headlinedata->postslastsevendayspreviousweek,
            true);

        $xraydiscussionsarrow = local_xray_get_email_icons($statusclassdiscussion[2]);
        $data->discussionarrowpdf = html_writer::img($xraydiscussionsarrow, $statusclassdiscussion[1], $reporticonarrowpdf);
        $discussionarrow = html_writer::img($xraydiscussionsarrow, $statusclassdiscussion[1], $reporticonarrow);
        $data->discussionarrow = html_writer::link($discussionarrowurl, $discussionarrow, $gotoreport);
        $data->discussiondatapdf = html_writer::span($headlinedata->postslastsevendays, '', $reportdata);
        $data->discussiondata = html_writer::link($discussionarrowurl, $headlinedata->postslastsevendays, $linksnum);
        $data->discussionposts = get_string('headline_posts', 'local_xray');
        $data->discussionlastweekwas = get_string("headline_lastweekwas_discussion", 'local_xray', $headlinedata->postslastsevendayspreviousweek);

        // Recommended Actions.
        $data->recommendationslist = '';
        $data->recommendations = false;
        $data->recommendationspdf = $headlinedata->recommendations;
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

/**
 * Get the url for the icons email.
 *
 * @return string.
 */
function local_xray_get_email_icons($imagename) {
    // Add format.
    $imagename = $imagename.'.gif';
    // Default value.
    $baseurl = 'https://cdn.xrayanalytics.net';
    $cfgxray = get_config('local_xray');
    if (isset($cfgxray->iconsurl)) {
        $baseurl = $cfgxray->iconsurl;
    }
    return sprintf('%s/pix/%s', $baseurl, $imagename);
}

/**
 * Check if the course has the Single Activity format.
 *
 * @return bool.
 */
function local_xray_single_activity_course($courseid) {
    global $DB;
    return $DB->record_exists('course', array('id' => $courseid, 'format' => 'singleactivity'));
}

/**
 * Create PDF.
 *
 * @param object $headlinedata
 * @param string $subject
 * @return object $pdf
 */
function local_xray_create_pdf($headlinedata, $subject) {
    global $CFG;

    require_once("$CFG->libdir/pdflib.php");

    $pdf = new pdf();

    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor(get_string('pluginname', 'local_xray'));
    $pdf->SetTitle($subject);
    $pdf->SetSubject($subject);
    $pdf->SetKeywords('MOODLE, XRAY');
    // Set header and footer fonts.
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    // Set default monospaced font.
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    // Set margins.
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    // Set auto page breaks.
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    // Set image scale factor.
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    // Set font.
    $pdf->SetFont('helvetica', '', 9);
    // Add a page.
    $pdf->AddPage();
    // Create some HTML content.

    $title = new html_table();
    $title->attributes = array('style' => 'padding-bottom: 20px;font-weight:bolder;font-size:22px;');
    $title->data[] = array($subject);
    $html = html_writer::table($title);

    $riskicontable = new html_table();
    $riskicontable->data = local_xray_report_head_row(get_string('risk', 'local_xray'), $headlinedata->riskiconpdf);

    $activityicontable = new html_table();
    $activityicontable->data = local_xray_report_head_row(get_string('activityreport', 'local_xray'), $headlinedata->activityiconpdf);

    $gradebookicontable = new html_table();
    $gradebookicontable->data = local_xray_report_head_row(get_string('gradebookreport', 'local_xray'), $headlinedata->gradebookiconpdf);

    $discussionicontable = new html_table();
    $discussionicontable->data = local_xray_report_head_row(get_string('discussionreport', 'local_xray'), $headlinedata->discussioniconpdf);

    $table = new html_table();
    $table->data[] = array(html_writer::table($riskicontable),
        html_writer::table($activityicontable),
        html_writer::table($gradebookicontable),
        html_writer::table($discussionicontable));

    $riskdatatable = new html_table();
    $riskdatatable->data[] = array($headlinedata->riskdatapdf, $headlinedata->riskarrowpdf);

    $activitydatatable = new html_table();
    $activitydatatable->data[] = array($headlinedata->activitydatapdf, $headlinedata->activityarrowpdf);

    $gradebooknumbertable = new html_table();
    $gradebooknumbertable->data[] = array($headlinedata->gradebooknumberpdf, $headlinedata->gradebookarrowpdf);

    $discussiondatatable = new html_table();
    $discussiondatatable->data[] = array($headlinedata->discussiondatapdf, $headlinedata->discussionarrowpdf);

    $table->data[] = array(html_writer::table($riskdatatable), html_writer::table($activitydatatable), html_writer::table($gradebooknumbertable), html_writer::table($discussiondatatable));
    $table->data[] = array($headlinedata->studentsrisk, $headlinedata->activityloggedstudents, $headlinedata->gradebookheadline, $headlinedata->discussionposts);
    $table->data[] = array($headlinedata->riskaverageweek, $headlinedata->activitylastweekwasof, $headlinedata->gradebookaverageofweek, $headlinedata->discussionlastweekwas);

    $html .= html_writer::table($table);

    // Add recommendations.
    if ($headlinedata->recommendations) {
        $recommendationtitle = new html_table();
        $recommendationtitle->attributes = array('style' => 'padding: 10px 10px 0 10px;font-weight:bolder;');
        $recommendationtitle->data[] = array(get_string('recommendedactions' , 'local_xray'));
        $html .= html_writer::table($recommendationtitle);

        $recommendationtable = new html_table();
        $recommendationtable->attributes = array('style' => 'padding: 10px 10px 0 10px;');
        $recommendationnumber = 1;
        foreach ($headlinedata->recommendationspdf as $recommendation) {
            $recommendationtable->data[] = local_xray_add_recommendation_pdf($recommendationnumber, $recommendation);
            $recommendationnumber++;
        }
        $html .= html_writer::table($recommendationtable);
    }

    // Add date of email.
    $xraydate = new html_table();
    $xraydate->attributes = array('style' => 'padding: 20px 10px 0 10px;font-weight:bolder;text-align:center;');


    $date = new DateTime($headlinedata->reportdate);
    $xrayemaildate = userdate($date->getTimestamp(), get_string('strftimedayshort', 'langconfig'), 'UTC');
    $xraydate->data[] = array(get_string('xrayemaildate', 'local_xray', $xrayemaildate));
    $html .= html_writer::table($xraydate);

    // Output the HTML content
    $pdf->writeHTML($html, true, 0, true, 0);
    // Reset pointer to the last page
    $pdf->lastPage();

    return $pdf;
}

/**
 * Add a recommendation in the PDF.
 *
 * @param int $recommendationnumber
 * @param string $recommendation
 * @return array of cells.
 */
function local_xray_add_recommendation_pdf($recommendationnumber, $recommendation) {

    $cellicon = new html_table_cell();
    $cellicon->text = $recommendationnumber;
    $cellicon->style = 'width:28px;color:#777777;font-weight:bolder;';
    $cellname = new html_table_cell();
    $cellname->text = $recommendation;
    $cellname->style = 'width:80%;';

    return array($cellicon, $cellname);
}

/**
 * Add report icon with the report title.
 *
 * @param string $reporttitle
 * @param string $reporticon
 * @return array of rows.
 */
function local_xray_report_head_row($reporttitle, $reporticon) {

    $cell1 = new html_table_cell();
    $cell1->text = $reporticon;
    $cell1->rowspan = 3;
    $cell1->style = 'width:42px;padding: 0;';
    $cell2 = new html_table_cell();
    $row = new html_table_row();
    $row->cells = array($cell1, $cell2);

    $cell2 = new html_table_cell();
    $cell2->text = $reporttitle;
    $row2 = new html_table_row();
    $row2->cells = array($cell2);

    $cell2 = new html_table_cell();
    $row3 = new html_table_row();
    $row3->cells = array($cell2);

    return array($row, $row2, $row3);
}