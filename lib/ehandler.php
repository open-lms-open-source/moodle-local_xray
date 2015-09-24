<?php
defined('MOODLE_INTERNAL') or die();

/**
 * @param Exception $e
 * @throws coding_exception
 */
function local_xray_default_exception_handler(Exception $e) {
    $action = optional_param('action', 'view', PARAM_ALPHANUMEXT);
    if ($action and (stripos($action, 'json') === 0)) {
        global $PAGE;
        $output = new core_renderer_ajax($PAGE, RENDERER_TARGET_AJAX);
        echo $output->header(), json_encode(array('data' => '-'));
        return;
    }

    default_exception_handler($e);
}
