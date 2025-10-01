import { test, expect } from '@playwright/test';
import { SITE_CONFIG, VIEWPORTS } from '../utils/constants.js';

test.describe('Sidebar Tests', () => {
	test('sidebar exists on blog page', async ({ page }) => {
		// Navigate to the blog page (correct URL based on site structure)
		await page.goto(`${SITE_CONFIG.BASE_URL}my-blog-page/`);

		// Check that sidebar exists and is visible
		// Sydney theme uses #secondary with widget-area class for sidebar
		const sidebar = page.locator('#secondary, .widget-area').first();
		await expect(sidebar).toBeVisible();

		// Verify sidebar contains widget content
		const sidebarWidgets = sidebar.locator('aside, .widget');
		await expect(sidebarWidgets.first()).toBeVisible();
	});

	test('sidebar exists on single post page', async ({ page }) => {
		// Navigate to a specific post (using the actual post URL from the site)
		await page.goto(`${SITE_CONFIG.BASE_URL}2025/09/04/hello-world/`);

		// Check that sidebar exists and is visible
		const sidebar = page.locator('#secondary, .widget-area').first();
		await expect(sidebar).toBeVisible();

		// Verify sidebar contains widget content
		const sidebarWidgets = sidebar.locator('aside, .widget');
		await expect(sidebarWidgets.first()).toBeVisible();
	});

	test('sidebar exists on category page', async ({ page }) => {
		// Navigate to a category page (using 'uncategorized' category)
		await page.goto(`${SITE_CONFIG.BASE_URL}category/uncategorized/`);

		// Check that sidebar exists and is visible
		const sidebar = page.locator('#secondary, .widget-area').first();
		await expect(sidebar).toBeVisible();

		// Verify sidebar contains widget content
		const sidebarWidgets = sidebar.locator('aside, .widget');
		await expect(sidebarWidgets.first()).toBeVisible();
	});

	test('sidebar exists on sample page', async ({ page }) => {
		// Navigate to the sample page
		await page.goto(`${SITE_CONFIG.BASE_URL}sample-page/`);

		// Check that sidebar exists and is visible
		const sidebar = page.locator('#secondary, .widget-area').first();
		await expect(sidebar).toBeVisible();

		// Verify sidebar contains widget content
		const sidebarWidgets = sidebar.locator('aside, .widget');
		await expect(sidebarWidgets.first()).toBeVisible();
	});

	test('sidebar responsive behavior', async ({ page }) => {
		// Test sidebar behavior on different viewport sizes
		const viewports = [VIEWPORTS.MOBILE, VIEWPORTS.TABLET, VIEWPORTS.DESKTOP];

		for (const viewport of viewports) {
			await page.setViewportSize(viewport);

			// Navigate to blog page for each viewport
			await page.goto(`${SITE_CONFIG.BASE_URL}my-blog-page/`);

			// Check that sidebar exists
			const sidebar = page.locator('#secondary, .widget-area').first();
			const sidebarExists = await sidebar.count() > 0;
			expect(sidebarExists).toBe(true);

			// On larger screens, sidebar should be visible
			if (viewport.width >= VIEWPORTS.TABLET.width) {
				await expect(sidebar).toBeVisible();
			}
			// On mobile, sidebar might be hidden or collapsed but should exist in DOM
		}
	});

	test('sidebar contains expected widgets', async ({ page }) => {
		// Navigate to the blog page
		await page.goto(`${SITE_CONFIG.BASE_URL}my-blog-page/`);

		// Check for common sidebar widgets in Sydney theme
		const sidebar = page.locator('#secondary, .widget-area').first();

		// Look for specific widgets that exist in the Sydney theme
		const searchWidget = sidebar.locator('form.search-form, input[type="search"]');
		const recentPostsWidget = sidebar.locator('aside:has(h3:has-text("Recent Posts"))');
		const categoriesWidget = sidebar.locator('aside:has(h3:has-text("Categories"))');
		const archivesWidget = sidebar.locator('aside:has(h3:has-text("Archives"))');

		// At least one of these widgets should exist
		const hasSearch = (await searchWidget.count()) > 0;
		const hasRecentPosts = (await recentPostsWidget.count()) > 0;
		const hasCategories = (await categoriesWidget.count()) > 0;
		const hasArchives = (await archivesWidget.count()) > 0;

		expect(hasSearch || hasRecentPosts || hasCategories || hasArchives).toBe(true);
	});

	test('sidebar background color matches theme design', async ({ page }) => {
		// Navigate to the blog page
		await page.goto(`${SITE_CONFIG.BASE_URL}my-blog-page/`);

		// Check sidebar background color
		const sidebar = page.locator('#secondary, .widget-area').first();
		const sidebarBgColor = await sidebar.evaluate(el => {
			return window.getComputedStyle(el).backgroundColor;
		});

		// Test that sidebar background color is the expected light gray: rgb(244, 245, 247)
		expect(sidebarBgColor).toBe('rgb(244, 245, 247)');
	});

	test('sidebar link colors match theme design', async ({ page }) => {
		// Navigate to the blog page
		await page.goto(`${SITE_CONFIG.BASE_URL}my-blog-page/`);

		// Check sidebar link colors
		const sidebar = page.locator('#secondary, .widget-area').first();
		const sidebarLinks = sidebar.locator('a[href]');

		// Get all link colors and verify they match the expected blue color
		const linkCount = await sidebarLinks.count();
		expect(linkCount).toBeGreaterThan(0);

		for (let i = 0; i < Math.min(linkCount, 5); i++) { // Test first 5 links to avoid timeout
			const linkColor = await sidebarLinks.nth(i).evaluate(el => {
				return window.getComputedStyle(el).color;
			});
			// Test that link color is the expected blue: rgb(59, 114, 208)
			expect(linkColor).toBe('rgb(59, 114, 208)');
		}
	});

	test('sidebar widget title colors match theme design', async ({ page }) => {
		// Navigate to the blog page
		await page.goto(`${SITE_CONFIG.BASE_URL}my-blog-page/`);

		// Check sidebar widget title colors
		const sidebar = page.locator('#secondary, .widget-area').first();
		const widgetTitles = sidebar.locator('h3');

		// Get all widget title colors and verify they match the expected dark blue color
		const titleCount = await widgetTitles.count();
		expect(titleCount).toBeGreaterThan(0);

		for (let i = 0; i < titleCount; i++) {
			const titleColor = await widgetTitles.nth(i).evaluate(el => {
				return window.getComputedStyle(el).color;
			});
			// Test that title color is the expected dark blue: rgb(0, 16, 46)
			expect(titleColor).toBe('rgb(0, 16, 46)');
		}
	});
});