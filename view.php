<?php
/**
 * View renderer
 * @author Pablo Pagnone
 * @author German Vitale
 * @package local_xray
 */
require_once('../../config.php');
require($CFG->dirroot.'/local/mr/bootstrap.php');
mr_controller::render('local/xray', 'pluginname', 'local_xray');