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

use tool_objectfs\local\store\object_file_system;

require_once($CFG->dirroot . '/admin/tool/objectfs/lib.php');

class file_system extends \tool_objectfs\local\store\swift\file_system {

    public function __construct() {
        global $CFG;
        parent::__construct(); // Setup filedir.

        $config = \tool_objectbackup\local\manager::get_config(); // Use objectbackup settings.

        $this->externalclient = $this->initialise_external_client($config);
        $this->externalclient->register_stream_wrapper();
    }
}
