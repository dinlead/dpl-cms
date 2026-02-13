// ***********************************************************
// This example support/e2e.ts is processed and
// loaded automatically before your test files.
//
// This is a great place to put global configuration and
// behavior that modifies Cypress.
//
// You can change the location of this file or turn off
// automatically serving support files with the
// 'supportFile' configuration option.
//
// You can read more here:
// https://on.cypress.io/configuration
// ***********************************************************

// Import commands.ts using ES2015 syntax:
import './commands';

// Alternatively you can use CommonJS syntax:
// require('./commands')

import 'cypress-plugin-api';

// Ignore uncaught exceptions from cross-origin scripts (e.g. Google Translate).
// These are third-party errors outside our control and should not fail tests.
Cypress.on('uncaught:exception', (err) => {
  if (err.message.includes('Script error')) {
    return false;
  }
  return true;
});

// Collect logs for the console.
import installLogsCollector from 'cypress-terminal-report/src/installLogsCollector';
installLogsCollector();
