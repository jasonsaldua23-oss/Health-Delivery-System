import sys
import docx
from docx import Document
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_ALIGN_VERTICAL
from docx.oxml import OxmlElement, parse_xml
from docx.oxml.ns import nsdecls, qn

def set_cell_background(cell, hex_color):
    """Set background color of a table cell."""
    tcPr = cell._tc.get_or_add_tcPr()
    shd = parse_xml(f'<w:shd {nsdecls("w")} w:fill="{hex_color}"/>')
    tcPr.append(shd)

def set_cell_margins(cell, top=100, bottom=100, left=150, right=150):
    """Set internal padding for a cell in dxa (1 pt = 20 dxa)."""
    tcPr = cell._tc.get_or_add_tcPr()
    tcMar = parse_xml(f'''
        <w:tcMar {nsdecls("w")}>
            <w:top w:w="{top}" w:type="dxa"/>
            <w:bottom w:w="{bottom}" w:type="dxa"/>
            <w:left w:w="{left}" w:type="dxa"/>
            <w:right w:w="{right}" w:type="dxa"/>
        </w:tcMar>
    ''')
    tcPr.append(tcMar)

def set_table_borders(table):
    """Set thin subtle borders for clean academic look."""
    tblPr = table._tbl.tblPr
    borders = parse_xml(f'''
        <w:tblBorders {nsdecls("w")}>
            <w:top w:val="single" w:sz="4" w:space="0" w:color="CBD5E1"/>
            <w:bottom w:val="single" w:sz="8" w:space="0" w:color="94A3B8"/>
            <w:left w:val="none" w:sz="0" w:space="0" w:color="auto"/>
            <w:right w:val="none" w:sz="0" w:space="0" w:color="auto"/>
            <w:insideH w:val="single" w:sz="4" w:space="0" w:color="E2E8F0"/>
            <w:insideV w:val="none" w:sz="0" w:space="0" w:color="auto"/>
        </w:tblBorders>
    ''')
    tblPr.append(borders)

def make_row_cant_split(row):
    """Prevent table row from splitting across pages."""
    trPr = row._tr.get_or_add_trPr()
    cantSplit = parse_xml(f'<w:cantSplit {nsdecls("w")}/>')
    trPr.append(cantSplit)

def set_repeat_header(row):
    """Repeat header row on each page."""
    trPr = row._tr.get_or_add_trPr()
    tblHeader = parse_xml(f'<w:tblHeader {nsdecls("w")}/>')
    trPr.append(tblHeader)

def build_data_dictionary():
    doc = Document()

    # Configure Margins (0.75 in for wider table fit)
    sections = doc.sections
    for section in sections:
        section.top_margin = Inches(0.8)
        section.bottom_margin = Inches(0.8)
        section.left_margin = Inches(0.8)
        section.right_margin = Inches(0.8)

    # Document Styles
    normal_style = doc.styles['Normal']
    normal_style.font.name = 'Calibri'
    normal_style.font.size = Pt(10.5)
    normal_style.font.color.rgb = RGBColor(0x1E, 0x29, 0x3B)

    # Cover / Header Title
    title_p = doc.add_paragraph()
    title_p.paragraph_format.space_before = Pt(12)
    title_p.paragraph_format.space_after = Pt(2)
    title_run = title_p.add_run("Health Delivery System")
    title_run.font.name = 'Calibri'
    title_run.font.size = Pt(26)
    title_run.font.bold = True
    title_run.font.color.rgb = RGBColor(0x0F, 0x17, 0x2A)

    subtitle_p = doc.add_paragraph()
    subtitle_p.paragraph_format.space_after = Pt(18)
    sub_run = subtitle_p.add_run("Official Database Data Dictionary & Technical Specification")
    sub_run.font.name = 'Calibri'
    sub_run.font.size = Pt(14)
    sub_run.font.color.rgb = RGBColor(0x02, 0x84, 0xC7) # Teal/sky accent

    # Overview box
    intro_table = doc.add_table(rows=1, cols=1)
    intro_table.alignment = WD_TABLE_ALIGNMENT.CENTER
    intro_table.autofit = False
    intro_table.columns[0].width = Inches(6.9)
    cell = intro_table.cell(0, 0)
    set_cell_background(cell, "F1F5F9")
    set_cell_margins(cell, top=140, bottom=140, left=180, right=180)
    
    p = cell.paragraphs[0]
    p.paragraph_format.space_after = Pt(4)
    r = p.add_run("System Architecture Overview & Metadata\n")
    r.font.bold = True
    r.font.size = Pt(11)
    r.font.color.rgb = RGBColor(0x0F, 0x17, 0x2A)
    
    desc_run = p.add_run(
        "• System Name: Bacolod City Barangay Health Station Delivery System (Health Delivery System)\n"
        "• Target Database: MySQL 8.0+ / MariaDB 10.4+ (InnoDB Engine, utf8mb4_unicode_ci)\n"
        "• Total Core Tables: 19 Implemented Physical Tables\n"
        "• Standard Columns: Field Name, Data Type, Field Length, Constraint, Description\n"
        "• Scope: User Accounts (Admin, Staff, Patient), Appointment Booking, Clinical Triage & Vital Signs, "
        "Pediatric & Maternal Health, Station Service Schedules, Notifications, and Audit Logs."
    )
    desc_run.font.size = Pt(9.5)
    desc_run.font.color.rgb = RGBColor(0x33, 0x41, 0x55)

    doc.add_paragraph() # Spacer

    # Section 1: Review & Analysis of User's Initial Draft
    h1 = doc.add_heading("1. Analysis & Comparison with Initial Draft", level=1)
    h1.style.font.color.rgb = RGBColor(0x0F, 0x17, 0x2A)
    h1.paragraph_format.space_before = Pt(14)
    h1.paragraph_format.space_after = Pt(6)

    p1 = doc.add_paragraph(
        "A preliminary review of your draft data dictionary against the live system codebase highlights several important design evolutions:"
    )
    p1.paragraph_format.space_after = Pt(6)

    bullets = [
        ("Unified Appointments vs. Split Tables: ", 
         "Your draft separated appointments into APPOINTMENT_LIST and APPOINTMENT_RECORD. In the live system, these are unified into the 'appointments' table. This eliminates unnecessary redundant joins and directly couples the booking schedule with vital triage measurements (blood pressure, temperature, pulse rate, respiration rate) and doctor's notes."),
        ("Health Station Modeling: ",
         "The draft used BARANGAY_HEALTH_STATION with admin_id. In the actual system, stations are represented in 'health_facilities' with unique station slugs (e.g., 'alijis', 'bata', 'mansilingan') and operated by station-specific health personnel registered in 'staff_accounts'."),
        ("Dynamic Service Scheduling: ",
         "Rather than a static HEALTH_SERVICE table, the system uses 'station_service_assignments', 'station_service_schedules', and 'station_slot_limits' to allow each barangay station to configure different weekly hours, operating days, and daily patient quotas per medical program."),
        ("Patient Authentication & Profiles: ",
         "Patients are structured into 'patient_accounts' (handling password hashing and portal authentication) and 'patient_profiles' (master demographic record), with additional specialized tables for 'infant_profiles' and 'immunized_infants'."),
        ("Operational Integrity & Security: ",
         "The full codebase contains 19 operational tables, including audit logs ('activity_log', 'patient_info_history'), real-time alerts ('appointment_status_notifications', 'patient_update_notifications'), unattended queue monitors, and OTP password recovery ('password_reset_otps').")
    ]

    for title, text in bullets:
        bp = doc.add_paragraph(style='List Bullet')
        bp.paragraph_format.space_after = Pt(3)
        rt = bp.add_run(title)
        rt.bold = True
        rt.font.color.rgb = RGBColor(0x03, 0x69, 0xA1)
        rx = bp.add_run(text)
        rx.font.size = Pt(10)

    doc.add_page_break()

    # Section 2: Complete Physical Data Dictionary
    h2 = doc.add_heading("2. Complete Physical Data Dictionary (Actual Implemented System)", level=1)
    h2.style.font.color.rgb = RGBColor(0x0F, 0x17, 0x2A)
    h2.paragraph_format.space_before = Pt(10)
    h2.paragraph_format.space_after = Pt(8)

    p2 = doc.add_paragraph(
        "The following tables document all 19 database tables currently implemented and active in the Health Delivery System codebase."
    )
    p2.paragraph_format.space_after = Pt(12)

    # Data Tables Dictionary Data Structure
    # Format: [table_name, category_and_desc, [ [col, type, len, constraint, desc], ... ]]
    tables_data = [
        (
            "admin_accounts",
            "Category: User Accounts & Authentication | Description: Stores administrator credentials, office assignments, and active session status for City Health Office executives.",
            [
                ["id", "INT UNSIGNED", "10", "PRIMARY KEY, AUTO_INCREMENT, NOT NULL", "Unique numerical identifier for the administrator account."],
                ["admin_name", "VARCHAR", "150", "NOT NULL", "Full legal name of the system administrator."],
                ["office_name", "VARCHAR", "255", "NOT NULL", "Governing health office name (e.g., Bacolod City Health Office)."],
                ["email", "VARCHAR", "150", "UNIQUE, NOT NULL", "Official email address used for administrative login."],
                ["password_hash", "VARCHAR", "255", "NOT NULL", "Secure Bcrypt cryptographic password hash."],
                ["last_active_at", "TIMESTAMP", "—", "NULL", "Date and time of administrator's most recent system interaction."],
                ["is_logged_in", "TINYINT", "1", "NOT NULL, DEFAULT 0", "Active login status flag (1 = Active/Logged in, 0 = Logged out)."],
                ["created_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP", "Timestamp when the administrator account was created."],
                ["updated_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP", "Timestamp when administrator details were last modified."]
            ]
        ),
        (
            "staff_accounts",
            "Category: User Accounts & Health Stations | Description: Stores health personnel profiles, security credentials, and assigned barangay health stations.",
            [
                ["id", "INT UNSIGNED", "10", "PRIMARY KEY, AUTO_INCREMENT, NOT NULL", "Unique numerical identifier for the health staff record."],
                ["station_slug", "VARCHAR", "100", "NOT NULL, INDEX", "URL-safe station slug code of the assigned barangay health station."],
                ["station_name", "VARCHAR", "255", "NOT NULL", "Full formal name of the assigned barangay health station."],
                ["staff_name", "VARCHAR", "150", "NOT NULL", "Full name of the assigned health station personnel."],
                ["email", "VARCHAR", "150", "UNIQUE, NOT NULL", "Official email address used for staff portal authentication."],
                ["password_hash", "VARCHAR", "255", "NOT NULL", "Bcrypt cryptographic hash of the staff account password."],
                ["birth_date", "DATE", "—", "NULL", "Date of birth of the staff member."],
                ["gender", "VARCHAR", "30", "NULL", "Biological sex / gender of the staff member."],
                ["contact_number", "VARCHAR", "30", "NULL", "Official contact telephone or mobile phone number."],
                ["home_address", "VARCHAR", "255", "NULL", "Residential home address of the staff member."],
                ["emergency_contact", "VARCHAR", "100", "NULL", "Full name of designated emergency contact person."],
                ["emergency_phone", "VARCHAR", "30", "NULL", "Telephone or mobile number of emergency contact."],
                ["last_active_at", "TIMESTAMP", "—", "NULL", "Timestamp of staff member's last active session."],
                ["is_logged_in", "TINYINT", "1", "NOT NULL, DEFAULT 0", "Online session status indicator (1 = Active, 0 = Logged out)."],
                ["created_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP", "Timestamp when the staff account was registered."],
                ["updated_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP", "Timestamp when the staff record was last modified."]
            ]
        ),
        (
            "patient_accounts",
            "Category: User Accounts & Patient Portal | Description: Stores authentication credentials, contact numbers, and address details for resident patient portal accounts.",
            [
                ["id", "INT UNSIGNED", "10", "PRIMARY KEY, AUTO_INCREMENT, NOT NULL", "Unique numerical identifier for the patient account."],
                ["patient_id", "VARCHAR", "32", "UNIQUE, NOT NULL", "Alphanumeric unique system patient code (e.g., 'V84QXW')."],
                ["email", "VARCHAR", "150", "UNIQUE, NOT NULL", "Patient email address used for portal authentication and notifications."],
                ["password_hash", "VARCHAR", "255", "NOT NULL", "Secure Bcrypt cryptographic hash of the patient password."],
                ["first_name", "VARCHAR", "100", "NOT NULL", "Patient's given first name."],
                ["middle_name", "VARCHAR", "100", "NULL", "Patient's middle name."],
                ["last_name", "VARCHAR", "100", "NOT NULL", "Patient's surname / last name."],
                ["birth_date", "DATE", "—", "NOT NULL", "Date of birth of the registered patient."],
                ["gender", "VARCHAR", "30", "NOT NULL", "Biological sex (e.g., 'Male', 'Female')."],
                ["contact_number", "VARCHAR", "20", "NOT NULL", "Primary mobile phone number for automated SMS notifications."],
                ["complete_address", "VARCHAR", "255", "NOT NULL", "Complete residential address including Purok and Barangay."],
                ["station_slug", "VARCHAR", "100", "NULL", "Slug code of the patient's default/catchment health station."],
                ["station_name", "VARCHAR", "255", "NULL", "Display name of the patient's default health station."],
                ["created_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP", "Timestamp when the patient account was registered."],
                ["updated_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP", "Timestamp when patient account details were last updated."]
            ]
        ),
        (
            "patient_profiles",
            "Category: Patient Records | Description: Master demographic repository and verification records for patients across all barangays.",
            [
                ["patient_id", "VARCHAR", "32", "PRIMARY KEY, NOT NULL", "Unique alphanumeric master patient identification code."],
                ["first_name", "VARCHAR", "100", "NOT NULL", "Patient's given first name."],
                ["middle_name", "VARCHAR", "100", "NULL", "Patient's middle name."],
                ["last_name", "VARCHAR", "100", "NOT NULL", "Patient's surname / last name."],
                ["birth_date", "DATE", "—", "NOT NULL", "Patient's date of birth."],
                ["gender", "VARCHAR", "30", "NOT NULL", "Patient's biological sex."],
                ["contact_number", "VARCHAR", "20", "NOT NULL", "Primary contact telephone or mobile number."],
                ["email", "VARCHAR", "150", "NULL", "Contact email address."],
                ["complete_address", "VARCHAR", "255", "NOT NULL", "Full residential address details."],
                ["photo_path", "VARCHAR", "255", "NULL", "File system path or image URL to patient's identification photo."],
                ["created_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP", "Timestamp when the master profile was initially stored."],
                ["updated_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP", "Timestamp when the master profile was last modified."]
            ]
        ),
        (
            "appointments",
            "Category: Appointments & Clinical Records | Description: Central operational table storing patient bookings, schedules, vital triage signs, and medical findings.",
            [
                ["id", "INT UNSIGNED", "10", "PRIMARY KEY, AUTO_INCREMENT, NOT NULL", "Unique numerical identifier for the appointment transaction."],
                ["reference_code", "VARCHAR", "32", "UNIQUE, NOT NULL", "Public tracking reference code (e.g., 'BK260520161625781')."],
                ["appointment_code", "VARCHAR", "10", "NULL", "Short 8-10 character check-in/queue code (e.g., 'ELVMHSMZ')."],
                ["patient_id", "VARCHAR", "32", "NULL, INDEX", "References master patient_id from patient profiles."],
                ["station_slug", "VARCHAR", "100", "NOT NULL, INDEX", "Slug code of the designated barangay health station."],
                ["station_name", "VARCHAR", "255", "NOT NULL", "Display name of the designated barangay health station."],
                ["service_slug", "VARCHAR", "100", "NOT NULL, INDEX", "Slug identifier of requested healthcare service program."],
                ["service_name", "VARCHAR", "255", "NOT NULL", "Display title of requested healthcare service."],
                ["first_name", "VARCHAR", "100", "NOT NULL", "Patient/applicant first name recorded at appointment time."],
                ["middle_name", "VARCHAR", "100", "NULL", "Patient/applicant middle name."],
                ["last_name", "VARCHAR", "100", "NOT NULL", "Patient/applicant last name."],
                ["birth_date", "DATE", "—", "NOT NULL", "Patient/applicant date of birth."],
                ["gender", "VARCHAR", "30", "NOT NULL", "Patient/applicant biological sex."],
                ["contact_number", "VARCHAR", "20", "NOT NULL", "Contact mobile number for appointment alerts."],
                ["email", "VARCHAR", "150", "NULL", "Email address for digital notifications."],
                ["complete_address", "VARCHAR", "255", "NOT NULL", "Residential address recorded during booking."],
                ["immunization_relationship", "VARCHAR", "100", "NULL", "Guardian relationship (e.g., 'Mother') if pediatric service."],
                ["recipient_first_name", "VARCHAR", "100", "NULL", "First name of child/dependent recipient."],
                ["recipient_middle_name", "VARCHAR", "100", "NULL", "Middle name of child/dependent recipient."],
                ["recipient_last_name", "VARCHAR", "100", "NULL", "Last name of child/dependent recipient."],
                ["recipient_birth_date", "DATE", "—", "NULL", "Date of birth of child/dependent recipient."],
                ["recipient_gender", "VARCHAR", "30", "NULL", "Biological sex of child/dependent recipient."],
                ["preferred_date", "DATE", "—", "NOT NULL, INDEX", "Scheduled appointment date requested by patient."],
                ["preferred_time", "VARCHAR", "30", "NOT NULL", "Scheduled appointment time slot window."],
                ["notes", "TEXT", "65535", "NULL", "Patient chief complaint, symptoms, or visit remarks."],
                ["body_temperature", "VARCHAR", "30", "NULL", "Clinical vital sign: Body temperature reading in °C."],
                ["pulse_rate", "VARCHAR", "30", "NULL", "Clinical vital sign: Heart / pulse rate in beats per minute."],
                ["respiration_rate", "VARCHAR", "30", "NULL", "Clinical vital sign: Respiration rate in breaths per minute."],
                ["blood_pressure", "VARCHAR", "30", "NULL", "Clinical vital sign: Blood pressure reading (e.g., '120/80')."],
                ["height", "VARCHAR", "50", "NULL", "Physical measurement: Patient height in cm."],
                ["weight", "VARCHAR", "50", "NULL", "Physical measurement: Patient weight in kg."],
                ["vaccine_type", "VARCHAR", "150", "NULL", "Vaccine brand/dose administered (for immunization appointments)."],
                ["doctor_notes", "TEXT", "65535", "NULL", "Healthcare provider diagnosis, prescription, and findings."],
                ["reminder_sms_sent", "TINYINT", "1", "NOT NULL, DEFAULT 0", "Flag indicating if SMS reminder was dispatched (1 = Sent, 0 = Pending)."],
                ["reminder_sent_at", "TIMESTAMP", "—", "NULL", "Exact timestamp when SMS reminder was sent."],
                ["photo_path", "VARCHAR", "255", "NULL", "File path to uploaded verification document or proof."],
                ["status", "VARCHAR", "30", "NOT NULL, DEFAULT 'Pending'", "Workflow state ('Pending', 'Confirmed', 'Serving', 'Completed', 'Cancelled', 'Declined')."],
                ["created_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP", "Timestamp when appointment booking was submitted."],
                ["updated_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP", "Timestamp when appointment or clinical findings were last updated."]
            ]
        ),
        (
            "appointment_status_notifications",
            "Category: Notifications | Description: Real-time patient alerts informing residents when their booking is confirmed, completed, or rescheduled.",
            [
                ["id", "INT UNSIGNED", "10", "PRIMARY KEY, AUTO_INCREMENT, NOT NULL", "Unique numerical identifier for the notification entry."],
                ["appointment_id", "INT UNSIGNED", "10", "FOREIGN KEY, NOT NULL", "References appointments(id) (ON DELETE CASCADE)."],
                ["reference_code", "VARCHAR", "32", "NOT NULL", "Appointment reference tracking code."],
                ["patient_id", "VARCHAR", "32", "NOT NULL, INDEX", "Patient system ID receiving the notification."],
                ["status", "VARCHAR", "30", "NOT NULL", "Target appointment status triggering the notice."],
                ["message", "TEXT", "65535", "NOT NULL", "Notification body message displayed in the patient portal."],
                ["is_read", "TINYINT", "1", "NOT NULL, DEFAULT 0", "Read receipt flag (0 = Unread, 1 = Read)."],
                ["created_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP", "Timestamp when the notification was created."]
            ]
        ),
        (
            "health_facilities",
            "Category: Station Management | Description: Directory of all barangay health stations and health centers in the delivery network.",
            [
                ["id", "INT UNSIGNED", "10", "PRIMARY KEY, AUTO_INCREMENT, NOT NULL", "Unique numerical identifier for the health facility."],
                ["slug", "VARCHAR", "100", "UNIQUE, NOT NULL", "URL-safe station slug code (e.g., 'bata', 'alijis', 'city-health')."],
                ["name", "VARCHAR", "255", "NOT NULL", "Formal facility name (e.g., 'Bata Barangay Health Station')."],
                ["barangay", "VARCHAR", "100", "NOT NULL, INDEX", "Name of barangay jurisdiction served."],
                ["location", "VARCHAR", "255", "NOT NULL", "Physical street address or geographic location of the station."],
                ["phone", "VARCHAR", "50", "NOT NULL", "Official telephone or mobile contact hotline."],
                ["color", "VARCHAR", "30", "NOT NULL, DEFAULT 'mint'", "UI accent theme color code for dashboard branding."],
                ["image", "VARCHAR", "255", "NULL", "File system path or image URL to station photo."],
                ["hours", "VARCHAR", "100", "NOT NULL, DEFAULT 'Monday - Saturday, 8:00 AM - 5:00 PM'", "Standard operating days and hours description."],
                ["created_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP", "Timestamp when the facility was registered."],
                ["updated_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP", "Timestamp when facility details were last updated."]
            ]
        ),
        (
            "station_service_assignments",
            "Category: Station Configuration | Description: Junction table mapping healthcare services and daily patient capacities to health stations.",
            [
                ["station_slug", "VARCHAR", "100", "PRIMARY KEY (Composite), NOT NULL", "Identifier slug of the barangay health station."],
                ["service_slug", "VARCHAR", "100", "PRIMARY KEY (Composite), NOT NULL", "Identifier slug of the offered healthcare service."],
                ["sort_order", "INT UNSIGNED", "10", "NOT NULL, DEFAULT 0", "Numerical sorting priority for frontend dropdown menus."],
                ["daily_capacity", "INT UNSIGNED", "10", "NOT NULL, DEFAULT 200", "Maximum number of patients accommodated per day."],
                ["created_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP", "Timestamp when the service assignment was recorded."],
                ["updated_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP", "Timestamp when the service assignment was modified."]
            ]
        ),
        (
            "station_service_schedules",
            "Category: Station Configuration | Description: Stores weekly service operating schedules, opening days, and session labels for each station.",
            [
                ["id", "INT UNSIGNED", "10", "PRIMARY KEY, AUTO_INCREMENT, NOT NULL", "Unique numerical identifier for the schedule record."],
                ["station_slug", "VARCHAR", "100", "NOT NULL, INDEX", "Slug identifier of the barangay health station."],
                ["service_slug", "VARCHAR", "100", "NOT NULL, INDEX", "Slug identifier of the healthcare service."],
                ["days_json", "TEXT", "65535", "NOT NULL", "JSON array of active operating weekdays (e.g., ['Mon','Wed','Fri'])."],
                ["schedule_label", "VARCHAR", "255", "NOT NULL", "Descriptive schedule summary (e.g., 'Wednesdays 8:00 AM - 12:00 PM')."],
                ["created_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP", "Timestamp when schedule configuration was created."],
                ["updated_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP", "Timestamp when schedule was last modified."]
            ]
        ),
        (
            "station_slot_limits",
            "Category: Station Configuration | Description: Manages reservation limits and slot quotas per service per health station to prevent overcrowding.",
            [
                ["id", "INT UNSIGNED", "10", "PRIMARY KEY, AUTO_INCREMENT, NOT NULL", "Unique quota record identifier."],
                ["station_slug", "VARCHAR", "100", "NOT NULL, INDEX", "Health station slug identifier."],
                ["service_slug", "VARCHAR", "100", "NOT NULL", "Healthcare service program slug identifier."],
                ["max_slots", "INT UNSIGNED", "10", "NOT NULL, DEFAULT 200", "Maximum allowable confirmed booking slots per day."],
                ["created_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP", "Timestamp when slot limit was initialized."],
                ["updated_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP", "Timestamp when slot limit was last updated."]
            ]
        ),
        (
            "infant_profiles",
            "Category: Maternal & Child Health | Description: Pediatric master records linking infants and young children to registered parents or guardians.",
            [
                ["id", "INT UNSIGNED", "10", "PRIMARY KEY, AUTO_INCREMENT, NOT NULL", "Unique numerical identifier for the infant profile."],
                ["patient_id", "VARCHAR", "32", "NOT NULL, INDEX", "References parent/guardian's master patient_id."],
                ["first_name", "VARCHAR", "100", "NOT NULL", "Given first name of the infant/child."],
                ["middle_name", "VARCHAR", "100", "NULL", "Middle name of the infant/child."],
                ["last_name", "VARCHAR", "100", "NOT NULL", "Last name of the infant/child."],
                ["birth_date", "DATE", "—", "NOT NULL, INDEX", "Infant/child date of birth."],
                ["gender", "VARCHAR", "30", "NULL", "Biological sex / gender of the infant."],
                ["relationship", "VARCHAR", "50", "NOT NULL, DEFAULT 'Child'", "Guardian relationship type (e.g., 'Child', 'Ward', 'Grandchild')."],
                ["mother_name", "VARCHAR", "150", "NULL", "Full legal name of the child's mother."],
                ["father_name", "VARCHAR", "150", "NULL", "Full legal name of the child's father."],
                ["guardian_name", "VARCHAR", "150", "NULL", "Legal guardian name if parents are absent."],
                ["custom_notes", "TEXT", "65535", "NULL", "Pediatric health remarks, birth weight, or allergies."],
                ["created_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP", "Date and time when the infant profile was created."],
                ["updated_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP", "Date and time when the profile was last modified."]
            ]
        ),
        (
            "immunized_infants",
            "Category: Maternal & Child Health | Description: Official clinical logbook of vaccines administered to children at barangay health stations.",
            [
                ["id", "INT UNSIGNED", "10", "PRIMARY KEY, AUTO_INCREMENT, NOT NULL", "Unique numerical identifier for the immunization log entry."],
                ["appointment_id", "INT UNSIGNED", "10", "NULL, INDEX", "Linked appointment ID where vaccination occurred."],
                ["appointment_code", "VARCHAR", "20", "NULL, INDEX", "Short alphanumeric appointment reference code."],
                ["patient_id", "VARCHAR", "32", "NULL, INDEX", "References parent/guardian patient ID."],
                ["first_name", "VARCHAR", "100", "NOT NULL", "Given first name of vaccinated infant/child."],
                ["middle_name", "VARCHAR", "100", "NULL", "Middle name of vaccinated infant/child."],
                ["last_name", "VARCHAR", "100", "NOT NULL, INDEX", "Last name of vaccinated infant/child."],
                ["birth_date", "DATE", "—", "NOT NULL", "Date of birth of vaccinated child."],
                ["gender", "VARCHAR", "30", "NULL", "Biological sex of the child."],
                ["relationship", "VARCHAR", "50", "NOT NULL, DEFAULT 'Child'", "Relationship to registered guardian."],
                ["station_slug", "VARCHAR", "100", "NULL, INDEX", "Slug code of the station administering the vaccine."],
                ["vaccine_type", "VARCHAR", "150", "NULL", "Vaccine brand/dose administered (e.g., BCG, Pentavalent, OPV, Measles)."],
                ["created_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP", "Timestamp when the vaccine dose was administered."],
                ["updated_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP", "Timestamp when the record was last modified."]
            ]
        ),
        (
            "patient_info_history",
            "Category: Audit & Compliance | Description: Historical audit log tracking personal data modifications (such as address and contact changes) submitted by patients.",
            [
                ["id", "INT UNSIGNED", "10", "PRIMARY KEY, AUTO_INCREMENT, NOT NULL", "Unique numerical identifier for the audit entry."],
                ["patient_id", "VARCHAR", "32", "NOT NULL, INDEX", "References patient_id of the modified record."],
                ["field_name", "VARCHAR", "50", "NOT NULL", "Column name modified (e.g., 'complete_address', 'contact_number')."],
                ["old_value", "TEXT", "65535", "NULL", "Previous value prior to modification."],
                ["new_value", "TEXT", "65535", "NULL", "Newly updated value submitted by the patient."],
                ["changed_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP, INDEX", "Exact date and time when the change occurred."]
            ]
        ),
        (
            "patient_update_notifications",
            "Category: Audit & Notifications | Description: Administrative alert queue notifying staff when a resident updates their contact or address details.",
            [
                ["id", "INT UNSIGNED", "10", "PRIMARY KEY, AUTO_INCREMENT, NOT NULL", "Unique numerical identifier for the notification."],
                ["patient_id", "VARCHAR", "32", "NOT NULL, INDEX", "Patient system ID who submitted the change."],
                ["patient_name", "VARCHAR", "255", "NOT NULL", "Full name of the patient for quick preview."],
                ["field_updated", "VARCHAR", "50", "NOT NULL", "Data field modified (e.g., 'Address', 'Contact Number')."],
                ["is_read", "TINYINT", "1", "NOT NULL, DEFAULT 0, INDEX", "Read status flag (0 = Unread by staff, 1 = Acknowledged)."],
                ["created_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP, INDEX", "Timestamp when notification alert was generated."]
            ]
        ),
        (
            "upcoming_events",
            "Category: Community Engagement | Description: Public bulletins, wellness caravans, immunization campaigns, and station announcements.",
            [
                ["id", "INT UNSIGNED", "10", "PRIMARY KEY, AUTO_INCREMENT, NOT NULL", "Unique numerical identifier for the event entry."],
                ["station_slug", "VARCHAR", "100", "NOT NULL, INDEX", "Station slug organizing the community event."],
                ["station_name", "VARCHAR", "255", "NOT NULL", "Full name of the organizing health station."],
                ["title", "VARCHAR", "255", "NOT NULL", "Event headline / title (e.g., 'Community Feeding Program')."],
                ["description", "TEXT", "65535", "NOT NULL", "Comprehensive narrative details and event instructions."],
                ["target_month", "VARCHAR", "20", "NULL", "Target month for periodic or recurring campaigns."],
                ["event_date", "DATE", "—", "NULL, INDEX", "Specific calendar date of the event."],
                ["time_label", "VARCHAR", "100", "NOT NULL", "Operating start time (e.g., '8:00 AM - 12:00 PM')."],
                ["end_time_label", "VARCHAR", "100", "NULL", "Operating conclusion time."],
                ["icon", "VARCHAR", "50", "NOT NULL, DEFAULT 'calendar'", "Icon style identifier for UI rendering ('syringe', 'heart', 'calendar')."],
                ["accent", "VARCHAR", "50", "NOT NULL, DEFAULT 'mint'", "Badge/card theme accent color ('mint', 'blue', 'pink')."],
                ["status", "VARCHAR", "20", "NOT NULL, DEFAULT 'inactive'", "Event publication state ('active', 'inactive', 'archived')."],
                ["created_by", "VARCHAR", "150", "NULL", "Email or username of the staff who created the event."],
                ["created_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP", "Timestamp when event announcement was published."],
                ["updated_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP", "Timestamp when event details were last modified."]
            ]
        ),
        (
            "unattended_appointments",
            "Category: Triage & Queue Monitoring | Description: Archive of pending appointment applications that lapsed or were left unconfirmed by staff prior to the appointment date.",
            [
                ["id", "INT UNSIGNED", "10", "PRIMARY KEY, AUTO_INCREMENT, NOT NULL", "Unique numerical identifier for the lapsed record."],
                ["appointment_id", "INT UNSIGNED", "10", "UNIQUE, NOT NULL", "References original record in appointments(id)."],
                ["reference_code", "VARCHAR", "20", "NOT NULL", "Appointment public tracking reference code."],
                ["appointment_code", "VARCHAR", "10", "NULL", "Queue/check-in code."],
                ["patient_id", "VARCHAR", "32", "NULL, INDEX", "Associated patient system code."],
                ["station_slug", "VARCHAR", "100", "NOT NULL, INDEX", "Station slug where booking was originally routed."],
                ["station_name", "VARCHAR", "255", "NOT NULL", "Health station display name."],
                ["service_slug", "VARCHAR", "100", "NOT NULL", "Medical service program identifier."],
                ["service_name", "VARCHAR", "255", "NOT NULL", "Medical service display title."],
                ["first_name", "VARCHAR", "100", "NOT NULL", "Patient given first name."],
                ["middle_name", "VARCHAR", "100", "NULL", "Patient middle name."],
                ["last_name", "VARCHAR", "100", "NOT NULL", "Patient last name."],
                ["birth_date", "DATE", "—", "NOT NULL", "Patient date of birth."],
                ["gender", "VARCHAR", "30", "NOT NULL", "Patient biological sex."],
                ["contact_number", "VARCHAR", "20", "NOT NULL", "Patient mobile contact number."],
                ["email", "VARCHAR", "150", "NULL", "Patient email address."],
                ["complete_address", "VARCHAR", "255", "NOT NULL", "Patient residential address."],
                ["preferred_date", "DATE", "—", "NOT NULL, INDEX", "Date originally requested by patient."],
                ["preferred_time", "VARCHAR", "30", "NOT NULL", "Time window requested."],
                ["notes", "TEXT", "65535", "NULL", "Patient notes or symptoms submitted."],
                ["original_status", "VARCHAR", "30", "NOT NULL, DEFAULT 'Pending'", "Status before transition to unattended."],
                ["reason_unattended", "VARCHAR", "255", "NOT NULL, DEFAULT 'Staff unconfirmed prior to date'", "Audit reason why appointment remained unserved."],
                ["marked_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP", "Timestamp when marked unattended."],
                ["created_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP", "Creation timestamp."],
                ["updated_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP", "Last modification timestamp."]
            ]
        ),
        (
            "unattended_queue",
            "Category: Triage & Queue Monitoring | Description: Archive of confirmed appointments where the patient failed to show up at the health station on the scheduled day (no-shows).",
            [
                ["id", "INT UNSIGNED", "10", "PRIMARY KEY, AUTO_INCREMENT, NOT NULL", "Unique numerical identifier for queue no-show record."],
                ["appointment_id", "INT UNSIGNED", "10", "UNIQUE, NOT NULL", "References original record in appointments(id)."],
                ["reference_code", "VARCHAR", "20", "NOT NULL", "Appointment public tracking reference code."],
                ["appointment_code", "VARCHAR", "10", "NULL", "Queue/check-in code."],
                ["patient_id", "VARCHAR", "32", "NULL, INDEX", "Associated patient system code."],
                ["station_slug", "VARCHAR", "100", "NOT NULL, INDEX", "Station slug where patient was expected."],
                ["station_name", "VARCHAR", "255", "NOT NULL", "Health station display name."],
                ["service_slug", "VARCHAR", "100", "NOT NULL", "Medical service program identifier."],
                ["service_name", "VARCHAR", "255", "NOT NULL", "Medical service display title."],
                ["first_name", "VARCHAR", "100", "NOT NULL", "Patient given first name."],
                ["middle_name", "VARCHAR", "100", "NULL", "Patient middle name."],
                ["last_name", "VARCHAR", "100", "NOT NULL", "Patient last name."],
                ["birth_date", "DATE", "—", "NOT NULL", "Patient date of birth."],
                ["gender", "VARCHAR", "30", "NOT NULL", "Patient biological sex."],
                ["contact_number", "VARCHAR", "20", "NOT NULL", "Patient mobile contact number."],
                ["email", "VARCHAR", "150", "NULL", "Patient email address."],
                ["complete_address", "VARCHAR", "255", "NOT NULL", "Complete residence address."],
                ["preferred_date", "DATE", "—", "NOT NULL, INDEX", "Scheduled date when patient did not appear."],
                ["preferred_time", "VARCHAR", "30", "NOT NULL", "Scheduled time slot window."],
                ["photo_path", "VARCHAR", "255", "NULL", "Verification photo path."],
                ["notes", "TEXT", "65535", "NULL", "Appointment notes."],
                ["original_status", "VARCHAR", "30", "NOT NULL, DEFAULT 'Confirmed'", "Status when placed in queue."],
                ["reason_unattended", "VARCHAR", "255", "NOT NULL, DEFAULT 'Patient did not show up / Left unserved in queue'", "Audit reason why consultation was missed."],
                ["marked_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP", "Timestamp when marked as no-show."],
                ["created_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP", "Creation timestamp."],
                ["updated_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP", "Last modification timestamp."]
            ]
        ),
        (
            "password_reset_otps",
            "Category: Security & Recovery | Description: Stores temporary cryptographic one-time password (OTP) verification PINs for password resets.",
            [
                ["id", "INT UNSIGNED", "10", "PRIMARY KEY, AUTO_INCREMENT, NOT NULL", "Unique numerical identifier for the OTP entry."],
                ["role", "VARCHAR", "30", "NOT NULL, INDEX", "Target user role ('admin', 'staff', 'patient')."],
                ["email", "VARCHAR", "150", "NOT NULL, INDEX", "Email address requesting password recovery."],
                ["otp_code", "VARCHAR", "10", "NOT NULL", "6-digit cryptographic numeric PIN sent to user."],
                ["expires_at", "DATETIME", "—", "NOT NULL, INDEX", "Expiration date and time of the PIN (15-minute window)."],
                ["attempts", "INT UNSIGNED", "10", "NOT NULL, DEFAULT 0", "Counter tracking incorrect attempts (security rate-limiting)."],
                ["is_used", "TINYINT", "1", "NOT NULL, DEFAULT 0", "Consumption status (1 = Code redeemed, 0 = Active/Unused)."],
                ["created_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP", "Timestamp when OTP was generated."],
                ["updated_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP", "Timestamp when OTP attempt or status changed."]
            ]
        ),
        (
            "activity_log",
            "Category: Audit Trail | Description: System-wide audit trail recording actions taken by administrators, station staff, and automated system routines.",
            [
                ["id", "INT UNSIGNED", "10", "PRIMARY KEY, AUTO_INCREMENT, NOT NULL", "Unique numerical identifier for the audit log entry."],
                ["user_type", "VARCHAR", "30", "NOT NULL, INDEX", "Category of actor ('admin', 'staff', 'patient', 'system')."],
                ["user_name", "VARCHAR", "150", "NOT NULL", "Display name or email of user who performed the action."],
                ["action", "VARCHAR", "100", "NOT NULL", "Action identifier (e.g., 'Confirm Appointment', 'Update Profile')."],
                ["description", "TEXT", "65535", "NULL", "Detailed narrative or contextual data of the event."],
                ["created_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP, INDEX", "Exact date and time when the event was performed."]
            ]
        )
    ]

    # Column widths (Total = 6.9 inches)
    # Field Name: 1.35 in, Data Type: 0.95 in, Length: 0.75 in, Constraint: 1.65 in, Description: 2.20 in
    col_widths = [Inches(1.35), Inches(0.95), Inches(0.75), Inches(1.65), Inches(2.20)]
    headers = ["Field Name", "Data Type", "Field Length", "Constraint", "Description"]

    for idx, (tbl_name, meta, cols) in enumerate(tables_data, 1):
        th = doc.add_heading(f"2.{idx} Table: {tbl_name}", level=2)
        th.style.font.color.rgb = RGBColor(0x0F, 0x17, 0x2A)
        th.paragraph_format.space_before = Pt(14)
        th.paragraph_format.space_after = Pt(3)

        mp = doc.add_paragraph()
        mp.paragraph_format.space_after = Pt(6)
        mr = mp.add_run(meta)
        mr.font.size = Pt(9.5)
        mr.font.italic = True
        mr.font.color.rgb = RGBColor(0x47, 0x55, 0x69)

        table = doc.add_table(rows=len(cols) + 1, cols=5)
        table.alignment = WD_TABLE_ALIGNMENT.CENTER
        table.autofit = False
        set_table_borders(table)

        # Style Header Row
        hdr_row = table.rows[0]
        make_row_cant_split(hdr_row)
        set_repeat_header(hdr_row)
        for col_idx, h_text in enumerate(headers):
            c = hdr_row.cells[col_idx]
            c.width = col_widths[col_idx]
            set_cell_background(c, "1E293B") # Navy Slate
            set_cell_margins(c, top=120, bottom=120, left=120, right=120)
            p = c.paragraphs[0]
            p.alignment = WD_ALIGN_PARAGRAPH.LEFT
            p.paragraph_format.space_after = Pt(0)
            run = p.add_run(h_text)
            run.font.bold = True
            run.font.size = Pt(9.5)
            run.font.color.rgb = RGBColor(0xFF, 0xFF, 0xFF)

        # Populate Data Rows
        for r_idx, col_data in enumerate(cols, 1):
            row = table.rows[r_idx]
            make_row_cant_split(row)
            bg_color = "F8FAFC" if (r_idx % 2 == 0) else "FFFFFF"

            for c_idx, val in enumerate(col_data):
                c = row.cells[c_idx]
                c.width = col_widths[c_idx]
                set_cell_background(c, bg_color)
                set_cell_margins(c, top=80, bottom=80, left=120, right=120)
                p = c.paragraphs[0]
                p.paragraph_format.space_after = Pt(0)
                run = p.add_run(val)
                run.font.size = Pt(9)
                if c_idx == 0:
                    run.font.bold = True
                    run.font.color.rgb = RGBColor(0x0F, 0x17, 0x2A)
                elif c_idx == 3:
                    # Highlight Primary and Foreign Keys
                    if "PRIMARY KEY" in val:
                        run.font.bold = True
                        run.font.color.rgb = RGBColor(0x03, 0x69, 0xA1)
                    elif "FOREIGN KEY" in val:
                        run.font.bold = True
                        run.font.color.rgb = RGBColor(0x0D, 0x94, 0x88)
                    else:
                        run.font.color.rgb = RGBColor(0x33, 0x41, 0x55)
                else:
                    run.font.color.rgb = RGBColor(0x33, 0x41, 0x55)

        doc.add_paragraph() # Spacer between tables

    # Section 3: Corrected Conceptual Relational Model (Draft Revision)
    doc.add_page_break()
    h3 = doc.add_heading("3. Corrected Conceptual Draft Tables (Academic Normalized Model)", level=1)
    h3.style.font.color.rgb = RGBColor(0x0F, 0x17, 0x2A)
    h3.paragraph_format.space_before = Pt(10)
    h3.paragraph_format.space_after = Pt(6)

    p3 = doc.add_paragraph(
        "If your capstone adviser or panel requires you to submit the normalized relational model (ERD) from your initial screenshots, the following tables provide the mathematically correct, normalized definitions with all foreign keys properly aligned:"
    )
    p3.paragraph_format.space_after = Pt(10)

    conceptual_tables = [
        (
            "BARANGAY_HEALTH_STATION",
            "Source: Corrected from Draft Image 1 & 2 | Description: Represents the physical health station facility within a specific barangay jurisdiction.",
            [
                ["barangay_id", "INT", "11", "PRIMARY KEY, AUTO_INCREMENT, NOT NULL", "Unique numerical identifier for barangay health station."],
                ["barangay_name", "VARCHAR", "100", "NOT NULL", "Official name of the barangay jurisdiction."],
                ["address", "VARCHAR", "255", "NOT NULL", "Physical location/address of the health station facility."],
                ["contact_number", "VARCHAR", "20", "NULL", "Official telephone or mobile contact number."],
                ["operating_hours", "VARCHAR", "100", "NOT NULL, DEFAULT '8:00 AM - 5:00 PM'", "Standard station operating days and hours."],
                ["admin_id", "INT", "11", "FOREIGN KEY, NOT NULL", "References ADMIN(admin_id)."]
            ]
        ),
        (
            "HEALTH_SERVICE",
            "Source: Corrected from Draft Image 4 | Description: Catalog of healthcare programs and medical services offered across the barangay delivery network.",
            [
                ["service_id", "INT", "11", "PRIMARY KEY, AUTO_INCREMENT, NOT NULL", "Unique numerical identifier for the healthcare service."],
                ["service_name", "VARCHAR", "100", "NOT NULL", "Name of healthcare service (e.g., Pre-Natal Care, Immunization)."],
                ["description", "TEXT", "65535", "NOT NULL", "Comprehensive clinical description of the healthcare service."],
                ["duration", "VARCHAR", "30", "NULL", "Typical consultation processing duration (e.g., '30-45 mins')."],
                ["admin_id", "INT", "11", "FOREIGN KEY, NOT NULL", "References ADMIN(admin_id)."]
            ]
        ),
        (
            "APPOINTMENT_LIST",
            "Source: Corrected from Draft Image 3 | Description: Represents the appointment application form submitted by the client.",
            [
                ["appointment_list_id", "INT", "11", "PRIMARY KEY, AUTO_INCREMENT, NOT NULL", "Unique identifier for the appointment application form."],
                ["reference_code", "VARCHAR", "32", "UNIQUE, NOT NULL", "Public tracking reference number for patient verification."],
                ["client_id", "INT", "11", "FOREIGN KEY, NOT NULL", "References CLIENT(client_id)."],
                ["service_id", "INT", "11", "FOREIGN KEY, NOT NULL", "References HEALTH_SERVICE(service_id)."],
                ["barangay_id", "INT", "11", "FOREIGN KEY, NOT NULL", "References BARANGAY_HEALTH_STATION(barangay_id)."],
                ["appointment_date", "DATE", "—", "NOT NULL", "Scheduled date requested for the health consultation."],
                ["appointment_time", "VARCHAR", "30", "NOT NULL", "Scheduled time slot window (e.g., '8:00 AM - 11:30 AM')."],
                ["appointment_details", "TEXT", "65535", "NULL", "Patient chief complaint, symptoms, or special remarks."],
                ["status", "VARCHAR", "20", "NOT NULL, DEFAULT 'Pending'", "Current booking status (Pending, Confirmed, Cancelled, Completed)."],
                ["created_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP", "Date and time when the booking form was submitted."]
            ]
        ),
        (
            "APPOINTMENT_RECORD",
            "Source: Corrected from Draft Image 5 | Description: Stores consultation outcomes, vital signs triage, and clinical diagnoses recorded by medical staff.",
            [
                ["record_id", "INT", "11", "PRIMARY KEY, AUTO_INCREMENT, NOT NULL", "Unique numerical identifier for clinical consultation record."],
                ["appointment_list_id", "INT", "11", "FOREIGN KEY, UNIQUE, NOT NULL", "References APPOINTMENT_LIST(appointment_list_id)."],
                ["client_id", "INT", "11", "FOREIGN KEY, NOT NULL", "References CLIENT(client_id)."],
                ["body_temperature", "VARCHAR", "20", "NULL", "Triage measurement: Body temperature in °C."],
                ["blood_pressure", "VARCHAR", "20", "NULL", "Triage measurement: Blood pressure reading (e.g., '120/80')."],
                ["pulse_rate", "VARCHAR", "20", "NULL", "Triage measurement: Heart/pulse rate in beats per minute."],
                ["respiration_rate", "VARCHAR", "20", "NULL", "Triage measurement: Respiratory rate in breaths per minute."],
                ["diagnosis_notes", "TEXT", "65535", "NULL", "Clinical diagnosis, physician findings, and assessment."],
                ["treatment_prescribed", "TEXT", "65535", "NULL", "Prescribed medications, dosage instructions, or referral advice."],
                ["attended_by", "VARCHAR", "150", "NOT NULL", "Name of healthcare worker or physician who served the client."],
                ["recorded_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP", "Date and time when the medical record was finalized."]
            ]
        ),
        (
            "CLIENT",
            "Source: Conceptual Entity | Description: Master entity for resident patients seeking barangay healthcare consultations.",
            [
                ["client_id", "INT", "11", "PRIMARY KEY, AUTO_INCREMENT, NOT NULL", "Unique numerical identifier for the client/patient."],
                ["first_name", "VARCHAR", "100", "NOT NULL", "Given first name of the client."],
                ["middle_name", "VARCHAR", "100", "NULL", "Middle name of the client."],
                ["last_name", "VARCHAR", "100", "NOT NULL", "Surname / last name of the client."],
                ["birth_date", "DATE", "—", "NOT NULL", "Date of birth of the client."],
                ["gender", "VARCHAR", "20", "NOT NULL", "Biological sex / gender of the client."],
                ["contact_number", "VARCHAR", "20", "NOT NULL", "Primary mobile contact telephone number."],
                ["email", "VARCHAR", "150", "UNIQUE, NULL", "Email address for digital appointment alerts."],
                ["address", "VARCHAR", "255", "NOT NULL", "Complete residential address."],
                ["barangay_id", "INT", "11", "FOREIGN KEY, NOT NULL", "References BARANGAY_HEALTH_STATION(barangay_id)."],
                ["created_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP", "Timestamp when client record was registered."]
            ]
        ),
        (
            "ADMIN",
            "Source: Conceptual Entity | Description: System administrator entity responsible for governing health station policies and accounts.",
            [
                ["admin_id", "INT", "11", "PRIMARY KEY, AUTO_INCREMENT, NOT NULL", "Unique numerical identifier for system administrator."],
                ["admin_name", "VARCHAR", "150", "NOT NULL", "Full legal name of the administrator."],
                ["office_name", "VARCHAR", "255", "NOT NULL", "Governing health office designation."],
                ["email", "VARCHAR", "150", "UNIQUE, NOT NULL", "Email address used for administrative authentication."],
                ["password", "VARCHAR", "255", "NOT NULL", "Securely hashed login password."],
                ["contact_number", "VARCHAR", "20", "NULL", "Contact mobile telephone number."],
                ["created_at", "TIMESTAMP", "—", "NOT NULL, DEFAULT CURRENT_TIMESTAMP", "Timestamp when administrator was registered."]
            ]
        )
    ]

    for idx, (tbl_name, meta, cols) in enumerate(conceptual_tables, 1):
        th = doc.add_heading(f"3.{idx} Conceptual Table: {tbl_name}", level=2)
        th.style.font.color.rgb = RGBColor(0x0F, 0x17, 0x2A)
        th.paragraph_format.space_before = Pt(14)
        th.paragraph_format.space_after = Pt(3)

        mp = doc.add_paragraph()
        mp.paragraph_format.space_after = Pt(6)
        mr = mp.add_run(meta)
        mr.font.size = Pt(9.5)
        mr.font.italic = True
        mr.font.color.rgb = RGBColor(0x47, 0x55, 0x69)

        table = doc.add_table(rows=len(cols) + 1, cols=5)
        table.alignment = WD_TABLE_ALIGNMENT.CENTER
        table.autofit = False
        set_table_borders(table)

        # Header Row
        hdr_row = table.rows[0]
        make_row_cant_split(hdr_row)
        set_repeat_header(hdr_row)
        for col_idx, h_text in enumerate(headers):
            c = hdr_row.cells[col_idx]
            c.width = col_widths[col_idx]
            set_cell_background(c, "0F766E") # Teal / Emerald Dark
            set_cell_margins(c, top=120, bottom=120, left=120, right=120)
            p = c.paragraphs[0]
            p.paragraph_format.space_after = Pt(0)
            run = p.add_run(h_text)
            run.font.bold = True
            run.font.size = Pt(9.5)
            run.font.color.rgb = RGBColor(0xFF, 0xFF, 0xFF)

        # Data Rows
        for r_idx, col_data in enumerate(cols, 1):
            row = table.rows[r_idx]
            make_row_cant_split(row)
            bg_color = "F0FDFA" if (r_idx % 2 == 0) else "FFFFFF"

            for c_idx, val in enumerate(col_data):
                c = row.cells[c_idx]
                c.width = col_widths[c_idx]
                set_cell_background(c, bg_color)
                set_cell_margins(c, top=80, bottom=80, left=120, right=120)
                p = c.paragraphs[0]
                p.paragraph_format.space_after = Pt(0)
                run = p.add_run(val)
                run.font.size = Pt(9)
                if c_idx == 0:
                    run.font.bold = True
                    run.font.color.rgb = RGBColor(0x0F, 0x17, 0x2A)
                elif c_idx == 3:
                    if "PRIMARY KEY" in val:
                        run.font.bold = True
                        run.font.color.rgb = RGBColor(0x0F, 0x76, 0x6E)
                    elif "FOREIGN KEY" in val:
                        run.font.bold = True
                        run.font.color.rgb = RGBColor(0x03, 0x69, 0xA1)
                    else:
                        run.font.color.rgb = RGBColor(0x33, 0x41, 0x55)
                else:
                    run.font.color.rgb = RGBColor(0x33, 0x41, 0x55)

        doc.add_paragraph()

    # Save to Word Document File
    output_path = "c:\\xampp\\htdocs\\Health-Delivery-System-Latest\\Health_Delivery_System_Data_Dictionary.docx"
    doc.save(output_path)
    print(f"Successfully generated Word document at: {output_path}")

if __name__ == "__main__":
    build_data_dictionary()
