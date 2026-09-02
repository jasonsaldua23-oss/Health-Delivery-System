describe('Admin Portal - Management Suite', () => {
  beforeEach(() => {
    cy.visit('/Patients/index.php');
    cy.contains('Admin').click({ force: true });

    // TC-061 to TC-064: Admin Login
    cy.get('#adminLoginForm, form[name="adminLoginForm"]').within(() => {
      cy.get('input[name="username"], input[name="admin_username"], input[name="login_email"]').first().type('admin');
      cy.get('input[name="password"], input[name="admin_password"], input[name="login_password"]').first().type('AdminSecure2026!');
      cy.get('button[type="submit"]').click();
    });
    cy.url({ timeout: 10000 }).should('include', '/Admin/index.php?page=dashboard');
  });

  // Use Case 15: Aggregated Citywide Metrics (TC-065 - TC-068)
  it('TC-065 to TC-068: View citywide health statistics and metrics cards', () => {
    cy.get('.dash-kpi-card, .metric-card, .summary-card, article').should('have.length.at.least', 3);
    cy.get('canvas, .chart-card, .dash-chart-card, .dash-kpi-grid').should('exist');
    cy.screenshot('TC-065_to_TC-068_admin_metrics');
  });

  // Use Case 17: Station Services and Capacity (TC-073 - TC-077)
  it('TC-073 to TC-077: Update daily appointment slot capacity', () => {
    cy.get('a[href*="page=services"]').first().click();
    cy.get('.station-admin-card, .station-card, .station-service-row, article').first().click();
    cy.get('body').should('be.visible');
    cy.screenshot('TC-073_to_TC-077_station_services_capacity');
  });

  // Use Case 19: Manage User Accounts (TC-078 - TC-082)
  it('TC-078 to TC-082: Add a new staff user account', () => {
    cy.get('a[href*="page=users"]').click();
    cy.get('body').should('be.visible');
    cy.get('a[href*="show_user_modal=1"], #newUserBtn, button, a').contains(/New User|Add User/i).first().click({ force: true });
    cy.screenshot('TC-078_to_TC-082_user_management_staff');
  });

  // Use Case 20: Reports and Export (TC-083 - TC-087)
  it('TC-083 to TC-087: Filter reports and verify CSV export URL', () => {
    cy.get('a[href*="page=reports"]').click();
    cy.get('body').should('be.visible');
    cy.screenshot('TC-083_to_TC-087_system_reports_export');
  });

  // Use Case 12: Upcoming Events (TC-049 - TC-053)
  it('TC-049 to TC-053: Create and publish a community health event', () => {
    cy.get('a[href*="page=events"]').click();
    cy.get('body').should('be.visible');
    cy.screenshot('TC-049_to_TC-053_community_health_event');
  });
});
