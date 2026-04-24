import { test, expect } from '@playwright/test';

type ConsoleEntry = {
  type: string;
  text: string;
  location?: { url?: string };
};

function smokePaths(): string[] {
  const raw = process.env.ROCKET_SMOKE_PATHS?.trim();
  if (raw) {
    return raw.split(',').map((item) => item.trim()).filter(Boolean);
  }

  return ['/', '/buy/', '/contact-us/', '/book-a-valuation/'];
}

function isIgnorableConsole(entry: ConsoleEntry): boolean {
  const text = entry.text.toLowerCase();
  return (
    text.includes('webpack-dev-server') ||
    text.includes("ws://localhost:3000/ws") ||
    text.includes('err_connection_refused') ||
    text.includes('jqmigrate') ||
    text.includes('sourcemap') ||
    text.includes('[forms] provider:')
  );
}

for (const route of smokePaths()) {
  test(`rocket smoke: ${route} loads without page errors`, async ({ page }, testInfo) => {
    const consoleEvents: ConsoleEntry[] = [];
    const failedRequests: string[] = [];

    page.on('console', (msg) => {
      consoleEvents.push({
        type: msg.type(),
        text: msg.text(),
        location: msg.location(),
      });
    });

    page.on('requestfailed', (request) => {
      failedRequests.push(`${request.failure()?.errorText ?? 'failed'} :: ${request.url()}`);
    });

    const response = await page.goto(route, { waitUntil: 'domcontentloaded', timeout: 60000 });
    expect(response?.ok(), `Failed to load ${route} (status ${response?.status() ?? 'none'})`).toBeTruthy();

    await page.waitForLoadState('load');
    await page.waitForTimeout(1200);

    const criticalConsole = consoleEvents.filter((entry) => {
      if (isIgnorableConsole(entry)) {
        return false;
      }
      return entry.type === 'error';
    });

    const failedAssetRequests = failedRequests.filter((line) => {
      const lower = line.toLowerCase();
      return (
        lower.includes('.js') ||
        lower.includes('.css') ||
        lower.includes('.woff') ||
        lower.includes('.woff2') ||
        lower.includes('leaflet') ||
        lower.includes('slick')
      );
    });

    if (criticalConsole.length || failedAssetRequests.length) {
      await testInfo.attach('rocket-smoke-debug.json', {
        body: JSON.stringify(
          {
            route,
            criticalConsole,
            failedAssetRequests,
          },
          null,
          2,
        ),
        contentType: 'application/json',
      });
    }

    expect(criticalConsole, `Console errors detected on ${route}`).toEqual([]);
    expect(failedAssetRequests, `Failed asset requests detected on ${route}`).toEqual([]);
  });
}
