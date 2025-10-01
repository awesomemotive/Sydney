import { test, expect } from '@playwright/test';
import { SITE_CONFIG, VIEWPORTS } from '../utils/constants.js';

test.describe('Header Search Functionality', () => {
	
	test('should perform search on desktop', async ({ page }) => {
		// Set desktop viewport
		await page.setViewportSize(VIEWPORTS.DESKTOP);
		
		// Navigate to the site
		await page.goto(SITE_CONFIG.BASE_URL);
		
		// Find and click the header search icon
		await expect(page.getByRole('link', { name: 'Search for a product' })).toBeVisible();
		await page.getByRole('link', { name: 'Search for a product' }).click();
		
		// Confirm the search form appears
		await expect(page.getByRole('search')).toBeVisible();
		await expect(page.getByRole('searchbox', { name: 'Search for:' })).toBeVisible();
		await expect(page.getByRole('button', { name: 'Search' })).toBeVisible();
		
		// Type "hello" in the search field
		await page.getByRole('searchbox', { name: 'Search for:' }).fill('hello');
		
		// Perform the search
		await page.getByRole('button', { name: 'Search' }).click();
		
		// Confirm we've reached the search page
		await expect(page).toHaveURL(/.*\?s=hello/);
		
		// Confirm search results heading is present
		await expect(page.getByRole('heading', { name: /Search Results for.*hello/ })).toBeVisible();
		
		// Confirm at least one search result is present
		await expect(page.locator('article').first()).toBeVisible();
	});
	
	
});