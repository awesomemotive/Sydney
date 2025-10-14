import { test, expect } from '@playwright/test';
import { SITE_CONFIG, VIEWPORTS } from '../utils/constants.js';
import { loginAndNavigateToAdmin } from '../utils/login.js';

/**
 * PHP Error Detection Tests - WordPress Admin Pages
 * 
 * These tests ensure that no PHP errors, warnings, or notices appear on the admin pages
 * for the Sydney theme dashboard and related admin functionality.
 */

// Define admin test pages with their expected characteristics
const ADMIN_TEST_PAGES = [
	{
		name: 'aThemes Patcher Preview',
		path: 'admin.php?page=athemes-patcher-preview-sp',
		expectedTitle: /Patcher.*Sydney/,
		expectedElements: ['#wpadminbar', '#wpbody-content', 'main', 'h2'],
		description: 'aThemes Patcher Preview page for Sydney Pro'
	},
	{
		name: 'Sydney Dashboard - Starter Sites',
		path: 'admin.php?page=sydney-dashboard&tab=starter-sites',
		expectedTitle: /Sydney.*WordPress/,
		expectedElements: ['#wpadminbar', '#wpbody-content', 'main', 'navigation'],
		description: 'Sydney Dashboard starter sites tab'
	},
	{
		name: 'Sydney Dashboard - Main',
		path: 'admin.php?page=sydney-dashboard',
		expectedTitle: /Sydney.*WordPress/,
		expectedElements: ['#wpadminbar', '#wpbody-content', 'main', 'navigation'],
		description: 'Main Sydney Dashboard page'
	}
];

// PHP error patterns to detect in admin content (same as frontend)
const PHP_ERROR_PATTERNS = [
	/Fatal error:/i,
	/Warning:/i,
	/Notice:/i,
	/Parse error:/i,
	/Deprecated:/i,
	/Strict Standards:/i,
	/<b>Fatal error<\/b>/i,
	/<b>Warning<\/b>/i,
	/<b>Notice<\/b>/i,
	/<b>Parse error<\/b>/i,
	/Call to undefined function/i,
	/Call to undefined method/i,
	/Undefined variable/i,
	/Undefined index/i,
	/Undefined offset/i,
	/Cannot redeclare/i,
	/Class .* not found/i,
	/Function .* not found/i,
	/Maximum execution time exceeded/i,
	/Memory limit exceeded/i,
	/Stack trace:/i,
	/PHP Stack trace:/i
];

test.describe('PHP Error Detection - WordPress Admin Pages', () => {
	
	// Test each admin page for PHP errors
	for (const testPage of ADMIN_TEST_PAGES) {
		test(`${testPage.name} should load without PHP errors`, async ({ page }) => {
			// Login and navigate to the admin page
			await loginAndNavigateToAdmin(page, testPage.path);
			
			// Wait for page to fully load
			await page.waitForLoadState('networkidle');
			
			// Additional wait for dynamic content
			await page.waitForTimeout(2000);
			
			// Check page title (admin pages may have different title patterns)
			if (testPage.expectedTitle) {
				await expect(page).toHaveTitle(testPage.expectedTitle);
			}
			
			// Check that expected admin elements are present
			for (const selector of testPage.expectedElements) {
				await expect(page.locator(selector).first()).toBeVisible();
			}
			
			// Get page content to check for PHP errors
			const pageContent = await page.content();
			
			// Check for PHP error patterns in page content
			for (const pattern of PHP_ERROR_PATTERNS) {
				if (pattern.test(pageContent)) {
					throw new Error(`PHP error detected in admin page content on ${testPage.name}: Pattern matched - ${pattern.source}`);
				}
			}
			
			// Additional check: ensure page doesn't contain common WordPress error indicators
			const adminErrorIndicators = [
				'There has been a critical error on this website',
				'The website is temporarily unable to service your request',
				'Internal Server Error',
				'Service Unavailable',
				'Database connection error',
				'WordPress database error',
				'Sorry, you are not allowed to access this page',
				'Cheatin&#8217; uh?',
				'Are you sure you want to do this?'
			];
			
			for (const indicator of adminErrorIndicators) {
				await expect(page.locator(`text=${indicator}`)).not.toBeVisible();
			}
			
			// Check that admin notices don't contain PHP errors
			const adminNotices = await page.locator('.notice, .error, .updated').all();
			for (const notice of adminNotices) {
				const noticeContent = await notice.textContent();
				if (noticeContent) {
					for (const pattern of PHP_ERROR_PATTERNS) {
						if (pattern.test(noticeContent)) {
							throw new Error(`PHP error detected in admin notice on ${testPage.name}: ${noticeContent}`);
						}
					}
				}
			}
		});
	}
	
	test.describe('Admin Page Functionality Checks', () => {
		
		test('Sydney Dashboard tabs should switch without PHP errors', async ({ page }) => {
			// Navigate to Sydney Dashboard main page
			await loginAndNavigateToAdmin(page, 'admin.php?page=sydney-dashboard');
			await page.waitForLoadState('networkidle');
			
			// Look for navigation tabs - the actual structure uses .sydney-dashboard-tabs-nav ul li a
			const tabs = await page.locator('.sydney-dashboard-tabs-nav ul li a').all();
			
			if (tabs.length > 0) {
				// Click each tab and check for PHP errors
				for (let i = 0; i < tabs.length; i++) {
					const tab = tabs[i];
					const tabText = await tab.textContent();
					
					// Skip if tab is already active (parent li has 'active' class)
					const parentLi = await tab.evaluateHandle(el => el.parentElement);
					const hasActiveClass = await parentLi.evaluate(li => li.classList.contains('active'));
					if (hasActiveClass) {
						continue;
					}
					
					console.log(`Clicking tab: ${tabText}`);
					await tab.click();
					
					// Wait for tab content to load
					await page.waitForLoadState('networkidle');
					await page.waitForTimeout(1000);
					
					// Check for PHP errors after tab switch
					const pageContent = await page.content();
					for (const pattern of PHP_ERROR_PATTERNS) {
						if (pattern.test(pageContent)) {
							throw new Error(`PHP error detected after clicking tab "${tabText}": Pattern matched - ${pattern.source}`);
						}
					}
				}
			}
		});
	});
});