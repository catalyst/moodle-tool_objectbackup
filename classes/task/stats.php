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
 * Task that generates stats on backup storage.
 *
 * @package   tool_objectbackup
 * @author    Dan Marsden
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_objectbackup\task;

use stdClass;

/**
 * Class to update the backup storage stats.
 */
class stats extends \core\task\scheduled_task {
    /**
     * Get task name
     * @return string
     * @throws coding_exception
     */
    public function get_name() {
        return get_string('statstask', 'tool_objectbackup');
    }

    /**
     * Execute task
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function execute() {
        global $DB;

        if (empty(get_config("tool_objectbackup", 'filesystem'))) {
            mtrace("objectbackup not configured");
            return;
        }
        $stat = new stdClass();
        // Get count of files missing from external backups.
        $sql = "SELECT count(f.id)
                  FROM {files} f
             LEFT JOIN {tool_objectbackup} b on b.contenthash = f.contenthash
                 WHERE b.id is null";
        $stat->missingfromexternal = $DB->get_field_sql($sql);

        // Get count of all backed up files.
        $stat->external = $DB->count_records('tool_objectbackup');

        // Get total size of all backup files.
        $sql = "SELECT sum(filesize) FROM {tool_objectbackup}";
        $stat->externalsize = $DB->get_field_sql($sql);

        // Get count of all files only in external backup.
        $sql = "SELECT count(id) FROM {tool_objectbackup} WHERE deleted IS NOT NULL";
        $stat->externalonly = $DB->get_field_sql($sql);

        // Get size of files only in external backup.
        $sql = "SELECT sum(filesize) FROM {tool_objectbackup} WHERE deleted IS NOT NULL";
        $stat->externalonlysize = $DB->get_field_sql($sql);

        $stat->timecreated = time();
        $DB->insert_record('tool_objectbackup_stats', $stat);
    }
}
