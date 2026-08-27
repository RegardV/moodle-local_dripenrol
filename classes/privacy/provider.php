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
 * Privacy provider for local_dripenrol.
 *
 * The plugin stores no personal data of its own. It reacts to course-completion
 * events and creates enrolments through core's manual enrolment API, so the
 * resulting data is owned and described by core_enrol.
 *
 * @package    local_dripenrol
 * @copyright  2026 InkyPyrus
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dripenrol\privacy;

/**
 * Null privacy provider — the plugin stores no personal data.
 */
class provider implements \core_privacy\local\metadata\null_provider {
    /**
     * Reason why this plugin stores no personal data.
     *
     * @return string
     */
    public static function get_reason(): string {
        return 'privacy:metadata';
    }
}
