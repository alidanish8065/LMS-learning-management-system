-- ============================================================
-- LMS - Drop Unused Tables Script
-- ============================================================
-- These tables exist in the schema but are NOT referenced by
-- any PHP file in the current project build.
--
-- They fall into two categories:
--   1. Audit / Log tables   – designed for enterprise auditing,
--                             not yet wired up in application code.
--   2. Future Feature tables – designed for planned features
--                             (e.g., exam proctoring, bulk ops,
--                              fine-grained permissions) that have
--                             not been implemented yet.
--
-- ⚠  WARNING: Run this ONLY on a development/testing database.
--              Take a full backup before running on production.
--              Import lms.sql first, then run this script.
--
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- -------------------------------------------------------
-- CATEGORY 1: Audit & Log Tables (not yet implemented)
-- -------------------------------------------------------

DROP TABLE IF EXISTS `academic_calendar`;
DROP TABLE IF EXISTS `admission_status_log`;
DROP TABLE IF EXISTS `attendance_change_log`;
DROP TABLE IF EXISTS `bulk_operation_log`;
DROP TABLE IF EXISTS `content_change_log`;
DROP TABLE IF EXISTS `database_change_log`;
DROP TABLE IF EXISTS `data_export_log`;
DROP TABLE IF EXISTS `enrollment_log`;
DROP TABLE IF EXISTS `exam_integrity_log`;
DROP TABLE IF EXISTS `file_access_log`;
DROP TABLE IF EXISTS `grade_change_log`;
DROP TABLE IF EXISTS `notification_delivery_log`;
DROP TABLE IF EXISTS `payment_transaction_log`;
DROP TABLE IF EXISTS `role_permission_log`;
DROP TABLE IF EXISTS `security_incident_log`;
DROP TABLE IF EXISTS `student_status_log`;
DROP TABLE IF EXISTS `system_config_log`;
DROP TABLE IF EXISTS `log_retention_policy`;  -- seed data only, never queried

-- -------------------------------------------------------
-- CATEGORY 2: Future Feature Tables (not yet built)
-- -------------------------------------------------------

DROP TABLE IF EXISTS `admission_document`;         -- document uploads per admission
DROP TABLE IF EXISTS `assessment_weight`;          -- grading weight configuration
DROP TABLE IF EXISTS `course_section`;             -- sections within an offering
DROP TABLE IF EXISTS `enrollment_withdrawal`;      -- formal withdrawal workflow
DROP TABLE IF EXISTS `exam_resource`;              -- file attachments for exams
DROP TABLE IF EXISTS `exam_section`;               -- multi-section exam structure
DROP TABLE IF EXISTS `exam_section_mark`;          -- marks per exam section
DROP TABLE IF EXISTS `fee_structure`;              -- per-program fee configuration
DROP TABLE IF EXISTS `forum_post_resource`;        -- file attachments in forum posts
DROP TABLE IF EXISTS `grade_component`;            -- grade breakdown components
DROP TABLE IF EXISTS `notification_resource`;      -- file attachments in notifications
DROP TABLE IF EXISTS `permission`;                 -- fine-grained permissions (RBAC)
DROP TABLE IF EXISTS `roles_permissions`;          -- role ↔ permission mapping
DROP TABLE IF EXISTS `role_change_requests`;       -- role change request workflow
DROP TABLE IF EXISTS `room_availability`;          -- room scheduling slots
DROP TABLE IF EXISTS `submission_resource`;        -- submission file linking (pivot)
DROP TABLE IF EXISTS `teacher_program`;            -- teacher ↔ program mapping

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- Done. The following tables REMAIN (actively used):
--
--   admission, assignment, assignment_resource,
--   assignment_submission, attendance_record, attendance_session,
--   campus, certificate, course, course_evaluation,
--   course_offering, course_prerequisite, course_teacher,
--   department, enrollment, exam, exam_attempt, faculty,
--   forum, forum_post, forum_thread, grade_appeal, invoice,
--   lesson, lesson_resource, module, notification,
--   notification_queue, payment, program, program_course,
--   resource, roles, room, student, student_number_sequence,
--   student_program_change_requests, teacher,
--   teacher_availability, timetable, users, user_activity_log,
--   user_code_sequence, user_notification, user_roles,
--   waiting_list
-- ============================================================
