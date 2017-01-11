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
 * Local xray capability definitions
 *
 * @package   local_xray
 * @author    Pablo Pagnone
 * @author    German Vitale
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') or die();

$capabilities = array(
    'local/xray:view' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
                'teacher' => CAP_ALLOW,
                'editingteacher' => CAP_ALLOW,
                'manager' => CAP_ALLOW
        )
    ),
    // View report activity.
    'local/xray:activityreport_view' => array(
            'captype' => 'read',
            'contextlevel' => CONTEXT_COURSE,
            'archetypes' => array(
                'teacher' => CAP_ALLOW,
                'editingteacher' => CAP_ALLOW,
                'manager' => CAP_ALLOW
            )
    ),
    // View report activity of course by day.
    'local/xray:discussionreport_view' => array(
            'captype' => 'read',
            'contextlevel' => CONTEXT_COURSE,
            'archetypes' => array(
                    'teacher' => CAP_ALLOW,
                    'editingteacher' => CAP_ALLOW,
                    'manager' => CAP_ALLOW
            )
    ),
    // View report activity individual.
    'local/xray:activityreportindividual_view' => array(
            'captype' => 'read',
            'contextlevel' => CONTEXT_COURSE,
            'archetypes' => array(
                    'teacher' => CAP_ALLOW,
                    'editingteacher' => CAP_ALLOW,
                    'manager' => CAP_ALLOW
            )
    ),
    // View report discussion individual.
    'local/xray:discussionreportindividual_view' => array(
            'captype' => 'read',
            'contextlevel' => CONTEXT_COURSE,
            'archetypes' => array(
                    'teacher' => CAP_ALLOW,
                    'editingteacher' => CAP_ALLOW,
                    'manager' => CAP_ALLOW
            )
    ),
    // View report discussion individual forum.
    'local/xray:discussionreportindividualforum_view' => array(
            'captype' => 'read',
            'contextlevel' => CONTEXT_MODULE,
            'archetypes' => array(
                    'teacher' => CAP_ALLOW,
                    'editingteacher' => CAP_ALLOW,
                    'manager' => CAP_ALLOW
            )
    ),
    // View report discussion endogenic plagiarism.
    'local/xray:discussionendogenicplagiarism_view' => array(
            'captype' => 'read',
            'contextlevel' => CONTEXT_COURSE,
            'archetypes' => array(
                    'teacher' => CAP_ALLOW,
                    'editingteacher' => CAP_ALLOW,
                    'manager' => CAP_ALLOW
            )
    ),
    // View report risk.
    'local/xray:risk_view' => array(
            'captype' => 'read',
            'contextlevel' => CONTEXT_COURSE,
            'archetypes' => array(
                    'teacher' => CAP_ALLOW,
                    'editingteacher' => CAP_ALLOW,
                    'manager' => CAP_ALLOW
            )
    ),
    // View report discussiongrading.
    'local/xray:discussiongrading_view' => array (
            'captype' => 'read',
            'contextlevel' => CONTEXT_COURSE,
            'archetypes' => array (
                    'teacher' => CAP_ALLOW,
                    'editingteacher' => CAP_ALLOW,
                    'manager' => CAP_ALLOW
            )
    ),
    // View Gradebook report.
    'local/xray:gradebookreport_view' => array(
            'captype' => 'read',
            'contextlevel' => CONTEXT_COURSE,
            'archetypes' => array(
                'teacher' => CAP_ALLOW,
                'editingteacher' => CAP_ALLOW,
                'manager' => CAP_ALLOW
            )
    ),
    // View Dashboard report.
    'local/xray:dashboard_view' => array(
            'captype' => 'read',
            'contextlevel' => CONTEXT_COURSE,
            'archetypes' => array(
                'teacher' => CAP_ALLOW,
                'editingteacher' => CAP_ALLOW,
                'manager' => CAP_ALLOW
            )
    ),
    // View System reports.
    'local/xray:systemreports_view' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),
    // View Course Subscription page.
    'local/xray:subscription_view' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),
    // View Global Subscription page.
    'local/xray:globalsub_view' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),
    // View Recommendations for Teacher.
    'local/xray:teacherrecommendations_view' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW
        )
    ),

    // View Recommendations for Admin.
    'local/xray:adminrecommendations_view' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),

    // View Recommendations for Admin.
    'local/xray:courseselection_view' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),
);
