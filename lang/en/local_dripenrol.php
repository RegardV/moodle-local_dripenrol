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
 * English language strings for local_dripenrol.
 *
 * @package    local_dripenrol
 * @copyright  2026 InkyPyrus
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['chain'] = 'Course chain';
$string['chain_desc'] = 'One mapping per line as <code>triggercourseid=targetcourseid</code>. When the trigger course is completed, the student is enrolled into the target course via its manual enrolment method. Example: <code>2=3</code> enrols into course 3 when course 2 is completed.';
$string['enabled'] = 'Enabled';
$string['enabled_desc'] = 'Automatically enrol a student into the next course when they complete the previous one.';
$string['pluginname'] = 'Drip enrolment';
$string['privacy:metadata'] = 'The Drip enrolment plugin does not store any personal data; it acts on course-completion events to create enrolments.';
$string['roleid'] = 'Role id';
$string['roleid_desc'] = 'Role to assign on auto-enrolment (5 = Student).';
