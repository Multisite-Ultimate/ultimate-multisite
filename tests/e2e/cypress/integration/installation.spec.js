describe("Plugin", () => {
  beforeEach(() => {
    cy.loginByApi(Cypress.env("admin").username, Cypress.env("admin").password);
  });

  it("Should show an error message that the plugin needs to be network activated", () => {
    cy.visit("/wp-admin/");
  });
});
