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
	 'local_xray-list_reports' => array(
        'files' => array(
            'list_reports.js'
        )
     ),    
	 'local_xray-show_on_lightbox' => array(
        'files' => array(
            'show_on_lightbox.js'
        )
     )
);