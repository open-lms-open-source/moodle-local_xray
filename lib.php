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
 * @param moodle_page $page
 * @param context $context
 * @return array
 * @throws coding_exception
 */
function local_xray_navigationlinks(moodle_page $page, context $context) {
    // Small caching to prevent double calculation call since we need the same information in both calls.
    static $reports = null;
    if ($reports !== null) {
        return $reports;
    }

    if (!is_callable('mr_on') or mr_on('xray', 'local')) {
        if (($context->contextlevel > CONTEXT_SYSTEM) and has_capability('local/xray:view', $context)) {
            $baseurl = new moodle_url('/local/xray/view.php', array('courseid' => $page->course->id));
            $reportlist = array();
            $reports = array();

            if (stripos($page->pagetype, 'course-view') === 0) {

                $reportlist = array(
                    'activityreport' => 'local/xray:activityreport_view',
                    'discussionreport' => 'local/xray:discussionreport_view',
                    'discussiongrading' => 'local/xray:discussiongrading_view',
                    'discussionendogenicplagiarism' => 'local/xray:discussionendogenicplagiarism_view',
                    'risk' => 'local/xray:risk_view',
                    'gradebookreport' => 'local/xray:gradebookreport_view',
                );

            } else {
                if (in_array($page->pagetype, array('mod-quiz-view', 'mod-forum-view'))) {
                    $baseurl->param('cmid', $context->instanceid);
                    $baseurl->param('forum', $page->cm->instance);
                    $reportlist = array(
                        'discussionreportindividualforum' => 'local/xray:discussionreportindividualforum_view',
                    );

                }
            }

            if (!empty($reportlist)) {
                foreach ($reportlist as $report => $capability) {
                    if (has_capability($capability, $context)) {
                        $reports[$report] = $baseurl->out(true, array('controller' => $report));
                    }
                }
            }

        }
    }

    return $reports;
}

/**
 * Extend navigations block.
 * @param settings_navigation $settings
 * @param context $context
 * @throws coding_exception
 */
function local_xray_extends_settings_navigation(settings_navigation $settings, context $context) {
    global $PAGE;

    $reports = local_xray_navigationlinks($PAGE, $context);
    if (empty($reports)) {
        return;
    }

    $plugin   = 'local_xray';
    $nodename = 'modulesettings';

    // Reports to show in course-view.
    if (stripos($PAGE->pagetype,'course-view') === 0) {
        //Show nav x-ray in courseadmin node.
        $nodename = 'courseadmin';
    }

    $coursenode = $settings->get($nodename);
    $extranavigation = $coursenode->add(get_string('navigation_xray', $plugin));

    foreach ($reports as $reportstring => $url) {
        $extranavigation->add(get_string($reportstring, $plugin), $url);
    }
}

/**
 * This is the version of the JS that should be used up to Moodle 2.8
 * New one will be required for Moodle 2.9+
 *
 * @param global_navigation $nav
 * @return void
 */
function local_xray_extends_navigation(global_navigation $nav) {
    ($nav); // Just to remove unused param warning.

    // TODO: This is a placeholder code for adding custom links on the Moodle page. For now disabled.
    /*
    global $PAGE;
    $reports = local_xray_navigationlinks($PAGE, $PAGE->context);
    if (empty($reports)) {
        return;
    }

    $items = array('itema', 'itemb', 'itemc');
    $menu = \html_writer::alist($items);

    if (stripos($PAGE->pagetype,'course-view') === 0) {
        $menu = \html_writer::alist();

        // Easy way to force include on every page (provided that navigation block is present).
        $PAGE->requires->yui_module(array('moodle-local_xray-custmenu'),
            'M.local_xray.custmenu.init',
            array(array('items' => $menu)),
            null,
            true
        );

    }
    */
}
