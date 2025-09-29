import { test, expect } from '@playwright/test';
import { SITE_CONFIG, VIEWPORTS } from '../utils/constants.js';

test.describe('404 Page', () => {
	test('404 page displays correct error message and status', async ({ page }) => {
		// Navigate to a non-existent page to trigger 404
		const response = await page.goto(`${SITE_CONFIG.BASE_URL}non-existing-page`);

		// Verify HTTP status is 404
		expect(response.status()).toBe(404);

		// Check page title
		await expect(page).toHaveTitle(/Page not found/);

		// Check main error heading
		const errorHeading = page.locator('main h1');
		await expect(errorHeading).toBeVisible();
		const headingText = await errorHeading.textContent();
		expect(headingText).toContain('Oops! That page can');
		expect(headingText).toContain('be found');

		// Check error message
		const errorMessage = page.locator('main p');
		await expect(errorMessage).toBeVisible();
		await expect(errorMessage).toHaveText('It looks like nothing was found at this location. Maybe try one of the links below or a search?');
	});

	test('404 page search functionality works', async ({ page }) => {
		// Navigate to a non-existent page
		await page.goto(`${SITE_CONFIG.BASE_URL}non-existing-page`);

		// Check that search form exists in main content
		const searchForm = page.locator('main form');
		await expect(searchForm).toBeVisible();

		// Check search input
		const searchInput = searchForm.locator('input[type="search"]');
		await expect(searchInput).toBeVisible();

		// Check search button
		const searchButton = searchForm.locator('input[type="submit"], button');
		await expect(searchButton).toBeVisible();
		await expect(searchButton).toHaveAttribute('value', 'Search');

		// Test search functionality (type in search and check it works)
		await searchInput.fill('test search');
		await expect(searchInput).toHaveValue('test search');

		// Note: We don't actually submit the form as that would navigate away from 404 page
	});

	test('404 page maintains site navigation and footer', async ({ page }) => {
		// Navigate to a non-existent page
		await page.goto(`${SITE_CONFIG.BASE_URL}non-existing-page`);

		// Check that navigation is still present and functional
		const nav = page.locator('#site-navigation');
		await expect(nav).toBeVisible();

		// Check main navigation links
		const homeLink = nav.locator('a:has-text("Home")');
		await expect(homeLink).toBeVisible();
		await expect(homeLink).toHaveAttribute('href', /\/$/);

		const blogLink = nav.locator('a:has-text("Blog")');
		await expect(blogLink).toBeVisible();
		await expect(blogLink).toHaveAttribute('href', /my-blog-page/);

		// Check that footer is present
		const footer = page.locator('.site-footer, footer');
		await expect(footer).toBeVisible();

		// Check footer content
		await expect(footer).toContainText('© 2025 Tests');
		await expect(footer).toContainText('Proudly powered by');
	});

	test('404 page accessibility features are present', async ({ page }) => {
		// Navigate to a non-existent page
		await page.goto(`${SITE_CONFIG.BASE_URL}non-existing-page`);

		// Check for skip link
		const skipLink = page.locator('a:has-text("Skip to content")');
		await expect(skipLink).toBeVisible();
		await expect(skipLink).toHaveAttribute('href', '#content');

		// Check that the error heading is an h1
		const h1 = page.locator('main h1');
		await expect(h1).toBeVisible();

		// Ensure no other h1 elements exist (accessibility best practice)
		const allH1s = page.locator('h1');
		await expect(allH1s).toHaveCount(1);
	});

	test('404 page is responsive across different viewports', async ({ page }) => {
		// Test on different viewports
		const viewports = [
			VIEWPORTS.MOBILE,
			VIEWPORTS.TABLET,
			VIEWPORTS.DESKTOP
		];

		for (const viewport of viewports) {
			// Set viewport size
			await page.setViewportSize({ width: viewport.width, height: viewport.height });

			// Navigate to a non-existent page
			await page.goto(`${SITE_CONFIG.BASE_URL}non-existing-page`);

			// Check that essential 404 elements are visible
			await expect(page.locator('main h1')).toBeVisible();
			await expect(page.locator('main p')).toBeVisible();
			await expect(page.locator('main form')).toBeVisible();

			// Check navigation is still present (may be hidden on mobile but should exist)
			const nav = page.locator('#site-navigation');
			await expect(nav).toBeAttached(); // Check it exists in DOM, not necessarily visible
		}
	});

	test('404 page maintains theme styling', async ({ page }) => {
		// Navigate to a non-existent page
		await page.goto(`${SITE_CONFIG.BASE_URL}non-existing-page`);

		// Check error heading colors
		const errorHeading = page.locator('main h1');
		const headingColor = await errorHeading.evaluate(el => {
			return window.getComputedStyle(el).color;
		});
		expect(headingColor).toBe('rgb(0, 16, 46)'); // Dark blue

		// Check error message colors
		const errorMessage = page.locator('main p');
		const messageColor = await errorMessage.evaluate(el => {
			return window.getComputedStyle(el).color;
		});
		expect(messageColor).toBe('rgb(20, 46, 44)'); // Dark teal
	});

	test('404 page handles different non-existent URLs', async ({ page }) => {
		// Test multiple non-existent URLs to ensure consistent 404 behavior
		const nonExistentUrls = [
			'non-existing-page',
			'random-url-12345',
			'fake-post-slug',
			'another-missing-page'
		];

		for (const urlSlug of nonExistentUrls) {
			// Navigate to non-existent page
			const response = await page.goto(`${SITE_CONFIG.BASE_URL}${urlSlug}`);
			expect(response.status()).toBe(404);

			// Verify 404 page loads consistently
			const headingText = await page.locator('main h1').textContent();
			expect(headingText).toContain('Oops! That page can');
			expect(headingText).toContain('be found');
			await expect(page.locator('main p')).toContainText('nothing was found at this location');
		}
	});

	test('404 page search form submits correctly', async ({ page }) => {
		// Navigate to a non-existent page
		await page.goto(`${SITE_CONFIG.BASE_URL}non-existing-page`);

		// Fill out search form
		const searchInput = page.locator('main input[type="search"]');
		await searchInput.fill('test query');

		// Submit search form
		const searchButton = page.locator('main input[type="submit"]');
		await searchButton.click();

		// Should redirect to search results page
		await page.waitForURL('**/?s=test+query');
		await expect(page).toHaveURL(/\?s=test\+query/);

		// Verify we're on a search results page
		await expect(page.locator('body')).toHaveClass(/search/);
	});

	test('404 page maintains proper semantic structure', async ({ page }) => {
		// Navigate to a non-existent page
		await page.goto(`${SITE_CONFIG.BASE_URL}non-existing-page`);

		// Check semantic HTML structure
		const main = page.locator('main');
		await expect(main).toBeVisible();

		// Error content should be properly contained
		const errorContainer = main.locator('> *').first();
		await expect(errorContainer).toBeVisible();

		// Search form should be in main content area
		const searchForm = main.locator('form');
		await expect(searchForm).toBeVisible();

		// Check that navigation and footer maintain their semantic roles
		const nav = page.locator('#site-navigation');
		await expect(nav).toBeVisible();

		const footer = page.locator('footer, .site-footer');
		await expect(footer).toBeVisible();
	});
});