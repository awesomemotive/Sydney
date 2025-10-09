import { test, expect } from '@playwright/test';
import { SITE_CONFIG, VIEWPORTS } from '../utils/constants.js';

test.describe('Navigation Tests', () => {
	test.beforeEach(async ({ page }) => {
		// Set desktop viewport as default
		await page.setViewportSize(VIEWPORTS.DESKTOP);
	});

	test('should have clickable About link that navigates to correct page', async ({ page }) => {
		// Navigate to homepage
		await page.goto(SITE_CONFIG.BASE_URL);
		await page.waitForLoadState('networkidle');

		// Find the About link in the navigation
		const aboutLink = page.getByRole('navigation').getByRole('link', { name: 'About' });
		
		// Verify the About link is visible and has correct href
		await expect(aboutLink).toBeVisible();
		await expect(aboutLink).toHaveAttribute('href', SITE_CONFIG.BASE_URL + 'about/');
		
		// Verify the link is clickable
		await expect(aboutLink).toBeEnabled();
		
		// Click the About link
		await aboutLink.click();
		
		// Wait for navigation to complete
		await page.waitForLoadState('networkidle');
		
		// Verify we're on the correct page
		await expect(page).toHaveURL(SITE_CONFIG.BASE_URL + 'about/');
		
		// Verify the page title contains "About"
		await expect(page).toHaveTitle(/About/);
		
		// Verify we can find expected content on the About page
		// This ensures the navigation actually worked and we're on the right page
		const pageContent = page.locator('main');
		await expect(pageContent).toBeVisible();
	});

	test('should have clickable second level menu items under About', async ({ page }) => {
		// Navigate to homepage
		await page.goto(SITE_CONFIG.BASE_URL);
		await page.waitForLoadState('networkidle');

		// Hover over About to reveal dropdown menu
		const aboutLink = page.getByRole('navigation').getByRole('link', { name: 'About' });
		await aboutLink.hover();

		// Wait for dropdown to appear
		await page.waitForTimeout(300);

		// Find the second level menu item "Hello world!"
		const secondLevelLink = page.getByRole('navigation').getByRole('link', { name: 'Hello world!' });
		
		// Verify the second level link is visible and has correct href
		await expect(secondLevelLink).toBeVisible();
		await expect(secondLevelLink).toHaveAttribute('href', SITE_CONFIG.BASE_URL + '2025/09/04/hello-world/');
		
		// Verify the link is clickable
		await expect(secondLevelLink).toBeEnabled();
		
		// Click the second level link
		await secondLevelLink.click();
		
		// Wait for navigation to complete
		await page.waitForLoadState('networkidle');
		
		// Verify we're on the correct page
		await expect(page).toHaveURL(SITE_CONFIG.BASE_URL + '2025/09/04/hello-world/');
		
		// Verify the page title contains "Hello world!"
		await expect(page).toHaveTitle(/Hello world!/);
		
		// Verify we can find expected content on the page
		const pageContent = page.locator('main');
		await expect(pageContent).toBeVisible();
		
		// Verify the page has the expected heading (main article heading, not comment heading)
		const pageHeading = page.locator('article').getByRole('heading', { name: 'Hello world!' });
		await expect(pageHeading).toBeVisible();
	});

	test('should have clickable third level menu items under About > Hello world!', async ({ page }) => {
		// Navigate to homepage
		await page.goto(SITE_CONFIG.BASE_URL);
		await page.waitForLoadState('networkidle');

		// Hover over About to reveal dropdown menu
		const aboutLink = page.getByRole('navigation').getByRole('link', { name: 'About' });
		await aboutLink.hover();

		// Wait for dropdown to appear
		await page.waitForTimeout(300);

		// Hover over "Hello world!" to reveal third level menu
		const secondLevelLink = page.getByRole('navigation').getByRole('link', { name: 'Hello world!' });
		await secondLevelLink.hover();

		// Wait for third level dropdown to appear
		await page.waitForTimeout(300);

		// Find the third level menu item "Est aut sed eaque consequatur rerum"
		const thirdLevelLink = page.getByRole('navigation').getByRole('link', { name: 'Est aut sed eaque consequatur rerum' });
		
		// Verify the third level link is visible and has correct href
		await expect(thirdLevelLink).toBeVisible();
		await expect(thirdLevelLink).toHaveAttribute('href', SITE_CONFIG.BASE_URL + '2021/11/03/est-aut-sed-eaque-consequatur-rerum/');
		
		// Verify the link is clickable
		await expect(thirdLevelLink).toBeEnabled();
		
		// Click the third level link
		await thirdLevelLink.click();
		
		// Wait for navigation to complete
		await page.waitForLoadState('networkidle');
		
		// Verify we're on the correct page
		await expect(page).toHaveURL(SITE_CONFIG.BASE_URL + '2021/11/03/est-aut-sed-eaque-consequatur-rerum/');
		
		// Verify the page title contains the expected text
		await expect(page).toHaveTitle(/Est aut sed eaque consequatur rerum/);
		
		// Verify we can find expected content on the page
		const pageContent = page.locator('main');
		await expect(pageContent).toBeVisible();
		
		// Verify the page has the expected heading
		const pageHeading = page.getByRole('heading', { name: 'Est aut sed eaque consequatur rerum' });
		await expect(pageHeading).toBeVisible();
	});

	test('should show dropdown menus on hover and hide on mouse leave', async ({ page }) => {
		// Navigate to homepage
		await page.goto(SITE_CONFIG.BASE_URL);
		await page.waitForLoadState('networkidle');

		// Find the dropdown menu containers
		const aboutDropdown = page.locator('.menu-item-has-children .sub-menu').first();
		const thirdLevelDropdown = page.locator('.menu-item-has-children .sub-menu .sub-menu').first();
		
		// Initially, dropdown menus should not be expanded (check aria-expanded attribute)
		const aboutLink = page.getByRole('navigation').getByRole('link', { name: 'About' });
		await expect(aboutLink).toHaveAttribute('aria-expanded', 'false');

		// Hover over About to reveal second level menu
		await aboutLink.hover();
		await page.waitForTimeout(300);

		// About link should now be expanded
		await expect(aboutLink).toHaveAttribute('aria-expanded', 'true');

		// Find the second level link after hover
		const secondLevelLink = page.getByRole('navigation').getByRole('link', { name: 'Hello world!' });
		await expect(secondLevelLink).toBeVisible();

		// Hover over second level item to reveal third level menu
		await secondLevelLink.hover();
		await page.waitForTimeout(300);

		// Third level should now be visible
		const thirdLevelLink = page.getByRole('navigation').getByRole('link', { name: 'Est aut sed eaque consequatur rerum' });
		await expect(thirdLevelLink).toBeVisible();

		// Move mouse away from navigation to hide dropdowns
		await page.locator('main').hover();
		await page.waitForTimeout(500);

		// About link should no longer be expanded
		await expect(aboutLink).toHaveAttribute('aria-expanded', 'false');
	});
});

test.describe('Mobile Navigation Tests', () => {
	test.beforeEach(async ({ page }) => {
		// Set mobile viewport
		await page.setViewportSize(VIEWPORTS.MOBILE);
	});

	test('should have clickable first level menu items in mobile offcanvas', async ({ page }) => {
		// Navigate to homepage
		await page.goto(SITE_CONFIG.BASE_URL);
		await page.waitForLoadState('networkidle');

		// Open the mobile menu
		const menuToggle = page.getByRole('link', { name: 'Open mobile offcanvas menu' });
		await menuToggle.click();
		await page.waitForTimeout(300);

		// Verify mobile menu is open
		const bodyClass = await page.evaluate(() => document.body.className);
		expect(bodyClass).toContain('mobile-menu-visible');

		// Test clicking the About link (first level)
		const aboutLink = page.locator('#mainnav').getByRole('link', { name: 'About' });
		await expect(aboutLink).toBeVisible();
		await expect(aboutLink).toHaveAttribute('href', SITE_CONFIG.BASE_URL + 'about/');
		
		// Click the About link
		await aboutLink.click();
		
		// Wait for navigation to complete
		await page.waitForLoadState('networkidle');
		
		// Verify we're on the correct page
		await expect(page).toHaveURL(SITE_CONFIG.BASE_URL + 'about/');
		await expect(page).toHaveTitle(/About/);
		
		// Verify page content
		const pageContent = page.locator('main');
		await expect(pageContent).toBeVisible();
	});

	test('should have clickable second level menu items in mobile offcanvas', async ({ page }) => {
		// Navigate to homepage
		await page.goto(SITE_CONFIG.BASE_URL);
		await page.waitForLoadState('networkidle');

		// Open the mobile menu
		const menuToggle = page.getByRole('link', { name: 'Open mobile offcanvas menu' });
		await menuToggle.click();
		
		// Wait for mobile menu to be fully open and dropdown indicators to be visible
		await page.waitForTimeout(500);
		
		// Verify mobile menu is open by checking body class
		const bodyClass = await page.evaluate(() => document.body.className);
		expect(bodyClass).toContain('mobile-menu-visible');

		// Click the dropdown indicator next to About to expand second level menu
		await page.evaluate(() => {
			const aboutDropdown = document.querySelector('#mainnav li:has(a[href*="about"]) .dropdown-symbol');
			if (aboutDropdown) aboutDropdown.click();
		});
		await page.waitForTimeout(500);

		// Test clicking the "Hello world!" link (second level)
		const secondLevelLink = page.locator('#mainnav').getByRole('link', { name: 'Hello world!' });
		await expect(secondLevelLink).toBeVisible();
		await expect(secondLevelLink).toHaveAttribute('href', SITE_CONFIG.BASE_URL + '2025/09/04/hello-world/');
		
		// Click the second level link
		await secondLevelLink.click();
		
		// Wait for navigation to complete
		await page.waitForLoadState('networkidle');
		
		// Verify we're on the correct page
		await expect(page).toHaveURL(SITE_CONFIG.BASE_URL + '2025/09/04/hello-world/');
		await expect(page).toHaveTitle(/Hello world!/);
		
		// Verify page content and heading
		const pageContent = page.locator('main');
		await expect(pageContent).toBeVisible();
		
		const pageHeading = page.locator('article').getByRole('heading', { name: 'Hello world!' });
		await expect(pageHeading).toBeVisible();
	});

	test('should maintain mobile menu state when navigating between pages', async ({ page }) => {
		// Navigate to homepage
		await page.goto(SITE_CONFIG.BASE_URL);
		await page.waitForLoadState('networkidle');

		// Open the mobile menu
		const menuToggle = page.getByRole('link', { name: 'Open mobile offcanvas menu' });
		await menuToggle.click();
		await page.waitForTimeout(300);

		// Expand the About dropdown
		await page.evaluate(() => {
			const aboutDropdown = document.querySelector('#mainnav li:has(a[href*="about"]) .dropdown-symbol');
			if (aboutDropdown) aboutDropdown.click();
		});
		await page.waitForTimeout(300);

		// Navigate to About page by clicking the About link
		const aboutLink = page.locator('#mainnav').getByRole('link', { name: 'About' });
		await aboutLink.click();
		await page.waitForLoadState('networkidle');

		// Verify we're on the About page
		await expect(page).toHaveURL(SITE_CONFIG.BASE_URL + 'about/');

		// The mobile menu should be closed after navigation
		const bodyClass = await page.evaluate(() => document.body.className);
		expect(bodyClass).not.toContain('mobile-menu-visible');

		// Verify mobile menu toggle is still available on the new page
		const newPageMenuToggle = page.getByRole('link', { name: 'Open mobile offcanvas menu' });
		await expect(newPageMenuToggle).toBeVisible();
	});

	test('should open and close mobile offcanvas menu with toggle button', async ({ page }) => {
		// Navigate to homepage
		await page.goto(SITE_CONFIG.BASE_URL);
		await page.waitForLoadState('networkidle');

		// Verify mobile menu is initially closed
		let bodyClass = await page.evaluate(() => document.body.className);
		expect(bodyClass).not.toContain('mobile-menu-visible');

		// Find and click the mobile menu toggle to open
		const menuToggle = page.getByRole('link', { name: 'Open mobile offcanvas menu' });
		await expect(menuToggle).toBeVisible();
		await menuToggle.click();
		await page.waitForTimeout(500);

		// Verify mobile menu is now open
		bodyClass = await page.evaluate(() => document.body.className);
		expect(bodyClass).toContain('mobile-menu-visible');

		// Verify the offcanvas navigation is visible
		const offcanvasNav = page.locator('#mainnav');
		await expect(offcanvasNav).toBeVisible();

		// Verify close button is visible and clickable
		const closeButton = page.getByRole('link', { name: 'Close mobile menu' });
		await expect(closeButton).toBeVisible();
		
		// Click close button to close menu
		await closeButton.click();
		await page.waitForTimeout(500);

		// Verify mobile menu is now closed
		bodyClass = await page.evaluate(() => document.body.className);
		expect(bodyClass).not.toContain('mobile-menu-visible');
	});

	test('should expand and collapse dropdown menus with dropdown icons in mobile offcanvas', async ({ page }) => {
		// Navigate to homepage
		await page.goto(SITE_CONFIG.BASE_URL);
		await page.waitForLoadState('networkidle');

		// Open the mobile menu
		const menuToggle = page.getByRole('link', { name: 'Open mobile offcanvas menu' });
		await menuToggle.click();
		await page.waitForTimeout(500);

		// Verify mobile menu is open
		const bodyClass = await page.evaluate(() => document.body.className);
		expect(bodyClass).toContain('mobile-menu-visible');

		// Initially, second level menu should not be visible
		let secondLevelLink = page.locator('#mainnav').getByRole('link', { name: 'Hello world!' });
		await expect(secondLevelLink).not.toBeVisible();

		// Click the About dropdown icon to expand second level
		await page.evaluate(() => {
			const aboutDropdown = document.querySelector('#mainnav li:has(a[href*="about"]) .dropdown-symbol');
			if (aboutDropdown) aboutDropdown.click();
		});
		await page.waitForTimeout(800);

		// Second level menu should now be visible
		secondLevelLink = page.locator('#mainnav').getByRole('link', { name: 'Hello world!' });
		await expect(secondLevelLink).toBeVisible();

		// Click the About dropdown icon again to collapse second level
		await page.evaluate(() => {
			const aboutDropdown = document.querySelector('#mainnav li:has(a[href*="about"]) .dropdown-symbol');
			if (aboutDropdown) aboutDropdown.click();
		});
		await page.waitForTimeout(500);

		// Second level menu should now be hidden again
		secondLevelLink = page.locator('#mainnav').getByRole('link', { name: 'Hello world!' });
		await expect(secondLevelLink).not.toBeVisible();
	});
});