import { test, expect } from '@playwright/test';

test('Patient homepage loads successfully', async ({ page }) => {
  // Open the Patient homepage
  const response = await page.goto('Health-Delivery-System-Latest/Patients/index.php');

  // Make sure the server responded successfully
  expect(response).not.toBeNull();
  expect(response.status()).toBe(200);

  // Make sure we are on the correct patient page
  await expect(page).toHaveURL(
    'http://localhost/Health-Delivery-System-Latest/Patients/index.php'
  );

  // Make sure the page itself is visible
  await expect(page.locator('body')).toBeVisible();
});