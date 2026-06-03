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
 * Task that updates the time files were last seen in db.
 *
 * @package   tool_objectbackup
 * @author    Dan Marsden
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_objectbackup\task;

/**
 * Class to update the time files were last seen.
 */
class update_deleted extends \core\task\scheduled_task {
    /**
     * Get task name
     * @return string
     * @throws coding_exception
     */
    public function get_name() {
        return get_string('updatedeleted', 'tool_objectbackup');
    }

    /**
     * Execute task
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function execute() {
        global $DB;

        $config = \tool_objectbackup\local\manager::get_objectfs_config();
        if (empty($config->filesystem)) {
            mtrace("objectbackup not configured");
            return;
        }

        $fs = new $config->filesystem();

        // Find all files that we have backed up that are no longer listed in files table.
        $sql = 'SELECT o.*
                  FROM {tool_objectbackup} o
             LEFT JOIN {files} f ON o.contenthash = f.contenthash
                 WHERE f.id IS NULL AND o.deleted IS NULL';

        $objects = $DB->get_recordset_sql($sql);
        $count = 0;
        $time = time();
        foreach ($objects as $object) {
            // Add a timestamp to this record to let us know approx when the file was deleted.
            $object->deleted = $time;
            $DB->update_record('tool_objectbackup', $object);
            $count++;

        }
        mtrace("Found $count files that have been recently deleted");

        // Now find all files that are already backed up and have recently been added to Moodle again.
        $sql = 'SELECT o.*
                  FROM {tool_objectbackup} o
                  JOIN {files} f ON o.contenthash = f.contenthash
                 WHERE o.deleted IS NOT NULL';

        $objects = $DB->get_recordset_sql($sql);
        $count = 0;
        foreach ($objects as $object) {
            $object->deleted = null;
            $DB->update_record('tool_objectbackup', $object);
            $count++;
        }
        mtrace("Found $count files that have been recently added back to Moodle and are already backed up");

        // Delete objects from external storage once they pass the configured deletion delay.
        $externaldeletiondelay = (int)$config->externaldeletiondelay;
        if ($externaldeletiondelay <= 0) {
            mtrace('External deletion delay is disabled; skipping external deletion pass');
            return;
        }

        $cutoff = time() - $externaldeletiondelay;
        $sql = 'SELECT o.*
                  FROM {tool_objectbackup} o
                 WHERE o.deleted IS NOT NULL
                   AND o.deleted < :cutoff';

        $objects = $DB->get_recordset_sql($sql, ['cutoff' => $cutoff]);
        $deletedcount = 0;
        $failedcount = 0;
        foreach ($objects as $object) {
            try {
                $fs->delete_external_file_from_hash($object->contenthash, true);
                $DB->delete_records('tool_objectbackup', ['id' => $object->id]);
                $deletedcount++;
            } catch (\Throwable $e) {
                $failedcount++;
                mtrace('Failed deleting external file for contenthash ' . $object->contenthash . ': ' . $e->getMessage());
            }
        }
        $objects->close();

        mtrace("Deleted $deletedcount files from external storage after deletion delay");
        if ($failedcount > 0) {
            mtrace("Failed deleting $failedcount files from external storage");
        }
    }
}
