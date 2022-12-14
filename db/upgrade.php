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
 * Provides the {@see xmldb_tool_objectbackup_upgrade()} function.
 *
 * @package     tool_objectbackup
 * @category    upgrade
 * @copyright   2022 Catalyst IT
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Define upgrade steps to be performed to upgrade the plugin from the old version to the current one.
 *
 * @param int $oldversion Version number the plugin is being upgraded from.
 * @return bool always true
 */
function xmldb_tool_objectbackup_upgrade($oldversion) {
    global $DB;

    $result = true;

    if ($oldversion < 2022102000) {

        // Define table tool_objectbackup to be created.
        $table = new xmldb_table('tool_objectbackup');

        // Conditionally launch create table for tool_objectbackup.
        $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

        // Define field monitor to be added to tool_objectbackup.
        $filesizefield = new xmldb_field('filesize', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'deleted');
        $mimetypefield = new xmldb_field('mimetype', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'filesize');

        if (!$dbman->field_exists($table, $filesizefield)) {
            $dbman->add_field($table, $filesizefield);
        }

        if (!$dbman->field_exists($table, $mimetypefield)) {
            $dbman->add_field($table, $mimetypefield);
        }

        // Objectbackup savepoint reached.
        upgrade_plugin_savepoint(true, 2022102000, 'tool', 'objectbackup');
    }

    return $result;
}
