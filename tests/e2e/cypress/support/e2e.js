import './commands/index';
import 'cypress-mailpit';

Cypress.on('uncaught:exception', (err) => {
  if (err.message.includes('Vue is not defined')) {
    return false;
  }
});
