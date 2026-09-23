const { chromium } = require('@playwright/test');

(async () => {
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({ viewport: { width: 1280, height: 900 } });
    const page = await context.newPage();

    page.on('pageerror', err => console.error('PAGE ERROR:', err.toString()));
    page.on('console', msg => console.log('PAGE LOG:', msg.text()));

    try {
        console.log('1. Logging in via login-handler.php...');
        const loginRes = await page.request.post('http://127.0.0.1:8000/Patients/login-handler.php', {
            form: {
                action: 'login_staff',
                email: 'staff-bata@bata.health',
                password: 'staff123'
            }
        });
        const loginData = await loginRes.json();
        console.log('Login result:', loginData);

        // Set cookies to page context
        const cookies = await context.cookies();

        console.log('2. Navigating to Barangay Health Station/?page=patients...');
        await page.goto('http://127.0.0.1:8000/Barangay%20Health%20Station/index.php?page=patients', { waitUntil: 'domcontentloaded' });
        console.log('Current URL:', page.url());

        console.log('3. Looking for infant toggle buttons...');
        const infantBtns = await page.locator('.patient-infant-toggle-btn').all();
        console.log(`Found ${infantBtns.length} infant toggle buttons.`);

        if (infantBtns.length === 0) {
            console.log('No infant toggle buttons found!');
            return;
        }

        // Click first infant toggle button
        await infantBtns[0].click();
        await page.waitForTimeout(300);

        // Click first infant popup item
        const infantCards = await page.locator('.infant-popup-card-item').all();
        console.log(`Found ${infantCards.length} infant cards in tray.`);
        await infantCards[0].click();
        await page.waitForTimeout(500);

        // Check modal visibility
        const modal = page.locator('#staffInfantModalBackdrop');
        const isVisibleBefore = await modal.evaluate(el => !el.classList.contains('hidden') && el.style.display !== 'none');
        console.log('Infant modal visible after click:', isVisibleBefore);

        // Find notes field and update it
        const notesField = page.locator('#staffInfantEditForm textarea[name="notes"]');
        await notesField.fill('Automated test note at ' + Date.now());

        // Listen for navigations or requests
        page.on('framenavigated', frame => {
            if (frame === page.mainFrame()) {
                console.log('PAGE NAVIGATED TO:', frame.url());
            }
        });

        // Click Save button
        console.log('4. Clicking Save Infant Profile Details button...');
        const saveBtn = page.locator('#saveInfantProfileBtn');
        await saveBtn.click();

        // Wait to see what happens
        await page.waitForTimeout(1000);

        const isVisibleAfter1s = await modal.evaluate(el => !el.classList.contains('hidden') && el.style.display !== 'none');
        console.log('Infant modal visible 1s after save:', isVisibleAfter1s);

        const alertBoxText = await page.locator('#staffInfantSaveAlert').textContent().catch(() => '');
        console.log('Alert box text:', alertBoxText);

        await page.waitForTimeout(3000);
        const isVisibleAfter3s = await modal.evaluate(el => !el.classList.contains('hidden') && el.style.display !== 'none');
        console.log('Infant modal visible 3s after save:', isVisibleAfter3s);

    } catch (err) {
        console.error('Test error:', err);
    } finally {
        await browser.close();
    }
})();
