<?php
/**
 * List of jQuery plugins moodletxt uses in its interface,
 * for loading via Moodle's plugin manager
 * 
 * @author Pablo Pagnone
 * @package local_xray
 */

$plugins = array(
    'local_xray-dataTables' => array(
        'files' => array(
            'dataTables/jquery.dataTables.min.js', 
        	'dataTables/jquery.dataTables.min.css'
        )
     ),    
    'local_xray-fancybox2' => array(
        'files' => array(
            'fancybox2/jquery.fancybox.js', 
            'fancybox2/jquery.fancybox.css'
        )
     ),    
	'local_xray_activityreport_students_activity' => array(
			'files' => array(
					'reports/activityreport_students_activity.js'
			)
	),
    'local_xray_discussionreport_participation_metrics' => array(
            'files' => array(
                    'reports/discussionreport_participation_metrics.js'
            )
    ),
    'local_xray_discussionreport_discussion_activity_by_week' => array(
            'files' => array(
                    'reports/discussionreport_discussion_activity_by_week.js'
            )
    ),
	'local_xray_activityreport_first_login_non_starters' => array(
			'files' => array(
					'reports/activityreport_first_login_non_starters.js'
			)
	),		
	'local_xray_risk_risk_measures' => array(
			'files' => array(
					'reports/risk_risk_measures.js'
			)
	),
    'local_xray-show_on_lightbox' => array(
        'files' => array(
            'reports/show_on_lightbox.js'
        )
    )
);