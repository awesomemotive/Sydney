import { test, expect } from '@playwright/test';
import { SITE_CONFIG, VIEWPORTS } from '../utils/constants.js';

/**
 * PHP Error Detection Tests
 * 
 * These tests ensure that no PHP errors, warnings, or notices appear on the frontend
 * across different page types and viewports.
 */

// Define test pages with their expected characteristics based on site exploration
const TEST_PAGES = [
	{
		name: 'Homepage',
		url: '',
		expectedTitle: /Tests/,
		expectedElements: ['header', 'img[alt="Tests"]', 'h1']
	},
	{
		name: 'Blog Page',
		url: 'my-blog-page/',
		expectedTitle: /My blog page/,
		expectedElements: ['header', 'main', 'article']
	},
	{
		name: 'Single Post',
		url: '2021/11/03/similique-quis-a-libero-enim-quod-corporis-3/',
		expectedTitle: /Similique quis a libero enim quod corporis/,
		expectedElements: ['article', '.entry-content', 'h2:has-text("Post navigation")']
	},
	{
		name: 'Category Archive',
		url: 'category/travel/',
		expectedTitle: /Travel/,
		expectedElements: ['header', 'main', 'article']
	},
	{
		name: 'Search Results',
		url: '?s=test',
		expectedTitle: /Search Results/,
		expectedElements: ['header', 'main']
	},
	{
		name: 'About Page',
		url: 'about/',
		expectedTitle: /About/,
		expectedElements: ['header', 'main']
	},
	{
		name: '404 Page',
		url: 'non-existent-page-12345/',
		expectedTitle: /Page not found/,
		expectedElements: ['header', 'main', 'h1'],
		expectedStatus: 404
	}
];

// PHP error patterns to detect in content
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

test.describe('PHP Error Detection - Frontend Pages', () => {
	
	// Test each page for PHP errors
	for (const testPage of TEST_PAGES) {
		test(`${testPage.name} should load without PHP errors`, async ({ page }) => {
			// Navigate to the page
			const response = await page.goto(`${SITE_CONFIG.BASE_URL}${testPage.url}`);
			
			// Wait for page to fully load
			await page.waitForLoadState('networkidle');
			
			// Check HTTP status code
			const expectedStatus = testPage.expectedStatus || 200;
			expect(response.status()).toBe(expectedStatus);
			
			// Check page title
			if (testPage.expectedTitle) {
				await expect(page).toHaveTitle(testPage.expectedTitle);
			}
			
			// Check that expected elements are present (page rendered correctly)
			for (const selector of testPage.expectedElements) {
				await expect(page.locator(selector).first()).toBeVisible();
			}
			
			// Get page content to check for PHP errors
			const pageContent = await page.content();
			
			// Check for PHP error patterns in page content
			for (const pattern of PHP_ERROR_PATTERNS) {
				if (pattern.test(pageContent)) {
					throw new Error(`PHP error detected in page content on ${testPage.name}: Pattern matched - ${pattern.source}`);
				}
			}
			
			// Additional check: ensure page doesn't contain common error indicators
			const errorIndicators = [
				'There has been a critical error on this website',
				'The website is temporarily unable to service your request',
				'Internal Server Error',
				'Service Unavailable',
				'Database connection error',
				'WordPress database error'
			];
			
			for (const indicator of errorIndicators) {
				await expect(page.locator(`text=${indicator}`)).not.toBeVisible();
			}
		});
	}
	
	test.describe('Form Submission Error Checks', () => {
				
		test('should handle search form without PHP errors', async ({ page }) => {
			// Navigate to blog page which has search widget
			await page.goto(`${SITE_CONFIG.BASE_URL}my-blog-page/`);
			await page.waitForLoadState('networkidle');
			
			// Look for search input in sidebar
			const searchInput = page.getByRole('searchbox', { name: 'Search for:' });
			if (await searchInput.isVisible()) {
				// Perform a search
				await searchInput.fill('test search');
				await page.keyboard.press('Enter');
				
				// Wait for search results and navigation to complete
				await page.waitForLoadState('networkidle');
				
				// Give extra time for any dynamic content loading
				await page.waitForTimeout(1000);
				
				// Check for PHP errors in search results
				const pageContent = await page.content();
				for (const pattern of PHP_ERROR_PATTERNS) {
					if (pattern.test(pageContent)) {
						throw new Error(`PHP error detected in search results: Pattern matched - ${pattern.source}`);
					}
				}
			}
		});
	});
});