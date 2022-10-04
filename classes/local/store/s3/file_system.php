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
 * object_file_system abstract class.
 *
 * Remote object storage providers extent this class.
 * At minimum you need to implement get_remote_client.
 *
 * @package   tool_objectbackup
 * @author    Dan Marsden
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_objectbackup\local\store\s3;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/admin/tool/objectfs/lib.php');
require_once($CFG->dirroot . '/admin/tool/objectbackup/locallib.php');

/**
 * Custom file_system class to pull through objectbackup config settings.
 */
class file_system extends \tool_objectfs\local\store\s3\file_system {
    /**
     * Custom construct function to load objectbackup config instead of objectfs.
     */
    public function __construct() {
        global $CFG;
        parent::__construct(); // Setup filedir.

        $config = \tool_objectbackup\local\manager::get_config(); // Use objectbackup settings.

        $this->externalclient = $this->initialise_external_client($config);
        $this->externalclient->register_stream_wrapper();
    }
    /**
     * Allows the file to be encrypted and passed to external storage.
     *
     * @param [type] $contenthash
     * @return void
     */
    public function copy_and_encrypt_from_local_to_external($contenthash) {
        $localpath = $this->get_local_path_from_hash($contenthash);
        if (!is_readable($localpath)) {
            // Try using Moodle's main file storage - might be an externally stored object.
            $fs = get_file_storage(); // Main core file_storage api instead of the custom one.
            $filesystem = $fs->get_file_system();
            $localpath = $filesystem->get_local_path_from_hash($contenthash, true);
            if (!is_readable($localpath)) {
                return false;
            }
        }
        $tempfile = make_request_directory() . '/' . $contenthash;
        // Create encrypted temp file and store.
        $encryptionkey = tool_objectbackup_get_encryption_key();

        \ParagonIE\Halite\File::encrypt($localpath, $tempfile, $encryptionkey);

        try {
            $this->get_external_client()->upload_to_s3($tempfile, $contenthash);
            unlink($tempfile);
            return true;
        } catch (\Exception $e) {
            $this->get_logger()->error_log(
                'ERROR: copy ' . $tempfile . ' to ' . $this->get_external_path_from_hash($contenthash) . ': ' . $e->getMessage()
            );
            unlink($tempfile);
            return false;
        }
    }
}
