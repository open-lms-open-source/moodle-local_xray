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
    'local_xray-reports_list' => array(
        'files' => array(
            'reports/reports_list.js'
        )
    ),
	'local_xray-students_activity' => array(
			'files' => array(
					'reports/activityreport_students_activity.js'
			)
	),
	'local_xray-first_login_non_starters' => array(
			'files' => array(
					'reports/activityreport_first_login_non_starters.js'
			)
	),		
    'local_xray-show_on_lightbox' => array(
        'files' => array(
            'reports/show_on_lightbox.js'
        )
    ),
    'local_xray-jssor' => array(
        'files' => array(
            'jssor/jssor.js',
            'jssor/jssor.slider.js',
            'jssor/jssor.slider.min.js',
        )
    ),
    'local_xray-image_gallery_with_vertical_thumbnail' => array(
        'files' => array(
            'image_gallery_with_vertical_thumbnail.js',
            'image_gallery_with_vertical_thumbnail.css',
        )
    )
);