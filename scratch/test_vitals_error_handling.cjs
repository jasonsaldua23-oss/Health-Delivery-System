const { chromium } = require('@playwright/test');
const fs = require('fs');
const path = require('path');

(async () => {
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage();

    // Read styles and scripts from actual project files
    const staffCss = fs.readFileSync(path.join(__dirname, '../Barangay Health Station/assets/styles.css'), 'utf8');
    const staffPhp = fs.readFileSync(path.join(__dirname, '../Barangay Health Station/index.php'), 'utf8');

    // Extract the exact validation script from Barangay Health Station/index.php
    const scriptMatch = staffPhp.match(/\/\/ Staff Vitals Encoding Inline Error Handling & Validation[\s\S]*?<\/script>/);
    if (!scriptMatch) {
        throw new Error('Could not find Staff Vitals script in Barangay Health Station/index.php');
    }
    const staffScript = scriptMatch[0].replace('</script>', '');

    // Create test HTML containing the exact vitals form with all service variations
    const testHtml = `
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Staff Vitals Test</title>
    <style>${staffCss}</style>
</head>
<body style="padding: 20px; background: #f1f5f9;">
    <section class="account-modal-backdrop" id="vitalsModalBackdrop">
        <div class="account-modal-card clinical-dialog-card">
            <form method="post" class="account-settings-form" id="staffVitalsForm" novalidate>
                <input type="hidden" name="action" value="save_vitals">

                <div class="account-modal-body">
                    <!-- Standard Vitals for all services -->
                    <div class="form-row-grid">
                        <div class="form-group-item">
                            <label for="queue_body_temp" class="form-field-label">
                                <span>Body Temperature</span>
                                <span class="required">*</span>
                            </label>
                            <div class="vital-input-group">
                                <input type="text" id="queue_body_temp" name="body_temperature" value="" placeholder="e.g. 36.5" class="form-input-field vital-numeric-input">
                                <span class="vital-unit-addon">°C</span>
                            </div>
                            <small class="field-subnote">Numbers only. Unit (°C) is automatically set by the system.</small>
                        </div>
                        <div class="form-group-item">
                            <label for="queue_pulse_rate" class="form-field-label">
                                <span>Pulse Rate (PR)</span>
                                <span class="required">*</span>
                            </label>
                            <div class="vital-input-group">
                                <input type="text" id="queue_pulse_rate" name="pulse_rate" value="" placeholder="e.g. 78" class="form-input-field vital-numeric-input">
                                <span class="vital-unit-addon">bpm</span>
                            </div>
                            <small class="field-subnote">Numbers only. Unit (bpm) is automatically set by the system.</small>
                        </div>
                    </div>

                    <div class="form-row-grid">
                        <div class="form-group-item">
                            <label for="queue_resp_rate" class="form-field-label">
                                <span>Respiration Rate (RR)</span>
                                <span class="required">*</span>
                            </label>
                            <div class="vital-input-group">
                                <input type="text" id="queue_resp_rate" name="respiration_rate" value="" placeholder="e.g. 18" class="form-input-field vital-numeric-input">
                                <span class="vital-unit-addon">cpm</span>
                            </div>
                            <small class="field-subnote">Numbers only. Unit (cpm) is automatically set by the system.</small>
                        </div>
                        <div class="form-group-item">
                            <label for="queue_blood_pres" class="form-field-label">
                                <span>Blood Pressure (BP)</span>
                                <span class="required">*</span>
                            </label>
                            <div class="vital-input-group">
                                <input type="text" id="queue_blood_pres" name="blood_pressure" value="" placeholder="e.g. 120/80" class="form-input-field vital-numeric-input">
                                <span class="vital-unit-addon">mmHg</span>
                            </div>
                            <small class="field-subnote">Numbers and slash only (e.g. 120/80). Unit (mmHg) is automatically set.</small>
                        </div>
                    </div>

                    <!-- Infant Immunization fields -->
                    <div class="form-row-grid">
                        <div class="form-group-item">
                            <label for="queue_height" class="form-field-label">
                                <span>Height (Infant &bull; Centimeters)</span>
                                <span class="required">*</span>
                            </label>
                            <div class="vital-input-group is-immunization-metric">
                                <input type="text" id="queue_height" name="height" value="" placeholder="e.g. 65" class="form-input-field vital-numeric-input">
                                <span class="vital-unit-addon">cm</span>
                            </div>
                            <small class="field-subnote">Numbers only. Unit (cm) is automatically set by the system.</small>
                        </div>
                        <div class="form-group-item">
                            <label for="queue_weight" class="form-field-label">
                                <span>Weight (Infant &bull; Kilograms)</span>
                                <span class="required">*</span>
                            </label>
                            <div class="vital-input-group is-immunization-metric">
                                <input type="text" id="queue_weight" name="weight" value="" placeholder="e.g. 7.2" class="form-input-field vital-numeric-input">
                                <span class="vital-unit-addon">kg</span>
                            </div>
                            <small class="field-subnote">Numbers only. Unit (kg) is automatically set by the system.</small>
                        </div>
                    </div>

                    <div class="form-group-item full-width" style="margin-top: 18px;">
                        <label for="queue_vaccine_select" class="form-field-label">
                            <span>Type of Vaccine (Infant / Child Schedule)</span>
                            <span class="required" style="color: #dc2626;">*</span>
                        </label>
                        <select id="queue_vaccine_select" name="vaccine_type_select" class="form-input-field">
                            <option value="">-- Select Standard Vaccine Type --</option>
                            <option value="BCG">BCG</option>
                            <option value="Hepatitis B">Hepatitis B</option>
                            <option value="Others">Others</option>
                        </select>
                        <div id="queue_vaccine_other_wrap" style="margin-top: 10px; display: none;">
                            <label for="queue_vaccine_other" class="form-field-label">Specify Other Vaccine Type:</label>
                            <input type="text" id="queue_vaccine_other" name="vaccine_type_other" value="" class="form-input-field">
                        </div>
                    </div>

                    <!-- TB DOTS Chest X-Ray -->
                    <div class="form-group-item full-width" style="margin-top: 18px;">
                        <label for="queue_chest_xray" class="form-field-label">
                            <span>Chest X-Ray Result</span>
                            <span class="required" style="color: #dc2626;">*</span>
                        </label>
                        <input type="text" id="queue_chest_xray" name="chest_xray" value="" class="form-input-field">
                    </div>
                </div>

                <div class="account-modal-footer">
                    <button type="submit" class="clinical-modal-save-btn" id="saveVitalsBtn">
                        Save Vital Signs
                    </button>
                </div>
            </form>
        </div>
    </section>

    <script>
    ${staffScript}
    </script>
</body>
</html>
    `;

    try {
        await page.setContent(testHtml);

        console.log('1. Submitting staff vitals form with all fields empty...');
        await page.locator('#saveVitalsBtn').click();
        await page.waitForTimeout(200);

        // Check error messages
        const errorElements = await page.locator('.field-error-text').all();
        console.log(`Found ${errorElements.length} inline error text messages in staff vitals modal.`);

        const errorDetails = [];
        for (const el of errorElements) {
            const text = await el.textContent();
            const color = await el.evaluate(e => window.getComputedStyle(e).color);
            const fontSize = await el.evaluate(e => window.getComputedStyle(e).fontSize);
            errorDetails.push({ text, color, fontSize });
        }
        console.log('Vitals error details:', JSON.stringify(errorDetails, null, 2));

        if (errorElements.length < 7) {
            throw new Error(`Expected at least 7 errors for empty fields, found ${errorElements.length}`);
        }

        // Verify color is red (#dc2626 -> rgb(220, 38, 38))
        if (!errorDetails[0].color.includes('220, 38, 38')) {
            throw new Error(`Expected red color rgb(220, 38, 38), got ${errorDetails[0].color}`);
        }

        // 2. Test live clearing when typing into Body Temp
        console.log('2. Typing valid Body Temperature (36.5)...');
        await page.locator('#queue_body_temp').fill('36.5');
        await page.waitForTimeout(100);
        const tempErrors = await page.locator('#queue_body_temp').locator('xpath=ancestor::div[contains(@class, "form-group-item")]//span[contains(@class, "field-error-text")]').count();
        console.log(`Errors under Body Temp after typing: ${tempErrors}`);
        if (tempErrors !== 0) throw new Error('Body temp error not cleared after typing!');

        // 3. Test invalid BP format
        console.log('3. Entering invalid BP format (120)...');
        await page.locator('#queue_blood_pres').fill('120');
        await page.locator('#saveVitalsBtn').click();
        await page.waitForTimeout(100);
        const bpErr = await page.locator('#queue_blood_pres').locator('xpath=ancestor::div[contains(@class, "form-group-item")]//span[contains(@class, "field-error-text")]').textContent();
        console.log('BP error text:', bpErr);
        if (!bpErr.includes('systolic/diastolic') && !bpErr.includes('120/80')) {
            throw new Error(`Unexpected BP error: ${bpErr}`);
        }

        // 4. Entering valid BP
        console.log('4. Entering valid BP (120/80)...');
        await page.locator('#queue_blood_pres').fill('120/80');
        await page.waitForTimeout(100);
        const bpErrorsAfter = await page.locator('#queue_blood_pres').locator('xpath=ancestor::div[contains(@class, "form-group-item")]//span[contains(@class, "field-error-text")]').count();
        console.log(`Errors under BP after typing valid: ${bpErrorsAfter}`);
        if (bpErrorsAfter !== 0) throw new Error('BP error not cleared after valid entry!');

        // 5. Test selecting vaccine dropdown
        console.log('5. Selecting BCG vaccine...');
        await page.locator('#queue_vaccine_select').selectOption('BCG');
        await page.waitForTimeout(100);
        const vacErrors = await page.locator('#queue_vaccine_select').locator('xpath=ancestor::div[contains(@class, "form-group-item")]//span[contains(@class, "field-error-text")]').count();
        console.log(`Errors under Vaccine Select after selecting: ${vacErrors}`);
        if (vacErrors !== 0) throw new Error('Vaccine error not cleared after selection!');

        console.log('✓ All Staff Vitals Encoding error handling checks passed successfully!');
    } catch (err) {
        console.error('Test FAILED:', err);
        process.exitCode = 1;
    } finally {
        await browser.close();
    }
})();
