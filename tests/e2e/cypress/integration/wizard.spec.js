const page_name = "wp-ultimo-setup";

describe("Wizard", () => {
  before(() => {
    cy.loginByApi(Cypress.env("admin").username, Cypress.env("admin").password);
    cy.visit(`/wp-admin/network/admin.php?page=${page_name}`);
  });

  it("Should be able to successfully complete the setup wizard", () => {
    /**
     * Steps: Welcome
     */
    cy.assertPageUrl({
      pathname: "/wp-admin/network/admin.php",
      page: page_name,
    });
    cy.clickPrimaryBtnByTxt("Get Started");

    /**
     * Steps: Checks
     */
    cy.assertPageUrl({
      pathname: "/wp-admin/network/admin.php",
      page: page_name,
      step: "checks",
    });
    cy.clickPrimaryBtnByTxt("Go to the Next Step");

    /**
     * Steps: Installation
     */
    cy.assertPageUrl({
      pathname: "/wp-admin/network/admin.php",
      page: page_name,
      step: "installation",
    });
    cy.clickPrimaryBtnByTxt("Install");

    /**
     * Steps: Your Company
     */
    cy.assertPageUrl({
      pathname: "/wp-admin/network/admin.php",
      page: page_name,
      step: "your-company",
    });
    cy.clickPrimaryBtnByTxt("Continue");

    /**
     * Steps: Defaults
     */
    cy.assertPageUrl({
      pathname: "/wp-admin/network/admin.php",
      page: page_name,
      step: "defaults",
    });
    cy.clickPrimaryBtnByTxt("Install");

    /**
     * Steps: Done
     */
    cy.clickPrimaryBtnByTxt("Thanks!");
    cy.assertPageUrl({
      pathname: "/wp-admin/network/index.php",
    });
  });
});
