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
 * Plugin administration pages are defined here.
 *
 * @package     tool_objectbackup
 * @category    admin
 * @copyright   2022 Catalyst IT
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot. '/admin/tool/objectfs/classes/local/manager.php');
require_once($CFG->dirroot. '/admin/tool/objectfs/lib.php');

global $PAGE;

if (!$hassiteconfig) {
    return;
}

$ADMIN->add('tools', new admin_category('tool_objectbackup', get_string('pluginname', 'tool_objectbackup')));

$settings = new admin_settingpage('tool_objectbackup_settings', get_string('pluginsettings', 'tool_objectbackup'));
$ADMIN->add('tool_objectbackup', $settings);


if ($ADMIN->fulltree) {

    $config = \tool_objectbackup\local\manager::get_objectfs_config();

    $settings->add(new admin_setting_heading('tool_objectbackup/storagefilesystemselection',
        new lang_string('settings:clientselection:header', 'tool_objectfs'), ''));

    $settings->add(new admin_setting_configselect('tool_objectbackup/filesystem',
        new lang_string('settings:clientselection:title', 'tool_objectfs'),
        new lang_string('settings:clientselection:title_help', 'tool_objectfs'), '',
        \tool_objectbackup\local\manager::get_available_fs_list()));

    $settings->add(new admin_setting_configcheckbox('tool_objectbackup/encrypt',
        new lang_string('settings:encrypt', 'tool_objectbackup'),
        new lang_string('settings:encrypt_help', 'tool_objectbackup'), 0));

    $client = \tool_objectbackup\local\manager::get_client($config);
    if ($client && $client->get_availability()) {
        $settings = $client->define_client_section($settings, $config);
    }
}
