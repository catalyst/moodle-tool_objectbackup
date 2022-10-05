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
 * Plugin strings are defined here.
 *
 * @package     tool_objectbackup
 * @category    string
 * @copyright   2022 Catalyst IT
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Objectfs backups';
$string['pluginsettings'] = 'Plugin settings';
$string['pushobjectstask'] = 'Push objects to external storage';
$string['updatelastseen'] = 'Update last time the file was seen in files table';
$string['settings:encrypt'] = 'Encrypt files';
$string['settings:encrypt_help'] = 'If enabled, the files sent to the external storage will be encrypted first - preventing anyone with access to the storage container from being able to view the files without the encryption key (stored in the database)';
