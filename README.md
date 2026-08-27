# Drip enrolment (local_dripenrol)

Automatically enrols a student into the next course in a sequence when they complete
the current one — turning a set of standalone courses into a gated learning path
without a paid course-sequencing plugin.


## How it works

The plugin observes `\core\event\course_completed`. When a student completes a course
listed as a *trigger*, it enrols them into the mapped *target* course using that
course's own manual enrolment method.

Because it enrols through core's manual enrolment API, the target course's configured
welcome message is what notifies the student. Set that message per course to explain
what has just unlocked — the plugin deliberately sends nothing itself, so there is no
duplicate email.

## Settings

*Site administration → Plugins → Local plugins → Drip enrolment*

| Setting | Meaning |
|---|---|
| `enabled` | Master switch. Defaults to on. |
| `chain` | One mapping per line, `triggercourseid=targetcourseid`. Example: `2=3` enrols into course 3 when course 2 is completed. |
| `roleid` | Role assigned on auto-enrolment. Defaults to `5` (Student). |

Malformed lines in `chain` are ignored rather than failing the enrolment, so a typo
disables one link in the path instead of breaking the observer.

## Behaviour worth knowing

- **Idempotent.** Already-enrolled users are skipped, so completion re-aggregation
  cannot produce duplicate enrolments.
- **Silent when it cannot act.** If the target course does not exist, or has no enabled
  manual enrolment method, the event is ignored — it does not raise an exception into
  the completion cron.
- **Stores no personal data.** Enrolments it creates belong to core, and are covered by
  core's own privacy provider.

## Requirements

Moodle 4.4 or later (`requires = 2024041600`). Tested by CI against 4.5, 5.1 and 5.2.

## Installation

Copy into `public/local/dripenrol/` (Moodle 5.x) or `local/dripenrol/` (Moodle 4.x)
and visit *Site administration → Notifications* to complete the install.

## Licence

GPL v3 or later — see [LICENSE](LICENSE).
