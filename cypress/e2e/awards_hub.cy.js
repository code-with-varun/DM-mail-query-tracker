describe('Datamatics Annual Achievers Awards (AAA) & Recognition Hub E2E', () => {

    Cypress.on('uncaught:exception', (err, runnable) => {
        return false;
    });

    beforeEach(() => {
        cy.viewport(1366, 768);
        cy.visit('/login');
        cy.get('#usernameInput').clear().type('EMP001');
        cy.get('#passwordInput').clear().type('ChangeMe@123');
        cy.get('button[type="submit"]').click();
        cy.url().should('include', '/dashboard');
    });

    it('1. Verifies Awards Hub loads with full Datamatics AAA structure & KPIs', () => {
        cy.visit('/performance/awards');
        cy.contains('Datamatics Annual Achievers Awards (AAA)').should('be.visible');
        cy.contains('Excellence, Innovation & Operational Honours').should('be.visible');
        cy.contains('Total Honours Conferred').should('be.visible');
        cy.contains('Standard Award Categories').should('be.visible');
        cy.contains('Automated Evaluation').should('be.visible');
    });

    it('2. Verifies Period Tiers & Grand Podium', () => {
        cy.visit('/performance/awards');
        
        // Check Monthly Recognition
        cy.contains('Monthly Recognition').should('be.visible');
        cy.contains('Quarterly Achievers').click();
        cy.url().should('include', 'period_type=quarterly');
        cy.contains('Grand Podium').should('be.visible');

        // Check Annual Prestigious Honours
        cy.contains('Annual Prestigious Honours').click();
        cy.url().should('include', 'period_type=annual');
        cy.contains('Champion of the Horizon').should('be.visible');

        // Check Long Service Milestones
        cy.contains('Long Service Milestones').click();
        cy.url().should('include', 'period_type=milestone');
        cy.contains('Dedication & Loyalty').should('be.visible');
    });

    it('3. Verifies Scoring Engine modal with 6-Factor weighting', () => {
        cy.visit('/performance/awards');
        cy.contains('Scoring Engine').click();
        cy.contains('Datamatics 6-Factor Automated Scoring Engine').should('be.visible');
        cy.contains('30%').should('be.visible');
        cy.contains('Productivity').should('be.visible');
        cy.contains('25%').should('be.visible');
        cy.contains('Quality Accuracy').should('be.visible');
        cy.contains('15%').should('be.visible');
        cy.contains('SLA Adherence').should('be.visible');
        cy.get('#scoringFormulaModal .btn-close').click();
    });

    it('4. Verifies Awards Catalog Directory & Live Slideshow', () => {
        cy.visit('/performance/awards');
        cy.contains('Awards Catalog & Criteria Matrix').should('be.visible');
        cy.contains('Employee of the Year').should('be.visible');
        cy.contains('Star Performer of the Month').should('be.visible');

        // Verify Live Slideshow
        cy.visit('/performance/awards-slideshow');
        cy.contains('DATAMATICS ANNUAL ACHIEVERS AWARDS').should('be.visible');
        cy.contains('Live Honours Showcase').should('be.visible');
        cy.contains('Exit').click();
        cy.url().should('include', '/performance/awards');
    });

    it('5. Verifies Honours & Accolades appear in Employee Performance Scorecard', () => {
        cy.visit('/performance/scorecard');
        cy.contains('Performance Scorecard').should('be.visible');
        cy.contains('Official Honours & Accolades').should('be.visible');
    });
});
