const { defineConfig } = require('cypress');
const baseConfig = require('./cypress.config');

const e2eOverride = {
    baseUrl: 'http://localhost:8889',
    defaultCommandTimeout: 30000,  // Increased from 20s to 30s
    requestTimeout: 45000,         // Increased from 30s to 45s
    responseTimeout: 45000,        // Increased from 30s to 45s
    pageLoadTimeout: 90000,        // Increased from 60s to 90s
    viewportWidth: 1280,           // Larger viewport for better testing
    viewportHeight: 720,
    video: true,                   // Ensure video recording
    screenshotOnRunFailure: true,  // Screenshots on failure
    trashAssetsBeforeRuns: true,   // Clean up old assets
    waitForAnimations: true,       // Wait for animations
    animationDistanceThreshold: 5,
    retries: {
        runMode: 2,                // Retry failed tests 2 times in CI
        openMode: 0                // No retries in dev mode
    }
}

module.exports = defineConfig({
    ...baseConfig,
    e2e: {
        ...baseConfig.e2e,
        ...e2eOverride,
        setupNodeEvents(on, config) {
            // Enhanced logging
            on('task', {
                log(message) {
                    console.log('🔍 CYPRESS TASK LOG:', message);
                    return null;
                },
                error(message) {
                    console.error('❌ CYPRESS ERROR:', message);
                    return null;
                }
            });

            // Browser launch args for better stability
            on('before:browser:launch', (browser = {}, launchOptions) => {
                if (browser.family === 'chromium') {
                    launchOptions.args.push('--disable-dev-shm-usage');
                    launchOptions.args.push('--no-sandbox');
                    launchOptions.args.push('--disable-gpu');
                    launchOptions.args.push('--disable-web-security');
                    launchOptions.args.push('--allow-running-insecure-content');
                }
                return launchOptions;
            });

            return config;
        }
    }
});
