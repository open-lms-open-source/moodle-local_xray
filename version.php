<?php
/**
 * Local xray integration
 * @author Pablo Pagnone
 * @author German Vitale
 * @package local_xray
 */

$plugin->version  = 2015070304;
$plugin->requires = 2014111005;//moodle 2.8
$plugin->cron = 0;
$plugin->component = 'local_xray';
$plugin->maturity = MATURITY_STABLE;
$plugin->release = '1.0 (Build: 2012123101)';
$plugin->dependencies = array(
        'local_mr'       => ANY_VERSION
);