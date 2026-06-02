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
 * Object backup status history page.
 *
 * @package   tool_objectbackup
 * @author    Dan Marsden
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/lib/adminlib.php');

admin_externalpage_setup('tool_objectbackup_object_status');

$baseurl = '/admin/tool/objectbackup/object_status.php';
$pageurl = new moodle_url($baseurl);
$heading = get_string('object_status:page', 'tool_objectbackup');
$PAGE->set_url($pageurl);
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('report');
$PAGE->set_title($heading);
$PAGE->set_heading($heading);

echo $OUTPUT->header();

$report = $DB->get_record('tool_objectbackup_stats', [], 'id DESC', IGNORE_MULTIPLE);
if (!$report) {
    echo $OUTPUT->heading(get_string('nothingtodisplay'));
    echo $OUTPUT->footer();
    exit;
}

echo $OUTPUT->heading(get_string('object_status:last_run', 'tool_objectbackup', userdate($report->timecreated)), 3);

$table = new html_table();
$table->head = [
    get_string('object_status:metric', 'tool_objectbackup'),
    get_string('object_status:value', 'tool_objectbackup'),
];
$table->data[] = [
    get_string('object_status:missingfromexternal', 'tool_objectbackup'),
    number_format((int)$report->missingfromexternal),
];
$table->data[] = [
    get_string('object_status:external', 'tool_objectbackup'),
    number_format((int)$report->external),
];
$table->data[] = [
    get_string('object_status:externalsize', 'tool_objectbackup'),
    display_size((int)$report->externalsize),
];
$table->data[] = [
    get_string('object_status:externalonly', 'tool_objectbackup'),
    number_format((int)$report->externalonly),
];
$table->data[] = [
    get_string('object_status:externalonlysize', 'tool_objectbackup'),
    display_size((int)$report->externalonlysize),
];

echo html_writer::table($table);

echo $OUTPUT->footer();
