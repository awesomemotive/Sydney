import { test, expect } from '@playwright/test';
import { SITE_CONFIG, VIEWPORTS } from '../utils/constants.js';
import { loginToCustomizer } from '../utils/login.js';

/**
 * PHP Error Detection Tests - WordPress Customizer
 * 
 * These tests ensure that no PHP errors, warnings, or notices appear when
 * accessing the WordPress Customizer interface.
 */

// PHP error patterns to detect in customizer content
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

test.describe('PHP Error Detection - WordPress Customizer', () => {
	
	test('Customizer should load without PHP errors', async ({ page }) => {
		// Login and navigate to the customizer
		await loginToCustomizer(page);
		
		// Wait for customizer to fully load
		await page.waitForLoadState('networkidle');
		
		// Additional wait for customizer interface to initialize
		await page.waitForTimeout(3000);
		
		// Verify customizer interface is present
		await expect(page.locator('#customize-controls')).toBeVisible();
		await expect(page.locator('#customize-preview')).toBeVisible();
		
		// Get page content to check for PHP errors
		const pageContent = await page.content();
		
		// Check for PHP error patterns in page content
		for (const pattern of PHP_ERROR_PATTERNS) {
			if (pattern.test(pageContent)) {
				throw new Error(`PHP error detected in customizer content: Pattern matched - ${pattern.source}`);
			}
		}
		
		// Additional check: ensure customizer doesn't contain common error indicators
		const customizerErrorIndicators = [
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
		
		for (const indicator of customizerErrorIndicators) {
			await expect(page.locator(`text=${indicator}`)).not.toBeVisible();
		}
		
		// Check that customizer notices don't contain PHP errors
		const customizerNotices = await page.locator('.notice, .error, .updated, .notification-message').all();
		for (const notice of customizerNotices) {
			const noticeContent = await notice.textContent();
			if (noticeContent) {
				for (const pattern of PHP_ERROR_PATTERNS) {
					if (pattern.test(noticeContent)) {
						throw new Error(`PHP error detected in customizer notice: ${noticeContent}`);
					}
				}
			}
		}
		
		// Verify customizer title
		await expect(page).toHaveTitle(/Customize/);
		
		console.log('Customizer loaded successfully without PHP errors');
	});
	
	test('Customizer preview frame should load without PHP errors', async ({ page }) => {
		// Login and navigate to the customizer
		await loginToCustomizer(page);
		
		// Wait for customizer to fully load
		await page.waitForLoadState('networkidle');
		await page.waitForTimeout(3000);
		
		// Get the preview frame
		const previewFrame = page.frameLocator('#customize-preview iframe');
		
		// Wait for preview frame to load
		await expect(previewFrame.locator('body')).toBeVisible({ timeout: 15000 });
		
		// Get preview frame content
		const previewContent = await previewFrame.locator('html').innerHTML();
		
		// Check for PHP error patterns in preview frame content
		for (const pattern of PHP_ERROR_PATTERNS) {
			if (pattern.test(previewContent)) {
				throw new Error(`PHP error detected in customizer preview frame: Pattern matched - ${pattern.source}`);
			}
		}
		
		// Check that preview frame doesn't contain error indicators
		const previewErrorIndicators = [
			'There has been a critical error on this website',
			'The website is temporarily unable to service your request',
			'Internal Server Error',
			'Service Unavailable',
			'Database connection error',
			'WordPress database error'
		];
		
		for (const indicator of previewErrorIndicators) {
			await expect(previewFrame.locator(`text=${indicator}`)).not.toBeVisible();
		}
		
		console.log('Customizer preview frame loaded successfully without PHP errors');
	});
});