describe("Plugin", () => {
  beforeEach(() => {
    cy.loginByForm(
      Cypress.env("admin").username,
      Cypress.env("admin").password
    );
  });

  it("Should be able to deactivate the plugin", () => {
    cy.visit("/wp-admin/network/plugins.php");
    cy.location("pathname").should("equal", "/wp-admin/network/plugins.php");
    cy.get("#deactivate-ultimate-multisite").scrollIntoView().should("be.visible").click();
    cy.get("#activate-ultimate-multisite").scrollIntoView().should("be.visible");
  });

  it("Should be able to activate the plugin", () => {
    cy.visit("/wp-admin/network/plugins.php");
    cy.location("pathname").should("equal", "/wp-admin/network/plugins.php");
    cy.get("#activate-ultimate-multisite").scrollIntoView().should("be.visible").click();
    cy.location("pathname").should("eq", "/wp-admin/network/admin.php");
    cy.location("search").should("include", "page=wp-ultimo-setup");
  });
});
