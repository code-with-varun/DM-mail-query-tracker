describe('Datamatics Operations Updates & Scorecard Lifecycle Suite', () => {

  beforeEach(() => {
    cy.viewport(1366, 768);
  });

  Cypress.on('uncaught:exception', (err, runnable) => {
    return false;
  });

  it('1. Verifies Division Master displays Assigned Team Leader column and modal dropdown', () => {
    cy.visit('/login');
    cy.get('#usernameInput').clear().type('EMP001');
    cy.get('#passwordInput').clear().type('ChangeMe@123');
    cy.get('button[type="submit"]').click();
    cy.url().should('include', '/dashboard');

    cy.visit('/master/divisions');
    cy.contains('Divisions Master').should('be.visible');
    cy.get('th').contains('Assigned Team Leader').should('be.visible');
    
    // Check seeded table row
    cy.get('table').contains('Retail Assets & Loans').should('be.visible');
    cy.get('table').contains('Priya Sundaram').should('be.visible');
    
    // Open New Division modal and check Team Leader select options
    cy.contains('New Division').click();
    cy.get('#divisionModal').should('be.visible');
    cy.get('#divisionModal select[name="team_leader_id"]').should('be.visible');
    cy.get('#divisionModal select[name="team_leader_id"]').contains('Priya Sundaram (EMP003)').should('exist');
    cy.get('#divisionModal button[data-bs-dismiss="modal"]').first().click();
  });

  it('2. Navigates to Daily Timesheet Tracker and validates entry submission', () => {
    cy.visit('/login');
    cy.get('#usernameInput').clear().type('EMP001');
    cy.get('#passwordInput').clear().type('ChangeMe@123');
    cy.get('button[type="submit"]').click();
    cy.url().should('include', '/dashboard');

    // Expand TRACKERS accordion section and click Timesheet Tracker
    cy.get('.sidebar-heading').contains('TRACKERS').click();
    cy.wait(400);
    cy.get('a[href*="tracker/timesheet"]').should('be.visible').click();
    
    cy.url().should('include', '/tracker/timesheet');
    cy.contains('Daily Timesheet & Effort Tracker').should('be.visible');
    cy.contains('Total Logged Effort').should('be.visible');

    // Check table displays seeded entries
    cy.get('table').contains('Varun Kumar').should('be.visible');
    cy.get('table').contains('Approved').should('be.visible');

    // Open Log Effort modal
    cy.contains('Log Timesheet Entry').click();
    cy.get('#timesheetModal').should('be.visible');
    cy.get('#timesheetModal select[name="user_id"]').should('be.visible');
    cy.get('#timesheetModal input[name="hours_spent"]').should('have.value', '8.0');
    cy.get('#timesheetModal textarea[name="task_description"]').clear().type('Automated Cypress timesheet effort entry for client reporting.');
    cy.get('#timesheetModal button[type="submit"]').click();

    cy.contains('Timesheet effort logged successfully').should('be.visible');
  });

  it('3. Inspects Monthly Scorecard Hub Datamatics Ledger, Appraisal Notes and Lifecycle Buttons', () => {
    cy.visit('/login');
    cy.get('#usernameInput').clear().type('EMP001');
    cy.get('#passwordInput').clear().type('ChangeMe@123');
    cy.get('button[type="submit"]').click();
    cy.url().should('include', '/dashboard');

    cy.visit('/performance/scorecard?month=2026-09&user_id=6');
    cy.contains('Performance Scorecard Hub').should('be.visible');

    // Verify Datamatics Operations Performance Breakdown is visible
    cy.contains('Detailed Operations Performance Breakdown').should('be.visible');
    cy.contains('Working Days').should('be.visible');
    cy.contains('Present').should('be.visible');
    cy.contains('Leave Days').should('be.visible');
    cy.contains('Avg Points/Day').should('be.visible');
    cy.contains('Points %').should('be.visible');
    cy.contains('Production Scale').should('be.visible');

    // Check user 6 Sneha has overtime leave (abs(-1.0) Overtime) and Locked status
    cy.get('table').contains('(Overtime)').should('be.visible');
    cy.contains('Locked & Frozen by Rajesh Sharma').should('be.visible');

    // Open Appraisal Notes Modal for user
    cy.get('button[data-bs-target="#notesModal"]').first().click();
    cy.get('#notesModal').should('be.visible');
    cy.get('#notesModal input[name="recognition_type"]').should('exist');
    cy.get('#notesModal textarea[name="special_note"]').should('exist');
    cy.get('#notesModal button[data-bs-dismiss="modal"]').first().click();
  });

});
