# Health Delivery System — Database Data Dictionary

This document provides the complete, authoritative Data Dictionary for the **Health Delivery System** (Bacolod City Barangay Health Station Delivery System), along with an evaluation and comparison of the initial conceptual draft.

---

## 1. Review & Analysis of Your Draft

### Why Your Draft Was Incomplete / Different from the Real Codebase:
1. **Consolidated Appointments vs. Split Tables:**
   - In your draft, you split appointments into `APPOINTMENT_LIST` (booking form) and `APPOINTMENT_RECORD` (status/medical notes).
   - In the actual implemented system, both are merged into a single comprehensive table: `appointments`. This table holds the patient appointment booking information, tracking code (`reference_code`, `appointment_code`), clinical triage measurements (temperature, pulse, respiration, BP, height, weight), doctor's notes, proof photo, and workflow status (`Pending`, `Confirmed`, `Serving`, `Completed`, `Cancelled`).
2. **Station Modeling:**
   - In your draft, you created `BARANGAY_HEALTH_STATION` with `admin_id`.
   - In the implemented codebase, health stations are modeled by `health_facilities` (facility profile, barangay jurisdiction, contact, opening hours) and `staff_accounts` (station health personnel assigned to each station via `station_slug`).
3. **Services Configuration:**
   - In your draft, `HEALTH_SERVICE` was a standalone entity with `service_id` and `admin_id`.
   - In the actual system, services are handled flexibly per station using `station_service_assignments` (which services each station provides), `station_service_schedules` (specific days/hours each service is offered), and `station_slot_limits` (daily booking limits).
4. **Patient / Client Architecture:**
   - The actual system distinguishes between `patient_accounts` (portal login credentials and authentication) and `patient_profiles` (master demographic records). In addition, specialized maternal and child health modules utilize `infant_profiles` and `immunized_infants`.
5. **Missing Operational Tables:**
   - The actual system contains 19 operational tables, including audit logs (`activity_log`, `patient_info_history`), notifications (`appointment_status_notifications`, `patient_update_notifications`), security (`password_reset_otps`), unattended queues (`unattended_appointments`, `unattended_queue`), and public events (`upcoming_events`).

---

## 2. Complete Data Dictionary (Actual Implemented System)

The following tables document every field in the system with the exact required columns: **Field Name**, **Data Type**, **Field Length**, **Constraint**, and **Description**.

---

### Table 1: `admin_accounts`
**Description:** Stores administrative user credentials and system access levels for City Health Office administrators.

| Field Name | Data Type | Field Length | Constraint | Description |
| :--- | :--- | :--- | :--- | :--- |
| `id` | INT UNSIGNED | 10 | PRIMARY KEY, AUTO_INCREMENT, NOT NULL | Unique numerical identifier for the administrator account |
| `admin_name` | VARCHAR | 150 | NOT NULL | Full legal name of the system administrator |
| `office_name` | VARCHAR | 255 | NOT NULL | Office or governing agency (e.g., Bacolod City Health Office) |
| `email` | VARCHAR | 150 | UNIQUE, NOT NULL | Official email address used for administrative login |
| `password_hash` | VARCHAR | 255 | NOT NULL | Secure Bcrypt hash of the administrator password |
| `last_active_at` | TIMESTAMP | — | NULL | Date and time when the administrator was last active |
| `is_logged_in` | TINYINT | 1 | NOT NULL, DEFAULT 0 | Active session status flag (1 = Active, 0 = Logged out) |
| `created_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Timestamp when the admin account was registered |
| `updated_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | Timestamp when the admin account record was last modified |

---

### Table 2: `staff_accounts`
**Description:** Stores credentials, personal profiles, and station assignments for barangay health station staff personnel.

| Field Name | Data Type | Field Length | Constraint | Description |
| :--- | :--- | :--- | :--- | :--- |
| `id` | INT UNSIGNED | 10 | PRIMARY KEY, AUTO_INCREMENT, NOT NULL | Unique numerical identifier for the health staff record |
| `station_slug` | VARCHAR | 100 | NOT NULL, INDEX | Unique slug code of the assigned barangay health station |
| `station_name` | VARCHAR | 255 | NOT NULL | Full name of the assigned health station facility |
| `staff_name` | VARCHAR | 150 | NOT NULL | Full name of the assigned health station personnel |
| `email` | VARCHAR | 150 | UNIQUE, NOT NULL | Official email address used for staff portal authentication |
| `password_hash` | VARCHAR | 255 | NOT NULL | Secure Bcrypt hash of the staff account password |
| `birth_date` | DATE | — | NULL | Date of birth of the staff member |
| `gender` | VARCHAR | 30 | NULL | Gender / biological sex of the staff member |
| `contact_number` | VARCHAR | 30 | NULL | Official contact phone/mobile number of the staff |
| `home_address` | VARCHAR | 255 | NULL | Residential home address of the staff member |
| `emergency_contact` | VARCHAR | 100 | NULL | Full name of designated emergency contact person |
| `emergency_phone` | VARCHAR | 30 | NULL | Contact telephone/mobile number for emergency contact |
| `last_active_at` | TIMESTAMP | — | NULL | Timestamp of staff member's most recent activity |
| `is_logged_in` | TINYINT | 1 | NOT NULL, DEFAULT 0 | Online session status flag (1 = Active, 0 = Offline) |
| `created_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Timestamp when the staff account was created |
| `updated_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | Timestamp when the staff account was last modified |

---

### Table 3: `patient_accounts`
**Description:** Stores login credentials, account security, and baseline registration data for resident patient portal accounts.

| Field Name | Data Type | Field Length | Constraint | Description |
| :--- | :--- | :--- | :--- | :--- |
| `id` | INT UNSIGNED | 10 | PRIMARY KEY, AUTO_INCREMENT, NOT NULL | Unique numerical identifier for the patient account |
| `patient_id` | VARCHAR | 32 | UNIQUE, NOT NULL | Alphanumeric unique system patient ID (e.g., 'V84QXW') |
| `email` | VARCHAR | 150 | UNIQUE, NOT NULL | Patient email address used for portal authentication |
| `password_hash` | VARCHAR | 255 | NOT NULL | Secure Bcrypt hash of the patient's portal password |
| `first_name` | VARCHAR | 100 | NOT NULL | Patient's given first name |
| `middle_name` | VARCHAR | 100 | NULL | Patient's middle name |
| `last_name` | VARCHAR | 100 | NOT NULL | Patient's last name / surname |
| `birth_date` | DATE | — | NOT NULL | Date of birth of the registered patient |
| `gender` | VARCHAR | 30 | NOT NULL | Gender / biological sex (e.g., 'Male', 'Female') |
| `contact_number` | VARCHAR | 20 | NOT NULL | Primary mobile number for appointment SMS alerts |
| `complete_address` | VARCHAR | 255 | NOT NULL | Residential address including Purok and Barangay |
| `station_slug` | VARCHAR | 100 | NULL | Slug of the patient's default/catchment health station |
| `station_name` | VARCHAR | 255 | NULL | Name of the patient's default/catchment health station |
| `created_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Timestamp when the patient registered their account |
| `updated_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | Timestamp when account information was last updated |

---

### Table 4: `patient_profiles`
**Description:** Master demographic repository and verification records for patients across all barangays.

| Field Name | Data Type | Field Length | Constraint | Description |
| :--- | :--- | :--- | :--- | :--- |
| `patient_id` | VARCHAR | 32 | PRIMARY KEY, NOT NULL | Unique alphanumeric patient identification code |
| `first_name` | VARCHAR | 100 | NOT NULL | Patient's given first name |
| `middle_name` | VARCHAR | 100 | NULL | Patient's middle name |
| `last_name` | VARCHAR | 100 | NOT NULL | Patient's last name / surname |
| `birth_date` | DATE | — | NOT NULL | Patient's date of birth |
| `gender` | VARCHAR | 30 | NOT NULL | Patient's biological sex |
| `contact_number` | VARCHAR | 20 | NOT NULL | Patient's primary telephone or mobile contact number |
| `email` | VARCHAR | 150 | NULL | Contact email address |
| `complete_address` | VARCHAR | 255 | NOT NULL | Full residential address details |
| `photo_path` | VARCHAR | 255 | NULL | File system path or image URL to patient's ID photo |
| `created_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Timestamp when the master profile was initially stored |
| `updated_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | Timestamp when the master profile was last modified |

---

### Table 5: `appointments`
**Description:** Central operational table storing all patient bookings, scheduled time slots, clinical vitals, triage findings, and consultation outcomes.

| Field Name | Data Type | Field Length | Constraint | Description |
| :--- | :--- | :--- | :--- | :--- |
| `id` | INT UNSIGNED | 10 | PRIMARY KEY, AUTO_INCREMENT, NOT NULL | Unique numerical identifier for the appointment transaction |
| `reference_code` | VARCHAR | 32 | UNIQUE, NOT NULL | Public tracking reference code (e.g., 'BK260520161625781') |
| `appointment_code` | VARCHAR | 10 | NULL | Short 8-10 character check-in/queue code (e.g., 'ELVMHSMZ') |
| `patient_id` | VARCHAR | 32 | NULL, INDEX | References master `patient_id` from patient profiles |
| `station_slug` | VARCHAR | 100 | NOT NULL, INDEX | Slug code of the barangay health station |
| `station_name` | VARCHAR | 255 | NOT NULL | Display name of the barangay health station |
| `service_slug` | VARCHAR | 100 | NOT NULL, INDEX | Slug identifier of requested healthcare program |
| `service_name` | VARCHAR | 255 | NOT NULL | Display title of requested healthcare service |
| `first_name` | VARCHAR | 100 | NOT NULL | Patient/applicant first name recorded at appointment time |
| `middle_name` | VARCHAR | 100 | NULL | Patient/applicant middle name |
| `last_name` | VARCHAR | 100 | NOT NULL | Patient/applicant last name |
| `birth_date` | DATE | — | NOT NULL | Patient/applicant date of birth |
| `gender` | VARCHAR | 30 | NOT NULL | Patient/applicant gender |
| `contact_number` | VARCHAR | 20 | NOT NULL | Contact telephone or mobile number |
| `email` | VARCHAR | 150 | NULL | Email address for digital notifications |
| `complete_address` | VARCHAR | 255 | NOT NULL | Residential address recorded at appointment booking time |
| `immunization_relationship` | VARCHAR | 100 | NULL | Guardian relationship (e.g., 'Mother', 'Father') if pediatric/immunization |
| `recipient_first_name` | VARCHAR | 100 | NULL | First name of child/dependent receiving vaccine or care |
| `recipient_middle_name` | VARCHAR | 100 | NULL | Middle name of child/dependent recipient |
| `recipient_last_name` | VARCHAR | 100 | NULL | Last name of child/dependent recipient |
| `recipient_birth_date` | DATE | — | NULL | Date of birth of child/dependent recipient |
| `recipient_gender` | VARCHAR | 30 | NULL | Biological sex of child/dependent recipient |
| `preferred_date` | DATE | — | NOT NULL, INDEX | Scheduled date requested for the health consultation |
| `preferred_time` | VARCHAR | 30 | NOT NULL | Scheduled time slot window (e.g., '8:00 AM - 11:30 AM') |
| `notes` | TEXT | 65535 | NULL | Patient chief complaint, symptoms, or visit remarks |
| `body_temperature` | VARCHAR | 30 | NULL | Clinical vital sign: Body temperature reading in °C |
| `pulse_rate` | VARCHAR | 30 | NULL | Clinical vital sign: Heart / pulse rate in beats per minute |
| `respiration_rate` | VARCHAR | 30 | NULL | Clinical vital sign: Respiratory rate in breaths per minute |
| `blood_pressure` | VARCHAR | 30 | NULL | Clinical vital sign: Blood pressure reading (e.g., '120/80') |
| `height` | VARCHAR | 50 | NULL | Physical measurement: Patient height (e.g., in cm) |
| `weight` | VARCHAR | 50 | NULL | Physical measurement: Patient weight (e.g., in kg) |
| `vaccine_type` | VARCHAR | 150 | NULL | Type of vaccine administered (for immunization appointments) |
| `doctor_notes` | TEXT | 65535 | NULL | Healthcare provider findings, diagnosis, and prescription details |
| `reminder_sms_sent` | TINYINT | 1 | NOT NULL, DEFAULT 0 | Flag indicating if SMS reminder was sent (1 = Sent, 0 = Pending) |
| `reminder_sent_at` | TIMESTAMP | — | NULL | Exact timestamp when SMS notification was dispatched |
| `photo_path` | VARCHAR | 255 | NULL | File system path to uploaded verification document or proof |
| `status` | VARCHAR | 30 | NOT NULL, DEFAULT 'Pending' | Workflow state ('Pending', 'Confirmed', 'Serving', 'Completed', 'Cancelled', 'Declined') |
| `created_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Timestamp when booking was submitted |
| `updated_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | Timestamp when booking or clinical record was last updated |

---

### Table 6: `appointment_status_notifications`
**Description:** Real-time in-app notification queue informing patients of appointment confirmation, completion, or rescheduling.

| Field Name | Data Type | Field Length | Constraint | Description |
| :--- | :--- | :--- | :--- | :--- |
| `id` | INT UNSIGNED | 10 | PRIMARY KEY, AUTO_INCREMENT, NOT NULL | Unique numerical identifier for the notification entry |
| `appointment_id` | INT UNSIGNED | 10 | FOREIGN KEY, NOT NULL | References `appointments(id)` (ON DELETE CASCADE) |
| `reference_code` | VARCHAR | 32 | NOT NULL | Appointment reference tracking code |
| `patient_id` | VARCHAR | 32 | NOT NULL, INDEX | Patient system ID receiving the notification |
| `status` | VARCHAR | 30 | NOT NULL | Target appointment status triggering the notice |
| `message` | TEXT | 65535 | NOT NULL | Notification body message displayed in the patient portal |
| `is_read` | TINYINT | 1 | NOT NULL, DEFAULT 0 | Status flag indicating if patient opened notice (0 = Unread, 1 = Read) |
| `created_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Date and time when the notification was created |

---

### Table 7: `health_facilities`
**Description:** Directory of all barangay health stations and main health centers in the delivery network.

| Field Name | Data Type | Field Length | Constraint | Description |
| :--- | :--- | :--- | :--- | :--- |
| `id` | INT UNSIGNED | 10 | PRIMARY KEY, AUTO_INCREMENT, NOT NULL | Unique numerical identifier for the health facility |
| `slug` | VARCHAR | 100 | UNIQUE, NOT NULL | URL-safe station slug code (e.g., 'bata', 'alijis', 'city-health') |
| `name` | VARCHAR | 255 | NOT NULL | Formal facility name (e.g., 'Bata Barangay Health Station') |
| `barangay` | VARCHAR | 100 | NOT NULL, INDEX | Name of barangay jurisdiction served |
| `location` | VARCHAR | 255 | NOT NULL | Street address or exact geographic location of the station |
| `phone` | VARCHAR | 50 | NOT NULL | Official telephone or mobile contact hotline |
| `color` | VARCHAR | 30 | NOT NULL, DEFAULT 'mint' | UI accent theme color code for dashboard branding |
| `image` | VARCHAR | 255 | NULL | File system path or URL to station landmark image |
| `hours` | VARCHAR | 100 | NOT NULL, DEFAULT 'Monday - Saturday, 8:00 AM - 5:00 PM' | Standard operating days and hours description |
| `created_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Timestamp when the facility was registered |
| `updated_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | Timestamp when facility details were last updated |

---

### Table 8: `station_service_assignments`
**Description:** Configuration junction table mapping healthcare services and daily capacities to each barangay health station.

| Field Name | Data Type | Field Length | Constraint | Description |
| :--- | :--- | :--- | :--- | :--- |
| `station_slug` | VARCHAR | 100 | PRIMARY KEY (Composite), NOT NULL | Identifier slug of the barangay health station |
| `service_slug` | VARCHAR | 100 | PRIMARY KEY (Composite), NOT NULL | Identifier slug of the offered healthcare service |
| `sort_order` | INT UNSIGNED | 10 | NOT NULL, DEFAULT 0 | Numerical sorting priority for frontend dropdown menus |
| `daily_capacity` | INT UNSIGNED | 10 | NOT NULL, DEFAULT 200 | Maximum number of patients accommodated per day |
| `created_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Date and time when the service assignment was recorded |
| `updated_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | Date and time when the service assignment was modified |

---

### Table 9: `station_service_schedules`
**Description:** Stores weekly service operating schedules, opening days, and session labels for each station.

| Field Name | Data Type | Field Length | Constraint | Description |
| :--- | :--- | :--- | :--- | :--- |
| `id` | INT UNSIGNED | 10 | PRIMARY KEY, AUTO_INCREMENT, NOT NULL | Unique identifier for the schedule record |
| `station_slug` | VARCHAR | 100 | NOT NULL, INDEX | Slug identifier of the barangay health station |
| `service_slug` | VARCHAR | 100 | NOT NULL, INDEX | Slug identifier of the healthcare service |
| `days_json` | TEXT | 65535 | NOT NULL | JSON array of active weekdays (e.g., `["Mon","Wed","Fri"]`) |
| `schedule_label` | VARCHAR | 255 | NOT NULL | Descriptive schedule summary (e.g., 'Wednesdays 8:00 AM - 12:00 PM') |
| `created_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Timestamp when schedule configuration was created |
| `updated_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | Timestamp when schedule was last modified |

---

### Table 10: `station_slot_limits`
**Description:** Manages reservation limits and slot quotas per service per health station to prevent facility overcrowding.

| Field Name | Data Type | Field Length | Constraint | Description |
| :--- | :--- | :--- | :--- | :--- |
| `id` | INT UNSIGNED | 10 | PRIMARY KEY, AUTO_INCREMENT, NOT NULL | Unique identifier for the quota configuration record |
| `station_slug` | VARCHAR | 100 | NOT NULL, INDEX | Slug of the barangay health station |
| `service_slug` | VARCHAR | 100 | NOT NULL | Slug of the medical service |
| `max_slots` | INT UNSIGNED | 10 | NOT NULL, DEFAULT 200 | Maximum allowable confirmed booking slots per day |
| `created_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Timestamp when slot limit was initialized |
| `updated_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | Timestamp when slot limit was last updated |

---

### Table 11: `infant_profiles`
**Description:** Pediatric patient master records linking infants/children to registered parents or guardians.

| Field Name | Data Type | Field Length | Constraint | Description |
| :--- | :--- | :--- | :--- | :--- |
| `id` | INT UNSIGNED | 10 | PRIMARY KEY, AUTO_INCREMENT, NOT NULL | Unique numerical identifier for the infant profile |
| `patient_id` | VARCHAR | 32 | NOT NULL, INDEX | References parent/guardian's master `patient_id` |
| `first_name` | VARCHAR | 100 | NOT NULL | Given first name of the infant/child |
| `middle_name` | VARCHAR | 100 | NULL | Middle name of the infant/child |
| `last_name` | VARCHAR | 100 | NOT NULL | Last name of the infant/child |
| `birth_date` | DATE | — | NOT NULL, INDEX | Infant/child date of birth |
| `gender` | VARCHAR | 30 | NULL | Gender / biological sex of the infant |
| `relationship` | VARCHAR | 50 | NOT NULL, DEFAULT 'Child' | Guardian relationship type (e.g., 'Child', 'Ward', 'Grandchild') |
| `mother_name` | VARCHAR | 150 | NULL | Full legal name of the child's mother |
| `father_name` | VARCHAR | 150 | NULL | Full legal name of the child's father |
| `guardian_name` | VARCHAR | 150 | NULL | Legal guardian name if parents are unavailable |
| `custom_notes` | TEXT | 65535 | NULL | Pediatric health remarks, birth weight, or allergies |
| `created_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Date and time when the infant profile was created |
| `updated_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | Date and time when the profile was last modified |

---

### Table 12: `immunized_infants`
**Description:** Official logbook of vaccines administered to infants and young children at barangay health stations.

| Field Name | Data Type | Field Length | Constraint | Description |
| :--- | :--- | :--- | :--- | :--- |
| `id` | INT UNSIGNED | 10 | PRIMARY KEY, AUTO_INCREMENT, NOT NULL | Unique numerical identifier for the immunization log entry |
| `appointment_id` | INT UNSIGNED | 10 | NULL, INDEX | Linked appointment ID where vaccination occurred |
| `appointment_code` | VARCHAR | 20 | NULL, INDEX | Short alphanumeric appointment reference code |
| `patient_id` | VARCHAR | 32 | NULL, INDEX | References parent/guardian patient ID |
| `first_name` | VARCHAR | 100 | NOT NULL | Given first name of vaccinated infant/child |
| `middle_name` | VARCHAR | 100 | NULL | Middle name of vaccinated infant/child |
| `last_name` | VARCHAR | 100 | NOT NULL, INDEX | Last name of vaccinated infant/child |
| `birth_date` | DATE | — | NOT NULL | Date of birth of vaccinated child |
| `gender` | VARCHAR | 30 | NULL | Gender of the child |
| `relationship` | VARCHAR | 50 | NOT NULL, DEFAULT 'Child' | Relationship to guardian |
| `station_slug` | VARCHAR | 100 | NULL, INDEX | Slug code of the station administering the vaccine |
| `vaccine_type` | VARCHAR | 150 | NULL | Vaccine brand/dose administered (e.g., BCG, Pentavalent, OPV, IPV, Measles) |
| `created_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Timestamp when the vaccine dose was administered |
| `updated_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | Timestamp when the record was last modified |

---

### Table 13: `patient_info_history`
**Description:** Historical audit log tracking demographic changes (such as changes in address or mobile number) made by patients.

| Field Name | Data Type | Field Length | Constraint | Description |
| :--- | :--- | :--- | :--- | :--- |
| `id` | INT UNSIGNED | 10 | PRIMARY KEY, AUTO_INCREMENT, NOT NULL | Unique numerical identifier for the audit entry |
| `patient_id` | VARCHAR | 32 | NOT NULL, INDEX | References `patient_id` of the modified record |
| `field_name` | VARCHAR | 50 | NOT NULL | Column name changed (e.g., 'complete_address', 'contact_number') |
| `old_value` | TEXT | 65535 | NULL | Previous value prior to modification |
| `new_value` | TEXT | 65535 | NULL | Newly updated value submitted by the patient |
| `changed_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP, INDEX | Exact date and time when the change occurred |

---

### Table 14: `patient_update_notifications`
**Description:** Administrative notification queue alerting staff and admin whenever a resident updates their contact details or address.

| Field Name | Data Type | Field Length | Constraint | Description |
| :--- | :--- | :--- | :--- | :--- |
| `id` | INT UNSIGNED | 10 | PRIMARY KEY, AUTO_INCREMENT, NOT NULL | Unique numerical identifier for the notification |
| `patient_id` | VARCHAR | 32 | NOT NULL, INDEX | Patient system ID who submitted the change |
| `patient_name` | VARCHAR | 255 | NOT NULL | Full name of the patient for quick administrative preview |
| `field_updated` | VARCHAR | 50 | NOT NULL | Data field modified (e.g., 'Address', 'Contact Number') |
| `is_read` | TINYINT | 1 | NOT NULL, DEFAULT 0, INDEX | Read status flag (0 = Unread by staff, 1 = Acknowledged) |
| `created_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP, INDEX | Timestamp when notification alert was generated |

---

### Table 15: `upcoming_events`
**Description:** Public bulletins, wellness caravans, immunization drives, and station announcements displayed on public boards.

| Field Name | Data Type | Field Length | Constraint | Description |
| :--- | :--- | :--- | :--- | :--- |
| `id` | INT UNSIGNED | 10 | PRIMARY KEY, AUTO_INCREMENT, NOT NULL | Unique numerical identifier for the event entry |
| `station_slug` | VARCHAR | 100 | NOT NULL, INDEX | Station slug organizing the community event |
| `station_name` | VARCHAR | 255 | NOT NULL | Full name of the organizing health station |
| `title` | VARCHAR | 255 | NOT NULL | Event headline / title (e.g., 'Community Feeding Program') |
| `description` | TEXT | 65535 | NOT NULL | Comprehensive narrative details and event instructions |
| `target_month` | VARCHAR | 20 | NULL | Target month for periodic or recurring campaigns |
| `event_date` | DATE | — | NULL, INDEX | Specific calendar date of the event |
| `time_label` | VARCHAR | 100 | NOT NULL | Operating start time (e.g., '8:00 AM - 12:00 PM') |
| `end_time_label` | VARCHAR | 100 | NULL | Operating conclusion time |
| `icon` | VARCHAR | 50 | NOT NULL, DEFAULT 'calendar' | Icon style identifier for UI rendering (e.g., 'syringe', 'heart') |
| `accent` | VARCHAR | 50 | NOT NULL, DEFAULT 'mint' | Badge/card theme accent color (e.g., 'mint', 'blue', 'pink') |
| `status` | VARCHAR | 20 | NOT NULL, DEFAULT 'inactive' | Event publication state ('active', 'inactive', 'archived') |
| `created_by` | VARCHAR | 150 | NULL | Email or username of the staff who created the event |
| `created_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Timestamp when event announcement was published |
| `updated_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | Timestamp when event details were last modified |

---

### Table 16: `unattended_appointments`
**Description:** Archive of pending appointment applications that lapsed or were left unconfirmed by health station staff prior to the appointment date.

| Field Name | Data Type | Field Length | Constraint | Description |
| :--- | :--- | :--- | :--- | :--- |
| `id` | INT UNSIGNED | 10 | PRIMARY KEY, AUTO_INCREMENT, NOT NULL | Unique numerical identifier for the lapsed record |
| `appointment_id` | INT UNSIGNED | 10 | UNIQUE, NOT NULL | References original record in `appointments(id)` |
| `reference_code` | VARCHAR | 20 | NOT NULL | Appointment public tracking code |
| `appointment_code` | VARCHAR | 10 | NULL | Queue/check-in code |
| `patient_id` | VARCHAR | 32 | NULL, INDEX | Associated patient system code |
| `station_slug` | VARCHAR | 100 | NOT NULL, INDEX | Station slug where booking was originally routed |
| `station_name` | VARCHAR | 255 | NOT NULL | Health station display name |
| `service_slug` | VARCHAR | 100 | NOT NULL | Medical service program identifier |
| `service_name` | VARCHAR | 255 | NOT NULL | Medical service display title |
| `first_name` | VARCHAR | 100 | NOT NULL | Patient given first name |
| `middle_name` | VARCHAR | 100 | NULL | Patient middle name |
| `last_name` | VARCHAR | 100 | NOT NULL | Patient last name |
| `birth_date` | DATE | — | NOT NULL | Patient date of birth |
| `gender` | VARCHAR | 30 | NOT NULL | Patient biological sex |
| `contact_number` | VARCHAR | 20 | NOT NULL | Mobile contact number |
| `email` | VARCHAR | 150 | NULL | Patient email address |
| `complete_address` | VARCHAR | 255 | NOT NULL | Residential address |
| `preferred_date` | DATE | — | NOT NULL, INDEX | Date originally requested by patient |
| `preferred_time` | VARCHAR | 30 | NOT NULL | Time window requested |
| `notes` | TEXT | 65535 | NULL | Patient notes or symptoms submitted |
| `original_status` | VARCHAR | 30 | NOT NULL, DEFAULT 'Pending' | Status before transition to unattended |
| `reason_unattended` | VARCHAR | 255 | NOT NULL, DEFAULT 'Staff unconfirmed prior to date' | Audit reason why appointment remained unserved |
| `marked_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Timestamp when record was marked unattended |
| `created_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| `updated_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | Last modification timestamp |

---

### Table 17: `unattended_queue`
**Description:** Archive of confirmed appointments where the patient failed to show up at the health station on the scheduled day (no-shows).

| Field Name | Data Type | Field Length | Constraint | Description |
| :--- | :--- | :--- | :--- | :--- |
| `id` | INT UNSIGNED | 10 | PRIMARY KEY, AUTO_INCREMENT, NOT NULL | Unique numerical identifier for queue no-show record |
| `appointment_id` | INT UNSIGNED | 10 | UNIQUE, NOT NULL | References original record in `appointments(id)` |
| `reference_code` | VARCHAR | 20 | NOT NULL | Appointment public tracking code |
| `appointment_code` | VARCHAR | 10 | NULL | Queue/check-in code |
| `patient_id` | VARCHAR | 32 | NULL, INDEX | Associated patient system code |
| `station_slug` | VARCHAR | 100 | NOT NULL, INDEX | Station slug where patient was expected |
| `station_name` | VARCHAR | 255 | NOT NULL | Health station display name |
| `service_slug` | VARCHAR | 100 | NOT NULL | Medical service program identifier |
| `service_name` | VARCHAR | 255 | NOT NULL | Medical service display title |
| `first_name` | VARCHAR | 100 | NOT NULL | Patient given first name |
| `middle_name` | VARCHAR | 100 | NULL | Patient middle name |
| `last_name` | VARCHAR | 100 | NOT NULL | Patient last name |
| `birth_date` | DATE | — | NOT NULL | Patient date of birth |
| `gender` | VARCHAR | 30 | NOT NULL | Patient biological sex |
| `contact_number` | VARCHAR | 20 | NOT NULL | Mobile contact number |
| `email` | VARCHAR | 150 | NULL | Patient email address |
| `complete_address` | VARCHAR | 255 | NOT NULL | Complete residence address |
| `preferred_date` | DATE | — | NOT NULL, INDEX | Scheduled date when patient did not appear |
| `preferred_time` | VARCHAR | 30 | NOT NULL | Scheduled time slot window |
| `photo_path` | VARCHAR | 255 | NULL | Verification photo path |
| `notes` | TEXT | 65535 | NULL | Appointment notes |
| `original_status` | VARCHAR | 30 | NOT NULL, DEFAULT 'Confirmed' | Status when placed in queue |
| `reason_unattended` | VARCHAR | 255 | NOT NULL, DEFAULT 'Patient did not show up / Left unserved in queue' | Audit reason why consultation was missed |
| `marked_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Timestamp when marked as no-show |
| `created_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| `updated_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | Last modification timestamp |

---

### Table 18: `password_reset_otps`
**Description:** Stores temporary one-time password (OTP) verification PINs for password resets across admin, staff, and patient portals.

| Field Name | Data Type | Field Length | Constraint | Description |
| :--- | :--- | :--- | :--- | :--- |
| `id` | INT UNSIGNED | 10 | PRIMARY KEY, AUTO_INCREMENT, NOT NULL | Unique numerical identifier for the OTP entry |
| `role` | VARCHAR | 30 | NOT NULL, INDEX | Target user role ('admin', 'staff', 'patient') |
| `email` | VARCHAR | 150 | NOT NULL, INDEX | Email address requesting password recovery |
| `otp_code` | VARCHAR | 10 | NOT NULL | 6-digit cryptographic numeric PIN sent to user |
| `expires_at` | DATETIME | — | NOT NULL, INDEX | Expiration date and time of the PIN (15-minute window) |
| `attempts` | INT UNSIGNED | 10 | NOT NULL, DEFAULT 0 | Counter tracking incorrect attempts (security rate-limiting) |
| `is_used` | TINYINT | 1 | NOT NULL, DEFAULT 0 | Consumption status (1 = Code redeemed, 0 = Active/Unused) |
| `created_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Timestamp when OTP was generated |
| `updated_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | Timestamp when OTP attempt or status changed |

---

### Table 19: `activity_log`
**Description:** System-wide audit trail recording actions taken by administrators, station staff, and automated system routines.

| Field Name | Data Type | Field Length | Constraint | Description |
| :--- | :--- | :--- | :--- | :--- |
| `id` | INT UNSIGNED | 10 | PRIMARY KEY, AUTO_INCREMENT, NOT NULL | Unique numerical identifier for the audit log entry |
| `user_type` | VARCHAR | 30 | NOT NULL, INDEX | Category of actor ('admin', 'staff', 'patient', 'system') |
| `user_name` | VARCHAR | 150 | NOT NULL | Display name or email of user who performed the action |
| `action` | VARCHAR | 100 | NOT NULL | Action identifier (e.g., 'Confirm Appointment', 'Update Profile') |
| `description` | TEXT | 65535 | NULL | Detailed narrative or contextual data of the event |
| `created_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP, INDEX | Exact date and time when the event was performed |

---

## 3. Corrected Version of Your Draft Tables (For Conceptual / Normalized Submission)

If your school or capstone adviser requires you to submit the **exact conceptual/normalized model** you started drafting in your images, here are your draft tables revised with corrected constraints, data types, field lengths, foreign key targets, and descriptions:

### Conceptual Table 1: `BARANGAY_HEALTH_STATION`
*(Corrected from your Image 1 & 2)*

| Field Name | Data Type | Field Length | Constraint | Description |
| :--- | :--- | :--- | :--- | :--- |
| `barangay_id` | INT | 11 | PRIMARY KEY, AUTO_INCREMENT, NOT NULL | Unique identifier for barangay health station |
| `barangay_name` | VARCHAR | 100 | NOT NULL | Name of the barangay jurisdiction |
| `address` | VARCHAR | 255 | NOT NULL | Physical address/location of the health station |
| `contact_number` | VARCHAR | 20 | NULL | Station telephone or mobile contact hotline |
| `operating_hours` | VARCHAR | 100 | NOT NULL, DEFAULT '8:00 AM - 5:00 PM' | Standard operating days and hours |
| `admin_id` | INT | 11 | FOREIGN KEY, NOT NULL | References `ADMIN(admin_id)` |

---

### Conceptual Table 2: `HEALTH_SERVICE`
*(Corrected from your Image 4)*

| Field Name | Data Type | Field Length | Constraint | Description |
| :--- | :--- | :--- | :--- | :--- |
| `service_id` | INT | 11 | PRIMARY KEY, AUTO_INCREMENT, NOT NULL | Unique identifier for the health service |
| `service_name` | VARCHAR | 100 | NOT NULL | Name of health service (e.g., Pre-Natal, Immunization) |
| `description` | TEXT | 65535 | NOT NULL | Comprehensive clinical description of the service |
| `duration` | VARCHAR | 30 | NULL | Typical duration or processing time of the service |
| `admin_id` | INT | 11 | FOREIGN KEY, NOT NULL | References `ADMIN(admin_id)` |

---

### Conceptual Table 3: `APPOINTMENT_LIST` (Booking Application Form)
*(Corrected from your Image 3)*

| Field Name | Data Type | Field Length | Constraint | Description |
| :--- | :--- | :--- | :--- | :--- |
| `appointment_list_id` | INT | 11 | PRIMARY KEY, AUTO_INCREMENT, NOT NULL | Unique identifier for appointment application form |
| `reference_code` | VARCHAR | 32 | UNIQUE, NOT NULL | Public tracking reference number for patient verification |
| `client_id` | INT | 11 | FOREIGN KEY, NOT NULL | References `CLIENT(client_id)` |
| `service_id` | INT | 11 | FOREIGN KEY, NOT NULL | References `HEALTH_SERVICE(service_id)` |
| `barangay_id` | INT | 11 | FOREIGN KEY, NOT NULL | References `BARANGAY_HEALTH_STATION(barangay_id)` |
| `appointment_date` | DATE | — | NOT NULL | Scheduled appointment date |
| `appointment_time` | VARCHAR | 30 | NOT NULL | Scheduled appointment time slot |
| `appointment_details` | TEXT | 65535 | NULL | Patient chief complaint, symptoms, or special remarks |
| `status` | VARCHAR | 20 | NOT NULL, DEFAULT 'Pending' | Booking status (Pending, Confirmed, Cancelled, Completed) |
| `created_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Date and time when the booking request was submitted |

---

### Conceptual Table 4: `APPOINTMENT_RECORD` (Clinical & Consultation Outcomes)
*(Corrected from your Image 5)*

| Field Name | Data Type | Field Length | Constraint | Description |
| :--- | :--- | :--- | :--- | :--- |
| `record_id` | INT | 11 | PRIMARY KEY, AUTO_INCREMENT, NOT NULL | Unique identifier for clinical examination record |
| `appointment_list_id` | INT | 11 | FOREIGN KEY, UNIQUE, NOT NULL | References `APPOINTMENT_LIST(appointment_list_id)` |
| `client_id` | INT | 11 | FOREIGN KEY, NOT NULL | References `CLIENT(client_id)` |
| `body_temperature` | VARCHAR | 20 | NULL | Triage measurement: Body temperature in °C |
| `blood_pressure` | VARCHAR | 20 | NULL | Triage measurement: Blood pressure (e.g., '120/80') |
| `pulse_rate` | VARCHAR | 20 | NULL | Triage measurement: Heart/pulse rate in bpm |
| `respiration_rate` | VARCHAR | 20 | NULL | Triage measurement: Respiratory rate in breaths per min |
| `diagnosis_notes` | TEXT | 65535 | NULL | Clinical diagnosis, findings, and doctor's remarks |
| `treatment_prescribed`| TEXT | 65535 | NULL | Dispensed medicines, prescribed dosage, or referral advice |
| `attended_by` | VARCHAR | 150 | NOT NULL | Name of the health worker or physician who served the client |
| `recorded_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Date and time when the medical record was finalized |

---

### Conceptual Table 5: `CLIENT` / `PATIENT`
*(The missing entity referenced by `client_id` in your draft)*

| Field Name | Data Type | Field Length | Constraint | Description |
| :--- | :--- | :--- | :--- | :--- |
| `client_id` | INT | 11 | PRIMARY KEY, AUTO_INCREMENT, NOT NULL | Unique identifier for client/patient |
| `first_name` | VARCHAR | 100 | NOT NULL | Given first name of client |
| `middle_name` | VARCHAR | 100 | NULL | Middle name of client |
| `last_name` | VARCHAR | 100 | NOT NULL | Last name / surname of client |
| `birth_date` | DATE | — | NOT NULL | Date of birth of client |
| `gender` | VARCHAR | 20 | NOT NULL | Biological sex of client |
| `contact_number` | VARCHAR | 20 | NOT NULL | Mobile contact number |
| `email` | VARCHAR | 150 | UNIQUE, NULL | Email address for digital notifications |
| `address` | VARCHAR | 255 | NOT NULL | Complete residential address |
| `barangay_id` | INT | 11 | FOREIGN KEY, NOT NULL | References `BARANGAY_HEALTH_STATION(barangay_id)` |
| `created_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Timestamp when client profile was registered |

---

### Conceptual Table 6: `ADMIN`
*(The missing entity referenced by `admin_id` in your draft)*

| Field Name | Data Type | Field Length | Constraint | Description |
| :--- | :--- | :--- | :--- | :--- |
| `admin_id` | INT | 11 | PRIMARY KEY, AUTO_INCREMENT, NOT NULL | Unique identifier for system administrator |
| `admin_name` | VARCHAR | 150 | NOT NULL | Full name of administrator |
| `office_name` | VARCHAR | 255 | NOT NULL | Official health office designation |
| `email` | VARCHAR | 150 | UNIQUE, NOT NULL | Email address used for authentication |
| `password` | VARCHAR | 255 | NOT NULL | Securely hashed login password |
| `contact_number` | VARCHAR | 20 | NULL | Contact mobile number |
| `created_at` | TIMESTAMP | — | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Registration timestamp |
