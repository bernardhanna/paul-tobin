import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

/**
 * Comma-separated paths under BASE_URL. Override for a wider crawl:
 *   A11Y_PATHS='/,/contact-us/,/sell/' npm run test:a11y:full
 */
function routesToScan(): string[] {
  const raw = process.env.A11Y_PATHS?.trim();
  if (raw) {
    return raw.split(',').map((p) => p.trim()).filter(Boolean);
  }
  return ['/'];
}

function summarize(violations: { id: string; impact?: string; help: string; nodes: { html: string }[] }[]) {
  return violations.map((v) => ({
    id: v.id,
    impact: v.impact,
    help: v.help,
    targets: v.nodes.slice(0, 5).map((n) => n.html.replace(/\s+/g, ' ').slice(0, 200)),
  }));
}

for (const route of routesToScan()) {
  test(`axe: no critical or serious on ${route}`, async ({ page }, testInfo) => {
    const response = await page.goto(route, { waitUntil: 'domcontentloaded', timeout: 60000 });
    if (!response || !response.ok()) {
      testInfo.skip(
        true,
        `Could not load ${route} (status ${response?.status() ?? 'none'}). Set BASE_URL in .env.`,
      );
      return;
    }
    await page.waitForLoadState('load');
    await page.waitForLoadState('networkidle', { timeout: 15_000 }).catch(() => {});

    // Password Protected plugin uses tabindex > 0 on #loginform (third-party; not theme code).
    let results: Awaited<ReturnType<AxeBuilder['analyze']>>;
    for (let attempt = 0; attempt < 4; attempt++) {
      const axe = new AxeBuilder({ page });
      if ((await page.locator('#loginform').count()) > 0) {
        axe.exclude('#loginform');
      }
      try {
        results = await axe.analyze();
        break;
      } catch (e) {
        const msg = e instanceof Error ? e.message : String(e);
        if (!msg.includes('Execution context was destroyed') || attempt === 3) {
          throw e;
        }
        await new Promise((r) => setTimeout(r, 1200));
      }
    }
    const severe = results.violations.filter(
      (v) => v.impact === 'critical' || v.impact === 'serious',
    );

    if (severe.length > 0) {
      await testInfo.attach('axe-violations.json', {
        body: JSON.stringify(summarize(severe), null, 2),
        contentType: 'application/json',
      });
    }
    expect(severe).toEqual([]);
  });
}
