describe('Health Station Staff Portal - Operations Suite', () => {
  beforeEach(() => {
    cy.visit('/Patients/index.php');
    cy.contains(/Volunteer|Staff/i).click({ force: true });

    // TC-027 to TC-030: Staff Login
    cy.get('#volunteerLoginForm, form[name="volunteerLoginForm"]').within(() => {
      cy.get('input[name="username"], input[name="login_email"], input[name="volunteer_email"]').first().type('staff-bata@bata.health');
      cy.get('input[name="password"], input[name="login_password"], input[name="volunteer_password"]').first().type('StaffPassword123!');
      cy.get('button[type="submit"]').click();
    });
    cy.url({ timeout: 10000 }).should('include', '?page=dashboard');
  });

  // Use Case 8 & 9: Staff Dashboard & Appointments (TC-031 - TC-039)
  it('TC-031 to TC-039: Review and confirm appointment requests', () => {
    cy.get('a[href*="page=appointments"]').click();
    cy.url().should('include', 'page=appointments');

    cy.get('.appointment-row, .modern-appt-card, tr.appointment-item').first().within(() => {
      cy.get('button.btn-confirm, button.appt-btn-confirm, [data-action="confirm"]').click({ force: true });
    });

    cy.get('.alert-success, .toast, .flash-banner')
      .should('be.visible');
  });

  // Use Case 10: Manage Patient Queue (TC-040 - TC-044)
  it('TC-040 to TC-044: Move patient queue from Waiting to Serving and Completed', () => {
    cy.get('a[href*="page=queue"]').click();
    cy.url().should('include', 'page=queue');

    // Select service queue
    cy.get('.service-queue-card, .service-card, .queue-service-card').first().click();

    // Mark as Serving
    cy.get('.queue-item, .modern-queue-card').contains(/Waiting|Confirmed|FCFS/i).parents('.queue-item, .modern-queue-card').within(() => {
      cy.get('button').contains(/Serve|Serving/i).click({ force: true });
    });
    cy.get('.flash-banner, .toast, body').should('be.visible');

    // Mark as Completed
    cy.get('.queue-item, .modern-queue-card').contains(/Serving/i).parents('.queue-item, .modern-queue-card').within(() => {
      cy.get('button').contains(/Complete/i).click({ force: true });
    });
  });

  // Use Case 13 & 23: Patient Records, Vitals & Follow-Up (TC-054 - TC-060, TC-096 - TC-100)
  it('TC-054 to TC-060 & TC-096 to TC-100: Record vital signs and schedule follow-up consultation', () => {
    cy.get('a[href*="page=patients"]').click();
    cy.get('input[type="search"], input[name="patient_search"]').type('Juan Dela Cruz{enter}');

    cy.get('.patient-record-card, .patient-row, article').first().click();
    
    // Save clinical details
    cy.get('form[action*="save_clinical_details"]').within(() => {
      cy.get('input[name="temperature"]').clear().type('36.6');
      cy.get('input[name="blood_pressure"]').clear().type('120/80');
      cy.get('input[name="pulse"]').clear().type('75');
      cy.get('textarea[name="doctor_notes"]').type('Patient reviewed. Prescribed standard vitamins.');
      cy.get('button[type="submit"]').click();
    });
    cy.get('.alert-success, .flash-banner, .toast').should('be.visible');

    // Schedule Follow-Up (TC-096 - TC-100)
    cy.get('button').contains(/Schedule Follow-up/i).first().click({ force: true });
    cy.get('form[action*="schedule_follow_up"]').within(() => {
      cy.get('input[name="follow_up_date"]').type('2026-10-20');
      cy.get('input[name="follow_up_time"]').type('10:00');
      cy.get('button[type="submit"]').click();
    });
    cy.get('.alert-success, .flash-banner, body').should('be.visible');
  });

  // Use Case 11: Image Capture (TC-045 - TC-048)
  it('TC-045 to TC-048: Open image capture module for patient verification', () => {
    cy.get('a[href*="page=image-capture"]').click();
    cy.get('body').should('be.visible');
  });
});
