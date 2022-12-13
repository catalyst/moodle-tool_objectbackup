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
 * File system for Openstack Object Storage
 *
 * @package    tool_objectbackup
 * @author     Dan Marsden
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_objectbackup\local\store\swift;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/admin/tool/objectfs/lib.php');

/**
 * Custom file_system class to load objectbackup settings.
 */
class file_system extends \tool_objectfs\local\store\swift\file_system {

    /**
     * Override to pull in objectbackup settings.
     */
    public function __construct() {
        parent::__construct(); // Setup filedir.

        $config = \tool_objectbackup\local\manager::get_objectfs_config(); // Use objectbackup settings.

        $this->externalclient = $this->initialise_external_client($config);
        $this->externalclient->register_stream_wrapper();
        // Set correct context in stream_wrapper.
        \tool_objectfs\local\store\swift\stream_wrapper::set_default_context($this->externalclient->get_seekable_stream_context());
    }

    /**
     * Allows the file to be encrypted and passed to external storage.
     *
     * @param [type] $contenthash
     * @param bool $encrypt
     * @return void
     */
    public function copy_and_encrypt_from_local_to_external($contenthash, $encrypt) {
        // First simple check - is this file stored locally.
        $localpath = $this->get_local_path_from_hash($contenthash);
        if (!file_exists($localpath)) {
            // Try using Moodle's main file storage - might be an externally stored object.
            $fs = get_file_storage(); // Main core file_storage api instead of the custom one.
            $filesystem = $fs->get_file_system();
            $localpath = $filesystem->get_local_path_from_hash($contenthash, true);
            if (!is_readable($localpath)) {
                return false;
            }
        }
        if ($encrypt) {
            $tempfile = make_request_directory() . '/' . $contenthash;
            // Create encrypted temp file and store.
            $encryptionkey = tool_objectbackup_get_encryption_key();

            \ParagonIE\Halite\File::encrypt($localpath, $tempfile, $encryptionkey);

            $externalpath = $this->get_external_path_from_hash($contenthash);
            $result = copy($tempfile, $externalpath);
            unlink($tempfile);
        } else {
            $externalpath = $this->get_external_path_from_hash($contenthash);
            $result = copy($localpath, $externalpath);
        }

        return $result;
    }
}
