# Database Structure Guide

The database has been reorganized into clear logical groups:

1. **User Accounts** — `admin_accounts`, `staff_accounts`, `patient_accounts`
2. **Patient Profile and History** — `patient_profiles`, `patient_info_history`, `patient_update_notifications`
3. **Appointments and Clinical Information** — `appointments`, `appointment_status_notifications`
4. **Station Configuration** — `station_service_assignments`, `station_open_hours`
5. **Events and Audit Logs** — `upcoming_events`, `activity_log`

## Naming Convention

- Tables use plural `snake_case` names.
- Every auto-increment table uses `id` as its primary key.
- Reference fields use a descriptive `<entity>_id` name where the project supports numeric references.
- Boolean fields start with `is_`.
- Time fields consistently use `created_at` and `updated_at`.
- Variables and table names are centralized in `shared/database.php` through `DB_TABLE_*` constants where the runtime creates tables.

## Important Files

- `health_delivery_system.sql` — clean, organized schema for fresh imports.
- `database_legacy_dump.sql` — original database export, preserved for reference and data recovery.
- `shared/database.php` — runtime connection, table bootstrap, and database helper functions.

## Notes

The project still preserves existing column names where they are used by the current PHP code. This avoids breaking the website while improving readability and organization.
