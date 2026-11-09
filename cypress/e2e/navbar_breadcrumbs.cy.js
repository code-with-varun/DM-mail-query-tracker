describe('Top Navbar Quick Nav and Breadcrumbs Test', () => {
  beforeEach(() => {
    Cypress.on('uncaught:exception', () => false);
  });

  it('Verifies Home & Dashboard buttons and breadcrumbs across pages', () => {
    cy.viewport(1366, 768);
    cy.visit('/login');
    cy.get('#usernameInput').clear().type('EMP001');
    cy.get('#passwordInput').clear().type('ChangeMe@123');
    cy.get('button[type="submit"]').click();
    cy.url().should('include', '/dashboard');

    // 1. Visit /home
    cy.visit('/home');
    cy.get('.navbar-quick-nav a[href*="home"]').should('be.visible').and('contain', 'Home').and('have.class', 'btn-primary');
    cy.get('.navbar-quick-nav a[href*="dashboard"]').should('be.visible').and('contain', 'Dashboard').and('not.have.class', 'btn-primary');
    cy.get('.navbar-breadcrumbs').should('be.visible').and('contain', 'Home');

    // Verify sidebar has merged TASKS & WORKSPACE and does NOT have Home/Dashboard links
    cy.get('.sidebar-heading').contains('TASKS & WORKSPACE').should('be.visible');
    cy.get('#sb-group-workspace a[href*="/home"]').should('not.exist');
    cy.get('#sb-group-workspace a[href*="/dashboard"]').should('not.exist');

    cy.screenshot('home_page_navbar_breadcrumbs', { capture: 'viewport' });

    // 2. Visit /dashboard
    cy.visit('/dashboard');
    cy.get('.navbar-quick-nav a[href*="dashboard"]').should('be.visible').and('have.class', 'btn-primary');
    cy.get('.navbar-quick-nav a[href*="home"]').should('be.visible').and('not.have.class', 'btn-primary');
    cy.get('.navbar-breadcrumbs').should('be.visible').and('contain', 'Home').and('contain', 'Dashboard');
    cy.screenshot('dashboard_page_navbar_breadcrumbs', { capture: 'viewport' });

    // 3. Visit /master/hierarchy
    cy.visit('/master/hierarchy');
    cy.get('.navbar-breadcrumbs').should('be.visible').and('contain', 'Org Hierarchy Tree');
    cy.contains('Senior Management & Executive Desk').should('be.visible');
    cy.wait(1000);
    cy.screenshot('new_top_down_org_chart', { capture: 'viewport' });
  });
});
