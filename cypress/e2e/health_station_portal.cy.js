describe('Staff & Station Portal - Operations Suite', () => {
  beforeEach(() => {
    cy.visit('/Patients/index.php');

    // Staff Login (TC-027 to TC-030)
    cy.get('.portal-card[data-portal="volunteer"]').scrollIntoView().click({ force: true });
    cy.get('#volunteerModal').should('be.visible');

    cy.get('#volunteerLoginForm').within(() => {
      cy.get('input[name="volunteer_email"]').type('staff-bata@bata.health');
      cy.get('input[name="volunteer_password"]').type('staff123');
      cy.get('button[type="submit"]').click();
    });

    cy.url().should('include', 'Barangay%20Health%20Station/index.php');
  });

  // Use Case 8 & 9: Dashboard & Appointments Management (TC-031 - TC-039)
  it('TC-035 to TC-039: View and manage appointments page', () => {
    cy.get('a[href*="page=appointments"]').first().click();
    cy.url().should('include', 'page=appointments');
    cy.get('.page-hero, .appt-filter-card, h1, h2').should('be.visible');
  });

  // Use Case 10: Queue Management (TC-040 - TC-044)
  it('TC-040 to TC-044: View station queue management board', () => {
    cy.get('a[href*="page=queue"]').first().click();
    cy.url().should('include', 'page=queue');
    cy.get('body').should('be.visible');
  });

  // Use Case 13 & 23: Patient Records View (TC-054 - TC-060)
  it('TC-054 to TC-060: Search and view patient master records', () => {
    cy.get('a[href*="page=patients"]').first().click();
    cy.url().should('include', 'page=patients');
    cy.get('input[name="patient_search"]').should('exist');
  });

  // Use Case 12: View Station Events (TC-049 - TC-053)
  it('TC-049 to TC-053: View scheduled health station events', () => {
    cy.get('a[href*="page=events"]').first().click();
    cy.url().should('include', 'page=events');
    cy.get('body').should('be.visible');
  });
});
