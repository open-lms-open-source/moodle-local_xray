<?php
defined('MOODLE_INTERNAL') or die();
/**
 * Local xray capability definitions
 *
 * @author Pablo Pagnone
 * @author German Vitale
 * @package local_xray
 */
 
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
    // View report activity
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
    // View report activity individual
    'local/xray:activityreportindividual_view' => array(
            'captype' => 'read',
            'contextlevel' => CONTEXT_COURSE,
            'archetypes' => array(
                    'student' => CAP_ALLOW,
                    'teacher' => CAP_ALLOW,
                    'editingteacher' => CAP_ALLOW,
                    'manager' => CAP_ALLOW
            )
    ),
    // View report discussion individual
    'local/xray:discussionreportindividual_view' => array(
            'captype' => 'read',
            'contextlevel' => CONTEXT_COURSE,
            'archetypes' => array(
                    'student' => CAP_ALLOW,
                    'teacher' => CAP_ALLOW,
                    'editingteacher' => CAP_ALLOW,
                    'manager' => CAP_ALLOW
            )
    ),
    // View report discussion individual forum
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
    // View Gradebook report 
    'local/xray:gradebookreport_view' => array(
            'captype' => 'read',
            'contextlevel' => CONTEXT_COURSE,
            'archetypes' => array(
                'teacher' => CAP_ALLOW,
                'editingteacher' => CAP_ALLOW,
                'manager' => CAP_ALLOW
            )
    )
);
