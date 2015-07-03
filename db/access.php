<?php
defined('MOODLE_INTERNAL') or die();
/**
 * Local xray capability definitions
 *
 * @author Pablo Pagnone
 * @author German Vitale
 * @package local/xray
 */
 
$capabilities = array(
    'local/xray:view' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'student' => CAP_ALLOW
        )
    )
);