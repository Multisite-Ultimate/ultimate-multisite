const { defineConfig } = require('cypress');
const baseConfig = require('./cypress.config');

const e2eOverride = {
    baseUrl: 'http://localhost:8888',
}

module.exports = defineConfig({
    ...baseConfig,
    e2e: {
        ...baseConfig.e2e,
        ...e2eOverride,
    }
});
