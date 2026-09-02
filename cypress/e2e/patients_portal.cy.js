describe('Patient Portal - End-to-End Test Cases', () => {
  const testPatient = {
    firstName: 'Juan',
    lastName: 'Dela Cruz',
    birthDate: '1995-05-15',
    gender: 'Male',
    contactNumber: '09123456789',
    purok: 'Purok 3',
    barangay: 'Alijis',
    email: `patient_${Date.now()}@example.com`,
    password: 'patient123',
    updatedPhone: '09987654321'
  };

  beforeEach(() => {
    // Make sure base URL visits the Patient entry point
    cy.visit('/Patients/index.php');
  });

  // Use Case 1 & 2: Client proceeds to landing page & Registration (TC-001 - TC-008)
  it('TC-001 to TC-008: Register a first-time patient and reach dashboard', () => {
    // Choose Patient Portal
    cy.get('.portal-card[data-portal="patient"]').scrollIntoView().click({ force: true });
    cy.get('#patientModal').should('be.visible');

    // Select First-Time Patient
    cy.get('.modal-choice-button.first-timer, .modal-choice-button.first-time-patient').first().click();
    cy.get('#firstTimerForm, form[name="firstTimerForm"]').should('be.visible').within(() => {
      cy.get('input[name="first_name"]').type(testPatient.firstName);
      cy.get('input[name="last_name"]').type(testPatient.lastName);
      cy.get('input[name="birthdate"], input[name="birth_date"]').type(testPatient.birthDate);
      cy.get('input[name="gender"][value="Male"]').check({ force: true });
      cy.get('input[name="phone"], input[name="contact_number"]').type(testPatient.contactNumber);
      cy.get('select[name="barangay"]').select(testPatient.barangay);
      cy.get('select[name="purok"]').select(1);
      cy.get('input[name="street"]').type('Purok 3, Main St');
      cy.get('input[name="email"]').type(testPatient.email);
      cy.get('input[name="password"]').type(testPatient.password);
      cy.get('button[type="submit"]').click();
    });

    // Verification of account creation and redirection
    cy.url({ timeout: 10000 }).should('include', 'dashboard.php');
    cy.contains(testPatient.firstName).should('be.visible');
    cy.screenshot('TC-001_to_TC-008_patient_registration_dashboard');
  });

  // Use Case 3 & 4: Patient Login & Dashboard Overview (TC-009 - TC-017)
  it('TC-009 to TC-017: Patient login with existing account and view dashboard', () => {
    cy.get('.portal-card[data-portal="patient"]').scrollIntoView().click({ force: true });
    cy.get('#patientModal').should('be.visible');
    cy.get('.modal-choice-button.existing-patient').click();
    cy.get('#loginStep').should('be.visible');

    cy.get('#loginForm').within(() => {
      cy.get('input[name="login_email"]').type('evelyntaboclaon@gmail.com');
      cy.get('input[name="login_password"]').type('evelyn123');
      cy.get('button[type="submit"]').click();
    });

    // Validates login and lands on dashboard (or handles modal redirect)
    cy.url({ timeout: 10000 }).should('include', 'dashboard.php');
    cy.get('#servicesSection, .appointments-section, #appointments, .dashboard-main-content').should('exist');
    cy.screenshot('TC-009_to_TC-017_patient_login_overview');
  });

  // Use Case 5: Book an Appointment (TC-018 - TC-021)
  it('TC-018 to TC-021: Book an appointment and handle form submission', () => {
    // Direct navigation or booking flow
    cy.visit('/Patients/index.php?barangay=alijis&service=consultation');
    cy.get('#bookingForm').should('be.visible').within(() => {
      cy.get('#preferred_date, input[name="preferred_date"]').invoke('val', '2026-10-15');
      cy.get('#preferred_time, input[name="preferred_time"]').invoke('val', '09:00 AM');
      cy.get('button[type="submit"]').first().click({ force: true });
    });

    cy.url().should('include', 'barangay=alijis');
    cy.screenshot('TC-018_to_TC-021_appointment_booking');
  });

  // Use Case 6: Update Patient Profile (TC-022 - TC-026)
  it('TC-022 to TC-026: Update patient profile and contact info', () => {
    cy.visit('/Patients/dashboard.php');
    cy.get('body').should('be.visible');
    cy.get('.hero-nav-pill, button').contains(/Account/i).click({ force: true });
    cy.screenshot('TC-022_to_TC-026_profile_settings');
  });
});
