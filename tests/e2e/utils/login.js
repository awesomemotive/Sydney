/**
 * Login utilities for Playwright E2E tests
 * 
 * This module provides login functionality for WordPress admin authentication
 * using credentials from GitHub secrets.
 */

import { expect } from '@playwright/test';
import { SITE_CONFIG } from './constants.js';

/**
 * Login to WordPress admin using GitHub secrets
 * 
 * @param {import('@playwright/test').Page} page - Playwright page object
 * @returns {Promise<void>}
 */
export async function loginToWordPressAdmin(page) {
	// Get credentials from environment variables (GitHub secrets)
	const username = process.env.E2E_TESTS_USER;
	const password = process.env.E2E_TESTS_PASSWORD;
	
	if (!username || !password) {
		throw new Error(
			'Login credentials not found. Please ensure E2E_TESTS_USER and E2E_TESTS_PASSWORD ' +
			'environment variables are set from GitHub secrets.'
		);
	}
	
	console.log(`Attempting to login to WordPress admin with user: ${username}`);
	
	// Navigate to WordPress login page
	await page.goto(`${SITE_CONFIG.ADMIN_URL}/`);
	
	// Wait for login form to be visible
	await expect(page.locator('#loginform')).toBeVisible();
	
	// Fill in login credentials
	await page.fill('#user_login', username);
	await page.fill('#user_pass', password);
	
	// Click login button
	await page.click('#wp-submit');
	
	// Wait for successful login - check for admin bar or dashboard
	await page.waitForLoadState('networkidle');
	
	// Verify we're logged in by checking for admin elements
	try {
		// Check for WordPress admin bar or dashboard elements
		await expect(
			page.locator('#wpadminbar, .wp-admin, #adminmenu').first()
		).toBeVisible({ timeout: 10000 });
		
		console.log('Successfully logged into WordPress admin');
	} catch (error) {
		// If login failed, check for error messages
		const errorMessage = await page.locator('.login .message').textContent();
		if (errorMessage) {
			throw new Error(`Login failed: ${errorMessage}`);
		}
		
		// Check if we're still on login page
		const isStillOnLogin = await page.locator('#loginform').isVisible();
		if (isStillOnLogin) {
			throw new Error('Login failed: Still on login page after submission');
		}
		
		throw new Error('Login verification failed: Could not confirm successful login');
	}
}

/**
 * Login and navigate to a specific admin page
 * 
 * @param {import('@playwright/test').Page} page - Playwright page object
 * @param {string} adminPath - Path relative to wp-admin (e.g., 'themes.php', 'customize.php')
 * @returns {Promise<void>}
 */
export async function loginAndNavigateToAdmin(page, adminPath = '') {
	await loginToWordPressAdmin(page);
	
	if (adminPath) {
		const targetUrl = `${SITE_CONFIG.ADMIN_URL}/${adminPath}`;
		console.log(`Navigating to admin page: ${targetUrl}`);
		await page.goto(targetUrl);
		await page.waitForLoadState('networkidle');
	}
}

/**
 * Login to WordPress Customizer
 * 
 * @param {import('@playwright/test').Page} page - Playwright page object
 * @returns {Promise<void>}
 */
export async function loginToCustomizer(page) {
	await loginAndNavigateToAdmin(page, 'customize.php');
	
	// Wait for customizer to load
	await expect(page.locator('#customize-controls')).toBeVisible({ timeout: 15000 });
	console.log('Successfully accessed WordPress Customizer');
}

/**
 * Logout from WordPress admin
 * 
 * @param {import('@playwright/test').Page} page - Playwright page object
 * @returns {Promise<void>}
 */
export async function logoutFromWordPress(page) {
	try {
		// Look for logout link in admin bar
		const logoutLink = page.locator('#wp-admin-bar-logout a');
		
		if (await logoutLink.isVisible()) {
			await logoutLink.click();
			await page.waitForLoadState('networkidle');
			
			// Verify logout by checking for login form
			await expect(page.locator('#loginform')).toBeVisible();
			console.log('Successfully logged out from WordPress');
		} else {
			console.log('Logout link not found, user may not be logged in');
		}
	} catch (error) {
		console.warn('Logout attempt failed:', error.message);
	}
}

/**
 * Check if user is currently logged in to WordPress admin
 * 
 * @param {import('@playwright/test').Page} page - Playwright page object
 * @returns {Promise<boolean>}
 */
export async function isLoggedIn(page) {
	try {
		// Check for admin bar or admin menu
		const adminElements = page.locator('#wpadminbar, #adminmenu');
		return await adminElements.first().isVisible();
	} catch {
		return false;
	}
}
