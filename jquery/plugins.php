<?php
/**
 * List of jQuery plugins moodletxt uses in its interface,
 * for loading via Moodle's plugin manager
 * 
 * @author Pablo Pagnone
 * @package local_xray
 */

$plugins = array(
    'local_xray-show_on_lightbox' => array(
        'files' => array(
        		'fancybox2/jquery.fancybox.js',
        		'fancybox2/jquery.fancybox.css',
                'reports/show_on_lightbox.js'
        )
    ),
    'local_xray-show_on_table' => array(
        'files' => array('dataTables/js/jquery.dataTables.min.js',
        		         'dataTables/css/jquery.dataTables.min.css',
        		         'dataTables-jqueryui-1.10.7/dataTables.jqueryui.css',
        		         'dataTables-jqueryui-1.10.7/dataTables.jqueryui.js',
                         'reports/show_on_table.js'
        )
    )
);