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
 * Openstack Swift client
 *
 * @package    tool_objectbackup
 * @copyright  2017 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_objectbackup\local\store\swift;

/**
 * * Custom client class for objectbackup.
 */
class client extends \tool_objectfs\local\store\swift\client {

    /**
     * swift settings form with the following elements:
     *
     * @param admin_settingpage $settings
     * @param  object $config
     * @return admin_settingpage
     */
    public function define_client_section($settings, $config) {

        $settings->add(new \admin_setting_heading('tool_objectbackup/openstack',
            new \lang_string('settings:openstack:header', 'tool_objectfs'), $this->define_client_check()));

        $settings->add(new \admin_setting_configtext('tool_objectbackup/openstack_authurl',
            new \lang_string('settings:openstack:authurl', 'tool_objectfs'),
            new \lang_string('settings:openstack:authurl_help', 'tool_objectfs'), ''));

        $settings->add(new \admin_setting_configtext('tool_objectbackup/openstack_region',
            new \lang_string('settings:openstack:region', 'tool_objectfs'),
            new \lang_string('settings:openstack:region_help', 'tool_objectfs'), ''));

        $settings->add(new \admin_setting_configtext('tool_objectbackup/openstack_container',
            new \lang_string('settings:openstack:container', 'tool_objectfs'),
            new \lang_string('settings:openstack:container_help', 'tool_objectfs'), ''));

        $settings->add(new \admin_setting_configtext('tool_objectbackup/openstack_username',
            new \lang_string('settings:openstack:username', 'tool_objectfs'),
            new \lang_string('settings:openstack:username_help', 'tool_objectfs'), ''));

        $settings->add(new \admin_setting_configpasswordunmask('tool_objectbackup/openstack_password',
            new \lang_string('settings:openstack:password', 'tool_objectfs'),
            new \lang_string('settings:openstack:password', 'tool_objectfs'), ''));

        $settings->add(new \admin_setting_configtext('tool_objectbackup/openstack_tenantname',
            new \lang_string('settings:openstack:tenantname', 'tool_objectfs'),
            new \lang_string('settings:openstack:tenantname_help', 'tool_objectfs'), ''));

        $settings->add(new \admin_setting_configtext('tool_objectbackup/openstack_projectid',
            new \lang_string('settings:openstack:projectid', 'tool_objectfs'),
            new \lang_string('settings:openstack:projectid_help', 'tool_objectfs'), ''));

        return $settings;
    }
}
