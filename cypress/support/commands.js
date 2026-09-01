// ***********************************************
// Custom Cypress Commands
// ***********************************************

// Example custom command to check if an element is visible
Cypress.Commands.add('getByDataCy', (selector) => {
  return cy.get(`[data-cy=${selector}]`);
});
