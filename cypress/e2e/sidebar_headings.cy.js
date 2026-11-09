describe('Sidebar Group Headings Readability Test', () => {
  beforeEach(() => {
    Cypress.on('uncaught:exception', (err, runnable) => {
      return false;
    });
  });

  it('Verifies all 9 concise sidebar group headings are present and visible', () => {
    cy.viewport(1366, 768);
    cy.visit('/login');
    cy.get('#usernameInput').clear().type('EMP001');
    cy.get('#passwordInput').clear().type('ChangeMe@123');
    cy.get('button[type="submit"]').click();

    cy.url().should('include', '/dashboard');

    const expectedHeadings = [
      'WORKSPACE',
      'PERFORMANCE',
      'REPORTS',
      'TASKS',
      'TRACKERS',
      'PEOPLE',
      'TRAINING',
      'WORKFLOWS',
      'ADMINISTRATION'
    ];

    expectedHeadings.forEach((h) => {
      cy.get('.sidebar-heading').contains(h).should('be.visible');
    });

    // Verify Daily Production Logger is inside TRACKERS group
    cy.get('.sidebar-heading').contains('TRACKERS').click();
    cy.wait(400);
    cy.get('#sb-group-trackers').should('have.class', 'show');
    cy.get('#sb-group-trackers a[href*="performance/production"]').should('be.visible').and('contain', 'Daily Production Logger');

    // Verify Daily Production Logger is NOT in PERFORMANCE group
    cy.get('.sidebar-heading').contains('PERFORMANCE').click();
    cy.wait(400);
    cy.get('#sb-group-performance a[href*="performance/production"]').should('not.exist');

    // Visit Home Hub and verify Daily Production Logger is under Trackers
    cy.visit('/home');
    cy.contains('h5', 'Trackers').should('be.visible');
    cy.contains('h5', 'Trackers').parent().find('a[href*="performance/production"]').should('be.visible').and('contain', 'Daily Production Logger');

    cy.screenshot('sidebar_trackers_daily_production', { capture: 'viewport' });
  });
});
