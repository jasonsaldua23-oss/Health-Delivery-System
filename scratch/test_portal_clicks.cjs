const { chromium } = require('@playwright/test');

(async () => {
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage();

    console.log('Navigating to landing page...');
    await page.goto('http://localhost/Health-Delivery-System-Latest/Patients/index.php');

    // 1. Choose Your Portal scroll button
    await page.locator('.js-portal-scroll').click();
    console.log('✓ "Choose Your Portal" button clicked.');

    // 2. Patient Portal Card
    await page.locator('.portal-card[data-portal="patient"]').click();
    await page.waitForSelector('#patientModal:not(.hidden)');
    console.log('✓ Patient portal modal opened.');

    // 3. Switch to Create Account & Login
    await page.locator('.modal-choice-button.first-timer').click();
    await page.waitForSelector('#firstTimerStep:not(.hidden-step)');
    console.log('✓ First Timer step opened.');

    await page.locator('#firstTimerStep [data-go-step="login"]').click();
    await page.waitForSelector('#loginStep:not(.hidden-step)');
    console.log('✓ Switched to Login step.');

    // 4. Close Patient Modal
    await page.locator('#modalClose').click();
    await page.waitForSelector('#patientModal', { state: 'hidden' });
    console.log('✓ Patient modal closed successfully.');

    // 5. Volunteer Portal Card
    await page.locator('.portal-card[data-portal="volunteer"]').click();
    await page.waitForSelector('#volunteerModal:not(.hidden)');
    console.log('✓ Volunteer modal opened.');
    await page.locator('#volunteerModalClose').click();
    await page.waitForSelector('#volunteerModal', { state: 'hidden' });
    console.log('✓ Volunteer modal closed successfully.');

    // 6. Admin Portal Card
    await page.locator('.portal-card[data-portal="admin"]').click();
    await page.waitForSelector('#adminModal:not(.hidden)');
    console.log('✓ Admin modal opened.');
    await page.locator('#adminModalClose').click();
    await page.waitForSelector('#adminModal', { state: 'hidden' });
    console.log('✓ Admin modal closed successfully.');

    await browser.close();
    console.log('\n========================================');
    console.log('ALL PORTAL CARDS AND BUTTONS WORKING 100%!');
    console.log('========================================');
})();
