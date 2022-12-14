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
 * tool_objectbackup tasks
 *
 * @package   tool_objectbackup
 * @author    Dan Marsden
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$tasks = [
    ['classname' => 'tool_objectbackup\task\push_objects_to_storage',
     'blocking'  => 0,
     'minute'    => 'R',
     'hour'      => '*',
     'day'       => '*',
     'dayofweek' => '*',
     'month'     => '*'],
     ['classname' => 'tool_objectbackup\task\stats',
     'blocking'  => 0,
     'minute'    => 'R',
     'hour'      => '*',
     'day'       => '*',
     'dayofweek' => '*',
     'month'     => '*'],
     ['classname' => 'tool_objectbackup\task\update_deleted',
      'blocking'  => 0,
      'minute'    => 'R',
      'hour'      => 'R',
      'day'       => 'R',
      'dayofweek' => '*',
      'month'     => '*'],
    ];
