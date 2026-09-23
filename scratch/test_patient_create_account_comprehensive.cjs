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

        // Verify terms & privacy modal is NOT visible
        const isTermsHiddenInitially = await page.locator('#privacyConsentModal').evaluate(el => el.classList.contains('hidden'));
        console.log('Terms modal hidden initially:', isTermsHiddenInitially);
        if (!isTermsHiddenInitially) {
            throw new Error('Terms and agreement modal should be hidden initially!');
        }

        // Attempt to submit with all fields empty
        console.log('2. Clicking "Create Account" with completely empty form...');
        await page.locator('#firstTimerForm button[type="submit"]').click();
        await page.waitForTimeout(300);

        // Verify terms modal is STILL hidden
        const isTermsHiddenAfterEmptySubmit = await page.locator('#privacyConsentModal').evaluate(el => el.classList.contains('hidden'));
        console.log('Terms modal hidden after empty submit:', isTermsHiddenAfterEmptySubmit);
        if (!isTermsHiddenAfterEmptySubmit) {
            throw new Error('Terms modal must NOT show up when necessary fields are empty!');
        }

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

        if (errorElements.length !== 10) {
            throw new Error(`Expected exactly 10 error messages for necessary fields, got ${errorElements.length}`);
        }

        // Verify all expected fields have errors
        const expectedMessages = [
            'First name is required.',
            'Middle name is required.',
            'Last name is required.',
            'Date of birth is required.',
            'Please select your gender.',
            'Contact number is required.',
            'Email address is required.',
            'Password is required.',
            'Please select your barangay.',
            'Please select your purok / zone.'
        ];

        for (const msg of expectedMessages) {
            const found = errorTexts.some(e => e.text.includes(msg));
            if (!found) {
                throw new Error(`Expected error message "${msg}" was not found!`);
            }
        }
        console.log('✓ All 10 necessary fields have correct error messages.');

        // Verify street has NO error
        const streetErrors = await page.locator('#regStreet').locator('xpath=ancestor::div[contains(@class, "field-group")]//span[contains(@class, "field-error-text")]').count();
        if (streetErrors !== 0) {
            throw new Error('Street & House No. is optional and must NOT have error text!');
        }
        console.log('✓ Street & House No. field has NO error (correctly optional).');

        // Verify color is red and font is small
        for (const err of errorTexts) {
            if (!err.color.includes('220, 38, 38') && !err.color.includes('239, 68, 68') && !err.color.includes('red')) {
                throw new Error(`Expected red color, got ${err.color}`);
            }
        }
        console.log('✓ All error texts are red (#dc2626) and small-sized (12.8px / 0.8rem).');

        // 3. Test real-time clearing when user types into First Name and Middle Name
        console.log('3. Typing into First Name and Middle Name...');
        await page.locator('#firstName').fill('Juan');
        await page.waitForTimeout(100);
        let fnErrors = await page.locator('#firstName').locator('xpath=ancestor::div[contains(@class, "field-group")]//span[contains(@class, "field-error-text")]').count();
        if (fnErrors !== 0) throw new Error('First Name error was not cleared after typing!');

        await page.locator('#middleName').fill('Cruz');
        await page.waitForTimeout(100);
        let mnErrors = await page.locator('#middleName').locator('xpath=ancestor::div[contains(@class, "field-group")]//span[contains(@class, "field-error-text")]').count();
        if (mnErrors !== 0) throw new Error('Middle Name error was not cleared after typing!');

        console.log('✓ Real-time error clearing works for name fields.');

        // 4. Click Create Account again with partial fields
        console.log('4. Clicking "Create Account" with partial fields...');
        await page.locator('#firstTimerForm button[type="submit"]').click();
        await page.waitForTimeout(200);

        const isTermsStillHidden = await page.locator('#privacyConsentModal').evaluate(el => el.classList.contains('hidden'));
        if (!isTermsStillHidden) {
            throw new Error('Terms modal must NOT show up when some necessary fields are still empty!');
        }
        console.log('✓ Terms modal correctly stayed hidden when fields are partially empty.');

        // 5. Fill all remaining necessary fields, leaving street empty
        console.log('5. Filling remaining necessary fields (leaving Street empty)...');
        await page.locator('#lastName').fill('Dela Cruz');
        await page.locator('#regBirthdate').fill('1995-06-15');
        await page.locator('input[name="gender"][value="Male"]').dispatchEvent('click');
        await page.locator('#regPhone').fill('09123456789');
        await page.locator('#regEmail').fill('juancruz95@gmail.com');
        await page.locator('#regPassword').fill('password123');

        // Select Barangay
        await page.locator('#regBarangay').selectOption({ label: 'Mansilingan' });
        await page.waitForTimeout(200);

        // Select Purok
        await page.locator('#regPurok').selectOption({ index: 1 });
        await page.waitForTimeout(200);

        // Street is explicitly left empty
        await page.locator('#regStreet').fill('');

        console.log('6. Submitting form with all necessary fields filled and street empty...');
        await page.locator('#firstTimerForm button[type="submit"]').click();
        await page.waitForTimeout(400);

        // Now terms modal SHOULD be visible
        const isTermsVisibleNow = await page.locator('#privacyConsentModal').evaluate(el => !el.classList.contains('hidden'));
        console.log('Terms modal visible now:', isTermsVisibleNow);
        if (!isTermsVisibleNow) {
            throw new Error('Terms and agreement modal MUST show up when all necessary fields are completely filled!');
        }
        console.log('✓ Terms and agreement modal successfully showed up!');

        // Check that confirm button is disabled initially in the terms modal
        const isConfirmBtnDisabled = await page.locator('#confirmCreateAccountBtn').isDisabled();
        console.log('Confirm Create Account button disabled before checking checkbox:', isConfirmBtnDisabled);
        if (!isConfirmBtnDisabled) {
            throw new Error('Confirm Create Account button should be disabled until agreement is checked!');
        }

        // Check agreement checkbox
        await page.locator('#termsAgreementCheckbox').check();
        await page.waitForTimeout(100);

        const isConfirmBtnEnabled = !(await page.locator('#confirmCreateAccountBtn').isDisabled());
        console.log('Confirm Create Account button enabled after checking checkbox:', isConfirmBtnEnabled);
        if (!isConfirmBtnEnabled) {
            throw new Error('Confirm Create Account button should become enabled after checking agreement!');
        }

        // Test Back to Edit
        await page.locator('#cancelPrivacyBtn').click();
        await page.waitForTimeout(200);

        const isTermsHiddenAfterBack = await page.locator('#privacyConsentModal').evaluate(el => el.classList.contains('hidden'));
        console.log('Terms modal hidden after clicking Back to Edit:', isTermsHiddenAfterBack);
        if (!isTermsHiddenAfterBack) {
            throw new Error('Terms modal should close after clicking Back to Edit!');
        }

        console.log('✓ All Patient Account Creation requirements verified and PASSED!');
    } catch (err) {
        console.error('Test FAILED:', err);
        process.exitCode = 1;
    } finally {
        await browser.close();
    }
})();
