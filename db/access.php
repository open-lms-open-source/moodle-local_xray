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
            'student' => CAP_ALLOW
        )
    ),
    // View report activity of course by day.
	'local/xray:activity_report_view' => array(
			'captype' => 'read',
			'contextlevel' => CONTEXT_COURSE,
			'archetypes' => array(
	            'teacher' => CAP_ALLOW,
	            'editingteacher' => CAP_ALLOW,
	            'manager' => CAP_ALLOW
			)
	),
    // View report activity of course by day.
    'local/xray:discussion_report_view' => array(
            'captype' => 'read',
            'contextlevel' => CONTEXT_COURSE,
            'archetypes' => array(
                    'teacher' => CAP_ALLOW,
                    'editingteacher' => CAP_ALLOW,
                    'manager' => CAP_ALLOW
            )
    )
		
);
