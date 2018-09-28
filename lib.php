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
 * @author Darko Miletic
 * @author David Castro <david.castro@blackboard.com>
 * @copyright Copyright (c) 2017 Blackboard Inc. (http://www.blackboard.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
    global $CFG;
    static $reports = null;
    // Course selection check.
    if (!\local_xray\local\api\course_manager::is_xray_course($page->course->id)) {
        return $reports;
    }

    // Small caching to prevent double calculation call since we need the same information in both calls.
    // The forums in home page should not display the X-ray link.
    if (!is_null($reports) || $page->course->format == "singleactivity" || ($context->contextlevel < CONTEXT_COURSE) ||
        ($page->course->id == SITEID) || !has_capability('local/xray:view', $context)) {
        return $reports;
    }

    $reports     = [];
    $extraparams = [];

    require_once($CFG->dirroot.'/local/xray/locallib.php');
    if (!local_xray_risk_disabled()) {
        $reportlist['courseadmin']['risk'] = 'local/xray:risk_view';
    }
    $reportlist['courseadmin']['activityreport'] = 'local/xray:activityreport_view';
    $reportlist['courseadmin']['gradebookreport'] = 'local/xray:gradebookreport_view';
    $reportlist['courseadmin']['discussionreport'] = 'local/xray:discussionreport_view';

    if (in_array($page->pagetype, ['mod-forum-view', 'mod-hsuforum-view',
        'mod-forum-discuss', 'mod-hsuforum-discuss'])) {

        if (local_xray_reports()) {
            $extraparams['forumid'] = $page->cm->instance;
        } else {
            $extraparams['forum'] = $page->cm->instance;
        }
        $extraparams['cmid' ] = $context->instanceid;
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
                require_once($CFG->dirroot.'/local/xray/locallib.php');
                if (local_xray_reports()) {
                    $reports[$nodename][$report] = $baseurl->out(false, ['controller' => 'xrayreports',
                            'name'   => local_xray_name_conversion($report),
                            'courseid'   => $page->course->id,
                            'action'     => 'view'] + $extraparams);
                } else {
                    $reports[$nodename][$report] = $baseurl->out(false, ['controller' => $report,
                            'courseid'   => $page->course->id,
                            'action'     => 'view'] + $extraparams);
                }
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
 * @param  navigation_node $nav
 * @return void
 */
function local_xray_extends_navigation($nav) {
    global $PAGE;
    ($nav); // Just to remove unused param warning.

    static $search = [
        'topics' => '#region-main',
        'weeks' => '#region-main',
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
        /** @var local_xray_renderer $renderer */
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
 * @param navigation_node $nav
 */
function local_xray_extend_navigation($nav) {
    local_xray_extends_navigation($nav);
}

/**
 * Add the link in the profile user for subscriptions.
 * @param \core_user\output\myprofile\tree $tree
 * @param stdClass $user
 * @param bool $iscurrentuser
 * @param stdClass $course
 * @return bool
 */
function local_xray_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    global $CFG;
    ($iscurrentuser);
    ($course);

    require_once($CFG->dirroot.'/local/xray/locallib.php');

    // Validate user.
    if ((!has_capability("local/xray:globalsub_view", context_system::instance()) &&
            !local_xray_is_teacher($user->id)) || !local_xray_email_enable()) {
        return false;
    }
    // Url for subscription.
    $subscriptionurl = new moodle_url("/local/xray/view.php", ["controller" => "globalsub"]);

    $node = new core_user\output\myprofile\node(
        'miscellaneous',
        'local_xray',
        get_string('profilelink', 'local_xray'),
        null,
        $subscriptionurl
    );
    $tree->add_node($node);
    return true;
}
