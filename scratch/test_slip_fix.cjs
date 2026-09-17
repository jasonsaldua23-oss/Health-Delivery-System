const assert = require('assert');

// The helper function extracted from Patients/dashboard.php
function isAppointmentImmunization(appt) {
    if (!appt) return false;
    if (appt.is_immunization === true || appt.is_immunization === 1 || appt.is_immunization === '1' || appt.is_immunization === 'true') {
        return true;
    }
    const serviceSlug = (appt.service_slug || '').toLowerCase().trim();
    const serviceName = (appt.service_name || '').toLowerCase().trim();
    return serviceSlug === 'immunization'
        || serviceSlug === 'vaccination'
        || serviceSlug === 'flu'
        || serviceSlug === 'covid-vaccine'
        || serviceSlug === 'vaccine'
        || serviceSlug.includes('immuniz')
        || serviceSlug.includes('vaccin')
        || serviceName.includes('immuniz')
        || serviceName.includes('vaccin');
}

// Mock DOM elements
class MockElement {
    constructor() {
        this.style = { display: '' };
        this.classList = {
            classes: new Set(),
            add(c) { this.classes.add(c); },
            remove(c) { this.classes.delete(c); },
            contains(c) { return this.classes.has(c); }
        };
        this.textContent = '';
        this.innerHTML = '';
    }
}

function simulateModalOpen(appt) {
    const elements = {
        slipModalRecipientNameRow: new MockElement(),
        slipModalRecipientName: new MockElement(),
        slipModalRelationshipRow: new MockElement(),
        slipModalRelationship: new MockElement(),
        slipModalRecipientDobRow: new MockElement(),
        slipModalRecipientDob: new MockElement(),
        slipModalVaccineRow: new MockElement(),
        slipModalVaccine: new MockElement(),
        slipModalNameLabel: new MockElement(),
        slipModalName: new MockElement()
    };

    const patientFullName = [appt.first_name || '', appt.middle_name || '', appt.last_name || ''].filter(Boolean).join(' ') || 'Patient';
    elements.slipModalName.textContent = patientFullName;

    const isImmunization = isAppointmentImmunization(appt);

    const recNameRow = elements.slipModalRecipientNameRow;
    const recNameEl = elements.slipModalRecipientName;
    const relRow = elements.slipModalRelationshipRow;
    const relEl = elements.slipModalRelationship;
    const recDobRow = elements.slipModalRecipientDobRow;
    const recDobEl = elements.slipModalRecipientDob;
    const vaccineRow = elements.slipModalVaccineRow;
    const vaccineEl = elements.slipModalVaccine;
    const nameLabelEl = elements.slipModalNameLabel;

    if (isImmunization) {
        const recipientFirst = (appt.recipient_first_name || '').trim();
        const recipientMiddle = (appt.recipient_middle_name || '').trim();
        const recipientLast = (appt.recipient_last_name || '').trim();
        const recipientDob = (appt.recipient_birth_date || '').trim();
        const rawRel = (appt.immunization_relationship || appt.relationship || appt.recipient_relationship || appt.recipient_rel || '').trim();
        const hasExplicitRecipient = Boolean(recipientFirst && recipientLast);

        let relationship = rawRel;
        if (!relationship) {
            relationship = hasExplicitRecipient ? 'Child' : 'Self';
        }

        let recipientFullName = (appt.recipient_full_name || '').trim() || [recipientFirst, recipientMiddle, recipientLast].filter(Boolean).join(' ');
        if (!recipientFullName) {
            recipientFullName = patientFullName;
        }

        if (recNameRow && recNameEl) {
            recNameEl.textContent = recipientFullName;
            recNameRow.style.display = '';
            recNameRow.classList.remove('slip-row-hidden');
        }
        if (relRow && relEl) {
            relEl.textContent = relationship || 'Self';
            relRow.style.display = '';
            relRow.classList.remove('slip-row-hidden');
        }
        if (recDobRow && recDobEl) {
            if (recipientDob && recipientDob !== '0000-00-00') {
                recDobEl.textContent = recipientDob;
                recDobRow.style.display = '';
                recDobRow.classList.remove('slip-row-hidden');
            } else {
                recDobRow.style.display = 'none';
                recDobRow.classList.add('slip-row-hidden');
            }
        }
        if (nameLabelEl) {
            nameLabelEl.textContent = 'Booked By (Account Holder)';
        }

        if (vaccineRow && vaccineEl) {
            vaccineEl.textContent = appt.vaccine_type ? appt.vaccine_type : 'Pending staff vitals encoding upon arrival';
            vaccineRow.style.display = '';
            vaccineRow.classList.remove('slip-row-hidden');
        }
    } else {
        if (recNameRow) { recNameRow.style.display = 'none'; recNameRow.classList.add('slip-row-hidden'); }
        if (relRow) { relRow.style.display = 'none'; relRow.classList.add('slip-row-hidden'); }
        if (recDobRow) { recDobRow.style.display = 'none'; recDobRow.classList.add('slip-row-hidden'); }
        if (vaccineRow) { vaccineRow.style.display = 'none'; vaccineRow.classList.add('slip-row-hidden'); }
        if (nameLabelEl) {
            nameLabelEl.textContent = 'Patient Name';
        }
    }

    return elements;
}

console.log('Testing appointment slip logic...\n');

// Test Case 1: TB DOTS appointment (matches user screenshot)
const tbAppt = {
    appointment_code: 'D6MAZNKW',
    service_name: 'TB DOTS',
    service_slug: 'tb-dots',
    first_name: 'Leo',
    middle_name: 'Taboclaon',
    last_name: 'Zacarias',
    recipient_first_name: 'Leo',
    recipient_last_name: 'Zacarias',
    recipient_full_name: 'Leo Taboclaon Zacarias',
    recipient_birth_date: '2002-11-17',
    relationship: 'Self',
    is_immunization: false,
    status: 'Completed'
};

const tbResult = simulateModalOpen(tbAppt);
assert.strictEqual(isAppointmentImmunization(tbAppt), false, 'TB DOTS should not be immunization');
assert.strictEqual(tbResult.slipModalRecipientNameRow.style.display, 'none', 'TB DOTS: Recipient Name row must be hidden');
assert.strictEqual(tbResult.slipModalRelationshipRow.style.display, 'none', 'TB DOTS: Relationship row must be hidden');
assert.strictEqual(tbResult.slipModalRecipientDobRow.style.display, 'none', 'TB DOTS: Recipient DOB row must be hidden');
assert.strictEqual(tbResult.slipModalVaccineRow.style.display, 'none', 'TB DOTS: Vaccine row must be hidden');
assert.strictEqual(tbResult.slipModalNameLabel.textContent, 'Patient Name', 'TB DOTS: Label should be Patient Name');
assert.strictEqual(tbResult.slipModalName.textContent, 'Leo Taboclaon Zacarias', 'TB DOTS: Patient name displayed correctly');
console.log('✔ Test Case 1 Passed: TB DOTS slip strictly hides recipient details and shows Patient Name');

// Test Case 2: General Consultation
const genAppt = {
    appointment_code: 'GEN12345',
    service_name: 'General Consultation',
    service_slug: 'general-consultation',
    first_name: 'Maria',
    last_name: 'Cruz',
    recipient_first_name: 'Maria',
    recipient_last_name: 'Cruz',
    is_immunization: false,
    status: 'Confirmed'
};

const genResult = simulateModalOpen(genAppt);
assert.strictEqual(isAppointmentImmunization(genAppt), false);
assert.strictEqual(genResult.slipModalRecipientNameRow.style.display, 'none');
assert.strictEqual(genResult.slipModalRelationshipRow.style.display, 'none');
assert.strictEqual(genResult.slipModalRecipientDobRow.style.display, 'none');
assert.strictEqual(genResult.slipModalNameLabel.textContent, 'Patient Name');
console.log('✔ Test Case 2 Passed: General Consultation strictly hides recipient details');

// Test Case 3: Immunization Appointment for Child
const immuChildAppt = {
    appointment_code: 'IMM98765',
    service_name: 'Child Immunization',
    service_slug: 'immunization',
    first_name: 'Juan',
    last_name: 'Dela Cruz',
    recipient_first_name: 'Baby',
    recipient_last_name: 'Dela Cruz',
    recipient_full_name: 'Baby Dela Cruz',
    recipient_birth_date: '2026-01-01',
    relationship: 'Parent',
    vaccine_type: 'Pentavalent',
    is_immunization: true,
    status: 'Pending'
};

const immuChildResult = simulateModalOpen(immuChildAppt);
assert.strictEqual(isAppointmentImmunization(immuChildAppt), true);
assert.strictEqual(immuChildResult.slipModalRecipientNameRow.style.display, '');
assert.strictEqual(immuChildResult.slipModalRecipientName.textContent, 'Baby Dela Cruz');
assert.strictEqual(immuChildResult.slipModalRelationshipRow.style.display, '');
assert.strictEqual(immuChildResult.slipModalRelationship.textContent, 'Parent');
assert.strictEqual(immuChildResult.slipModalRecipientDobRow.style.display, '');
assert.strictEqual(immuChildResult.slipModalVaccineRow.style.display, '');
assert.strictEqual(immuChildResult.slipModalVaccine.textContent, 'Pentavalent');
assert.strictEqual(immuChildResult.slipModalNameLabel.textContent, 'Booked By (Account Holder)');
assert.strictEqual(immuChildResult.slipModalName.textContent, 'Juan Dela Cruz');
console.log('✔ Test Case 3 Passed: Child Immunization slip shows full recipient details and Booked By label');

// Test Case 4: Immunization Appointment for Self
const immuSelfAppt = {
    appointment_code: 'FLU11223',
    service_name: 'Flu Vaccination',
    service_slug: 'flu',
    first_name: 'Juan',
    last_name: 'Dela Cruz',
    relationship: 'Self',
    vaccine_type: 'Influenza',
    is_immunization: true,
    status: 'Confirmed'
};

const immuSelfResult = simulateModalOpen(immuSelfAppt);
assert.strictEqual(isAppointmentImmunization(immuSelfAppt), true);
assert.strictEqual(immuSelfResult.slipModalRecipientNameRow.style.display, '');
assert.strictEqual(immuSelfResult.slipModalRecipientName.textContent, 'Juan Dela Cruz');
assert.strictEqual(immuSelfResult.slipModalRelationshipRow.style.display, '');
assert.strictEqual(immuSelfResult.slipModalRelationship.textContent, 'Self');
assert.strictEqual(immuSelfResult.slipModalVaccineRow.style.display, '');
assert.strictEqual(immuSelfResult.slipModalNameLabel.textContent, 'Booked By (Account Holder)');
console.log('✔ Test Case 4 Passed: Self Immunization slip shows recipient details as Self');

console.log('\nAll slip logic tests passed successfully!');
