import { defineConfig, devices } from '@playwright/test';
import * as dotenv from 'dotenv';
import path from 'path';

dotenv.config({ path: path.resolve(__dirname, '.env') });

/**
 * Override with BASE_URL in .env if needed.
 * Optional: A11Y_PATHS — comma-separated paths (default "/") e.g. /,/contact-us/,/sell/
 */
const baseURL = (process.env.BASE_URL || 'http://localhost:10059').replace(/\/$/, '');

export default defineConfig({
  testDir: path.join(__dirname, 'tests', 'a11y'),
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  reporter: [['list'], ['html', { open: 'never', outputFolder: 'tests/playwright-report' }]],
  use: {
    baseURL,
    trace: 'retain-on-failure',
    ignoreHTTPSErrors: true,
  },
  projects: [{ name: 'chromium', use: { ...devices['Desktop Chrome'] } }],
});
