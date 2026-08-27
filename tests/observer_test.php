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
 * Unit tests for the local_dripenrol event observer.
 *
 * @package    local_dripenrol
 * @copyright  2026 InkyPyrus
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dripenrol;

/**
 * Unit tests for the local_dripenrol event observer.
 *
 * @covers \local_dripenrol\observer
 */
final class observer_test extends \advanced_testcase {
    /**
     * Build a course_completed event for a user finishing a course.
     *
     * @param int $courseid course that was completed
     * @param int $userid user who completed it
     * @return \core\event\course_completed
     */
    private function completion_event(int $courseid, int $userid): \core\event\course_completed {
        $completion = (object) [
            'id' => 1,
            'course' => $courseid,
            'userid' => $userid,
            'timeenrolled' => 0,
            'timestarted' => 0,
            'timecompleted' => time(),
            'reaggregate' => 0,
        ];
        return \core\event\course_completed::create_from_completion($completion);
    }

    /**
     * The chain setting is parsed into a trigger => target map.
     *
     * @return void
     */
    public function test_chain_parses_only_well_formed_numeric_pairs(): void {
        $this->resetAfterTest();
        set_config('chain', "2=3\n\n  4 = 5 \nnonsense\n6=\n=7\nx=y", 'local_dripenrol');

        $this->assertSame([2 => 3, 4 => 5], observer::chain());
    }

    /**
     * Completing the trigger course enrols the user into the target course.
     *
     * @return void
     */
    public function test_completing_trigger_course_enrols_into_target(): void {
        $this->resetAfterTest();
        $trigger = $this->getDataGenerator()->create_course();
        $target = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();

        set_config('enabled', 1, 'local_dripenrol');
        set_config('chain', "{$trigger->id}={$target->id}", 'local_dripenrol');
        set_config('roleid', 5, 'local_dripenrol');

        observer::course_completed($this->completion_event($trigger->id, $user->id));

        $this->assertTrue(is_enrolled(\context_course::instance($target->id), $user->id));
    }

    /**
     * Re-firing the event does not create a second enrolment.
     *
     * @return void
     */
    public function test_enrolment_is_idempotent(): void {
        global $DB;
        $this->resetAfterTest();
        $trigger = $this->getDataGenerator()->create_course();
        $target = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();

        set_config('enabled', 1, 'local_dripenrol');
        set_config('chain', "{$trigger->id}={$target->id}", 'local_dripenrol');

        $event = $this->completion_event($trigger->id, $user->id);
        observer::course_completed($event);
        observer::course_completed($event);

        $instance = $DB->get_record('enrol', ['courseid' => $target->id, 'enrol' => 'manual']);
        $this->assertEquals(1, $DB->count_records(
            'user_enrolments',
            ['enrolid' => $instance->id, 'userid' => $user->id]
        ));
    }

    /**
     * With the plugin disabled no enrolment is created.
     *
     * @return void
     */
    public function test_disabled_plugin_does_not_enrol(): void {
        $this->resetAfterTest();
        $trigger = $this->getDataGenerator()->create_course();
        $target = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();

        set_config('enabled', 0, 'local_dripenrol');
        set_config('chain', "{$trigger->id}={$target->id}", 'local_dripenrol');

        observer::course_completed($this->completion_event($trigger->id, $user->id));

        $this->assertFalse(is_enrolled(\context_course::instance($target->id), $user->id));
    }

    /**
     * A chain pointing at a course that no longer exists is ignored.
     *
     * @return void
     */
    public function test_missing_target_course_is_ignored(): void {
        $this->resetAfterTest();
        $trigger = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();

        set_config('enabled', 1, 'local_dripenrol');
        set_config('chain', "{$trigger->id}=99999", 'local_dripenrol');

        observer::course_completed($this->completion_event($trigger->id, $user->id));

        // The user gains no enrolment anywhere: the chain target simply does not exist.
        $this->assertEmpty(enrol_get_all_users_courses($user->id));
    }
}
