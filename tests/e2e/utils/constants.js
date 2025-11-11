/**
 * Constants for Playwright E2E tests
 * 
 * This file contains all the constants used across the test suite
 * to maintain consistency and make configuration changes easier.
 */

// Site Configuration
export const SITE_CONFIG = {
	BASE_URL: 'https://demo.athemes.com/sydney-tests/',
	ADMIN_URL: 'https://demo.athemes.com/sydney-tests/wp-admin',
	WP_JSON_URL: 'https://demo.athemes.com/sydney-tests/wp-json/wp/v2',
	CUSTOMIZER_API_URL: 'https://demo.athemes.com/sydney-tests/wp-json/wp/v2/customizer/settings'
};

// Theme Settings
export const THEME_SETTINGS = {
	THEME_NAME: 'sydney',
};

// Viewport Sizes
export const VIEWPORTS = {
	MOBILE: { width: 375, height: 667 },
	TABLET: { width: 768, height: 1024 },
	DESKTOP: { width: 1920, height: 1080 },
	LARGE_DESKTOP: { width: 2560, height: 1440 }
};