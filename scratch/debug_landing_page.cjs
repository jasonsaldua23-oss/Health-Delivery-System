const { chromium } = require('@playwright/test');

(async () => {
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({ viewport: { width: 1280, height: 800 } });
    const page = await context.newPage();

    const consoleLogs = [];
    const pageErrors = [];

    page.on('console', msg => consoleLogs.push(`[${msg.type()}] ${msg.text()}`));
    page.on('pageerror', err => pageErrors.push(err.toString()));

    try {
        console.log('Navigating to http://localhost/Health-Delivery-System-Latest/Patients/index.php ...');
        const response = await page.goto('http://localhost/Health-Delivery-System-Latest/Patients/index.php', { waitUntil: 'networkidle' });
        console.log('Response status:', response ? response.status() : 'null');
        console.log('Page URL:', page.url());

        console.log('\n--- Console Logs ---');
        consoleLogs.forEach(l => console.log(l));

        console.log('\n--- Page Errors ---');
        if (pageErrors.length === 0) {
            console.log('No page errors detected.');
        } else {
            pageErrors.forEach(e => console.error(e));
        }

        // Test clicking .js-portal-scroll
        const choosePortalBtn = page.locator('.js-portal-scroll');
        const chooseCount = await choosePortalBtn.count();
        console.log('\n.js-portal-scroll count:', chooseCount);
        if (chooseCount > 0) {
            await choosePortalBtn.click();
            console.log('Clicked .js-portal-scroll successfully.');
        }

        // Test clicking .portal-card[data-portal="patient"]
        const patientCard = page.locator('.portal-card[data-portal="patient"]');
        const patientCardCount = await patientCard.count();
        console.log('.portal-card[data-portal="patient"] count:', patientCardCount);
        if (patientCardCount > 0) {
            await patientCard.click();
            console.log('Clicked patient portal card.');
            await page.waitForTimeout(500);

            const patientModal = page.locator('#patientModal');
            const isVisible = await patientModal.isVisible();
            const classList = await patientModal.getAttribute('class');
            console.log('patientModal visible:', isVisible, '| classes:', classList);
        }

        // Test clicking volunteer card
        const volCard = page.locator('.portal-card[data-portal="volunteer"]');
        if (await volCard.count() > 0) {
            await volCard.click();
            console.log('Clicked volunteer portal card.');
            await page.waitForTimeout(500);
            const volModal = page.locator('#volunteerModal');
            console.log('volunteerModal visible:', await volModal.isVisible(), '| classes:', await volModal.getAttribute('class'));
        }

    } catch (err) {
        console.error('Test execution error:', err);
    } finally {
        await browser.close();
    }
})();
