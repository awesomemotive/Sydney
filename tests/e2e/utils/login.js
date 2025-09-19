import { SITE_CONFIG } from './constants.js';

export const adminLoginAction = async (page) => {
	// Navigate to the admin area which will redirect to login
	await page.goto(`${SITE_CONFIG.ADMIN_URL}`);
	
	// Wait for the login page to load
	await page.waitForLoadState('networkidle');
	
	// Click on "Sign in with Google" button
	const googleSignInButton = page.locator('text=Sign in with Google');
	await googleSignInButton.click();
	
	// Wait for Google OAuth page to load
	await page.waitForLoadState('networkidle');
	
	// Note: This will require manual intervention or pre-configured Google account
	// For automated testing, you would need to:
	// 1. Fill in the email field with a test account
	// 2. Handle the password flow
	// 3. Handle any 2FA if enabled
	
	// For now, we'll wait for the user to complete the OAuth flow manually
	// or you can extend this with specific email/password if you have test credentials
	
	// Wait for successful login (should redirect back to wp-admin)
	await page.waitForURL(`${SITE_CONFIG.ADMIN_URL}**`, { timeout: 60000 });
}