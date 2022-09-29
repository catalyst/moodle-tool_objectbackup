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
 * Task that pushes files to S3.
 *
 * @package   tool_objectbackup
 * @author    Dan Marsden
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_objectbackup\task;


class push_objects_to_storage extends \core\task\scheduled_task {
    /**
     * Get task name
     * @return string
     * @throws coding_exception
     */
    public function get_name() {
        return get_string('pushobjectstask', 'tool_objectbackup');
    }

    /**
     * Execute task
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function execute() {
        global $DB;

        $config = \tool_objectbackup\local\manager::get_config();
        //$fs = new $config->filesystem(); // TODO: tidy up class access and allow selecting different file system classes.
        $fs = new \tool_objectbackup\local\store\swift\file_system;
        $maxfiles = 100; // TODO: Make this a setting.
        $now = time();
        $sql = "SELECT *
                  FROM {files} f
                  LEFT JOIN {tool_objectbackup} b on b.contenthash = f.contenthash
                  WHERE b.id is null";
        $filerecords = $DB->get_recordset_sql($sql, [], 0, $maxfiles);
        $filestoadd = [];
        foreach ($filerecords as $file) {
            $fs->copy_from_local_to_external($file->contenthash);
            // Upload this file to external storage.
            $filestoadd[] = ['contenthash' => $file->contenthash, 'lastseen' => $now];

        }
        $filerecords->close();
        $DB->insert_records('tool_objectbackup', $filestoadd);
        mtrace(count($filestoadd). " files uploaded to external storage");
    }
}
