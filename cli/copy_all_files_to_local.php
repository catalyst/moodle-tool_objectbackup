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
require_once($CFG->dirroot . '/admin/tool/objectbackup/locallib.php');

/**
 * Returns local filedir path for a content hash.
 *
 * @param string $contenthash
 * @return string
 */
function tool_objectbackup_local_path_from_hash(string $contenthash): string {
    global $CFG;

    return $CFG->dataroot . '/filedir/' . substr($contenthash, 0, 2) . '/' . substr($contenthash, 2, 2) . '/' . $contenthash;
}

$help = "Restore files from external object storage to local filedir using objectbackup configuration.

Options:
--limit=INT            Maximum number of content hashes to process. Defaults to all.
--checklocal=INT       Check local file exists before copying (1=yes, 0=no). Default 1.
--help, -h             Print out this help.

Example:
\$ sudo -u www-data /usr/bin/php admin/tool/objectbackup/cli/copy_all_files_to_local.php --limit=5000
";

list($options, $unrecognized) = cli_get_params(
    [
        'help' => false,
        'limit' => 0,
        'checklocal' => 1,
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

$checklocal = (int)$options['checklocal'];
if ($checklocal !== 0 && $checklocal !== 1) {
    cli_error('--checklocal must be either 0 or 1');
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

$encryptenabled = !empty($config->encrypt);
$encryptionkey = null;
if ($encryptenabled) {
    $encryptionkey = tool_objectbackup_get_encryption_key();
}

$sql = 'SELECT contenthash, MAX(filesize) AS filesize
          FROM {files}
      GROUP BY contenthash';
$records = $DB->get_recordset_sql($sql, [], 0, $limit);

$total = 0;
$copied = 0;
$decrypted = 0;
$decryptfailures = 0;
$filesizemismatches = 0;
$alreadylocal = 0;
$missingexternal = 0;
$errors = 0;

foreach ($records as $record) {
    $total++;
    $contenthash = $record->contenthash;
    $filesize = (int)$record->filesize;
    $localpath = tool_objectbackup_local_path_from_hash($contenthash);

    if ($checklocal && is_readable($localpath)) {
        $alreadylocal++;
        continue;
    }

    try {
        $initiallocation = $filesystem->get_object_location_from_hash($contenthash);
        $finallocation = $filesystem->copy_object_from_external_to_local_by_hash($contenthash, $filesize);

        if ($initiallocation === OBJECT_LOCATION_EXTERNAL && $finallocation === OBJECT_LOCATION_DUPLICATED) {
            $copied++;

            if ($encryptenabled) {
                $localpath = tool_objectbackup_local_path_from_hash($contenthash);
                if (!is_readable($localpath)) {
                    throw new \moodle_exception('Local file is not readable after restore: ' . $contenthash);
                }

                $tempdecrypted = make_request_directory() . '/' . $contenthash . '.decrypted';

                try {
                    \ParagonIE\Halite\File::decrypt($localpath, $tempdecrypted, $encryptionkey);

                    if (!@rename($tempdecrypted, $localpath)) {
                        // Fallback for cross-filesystem edge cases.
                        if (!@copy($tempdecrypted, $localpath)) {
                            throw new \moodle_exception('Failed replacing encrypted local file with decrypted content.');
                        }
                        @unlink($tempdecrypted);
                    }
                    $decrypted++;
                } catch (\Throwable $e) {
                    $decryptfailures++;
                    @unlink($tempdecrypted);
                    throw new \moodle_exception('Decryption failed for ' . $contenthash . ': ' . $e->getMessage());
                }
            }

            if ($filesize > 0) {
                $localpath = tool_objectbackup_local_path_from_hash($contenthash);
                clearstatcache(true, $localpath);
                $actualsize = @filesize($localpath);

                if ($actualsize === false || (int)$actualsize !== $filesize) {
                    $filesizemismatches++;
                    throw new \moodle_exception(
                        'Filesize mismatch for ' . $contenthash .
                        ' (expected ' . $filesize . ', got ' . (($actualsize === false) ? 'unknown' : (int)$actualsize) . ')'
                    );
                }
            }
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
if ($encryptenabled) {
    mtrace('Decrypted after download: ' . $decrypted);
    mtrace('Decryption failures: ' . $decryptfailures);
}
mtrace('Filesize mismatches: ' . $filesizemismatches);
mtrace('Already local/duplicated: ' . $alreadylocal);
mtrace('Missing or unreadable in external storage: ' . $missingexternal);
mtrace('Errors: ' . $errors);
