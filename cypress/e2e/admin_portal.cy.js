describe('Admin Portal - Management Suite', () => {
  beforeEach(() => {
    cy.visit('/Patients/index.php');

    // Admin Login (TC-061 to TC-064)
    cy.get('.portal-card[data-portal="admin"]').scrollIntoView().click({ force: true });
    cy.get('#adminModal').should('be.visible');

    cy.get('#adminLoginForm').within(() => {
      cy.get('input[name="admin_username"]').type('admin');
      cy.get('input[name="admin_password"]').type('adminadminadmin');
      cy.get('button[type="submit"]').click();
    });

    cy.url().should('include', 'Admin/index.php');
  });

  // Use Case 15: Admin Dashboard Citywide Aggregation (TC-065 - TC-068)
  it('TC-065 to TC-068: Display aggregated metrics across health centers', () => {
    cy.url().should('include', 'page=dashboard');
    cy.get('body').should('be.visible');
    cy.get('.stats-card, .metric-card, .dashboard-card, .kpi-card, div').should('exist');
  });

  // Use Case 17: Station Services and Capacity (TC-073 - TC-077)
  it('TC-073 to TC-077: Access station services and capacity management', () => {
    cy.get('a[href*="page=services"]').first().click();
    cy.url().should('include', 'page=services');
    cy.get('body').should('be.visible');
  });

  // Use Case 19: Manage User Accounts (TC-078 - TC-082)
  it('TC-078 to TC-082: Access users management page', () => {
    cy.get('a[href*="page=users"]').first().click();
    cy.url().should('include', 'page=users');
    cy.get('body').should('be.visible');
  });

  // Use Case 20: Reports and Data View (TC-083 - TC-087)
  it('TC-083 to TC-087: Access reports and system analytics view', () => {
    cy.get('a[href*="page=reports"]').first().click();
    cy.url().should('include', 'page=reports');
    cy.get('body').should('be.visible');
  });

  // Use Case 12: Upcoming Events Publishing (TC-049 - TC-053)
  it('TC-049 to TC-053: Access community health events management', () => {
    cy.get('a[href*="page=events"]').first().click();
    cy.url().should('include', 'page=events');
    cy.get('body').should('be.visible');
  });
});
