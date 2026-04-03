import { test } from '@playwright/test';

test('debug tawk', async ({ page }) => {
  page.on('console', msg => console.log('console:', msg.type(), msg.text()));
  page.on('pageerror', err => console.log('pageerror:', err.message));
  page.on('request', req => {
    if (/tawk/i.test(req.url())) console.log('request:', req.method(), req.url());
  });
  page.on('response', res => {
    if (/tawk/i.test(res.url())) console.log('response:', res.status(), res.url());
  });

  await page.goto('https://cetsy.co', { waitUntil: 'domcontentloaded' });
  await page.waitForTimeout(15000);

  const state = await page.evaluate(() => ({
    hasTawkApi: !!window.Tawk_API,
    onLoadType: window.Tawk_API && typeof window.Tawk_API.onLoad,
    scripts: Array.from(document.scripts).map(s => s.src).filter(Boolean).filter(src => /tawk/i.test(src)),
    iframeCount: document.querySelectorAll('iframe').length,
    bodyHtmlHasTawk: document.body.innerHTML.toLowerCase().includes('tawk')
  }));
  console.log('state:', JSON.stringify(state));
});
