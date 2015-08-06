<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Serving web service images from XRay
 * To use call the script as follows: <moodle url>/local/xray/img.php?src=<imagefilename>
 * Imagefilename is from element web service call with prepend GUID
 * for example: 40ee0842-9fc6-11e4-920f-89fc399c3afadefecto4.png
 *
 * @package local_xray
 * @author Darko Miletic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright Moodlerooms
 */

define('NO_DEBUG_DISPLAY', true);

require_once('../../config.php');

require_login(null, false, null, true, true);

$filename = required_param('src', PARAM_FILE);

core\session\manager::write_close(); // Unlock session during file serving.

if (!local_xray\api\wsapi::login()) {
    return;
}

$baseurl = get_config('local_xray', 'xrayurl');
$domain  = get_config('local_xray', 'xrayclientid');
$url     = sprintf('%s/%s/%s', $baseurl, $domain, $filename);

header("Content-Disposition: inline; filename={$filename}");
header("Content-Type: image/png");
if (is_https()) { // HTTPS sites - watch out for IE! KB812935 and KB316431.
    header('Cache-Control: private, max-age=10, no-transform');
    header('Expires: '. gmdate('D, d M Y H:i:s', 0) .' GMT');
    header('Pragma: ');
} else { //normal http - prevent caching at all cost
    header('Cache-Control: private, must-revalidate, pre-check=0, post-check=0, max-age=0, no-transform');
    header('Expires: '. gmdate('D, d M Y H:i:s', 0) .' GMT');
    header('Pragma: no-cache');
}

$options = array(
    'http' => array(
        'method' => 'GET',
        'header' => array(
            'Cookie: '.local_xray\api\xrayws::instance()->getcookie()
        )
    )
);

$context = stream_context_create($options);
readfile($url, null, $context);
