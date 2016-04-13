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
 * Upgrade file
 *
 * @package   local_xray
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') or die();

/**
 * @param int $oldversion
 * @return bool
 * @throws downgrade_exception
 * @throws upgrade_exception
 */
function xmldb_local_xray_upgrade($oldversion = 0) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2015070324) {
        // Delete old table if present.
        $oldtable = new xmldb_table('local_xray_uctmp');
        if ($dbman->table_exists($oldtable)) {
            $dbman->drop_table($oldtable);
        }

        upgrade_plugin_savepoint(true, 2015070324, 'local', 'xray');
    }

    if ($oldversion < 2015070327) {
        $task = \core\task\manager::get_scheduled_task('local_xray\task\data_sync');
        if (!empty($task)) {
            $minute = $task->get_minute();
            $hour = $task->get_hour();
            if (($hour == '*/12') and ($minute == '*')) {
                $task->set_minute('0');
                $task->set_customised(false);
                try {
                    \core\task\manager::configure_scheduled_task($task);
                } catch (Exception $e) {
                    // This should not happen but we will let it pass.
                    ($task);
                }
            }
        }

        upgrade_plugin_savepoint(true, 2015070327, 'local', 'xray');
    }

    if ($oldversion < 2015070328) {
        // Deprecated settings to be deleted.
        $deprecated = array('risk1', 'risk2', 'visitreg1', 'visitreg2', 'partreg1', 'partreg2', 'partc1', 'partc2');

        $params = array('plugin' => 'local_xray');
        $select = 'plugin = :plugin AND (';
        $count = 0;
        foreach ($deprecated as $name) {
            if (get_config('local_xray', $name)) {
                $params[$name] = $name;
                if ($count > 0) {
                    $select .= ' OR ';
                }
                $select .= 'name = :'.$name;
                $count++;
            }
        }

        if ($count) {
            $select .= ')';
            $DB->delete_records_select('config_plugins', $select, $params);
        }

        upgrade_plugin_savepoint(true, 2015070328, 'local', 'xray');
    }

    if ($oldversion < 2015070329) {
        $xmlfile = core_component::get_component_directory('local_xray').'/db/install.xml';

        // The reason this is done table by table is to avoid any issues with any future changes,
        // that may occur in the install.xml.
        $dbman->install_one_table_from_xmldb_file($xmlfile, 'local_xray_course'   );
        $dbman->install_one_table_from_xmldb_file($xmlfile, 'local_xray_coursecat');
        $dbman->install_one_table_from_xmldb_file($xmlfile, 'local_xray_hsudisc'  );
        $dbman->install_one_table_from_xmldb_file($xmlfile, 'local_xray_hsupost'  );
        $dbman->install_one_table_from_xmldb_file($xmlfile, 'local_xray_disc'     );
        $dbman->install_one_table_from_xmldb_file($xmlfile, 'local_xray_post'     );
        $dbman->install_one_table_from_xmldb_file($xmlfile, 'local_xray_cm'       );
        $dbman->install_one_table_from_xmldb_file($xmlfile, 'local_xray_roleunas' );

        upgrade_plugin_savepoint(true, 2015070329, 'local', 'xray');
    }

    return true;
}
