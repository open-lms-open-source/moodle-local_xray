<?php
/**
 * View renderer
 * @author Pablo Pagnone
 * @author German Vitale
 * @package local_xray
 */
require_once('../../config.php');
require($CFG->dirroot.'/local/mr/bootstrap.php');
require($CFG->dirroot.'/local/xray/lib/ehandler.php');

if (!PHPUNIT_TEST or PHPUNIT_UTIL) {
    set_exception_handler('local_xray_default_exception_handler');
}

mr_controller::render('local/xray', 'pluginname', 'local_xray');