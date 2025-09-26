import "./login";
import "./wizard";
import "./checkout";

Cypress.Commands.add("wpCli", (command, options = {}) => {
  cy.exec(`npm run env run tests-cli wp ${command}`, options);
});

Cypress.Commands.overwrite("type", (originalFn, subject, string, options) =>
  originalFn(subject, string, Object.assign({ delay: 0 }, options))
);

Cypress.Commands.add("setValue", { prevSubject: true }, (subject, value) => {
  subject[0].setAttribute("value", value);
  return subject;
});

Cypress.Commands.add("saveDraft", () => {
  cy.window().then((w) => (w.stillOnCurrentPage = true));
  cy.get("#save-post").should("not.have.class", "disabled").click();
});

Cypress.Commands.add("publishPost", () => {
  cy.window().then((w) => (w.stillOnCurrentPage = true));
  cy.get("#publish").should("not.have.class", "disabled").click();
});

Cypress.Commands.add("waitForPageLoad", () => {
  cy.window().its("stillOnCurrentPage").should("be.undefined");
  cy.get("#message .notice-dismiss").click();
});

Cypress.Commands.add("blockAutosaves", () => {
  cy.intercept("/wp-admin/admin-ajax.php", (req) => {
    if (req.body.includes("wp_autosave")) {
      req.reply({
        status: 400,
      });
    }
  }).as("adminAjax");
});
