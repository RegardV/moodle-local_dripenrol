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
 * Event observer for local_dripenrol.
 *
 * @package    local_dripenrol
 * @copyright  2026 InkyPyrus
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dripenrol;

/**
 * Drip enrolment: when a student completes a "trigger" course, enrol them into the
 * configured "target" course via the target's manual enrolment method. Idempotent.
 * The per-course manual enrolment welcome message (set on the instance's customtext1)
 * is what notifies the student — so each target course's welcome should explain the unlock.
 *
 * @package local_dripenrol
 */
class observer {
    /**
     * Parse the configured course chain.
     *
     * @return array map of triggercourseid => targetcourseid, from the 'chain' setting
     */
    public static function chain(): array {
        $raw = (string) get_config('local_dripenrol', 'chain');
        $map = [];
        foreach (preg_split('/[\r\n]+/', $raw) as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '=') === false) {
                continue;
            }
            [$from, $to] = array_map('trim', explode('=', $line, 2));
            if (ctype_digit($from) && ctype_digit($to)) {
                $map[(int) $from] = (int) $to;
            }
        }
        return $map;
    }

    /**
     * Enrol the user into the next course when they complete the current one.
     *
     * @param \core\event\course_completed $event the completion event
     * @return void
     */
    public static function course_completed(\core\event\course_completed $event): void {
        global $DB, $CFG;
        require_once($CFG->libdir . '/enrollib.php');

        if (!get_config('local_dripenrol', 'enabled')) {
            return;
        }
        $map = self::chain();
        $source = (int) $event->courseid;
        if (empty($map[$source])) {
            return;
        }
        $target = (int) $map[$source];
        $userid = (int) $event->relateduserid;
        if (!$userid || !$DB->record_exists('course', ['id' => $target])) {
            return;
        }

        $context = \context_course::instance($target);
        if (is_enrolled($context, $userid)) {
            return; // Already enrolled - nothing to do (idempotent on re-aggregation).
        }

        // Enrol via the target course's enabled manual enrolment method.
        $instance = $DB->get_record(
            'enrol',
            ['courseid' => $target, 'enrol' => 'manual', 'status' => ENROL_INSTANCE_ENABLED]
        );
        if (!$instance) {
            return; // No manual enrolment method available on the target.
        }
        $roleid = (int) get_config('local_dripenrol', 'roleid');
        if (!$roleid) {
            $roleid = 5; // Student.
        }

        // enrol_user() sends the per-course branded welcome itself (synchronously) when the manual
        // instance has a send option (customint1) + message (customtext1) — which we've configured
        // per target course to explain the unlock. No explicit send needed (it would double up).
        $plugin = enrol_get_plugin('manual');
        $plugin->enrol_user($instance, $userid, $roleid);
    }
}
