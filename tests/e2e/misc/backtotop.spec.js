import { test, expect } from '@playwright/test';
import { SITE_CONFIG, VIEWPORTS } from '../utils/constants.js';

test.describe('Back to Top Button Functionality', () => {
	
	test('should show back to top button when scrolling down and hide when at top', async ({ page }) => {
		// Set desktop viewport
		await page.setViewportSize(VIEWPORTS.DESKTOP);
		
		// Navigate to the site
		await page.goto(SITE_CONFIG.BASE_URL);
		
		// Verify we start at the top of the page
		const initialScrollPosition = await page.evaluate(() => window.pageYOffset);
		expect(initialScrollPosition).toBe(0);
		
		// Verify back to top button is not visible initially
		const backToTopButton = page.locator('.go-top');
		await expect(backToTopButton).not.toBeVisible();
		
		// Scroll down enough to trigger the back to top button
		await page.evaluate(() => window.scrollTo(0, 1000));
		
		// Wait for scroll to complete
		await page.waitForTimeout(500);
		
		// Wait for the button to appear and verify it's visible
		await expect(backToTopButton).toBeVisible({ timeout: 10000 });
		
		// Verify we're actually scrolled down
		const scrolledPosition = await page.evaluate(() => window.pageYOffset);
		expect(scrolledPosition).toBeGreaterThan(500);
		
		// Scroll back to top manually
		await page.evaluate(() => window.scrollTo(0, 0));
		
		// Wait a moment for any animations to complete
		await page.waitForTimeout(500);
		
		// Verify back to top button is hidden again
		await expect(backToTopButton).not.toBeVisible();
		
		// Verify we're back at the top
		const finalScrollPosition = await page.evaluate(() => window.pageYOffset);
		expect(finalScrollPosition).toBe(0);
	});
	
	test('should scroll to top when back to top button is clicked', async ({ page }) => {
		// Set desktop viewport
		await page.setViewportSize(VIEWPORTS.DESKTOP);
		
		// Navigate to the site
		await page.goto(SITE_CONFIG.BASE_URL);
		
		// Scroll down to make the back to top button visible
		await page.evaluate(() => window.scrollTo(0, 2000));
		
		// Wait a moment for scroll to complete
		await page.waitForTimeout(500);
		
		// Verify we're scrolled down
		const scrolledPosition = await page.evaluate(() => window.pageYOffset);
		expect(scrolledPosition).toBeGreaterThan(1000);
		
		// Wait for and verify the back to top button is visible
		const backToTopButton = page.locator('.go-top');
		await expect(backToTopButton).toBeVisible({ timeout: 10000 });
		
		// Click the back to top button
		await backToTopButton.click();
		
		// Wait for scroll animation to complete
		await page.waitForTimeout(1500);
		
		// Verify we're back at the top of the page
		const finalScrollPosition = await page.evaluate(() => window.pageYOffset);
		expect(finalScrollPosition).toBe(0);
		
		// Verify the button is no longer visible
		await expect(backToTopButton).not.toBeVisible();
	});
	
	test('should work on mobile viewport', async ({ page }) => {
		// Set mobile viewport
		await page.setViewportSize(VIEWPORTS.MOBILE);
		
		// Navigate to the site
		await page.goto(SITE_CONFIG.BASE_URL);
		
		// Verify we start at the top
		const initialScrollPosition = await page.evaluate(() => window.pageYOffset);
		expect(initialScrollPosition).toBe(0);
		
		// Verify back to top button is not visible initially
		const backToTopButton = page.locator('.go-top');
		await expect(backToTopButton).not.toBeVisible();
		
		// Scroll down on mobile
		await page.evaluate(() => window.scrollTo(0, 1200));
		
		// Wait for scroll to complete
		await page.waitForTimeout(500);
		
		// Wait for the button to appear
		await expect(backToTopButton).toBeVisible({ timeout: 10000 });
		
		// Click the back to top button
		await backToTopButton.click();
		
		// Wait for scroll animation
		await page.waitForTimeout(1500);
		
		// Verify we're back at the top
		const finalScrollPosition = await page.evaluate(() => window.pageYOffset);
		expect(finalScrollPosition).toBe(0);
		
		// Verify button is hidden
		await expect(backToTopButton).not.toBeVisible();
	});	
});
