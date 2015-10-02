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
 * Xray view renderer
 * @author Pablo Pagnone
 * @author German Vitale
 * @package local_xray
 */

require_once('../../config.php');
/* @var object $CFG */
require($CFG->dirroot.'/local/mr/bootstrap.php');
require($CFG->dirroot.'/local/xray/lib/ehandler.php');

if (!PHPUNIT_TEST or PHPUNIT_UTIL) {
    set_exception_handler('local_xray_default_exception_handler');
}

mr_controller::render('local/xray', 'pluginname', 'local_xray');