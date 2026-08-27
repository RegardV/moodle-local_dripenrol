# Changelog

## 1.0 — 2026-08-27

First tagged release. Extracted from the AfrEcoSoil and Hermes Forge Moodle sites,
where it had been running untracked inside the Moodle tree since June 2026.

- Course-completion driven enrolment into a mapped target course.
- Configurable chain, role and master switch.
- Idempotent: will not double-enrol on completion re-aggregation.
- Added in extraction: GPL v3 licence, privacy provider (null — stores no personal
  data), phpunit coverage of chain parsing, enrolment, idempotency and the disabled
  and missing-target paths, and Moodle Plugin CI against 4.5 / 5.1 / 5.2.

No functional change to the code that was running in production.
