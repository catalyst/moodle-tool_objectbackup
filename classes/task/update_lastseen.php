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
class update_lastseen extends \core\task\scheduled_task {
    /**
     * Get task name
     * @return string
     * @throws coding_exception
     */
    public function get_name() {
        return get_string('updatelastseen', 'tool_objectbackup');
    }

    /**
     * Execute task
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function execute() {
        global $DB, $CFG;
        // TODO: this is probably not mysql compatible - check performance, look at making it cross-db compatible.
        $sql = "UPDATE {tool_objectbackup}
                       SET lastseen = ?
                 WHERE contenthash in(SELECT contenthash FROM {files})";
        $DB->execute($sql, [time()]);
    }
}
