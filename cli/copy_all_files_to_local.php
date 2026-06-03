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
 * Restore all externally stored files to local filedir.
 *
 * @package   tool_objectbackup
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require_once(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir . '/clilib.php');

$help = "Restore files from external object storage to local filedir using objectbackup configuration.

Options:
--limit=INT            Maximum number of content hashes to process. Defaults to all.
--help, -h             Print out this help.

Example:
\$ sudo -u www-data /usr/bin/php admin/tool/objectbackup/cli/copy_all_files_to_local.php --limit=5000
";

list($options, $unrecognized) = cli_get_params(
    [
        'help' => false,
        'limit' => 0,
    ],
    [
        'h' => 'help',
    ]
);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    echo $help;
    exit(0);
}

$limit = (int)$options['limit'];
if ($limit < 0) {
    cli_error('--limit must be greater than or equal to 0');
}

$config = \tool_objectbackup\local\manager::get_objectfs_config();
if (empty($config->filesystem)) {
    cli_error('tool_objectbackup is not configured: filesystem setting is empty.');
}

$filesystemclass = $config->filesystem;
if (!class_exists($filesystemclass)) {
    cli_error('Configured filesystem class does not exist: ' . $filesystemclass);
}

$filesystem = new $filesystemclass();
if (!method_exists($filesystem, 'copy_object_from_external_to_local_by_hash')) {
    cli_error('Configured filesystem does not support copy_object_from_external_to_local_by_hash.');
}

mtrace('Starting restore from external storage to local filedir.');

$sql = 'SELECT distinct contenthash
          FROM {files}';
$records = $DB->get_recordset_sql($sql, [], 0, $limit);

$total = 0;
$copied = 0;
$alreadylocal = 0;
$missingexternal = 0;
$errors = 0;

foreach ($records as $record) {
    $total++;
    $contenthash = $record->contenthash;
    $filesize = (int)$record->filesize;

    try {
        $initiallocation = $filesystem->get_object_location_from_hash($contenthash);
        $finallocation = $filesystem->copy_object_from_external_to_local_by_hash($contenthash, $filesize);

        if ($initiallocation === OBJECT_LOCATION_EXTERNAL && $finallocation === OBJECT_LOCATION_DUPLICATED) {
            $copied++;
        } else if ($initiallocation === OBJECT_LOCATION_LOCAL || $initiallocation === OBJECT_LOCATION_DUPLICATED) {
            $alreadylocal++;
        } else if ($initiallocation === OBJECT_LOCATION_ERROR || $finallocation === OBJECT_LOCATION_ERROR) {
            $missingexternal++;
        }
    } catch (\Throwable $e) {
        $errors++;
        mtrace('Error processing ' . $contenthash . ': ' . $e->getMessage());
    }

    if ($total % 1000 === 0) {
        mtrace('Processed ' . $total . ' content hashes...');
    }
}
$records->close();

mtrace('Restore complete.');
mtrace('Total processed: ' . $total);
mtrace('Copied from external to local: ' . $copied);
mtrace('Already local/duplicated: ' . $alreadylocal);
mtrace('Missing or unreadable in external storage: ' . $missingexternal);
mtrace('Errors: ' . $errors);
