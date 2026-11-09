describe('Mail Query Tracker (MQT) - End to End Application Suite', () => {

  beforeEach(() => {
    cy.viewport(1366, 768);
  });

  Cypress.on('uncaught:exception', (err, runnable) => {
    console.log('Application Uncaught Exception:', err.message);
    return false;
  });

  it('1. Visually perform Login as Super Admin', () => {
    cy.visit('/login');
    cy.contains('DM-Ispark').should('be.visible');
    
    // Fill credentials visually
    cy.get('#usernameInput').clear().type('EMP001');
    cy.wait(400);
    cy.get('#passwordInput').clear().type('ChangeMe@123');
    cy.wait(400);
    cy.get('button[type="submit"]').click();

    // Verify redirected to Dashboard
    cy.url().should('include', '/dashboard');
    cy.contains('Dashboard').should('be.visible');
    cy.contains('Super Admin').should('be.visible');
    cy.wait(800);
  });

  it('2. Create Manager & Team Lead Accounts in Employee Master', () => {
    cy.visit('/login');
    cy.get('#usernameInput').clear().type('EMP001');
    cy.get('#passwordInput').clear().type('ChangeMe@123');
    cy.get('button[type="submit"]').click();

    cy.visit('/employees');
    cy.contains('Employee & User Management').should('be.visible');
    cy.wait(600);

    const runId = Math.floor(Math.random() * 899 + 100);

    // Create Manager Account
    cy.contains('Create User Account').click();
    cy.get('#userModal').should('be.visible');
    cy.get('#userModal input[name="user_code"]').clear().type('MGR' + runId);
    cy.get('#userModal input[name="full_name"]').clear().type('Operations Manager ' + runId);
    cy.get('#userModal input[name="email"]').clear().type('mgr' + runId + '@company.com');
    cy.get('#userModal select[name="role_id"]').select('3'); // Manager
    cy.get('#userModal input[name="department"]').clear().type('Finance & Billing');
    cy.wait(500);
    cy.get('#userModal button[type="submit"]').click();

    cy.contains('created successfully').should('be.visible');
    cy.wait(800);

    // Create Team Lead Account
    cy.contains('Create User Account').click();
    cy.get('#userModal').should('be.visible');
    cy.get('#userModal input[name="user_code"]').clear().type('TL' + runId);
    cy.get('#userModal input[name="full_name"]').clear().type('Team Lead ' + runId);
    cy.get('#userModal input[name="email"]').clear().type('tl' + runId + '@company.com');
    cy.get('#userModal select[name="role_id"]').select('2'); // Admin / Team Lead
    cy.get('#userModal input[name="department"]').clear().type('Finance & Billing');
    cy.wait(500);
    cy.get('#userModal button[type="submit"]').click();

    cy.contains('created successfully').should('be.visible');
    cy.wait(800);

    // Test Dedicated Skill Matrix Hub
    cy.visit('/employees/skill-matrix');
    cy.contains('Skill Matrix & Competency Alignment Hub').should('be.visible');
    cy.contains('Total System SLA').should('be.visible');
    cy.wait(800);
  });

  it('3. Create Project / Team & Map Users', () => {
    cy.visit('/login');
    cy.get('#usernameInput').clear().type('EMP001');
    cy.get('#passwordInput').clear().type('ChangeMe@123');
    cy.get('button[type="submit"]').click();

    cy.visit('/master/projects');
    cy.contains('Projects & Teams Master').should('be.visible');
    cy.wait(600);

    const pCode = 'AX-' + Math.floor(Math.random() * 899 + 100);

    cy.contains('Create New Project / Team').click();
    cy.get('#projectModal').should('be.visible');
    cy.get('#project_name').clear().type('Axalta EMEA Operations ' + pCode);
    cy.get('#project_code').clear().type(pCode);
    cy.get('#project_desc').clear().type('Invoice processing & vendor query management for EMEA region');
    cy.wait(500);
    cy.get('#projectModal button[type="submit"]').click();

    cy.contains('Project / Team created successfully').should('be.visible');
    cy.wait(800);
  });

  it('4. Create Business Division & Activity Master', () => {
    cy.visit('/login');
    cy.get('#usernameInput').clear().type('EMP001');
    cy.get('#passwordInput').clear().type('ChangeMe@123');
    cy.get('button[type="submit"]').click();

    const dCode = 'DIV-' + Math.floor(Math.random() * 899 + 100);

    // Create Division
    cy.visit('/master/divisions');
    cy.contains('Divisions Master').should('be.visible');
    cy.wait(600);

    cy.contains('New Division').click();
    cy.get('#divisionModal').should('be.visible');
    cy.get('#divisionModal input[name="division_name"]').clear().type('Media Purchasing Division ' + dCode);
    cy.get('#divisionModal input[name="code"]').clear().type(dCode);
    cy.wait(500);
    cy.get('#divisionModal button[type="submit"]').click();

    cy.contains('Division created successfully').should('be.visible');
    cy.wait(800);

    // Create Activity
    cy.visit('/master/activities');
    cy.contains('Activities Master').should('be.visible');
    cy.wait(600);

    cy.contains('Add New Activity').click();
    cy.get('#activityModal').should('be.visible');
    cy.get('#activityModal input[name="activity_name"]').clear().type('Invoice Verification ' + dCode);
    cy.wait(500);
    cy.get('#activityModal button[type="submit"]').click();

    cy.contains('Activity created successfully').should('be.visible');
    cy.wait(800);
  });

  it('5. Visually Inspect Organizational Hierarchy Tree', () => {
    cy.visit('/login');
    cy.get('#usernameInput').clear().type('EMP001');
    cy.get('#passwordInput').clear().type('ChangeMe@123');
    cy.get('button[type="submit"]').click();

    cy.visit('/master/hierarchy');
    cy.contains('Organizational Hierarchy Tree').should('be.visible');
    cy.contains('Senior Management & Executive Desk').should('be.visible');
    cy.wait(1000);

    // Test Expand and Collapse All buttons
    cy.contains('Collapse All').click();
    cy.wait(600);
    cy.contains('Expand All').click();
    cy.wait(800);
  });

  it('6. Log Daily Production Count & Verify Scorecard Hub', () => {
    cy.visit('/login');
    cy.get('#usernameInput').clear().type('EMP001');
    cy.get('#passwordInput').clear().type('ChangeMe@123');
    cy.get('button[type="submit"]').click();

    // Log Daily Production
    cy.visit('/performance/production');
    cy.contains('Daily Production & Count Logger').should('be.visible');
    cy.wait(600);

    cy.contains('Log Production Entry').click();
    cy.get('#logModal').should('be.visible');
    cy.get('#logModal select[name="user_id"]').then($select => {
      if ($select.length && $select.is(':visible')) {
        cy.wrap($select).select(0);
      }
    });
    cy.get('#logModal input[name="processed_count"]').clear().type('280');
    cy.get('#logModal input[name="target_count"]').clear().type('200');
    cy.get('#logModal input[name="worked_hours"]').clear().type('8.0');
    cy.get('#logModal input[name="internal_errors"]').clear().type('1');
    cy.get('#logModal input[name="external_errors"]').clear().type('0');
    cy.get('#logModal textarea[name="remarks"]').clear().type('High transaction output achieved with minimal errors');
    cy.wait(600);
    cy.get('#logModal button[type="submit"]').click();

    cy.contains('Daily production count logged').should('be.visible');
    cy.wait(800);

    // Verify Scorecard Hub
    cy.visit('/performance/scorecard');
    cy.contains('Performance Scorecard Hub').should('be.visible');
    cy.contains('Overall Score').should('be.visible');
    cy.wait(1000);

    // Verify Analytics
    cy.visit('/performance/analytics');
    cy.contains('Performance Analytics').should('be.visible');
    cy.wait(1000);
  });

});
