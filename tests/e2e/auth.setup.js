/**
 * Authentication setup for Playwright E2E tests
 * 
 * This file runs once before all tests to authenticate and save the session state.
 * All other tests will reuse this authenticated state.
 */

import { test as setup, expect } from '@playwright/test';
import { SITE_CONFIG } from './utils/constants.js';

const authFile = 'playwright/.auth/user.json';

setup('authenticate', async ({ page }) => {
	// Get credentials from environment variables (GitHub secrets)
	const username = process.env.E2E_TESTS_USER;
	const password = process.env.E2E_TESTS_PASSWORD;
	
	if (!username || !password) {
		throw new Error(
			'Login credentials not found. Please ensure E2E_TESTS_USER and E2E_TESTS_PASSWORD ' +
			'environment variables are set from GitHub secrets.'
		);
	}
	
	console.log(`Setting up authentication for user: ${username}`);
	console.log(`Target URL: ${SITE_CONFIG.ADMIN_URL}/`);
	
	// Navigate to WordPress login page (wp-login.php is more reliable than wp-admin redirect)
	const loginUrl = `${SITE_CONFIG.ADMIN_URL}/`.replace('/wp-admin/', '/wp-login.php');
	console.log(`Navigating to login URL: ${loginUrl}`);
	
	await page.goto(loginUrl, { waitUntil: 'networkidle' });
	
	console.log(`Current URL after navigation: ${page.url()}`);
	
	// Check if we're already logged in (might happen if session persists or redirect occurred)
	const isAlreadyLoggedIn = await page.locator('#wpadminbar, .wp-admin, #adminmenu').first().isVisible().catch(() => false);
	
	if (isAlreadyLoggedIn) {
		console.log('✅ Already authenticated - session exists');
		await page.context().storageState({ path: authFile });
		console.log(`✅ Authentication state saved to ${authFile}`);
		return;
	}
	
	// Check page title for debugging
	const pageTitle = await page.title();
	console.log(`Page title: ${pageTitle}`);
	
	// Wait for login form to be visible
	const loginForm = page.locator('#loginform, form[name="loginform"], form#login');
	try {
		await expect(loginForm).toBeVisible({ timeout: 10000 });
	} catch (error) {
		// Take screenshot to debug what page we're on
		await page.screenshot({ path: 'test-results/auth-page-no-login-form.png', fullPage: true });
		const bodyText = await page.locator('body').textContent();
		console.error(`Failed to find login form. Page body text: ${bodyText?.substring(0, 500)}`);
		throw new Error(`Login form not found at ${page.url()}. Check screenshot at test-results/auth-page-no-login-form.png`);
	}
	
	// Fill in login credentials
	await page.fill('#user_login', username);
	await page.fill('#user_pass', password);
	
	// Click login button
	await page.click('#wp-submit');
	
	// Wait for successful login
	await page.waitForLoadState('networkidle');
	
	// Verify we're logged in by checking for admin elements
	try {
		await expect(
			page.locator('#wpadminbar, .wp-admin, #adminmenu').first()
		).toBeVisible({ timeout: 10000 });
		
		console.log('✅ Successfully authenticated - saving session state');
	} catch (error) {
		// Enhanced error reporting
		const currentUrl = page.url();
		console.error(`❌ Authentication failed. Current URL: ${currentUrl}`);
		
		// Take screenshot for debugging
		await page.screenshot({ path: 'playwright-report/auth-failure.png', fullPage: true });
		
		// Check for error messages
		const errorElement = page.locator('.login .message, #login_error');
		if (await errorElement.isVisible()) {
			const errorMessage = await errorElement.textContent();
			throw new Error(`Authentication failed: ${errorMessage}`);
		}
		
		// Check if we're still on login page
		const isStillOnLogin = await page.locator('#loginform').isVisible();
		if (isStillOnLogin) {
			throw new Error('Authentication failed: Still on login page after submission');
		}
		
		throw new Error(`Authentication verification failed at ${currentUrl}`);
	}
	
	// Save the authenticated state to file
	await page.context().storageState({ path: authFile });
	console.log(`✅ Authentication state saved to ${authFile}`);
});

