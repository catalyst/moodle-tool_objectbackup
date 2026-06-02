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
 * S3 client.
 *
 * @package   tool_objectbackup
 * @author    Dan Marsden
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_objectbackup\local\store\s3;

/**
 * Custom client class for objectbackup.
 */
class client extends \tool_objectfs\local\store\s3\client {

    /**
     * S3 settings form.
     *
     * @param  \admin_settingpage  $settings
     * @param  object              $config
     *
     * @return \admin_settingpage
     * @throws \coding_exception
     */
    public function define_client_section($settings, $config) {
        global $OUTPUT;
        $plugins = \core_component::get_plugin_list('local');

        $settings->add(new \admin_setting_heading('tool_objectbackup/aws',
            new \lang_string('settings:aws:header', 'tool_objectbackup'), $this->define_client_check()));

        $settings->add(new \admin_setting_configcheckbox('tool_objectbackup/s3_usesdkcreds',
            new \lang_string('settings:aws:usesdkcreds', 'tool_objectbackup'),
            '',''));

        if (empty($config->s3_usesdkcreds)) {
            $settings->add(new \admin_setting_configtext('tool_objectbackup/s3_key',
                new \lang_string('settings:aws:key', 'tool_objectfs'),
                new \lang_string('settings:aws:key_help', 'tool_objectfs'), ''));

            $settings->add(new \admin_setting_configpasswordunmask('tool_objectbackup/s3_secret',
                new \lang_string('settings:aws:secret', 'tool_objectfs'),
                new \lang_string('settings:aws:secret_help', 'tool_objectfs'), ''));
        }

        $settings->add(new \admin_setting_configtext('tool_objectbackup/s3_bucket',
            new \lang_string('settings:aws:bucket', 'tool_objectfs'),
            new \lang_string('settings:aws:bucket_help', 'tool_objectfs'), ''));

        $settings->add(new \admin_setting_configtext('tool_objectbackup/s3_region',
            new \lang_string('settings:aws:region', 'tool_objectfs'),
            new \lang_string('settings:aws:region_help', 'tool_objectfs'), ''));

        $settings->add(new \admin_setting_configtext('tool_objectbackup/s3_base_url',
            new \lang_string('settings:aws:base_url', 'tool_objectfs'),
            new \lang_string('settings:aws:base_url_help', 'tool_objectfs'), ''));

        $settings->add(new \admin_setting_configtext('tool_objectbackup/key_prefix',
            new \lang_string('settings:aws:key_prefix', 'tool_objectfs'),
            new \lang_string('settings:aws:key_prefix_help', 'tool_objectfs'), ''));

        return $settings;
    }
}
