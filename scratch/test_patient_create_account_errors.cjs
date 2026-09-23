const { chromium } = require('@playwright/test');

(async () => {
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({ viewport: { width: 1280, height: 900 } });
    const page = await context.newPage();

    const pageErrors = [];
    page.on('pageerror', err => pageErrors.push(err.toString()));

    try {
        console.log('1. Navigating to Patients/index.php...');
        await page.goto('http://127.0.0.1:8000/Patients/index.php', { waitUntil: 'domcontentloaded', timeout: 60000 });

        // Open patient modal
        await page.locator('.portal-card[data-portal="patient"]').click();
        await page.waitForSelector('#patientModal:not(.hidden)');
        console.log('✓ Patient portal modal opened.');

        // Click First Timer button
        await page.locator('.modal-choice-button.first-timer').click();
        await page.waitForSelector('#firstTimerStep:not(.hidden-step)');
        console.log('✓ Create Account step displayed.');

        // Attempt to submit with all fields empty
        console.log('2. Clicking "Create Account" with empty form...');
        await page.locator('#firstTimerForm button[type="submit"]').click();

        // Wait a brief moment for DOM updates
        await page.waitForTimeout(300);

        // Check for error text elements
        const errorElements = await page.locator('#firstTimerForm .field-error-text').all();
        console.log(`Found ${errorElements.length} inline error text messages.`);

        const errorTexts = [];
        for (const el of errorElements) {
            const text = await el.textContent();
            const color = await el.evaluate(e => window.getComputedStyle(e).color);
            const fontSize = await el.evaluate(e => window.getComputedStyle(e).fontSize);
            errorTexts.push({ text, color, fontSize });
        }
        console.log('Error details:', JSON.stringify(errorTexts, null, 2));

        if (errorElements.length < 8) {
            throw new Error(`Expected at least 8 error messages for required fields, got ${errorElements.length}`);
        }

        // Verify color is red (e.g. rgb(220, 38, 38) for #dc2626)
        const firstColor = errorTexts[0].color;
        console.log('Error text color is:', firstColor);
        if (!firstColor.includes('220, 38, 38') && !firstColor.includes('239, 68, 68') && !firstColor.includes('red')) {
            throw new Error(`Expected red color, got ${firstColor}`);
        }

        // Check inputs with input-has-error class
        const errorInputs = await page.locator('#firstTimerForm .input-has-error').count();
        console.log(`Found ${errorInputs} inputs marked with input-has-error.`);

        // 3. Test real-time clearing when user types into First Name
        console.log('3. Typing into First Name...');
        await page.locator('#firstName').fill('Juan');
        await page.waitForTimeout(100);
        const firstNameErrors = await page.locator('#firstName').locator('xpath=ancestor::div[contains(@class, "field-group")]//span[contains(@class, "field-error-text")]').count();
        console.log(`Errors under First Name after typing: ${firstNameErrors}`);
        if (firstNameErrors !== 0) {
            throw new Error('First Name error was not cleared after typing!');
        }

        // 4. Test real-time clearing for gender
        console.log('4. Selecting Male gender...');
        await page.locator('input[name="gender"][value="Male"]').dispatchEvent('click');
        await page.waitForTimeout(100);
        const genderErrors = await page.locator('.gender-options').locator('xpath=ancestor::div[contains(@class, "field-group")]//span[contains(@class, "field-error-text")]').count();
        console.log(`Errors under Gender after checking radio: ${genderErrors}`);
        if (genderErrors !== 0) {
            throw new Error('Gender error was not cleared after selection!');
        }

        // 5. Test invalid phone number error
        console.log('5. Testing invalid phone number format...');
        await page.locator('#regPhone').fill('12345');
        await page.locator('#firstTimerForm button[type="submit"]').click();
        await page.waitForTimeout(200);
        const phoneError = await page.locator('#regPhone').locator('xpath=ancestor::div[contains(@class, "field-group")]//span[contains(@class, "field-error-text")]').textContent();
        console.log('Phone error text:', phoneError);
        if (!phoneError.includes('09') || !phoneError.includes('11 digits')) {
            throw new Error(`Unexpected phone error text: ${phoneError}`);
        }

        // Fix phone number
        await page.locator('#regPhone').fill('09123456789');

        // 6. Test short password error
        console.log('6. Testing short password error...');
        await page.locator('#regPassword').fill('123');
        await page.locator('#firstTimerForm button[type="submit"]').click();
        await page.waitForTimeout(200);
        const passError = await page.locator('#regPassword').locator('xpath=ancestor::div[contains(@class, "field-group")]//span[contains(@class, "field-error-text")]').textContent();
        console.log('Password error text:', passError);
        if (!passError.includes('6 characters')) {
            throw new Error(`Unexpected password error text: ${passError}`);
        }

        console.log('✓ All Patient Account Creation error handling checks passed successfully!');
    } catch (err) {
        console.error('Test FAILED:', err);
        process.exitCode = 1;
    } finally {
        await browser.close();
    }
})();
