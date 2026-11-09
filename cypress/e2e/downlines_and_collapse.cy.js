describe('Sidebar Collapse Accordion, Downlines Hierarchy, and Grand Prix Access Test', () => {
  beforeEach(() => {
    Cypress.on('uncaught:exception', () => false);
  });

  it('1. Super Admin: Accordion collapse works cleanly, sees all 8 downlines (self excluded), has Grand Prix menu', () => {
    cy.viewport(1366, 768);
    cy.visit('/login');
    cy.get('#usernameInput').clear().type('EMP001');
    cy.get('#passwordInput').clear().type('ChangeMe@123');
    cy.get('button[type="submit"]').click();

    cy.url().should('include', '/dashboard');

    // Grand Prix menu should be visible for Super Admin
    cy.get('.sidebar-heading').contains('PERFORMANCE').click();
    cy.wait(400);
    cy.get('#sb-group-performance').should('have.class', 'show');
    // Workspace should now be collapsed by accordion parent
    cy.get('#sb-group-workspace').should('not.have.class', 'show');
    cy.get('a[href*="performance/contest"]').should('be.visible').and('contain', 'Grand Prix Contest');

    // Visit Employee Management: Super Admin sees 8 reporting downlines (self EMP001 excluded)
    cy.visit('/employees');
    cy.get('table.datatable tbody tr').should('have.length', 8);
    cy.contains('EMP001').should('not.exist'); // Self excluded!
    cy.contains('EMP009').should('be.visible');
    cy.contains('EMP002').should('be.visible');
    cy.contains('EMP003').should('be.visible');

    // Logout
    cy.visit('/logout');
  });

  it('2. Manager (EMP002 Rajesh Sharma): Downlines strictly enforced (6 users, self excluded), NO Grand Prix Contest', () => {
    cy.viewport(1366, 768);
    cy.visit('/login');
    cy.get('#usernameInput').clear().type('EMP002');
    cy.get('#passwordInput').clear().type('ChangeMe@123');
    cy.get('button[type="submit"]').click();

    // PERFORMANCE submenu should NOT have Grand Prix Contest
    cy.get('.sidebar-heading').contains('PERFORMANCE').click();
    cy.wait(400);
    cy.get('a[href*="performance/contest"]').should('not.exist');

    // Direct navigation to contest should be blocked / redirected
    cy.visit('/performance/contest');
    cy.url().should('not.include', '/performance/contest');

    // Visit Employee Management: Manager only sees 6 reporting downlines (self EMP002 excluded)
    cy.visit('/employees');
    cy.get('table.datatable tbody tr').should('have.length', 6);
    // Should NOT see Super Admin EMP001, Senior Mgmt EMP009, or Self EMP002
    cy.contains('EMP001').should('not.exist');
    cy.contains('EMP009').should('not.exist');
    cy.contains('EMP002').should('not.exist'); // Self excluded!
    // Should see downlines
    cy.contains('EMP003').should('be.visible');
    cy.contains('EMP005').should('be.visible');

    // Logout
    cy.visit('/logout');
  });

  it('3. Team Lead (EMP003 Priya Sundaram): Downlines strictly enforced (3 users, self excluded), NO Grand Prix Contest', () => {
    cy.viewport(1366, 768);
    cy.visit('/login');
    cy.get('#usernameInput').clear().type('EMP003');
    cy.get('#passwordInput').clear().type('ChangeMe@123');
    cy.get('button[type="submit"]').click();

    // PERFORMANCE submenu should NOT have Grand Prix Contest
    cy.get('.sidebar-heading').contains('PERFORMANCE').click();
    cy.wait(400);
    cy.get('a[href*="performance/contest"]').should('not.exist');

    // Visit Employee Management: Team Lead only sees 3 downlines (Anand, Varun, Sneha, self EMP003 excluded)
    cy.visit('/employees');
    cy.get('table.datatable tbody tr').should('have.length', 3);
    cy.contains('EMP001').should('not.exist');
    cy.contains('EMP009').should('not.exist');
    cy.contains('EMP002').should('not.exist');
    cy.contains('EMP003').should('not.exist'); // Self excluded!
    cy.contains('EMP007').should('not.exist');
    cy.contains('EMP008').should('not.exist');
    // Should see her employees
    cy.contains('EMP004').should('be.visible');
    cy.contains('EMP005').should('be.visible');
    cy.contains('EMP006').should('be.visible');

    // Logout
    cy.visit('/logout');
  });

  it('4. Senior Management (EMP009 Vikram Malhotra): Sees Grand Prix Contest and 7 downlines (self excluded)', () => {
    cy.viewport(1366, 768);
    cy.visit('/login');
    cy.get('#usernameInput').clear().type('EMP009');
    cy.get('#passwordInput').clear().type('ChangeMe@123');
    cy.get('button[type="submit"]').click();

    // Grand Prix menu should be visible for Senior Management
    cy.get('.sidebar-heading').contains('PERFORMANCE').click();
    cy.wait(400);
    cy.get('a[href*="performance/contest"]').should('be.visible').and('contain', 'Grand Prix Contest');

    // Direct navigation should succeed
    cy.visit('/performance/contest');
    cy.url().should('include', '/performance/contest');
    cy.contains('Grand Prix').should('be.visible');

    // Visit Employee Management: Senior Management sees 7 downlines (self EMP009 and Super Admin EMP001 excluded)
    cy.visit('/employees');
    cy.get('table.datatable tbody tr').should('have.length', 7);
    cy.contains('EMP001').should('not.exist');
    cy.contains('EMP009').should('not.exist'); // Self excluded!
    cy.contains('EMP002').should('be.visible');
    cy.contains('EMP003').should('be.visible');

    // Logout
    cy.visit('/logout');
  });
});
