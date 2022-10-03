<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Locallib functions.
 *
 * @package     tool_objectbackup
 * @copyright   2022 Catalyst IT
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/.extlib/halite/autoload.php');

use ParagonIE\Halite\KeyFactory;
use ParagonIE\HiddenString\HiddenString;
/**
 * Get encryptionkey, sets a new one if we don't have one already.
 *
 * @return HiddenString
 */
function tool_objectbackup_get_encryption_key() {
    $key = get_config('tool_objectbackup', 'key');
    if (empty($key)) {
        $key = KeyFactory::generateEncryptionKey();
        set_config('key', $key, 'tool_objectbackup');
    }
    return new HiddenString($key);
}
