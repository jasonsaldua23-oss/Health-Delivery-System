const { chromium } = require('@playwright/test');

(async () => {
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({ viewport: { width: 1280, height: 800 } });
    const page = await context.newPage();

    const errors = [];
    page.on('pageerror', err => errors.push(err.toString()));

    try {
        console.log('Navigating to http://localhost/Health-Delivery-System-Latest/Patients/index.php ...');
        await page.goto('http://localhost/Health-Delivery-System-Latest/Patients/index.php', { waitUntil: 'networkidle' });

        // 1. Check Choose Your Portal button
        console.log('1. Testing "Choose Your Portal" scroll button...');
        await page.locator('.js-portal-scroll').click();
        console.log('✓ "Choose Your Portal" button clicked and scrolled.');

        // 2. Click Patient portal card
        console.log('2. Testing Patient portal card click...');
        await page.locator('.portal-card[data-portal="patient"]').click();
        await page.waitForSelector('#patientModal:not(.hidden)');
        console.log('✓ Patient portal modal opened successfully.');

        // 3. Test First Timer button
        console.log('3. Testing First Timer choice button...');
        await page.locator('.modal-choice-button.first-timer').click();
        await page.waitForSelector('#firstTimerStep:not(.hidden-step)');
        console.log('✓ Patient "Create Account" step displayed.');

        // 4. Test switch to login step
        console.log('4. Testing switch to Login step...');
        await page.locator('#firstTimerStep [data-go-step="login"]').click();
        await page.waitForSelector('#loginStep:not(.hidden-step)');
        console.log('✓ Switched to Login step.');

        // 5. Test close Patient modal
        console.log('5. Testing closing Patient modal...');
        await page.locator('#modalClose').click();
        await page.waitForSelector('#patientModal.hidden');
        console.log('✓ Patient modal closed successfully.');

        // 6. Test Volunteer portal modal
        console.log('6. Testing Volunteer portal modal...');
        await page.locator('.portal-card[data-portal="volunteer"]').click();
        await page.waitForSelector('#volunteerModal:not(.hidden)');
        console.log('✓ Volunteer portal modal opened.');
        await page.locator('#volunteerModalClose').click();
        await page.waitForSelector('#volunteerModal.hidden');
        console.log('✓ Volunteer modal closed successfully.');

        // 7. Test Admin portal modal
        console.log('7. Testing Admin portal modal...');
        await page.locator('.portal-card[data-portal="admin"]').click();
        await page.waitForSelector('#adminModal:not(.hidden)');
        console.log('✓ Admin portal modal opened.');
        await page.locator('#adminModalClose').click();
        await page.waitForSelector('#adminModal.hidden');
        console.log('✓ Admin modal closed successfully.');

        if (errors.length === 0) {
            console.log('\n======================================================');
            console.log('SUCCESS: All portal cards, modals, and buttons work 100%!');
            console.log('======================================================');
        } else {
            console.error('Errors encountered:', errors);
            process.exit(1);
        }
    } catch (e) {
        console.error('Test failure:', e);
        process.exit(1);
    } finally {
        await browser.close();
    }
})();
