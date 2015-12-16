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
    static $reports = null;
    if (($reports !== null) || ($context->contextlevel < CONTEXT_COURSE) || !has_capability('local/xray:view', $context)) {
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

    if (in_array($page->pagetype, ['mod-quiz-view', 'mod-forum-view', 'mod-hsuforum-view'])) {
        $extraparams['cmid' ] = $context->instanceid;
        $extraparams['forum'] = $page->cm->instance;
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
    $courseview = $PAGE->url->compare(new moodle_url('/course/view.php', ['id' => $PAGE->course->id]));
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
            true
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
