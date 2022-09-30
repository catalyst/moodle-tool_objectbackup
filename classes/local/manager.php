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
 * manager class.
 *
 * @package   tool_objectfs
 * @author    Gleimer Mora <gleimermora@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_objectbackup\local;

use stdClass;
class manager extends \tool_objectfs\local\manager {

    /**
     * @return stdClass
     * @throws \dml_exception
     */
    public static function get_config() {
        global $CFG;
        // TODO: Clean up vars not used.
        $config = new stdClass;
        $config->enabletasks = 0;
        $config->enablelogging = 0;
        $config->sizethreshold = 1024 * 10;
        $config->minimumage = 7 * DAYSECS;
        $config->deletelocal = 0;
        $config->consistencydelay = 10 * MINSECS;
        $config->maxtaskruntime = MINSECS;
        $config->logging = 0;
        $config->preferexternal = 0;
        $config->batchsize = 10000;
        $config->useproxy = 0;

        $config->filesystem = '';
        $config->enablepresignedurls = 0;
        $config->expirationtime = 2 * HOURSECS;
        $config->presignedminfilesize = 0;
        $config->proxyrangerequests = 0;

        // S3 file system.
        $config->s3_usesdkcreds = 0;
        $config->s3_key = '';
        $config->s3_secret = '';
        $config->s3_bucket = '';
        $config->s3_region = 'us-east-1';
        $config->s3_base_url = '';
        $config->key_prefix = '';

        // Digital ocean file system.
        $config->do_key = '';
        $config->do_secret = '';
        $config->do_space = '';
        $config->do_region = 'sfo2';

        // Azure file system.
        $config->azure_accountname = '';
        $config->azure_container = '';
        $config->azure_sastoken = '';

        // Swift(OpenStack) file system.
        $config->openstack_authurl = '';
        $config->openstack_region = '';
        $config->openstack_container = '';
        $config->openstack_username = '';
        $config->openstack_password = '';
        $config->openstack_tenantname = '';
        $config->openstack_projectid = '';

        // Cloudfront CDN with Signed URLS - canned policy.
        $config->cloudfrontresourcedomain = '';
        $config->cloudfrontkeypairid = '';

        // SigningMethod - determine whether S3 or Cloudfront etc should be used.
        $config->signingmethod = '';  // This will be the default if not otherwise set. Values ('s3' | 'cf').

        $storedconfig = get_config('tool_objectbackup');

        // Override defaults if set.
        foreach ($storedconfig as $key => $value) {
            $config->$key = $value;
        }
        return $config;
    }

    /**
     * @param $config
     * @return bool
     */
    public static function get_client($config) {
        $clientclass = self::get_client_classname_from_fs($config->filesystem);

        if (class_exists($clientclass)) {
            return new $clientclass($config);
        }

        return false;
    }

    /**
     * Returns the list of installed and available filesystems.
     *
     * @return array
     * @throws \coding_exception
     */
    public static function get_available_fs_list() {
        $result[''] = get_string('pleaseselect', OBJECTFS_PLUGIN_NAME);

        // $filesystems['\tool_objectbackup\local\store\azure\file_system'] = 'azure_file_system';
        // $filesystems['\tool_objectbackup\local\store\digitalocean\file_system'] = 'digitalocean_file_system';
        $filesystems['\tool_objectbackup\local\store\s3\file_system'] = '\tool_objectbackup\local\store\s3\file_system';
        $filesystems['\tool_objectbackup\local\store\swift\file_system'] = '\tool_objectbackup\local\store\swift\file_system';

        foreach ($filesystems as $filesystem) {
            $clientclass = self::get_client_classname_from_fs($filesystem);
            $client = new $clientclass(null);

            if ($client && $client->get_availability()) {
                $result[$filesystem] = $filesystem;
            }
        }
        return $result;
    }

    /**
     * Returns client classname for given filesystem.
     *
     * @param string $filesystem File system
     * @return string
     */
    public static function get_client_classname_from_fs($filesystem) {
        return str_replace('file_system', 'client', $filesystem);
    }
}
