<?php
defined('MOODLE_INTERNAL') or die();

/**
 * @param Exception $e
 * @throws coding_exception
 */
function local_xray_default_exception_handler(Exception $e) {
    global $PAGE, $ME;
    $url = new moodle_url('/local/xray/view.php');
    $current = new moodle_url($ME);
    $validurl = ($url->compare($current, URL_MATCH_BASE));
    $courseid = optional_param('courseid', false, PARAM_INT);
    $controller = optional_param('controller', false, PARAM_ALPHA);
    $action = optional_param('action', 'view', PARAM_ALPHANUMEXT);
    if ($validurl and $courseid and $controller and $action and stripos($action, 'json') === 0) {
        $output = new core_renderer_ajax($PAGE, RENDERER_TARGET_AJAX);
        echo $output->header(), json_encode(array('data' => '-'));
        return;
    }

    default_exception_handler($e);
}