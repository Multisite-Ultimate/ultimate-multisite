const { defineConfig } = require("cypress");

module.exports = defineConfig({
  viewportWidth: 1000,
  viewportHeight: 600,
  fileServerFolder: "tests/e2e/cypress",
  fixturesFolder: "tests/e2e/cypress/fixtures",
  downloadsFolder: "tests/e2e/cypress/downloads",
  screenshotsFolder: "tests/e2e/cypress/screenshots",
  videosFolder: "tests/e2e/cypress/videos",
  video: true,
  retries: {
    runMode: 1,
    openMode: 0,
  },
  e2e: {
    specPattern: "tests/e2e/cypress/integration/**/*.{js,jsx,ts,tsx}",
    supportFile: "tests/e2e/cypress/support/e2e.js",
    baseUrl: "http://localhost:8889",
    defaultCommandTimeout: 20000,
    requestTimeout: 30000,
    responseTimeout: 30000,
    pageLoadTimeout: 60000,
    setupNodeEvents(on, config) {},
  },
  env: {
    MAILPIT_URL: "http://localhost:8025",
  },
});
