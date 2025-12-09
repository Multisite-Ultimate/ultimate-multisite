import { wizard_page } from "../selectors/wizard.js";

const { section_btn } = wizard_page;

/**
 * Assert that the current URL matches expected pathname, page, and step (optional)
 *
 * @param {Object} options
 * @param {string} options.pathname - The expected pathname (e.g., "/wp-admin/network/admin.php")
 * @param {string} options.page - The 'page' query param (e.g., "wp-ultimo-setup")
 * @param {string} [options.step] - Optional 'step' query param (e.g., "checks")
 *
 * Usage:
 * cy.assertPageUrl({ pathname: "/wp-admin/network/admin.php", page: "wp-ultimo-setup", step: "checks" });
 */
Cypress.Commands.add("assertPageUrl", ({ pathname, page, step }) => {
  cy.location("pathname").should("eq", pathname);
  if (page) {
    cy.location("search").should((search) => {
      const params = new URLSearchParams(search);
      expect(params.get("page")).to.eq(page);
      if (step) {
        expect(params.get("step")).to.eq(step);
      }
    });
  }
});

/**
 * Click on the button by text.
 *
 * @param {string} text - e.g. Install.
 *
 * Usage:
 * cy.clickBtnByTxt('Install');
 */
Cypress.Commands.add("clickPrimaryBtnByTxt", (text) => {
  cy.get(section_btn).contains(text).should("be.visible").click();
});
