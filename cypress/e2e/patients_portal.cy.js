describe('Patient Portal - End-to-End Suite', () => {
  const patientData = {
    firstName: 'Juan',
    lastName: 'Dela Cruz',
    birthDate: '1995-05-15',
    gender: 'Male',
    contactNumber: '09123456789',
    purok: 'Purok 3',
    barangay: 'Alijis',
    password: 'Password123!',
    updatedPhone: '09987654321'
  };

  const dynamicEmail = `juan_${Date.now()}@example.com`;

  beforeEach(() => {
    cy.visit('/Patients/index.php');
  });

  // Use Case 1 & 2: Landing Page & Registration (TC-001 - TC-008)
  it('TC-005 to TC-008: Register as a first-time patient', () => {
    // Click Patient portal card
    cy.get('.portal-card[data-portal="patient"]').scrollIntoView().click({ force: true });
    cy.get('#patientModal').should('be.visible');

    // Select First Timer step
    cy.get('.modal-choice-button.first-timer').click();
    cy.get('#firstTimerStep').should('be.visible');

    // Fill registration form
    cy.get('#firstTimerForm').within(() => {
      cy.get('input[name="first_name"]').type(patientData.firstName);
      cy.get('input[name="last_name"]').type(patientData.lastName);
      cy.get('input[name="birthdate"]').type(patientData.birthDate);
      cy.get('input[name="gender"][value="Male"]').check({ force: true });
      cy.get('input[name="phone"]').type(patientData.contactNumber);
      cy.get('select[name="barangay"]').select(patientData.barangay);
      cy.get('input[name="purok"]').type(patientData.purok);
      cy.get('input[name="street"]').type('123 Main St');
      cy.get('input[name="email"]').type(dynamicEmail);
      cy.get('input[name="password"]').type(patientData.password);
      cy.get('button[type="submit"]').click();
    });

    // Verification of redirect to patient dashboard
    cy.url({ timeout: 10000 }).should('include', 'dashboard.php');
    cy.contains(patientData.firstName).should('be.visible');
  });

  // Use Case 3 & 4: Patient Login & Dashboard View (TC-009 - TC-017)
  it('TC-009 to TC-017: Log in existing patient and view dashboard metrics', () => {
    // Click Patient portal card
    cy.get('.portal-card[data-portal="patient"]').scrollIntoView().click({ force: true });
    cy.get('#patientModal').should('be.visible');

    // Select Existing Patient / Login step
    cy.get('.modal-choice-button.existing-patient').click();
    cy.get('#loginStep').should('be.visible');

    // Submit login modal using known account
    cy.get('#loginForm').within(() => {
      cy.get('input[name="login_email"]').type('juan.delacruz@gmail.com');
      cy.get('input[name="login_password"]').type('patient123');
      cy.get('button[type="submit"]').click();
    });

    cy.url({ timeout: 10000 }).should('include', 'dashboard.php');
    cy.get('#servicesSection').should('exist');
  });

  // Use Case 5: Book an Appointment (TC-018 - TC-021)
  it('TC-018 to TC-021: Book a health service appointment', () => {
    // Navigate directly to service booking form for Alijis Consultation
    cy.visit('/Patients/index.php?barangay=alijis&service=consultation');
    cy.get('#bookingForm').should('be.visible');
    cy.get('.schedule-picker').should('be.visible');
    cy.get('#scheduleCalendarGrid').should('exist');
  });

  // Use Case 6: Update Patient Profile (TC-022 - TC-026)
  it('TC-022 to TC-026: Update profile information and contact details', () => {
    // Log in first to access dashboard profile
    cy.get('.portal-card[data-portal="patient"]').scrollIntoView().click({ force: true });
    cy.get('.modal-choice-button.existing-patient').click();
    cy.get('#loginForm').within(() => {
      cy.get('input[name="login_email"]').type('juan.delacruz@gmail.com');
      cy.get('input[name="login_password"]').type('patient123');
      cy.get('button[type="submit"]').click();
    });

    cy.url({ timeout: 10000 }).should('include', 'dashboard.php');

    // Open account modal
    cy.get('#openAccountModalBtn').click();
    cy.get('#accountModal').should('have.class', 'active');

    // Update contact number
    cy.get('#accountForm').should('be.visible').within(() => {
      cy.get('#accPhone').clear().type(patientData.updatedPhone);
      cy.get('button[type="submit"]').click();
    });

    // Verification
    cy.url({ timeout: 10000 }).should('include', 'dashboard.php');
  });
});
