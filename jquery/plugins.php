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
 * List of jQuery plugins moodletxt uses in its interface,
 * for loading via Moodle's plugin manager
 *
 * @author Pablo Pagnone
 * @package local_xray
 */

$plugins = array(
    'local_xray-show_on_table' => array(
        'files' => array('dataTables/js/jquery.dataTables.min.js',
            'dataTables/css/jquery.dataTables.min.css',
            'dataTables-jqueryui-1.10.7/dataTables.jqueryui.css',
            'dataTables-jqueryui-1.10.7/dataTables.jqueryui.js',
            'reports/show_on_table.js'
        )
    ),
    'local_xray-systemreports' => array(
        'files' => array(
            'iframe-resizer-master/js/iframeResizer.min.js',
            'reports/systemreports.js'
        )
    ),
    'local_xray-recommendations' => array(
        'files' => array(
            'reports/recommendations.js'
        )
    )
);
