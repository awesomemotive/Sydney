import { test, expect } from '@playwright/test';
import { SITE_CONFIG, VIEWPORTS } from '../utils/constants.js';

// Button-specific constants
const BUTTON_COLORS = {
	PRIMARY_BACKGROUND: 'rgb(255, 208, 10)', // Yellow background
	PRIMARY_TEXT: 'rgb(0, 16, 46)', // Dark blue text
	OUTLINE_BACKGROUND: 'rgba(0, 0, 0, 0)', // Transparent background for outline buttons
	OUTLINE_TEXT: 'rgb(255, 208, 10)', // Yellow text for outline buttons
	OUTLINE_BORDER: '2px solid rgb(255, 208, 10)' // Yellow border for outline buttons
};

const BUTTON_STYLES = {
	PADDING: '12px 35px',
	PADDING_LARGE: '19px 40px', // For hero Learn More button
	BORDER_RADIUS: '0px',
	FONT_SIZE: '14px',
	FONT_WEIGHT_NORMAL: '400',
	FONT_WEIGHT_BOLD: '600',
	TEXT_TRANSFORM: 'uppercase',
	LINE_HEIGHT: '24px',
	LINE_HEIGHT_SMALL: '14px', // For hero Learn More button
	BORDER_WIDTH_THIN: '1px solid rgb(255, 208, 10)',
	BORDER_WIDTH_THICK: '2px solid rgb(255, 208, 10)'
};

// Test URLs
const TEST_URLS = {
	HOMEPAGE: SITE_CONFIG.BASE_URL,
	BLOG_POST: 'https://demo.athemes.com/sydney-tests/2021/11/03/est-aut-sed-eaque-consequatur-rerum/'
};

test.describe('Button Styles and Functionality', () => {
	
	test.describe('Homepage Buttons', () => {
		test.beforeEach(async ({ page }) => {
			// Set desktop viewport
			await page.setViewportSize(VIEWPORTS.DESKTOP);
			await page.goto(TEST_URLS.HOMEPAGE);
			await page.waitForLoadState('networkidle');
		});

		test('should have correct styles for hero Learn More button', async ({ page }) => {
			// Find the Learn More button in the hero section
			const learnMoreButtons = await page.locator('a').filter({ hasText: 'LEARN MORE' }).all();
			expect(learnMoreButtons.length).toBeGreaterThan(0);
			
			// Get the first Learn More button (hero section)
			const heroLearnMoreButton = learnMoreButtons[0];
			await expect(heroLearnMoreButton).toBeVisible();

			// Test button styles
			const buttonStyles = await heroLearnMoreButton.evaluate((button) => {
				const styles = window.getComputedStyle(button);
				return {
					backgroundColor: styles.backgroundColor,
					color: styles.color,
					padding: styles.padding,
					paddingTop: styles.paddingTop,
					paddingRight: styles.paddingRight,
					paddingBottom: styles.paddingBottom,
					paddingLeft: styles.paddingLeft,
					borderRadius: styles.borderRadius,
					fontSize: styles.fontSize,
					fontWeight: styles.fontWeight,
					textTransform: styles.textTransform,
					border: styles.border,
					borderWidth: styles.borderWidth,
					borderStyle: styles.borderStyle,
					borderColor: styles.borderColor,
					lineHeight: styles.lineHeight,
					display: styles.display
				};
			});

			// Verify button colors
			expect(buttonStyles.backgroundColor).toBe(BUTTON_COLORS.PRIMARY_BACKGROUND);
			expect(buttonStyles.color).toBe(BUTTON_COLORS.PRIMARY_TEXT);
			expect(buttonStyles.borderColor).toBe(BUTTON_COLORS.PRIMARY_BACKGROUND);

			// Verify button padding (hero button has larger padding)
			expect(buttonStyles.padding).toBe(BUTTON_STYLES.PADDING_LARGE);
			expect(buttonStyles.paddingTop).toBe('19px');
			expect(buttonStyles.paddingRight).toBe('40px');
			expect(buttonStyles.paddingBottom).toBe('19px');
			expect(buttonStyles.paddingLeft).toBe('40px');

			// Verify other styles
			expect(buttonStyles.borderRadius).toBe(BUTTON_STYLES.BORDER_RADIUS);
			expect(buttonStyles.fontSize).toBe(BUTTON_STYLES.FONT_SIZE);
			expect(buttonStyles.fontWeight).toBe(BUTTON_STYLES.FONT_WEIGHT_BOLD);
			expect(buttonStyles.textTransform).toBe(BUTTON_STYLES.TEXT_TRANSFORM);
			expect(buttonStyles.lineHeight).toBe(BUTTON_STYLES.LINE_HEIGHT_SMALL);
			expect(buttonStyles.display).toBe('inline-block');

			// Verify border style
			expect(buttonStyles.borderWidth).toBe('1px');
			expect(buttonStyles.borderStyle).toBe('solid');

			// Test button functionality (should be clickable)
			await expect(heroLearnMoreButton).toBeEnabled();
		});

		test('should show and style search button correctly when search icon is clicked', async ({ page }) => {
			// Click the search icon to reveal the search form
			const searchIcon = page.getByRole('link', { name: 'Search for a product' });
			await expect(searchIcon).toBeVisible();
			await searchIcon.click();

			// Wait for search form to appear
			await expect(page.getByRole('searchbox', { name: 'Search for:' })).toBeVisible();
			
			// Find the search submit button
			const searchButton = page.locator('input[type="submit"][value="Search"]').first();
			await expect(searchButton).toBeVisible();

			// Test search button styles
			const searchButtonStyles = await searchButton.evaluate((button) => {
				const styles = window.getComputedStyle(button);
				return {
					backgroundColor: styles.backgroundColor,
					color: styles.color,
					padding: styles.padding,
					paddingTop: styles.paddingTop,
					paddingRight: styles.paddingRight,
					paddingBottom: styles.paddingBottom,
					paddingLeft: styles.paddingLeft,
					borderRadius: styles.borderRadius,
					fontSize: styles.fontSize,
					fontWeight: styles.fontWeight,
					textTransform: styles.textTransform,
					border: styles.border,
					borderWidth: styles.borderWidth,
					borderStyle: styles.borderStyle,
					borderColor: styles.borderColor,
					lineHeight: styles.lineHeight,
					display: styles.display
				};
			});

			// Verify search button colors
			expect(searchButtonStyles.backgroundColor).toBe(BUTTON_COLORS.PRIMARY_BACKGROUND);
			expect(searchButtonStyles.color).toBe(BUTTON_COLORS.PRIMARY_TEXT);
			expect(searchButtonStyles.borderColor).toBe(BUTTON_COLORS.PRIMARY_BACKGROUND);

			// Verify search button padding
			expect(searchButtonStyles.padding).toBe(BUTTON_STYLES.PADDING);
			expect(searchButtonStyles.paddingTop).toBe('12px');
			expect(searchButtonStyles.paddingRight).toBe('35px');
			expect(searchButtonStyles.paddingBottom).toBe('12px');
			expect(searchButtonStyles.paddingLeft).toBe('35px');

			// Verify other styles
			expect(searchButtonStyles.borderRadius).toBe(BUTTON_STYLES.BORDER_RADIUS);
			expect(searchButtonStyles.fontSize).toBe(BUTTON_STYLES.FONT_SIZE);
			expect(searchButtonStyles.fontWeight).toBe(BUTTON_STYLES.FONT_WEIGHT_NORMAL);
			expect(searchButtonStyles.textTransform).toBe(BUTTON_STYLES.TEXT_TRANSFORM);
			expect(searchButtonStyles.lineHeight).toBe(BUTTON_STYLES.LINE_HEIGHT);
			expect(searchButtonStyles.display).toBe('block');

			// Verify border style
			expect(searchButtonStyles.borderWidth).toBe('1px');
			expect(searchButtonStyles.borderStyle).toBe('solid');

			// Test search functionality
			await page.getByRole('searchbox', { name: 'Search for:' }).fill('test');
			await searchButton.click();
			
			// Should navigate to search results page
			await expect(page).toHaveURL(/.*\?s=test/);
		});
	});

	test.describe('Blog Post Buttons', () => {
		test.beforeEach(async ({ page }) => {
			// Set desktop viewport
			await page.setViewportSize(VIEWPORTS.DESKTOP);
			await page.goto(TEST_URLS.BLOG_POST);
			await page.waitForLoadState('networkidle');
		});

		test('should have correct styles for post content buttons', async ({ page }) => {
			// Find the WordPress block buttons in post content
			const postButtons = page.locator('.wp-block-button__link');
			await expect(postButtons).toHaveCount(2);

			// Test the first button ("Just a button")
			const firstButton = postButtons.first();
			await expect(firstButton).toBeVisible();
			await expect(firstButton).toHaveText('Just a button');

			const firstButtonStyles = await firstButton.evaluate((button) => {
				const styles = window.getComputedStyle(button);
				return {
					backgroundColor: styles.backgroundColor,
					color: styles.color,
					padding: styles.padding,
					paddingTop: styles.paddingTop,
					paddingRight: styles.paddingRight,
					paddingBottom: styles.paddingBottom,
					paddingLeft: styles.paddingLeft,
					borderRadius: styles.borderRadius,
					fontSize: styles.fontSize,
					fontWeight: styles.fontWeight,
					textTransform: styles.textTransform,
					border: styles.border,
					borderWidth: styles.borderWidth,
					borderStyle: styles.borderStyle,
					borderColor: styles.borderColor,
					lineHeight: styles.lineHeight,
					display: styles.display
				};
			});

			// Verify first button (solid style) colors and styles
			expect(firstButtonStyles.backgroundColor).toBe(BUTTON_COLORS.PRIMARY_BACKGROUND);
			expect(firstButtonStyles.color).toBe(BUTTON_COLORS.PRIMARY_TEXT);
			expect(firstButtonStyles.padding).toBe(BUTTON_STYLES.PADDING);
			expect(firstButtonStyles.paddingTop).toBe('12px');
			expect(firstButtonStyles.paddingRight).toBe('35px');
			expect(firstButtonStyles.paddingBottom).toBe('12px');
			expect(firstButtonStyles.paddingLeft).toBe('35px');
			expect(firstButtonStyles.borderRadius).toBe(BUTTON_STYLES.BORDER_RADIUS);
			expect(firstButtonStyles.fontSize).toBe(BUTTON_STYLES.FONT_SIZE);
			expect(firstButtonStyles.fontWeight).toBe(BUTTON_STYLES.FONT_WEIGHT_BOLD);
			expect(firstButtonStyles.textTransform).toBe(BUTTON_STYLES.TEXT_TRANSFORM);
			expect(firstButtonStyles.display).toBe('inline-block');
			expect(firstButtonStyles.borderWidth).toBe('0px');
			expect(firstButtonStyles.borderStyle).toBe('none');

			// Test the second button ("Another button" - outline style)
			const secondButton = postButtons.nth(1);
			await expect(secondButton).toBeVisible();
			await expect(secondButton).toHaveText('Another button');

			const secondButtonStyles = await secondButton.evaluate((button) => {
				const styles = window.getComputedStyle(button);
				return {
					backgroundColor: styles.backgroundColor,
					color: styles.color,
					padding: styles.padding,
					paddingTop: styles.paddingTop,
					paddingRight: styles.paddingRight,
					paddingBottom: styles.paddingBottom,
					paddingLeft: styles.paddingLeft,
					borderRadius: styles.borderRadius,
					fontSize: styles.fontSize,
					fontWeight: styles.fontWeight,
					textTransform: styles.textTransform,
					border: styles.border,
					borderWidth: styles.borderWidth,
					borderStyle: styles.borderStyle,
					borderColor: styles.borderColor,
					lineHeight: styles.lineHeight,
					display: styles.display
				};
			});

			// Verify second button (outline style) colors and styles
			expect(secondButtonStyles.backgroundColor).toBe(BUTTON_COLORS.OUTLINE_BACKGROUND);
			expect(secondButtonStyles.color).toBe(BUTTON_COLORS.OUTLINE_TEXT);
			expect(secondButtonStyles.borderColor).toBe(BUTTON_COLORS.OUTLINE_TEXT);
			expect(secondButtonStyles.padding).toBe(BUTTON_STYLES.PADDING);
			expect(secondButtonStyles.paddingTop).toBe('12px');
			expect(secondButtonStyles.paddingRight).toBe('35px');
			expect(secondButtonStyles.paddingBottom).toBe('12px');
			expect(secondButtonStyles.paddingLeft).toBe('35px');
			expect(secondButtonStyles.borderRadius).toBe(BUTTON_STYLES.BORDER_RADIUS);
			expect(secondButtonStyles.fontSize).toBe(BUTTON_STYLES.FONT_SIZE);
			expect(secondButtonStyles.fontWeight).toBe(BUTTON_STYLES.FONT_WEIGHT_BOLD);
			expect(secondButtonStyles.textTransform).toBe(BUTTON_STYLES.TEXT_TRANSFORM);
			expect(secondButtonStyles.display).toBe('inline-block');
			expect(secondButtonStyles.borderWidth).toBe('2px');
			expect(secondButtonStyles.borderStyle).toBe('solid');

			// Test button functionality (should be clickable)
			await expect(firstButton).toBeEnabled();
			await expect(secondButton).toBeEnabled();
		});

		test('should have correct styles for post comment button', async ({ page }) => {
			// Scroll down to the comment form
			await page.evaluate(() => {
				document.querySelector('#submit')?.scrollIntoView();
			});

			// Find the Post Comment button
			const commentButton = page.locator('input[type="submit"]#submit');
			await expect(commentButton).toBeVisible();
			await expect(commentButton).toHaveValue('Post Comment');

			const commentButtonStyles = await commentButton.evaluate((button) => {
				const styles = window.getComputedStyle(button);
				return {
					backgroundColor: styles.backgroundColor,
					color: styles.color,
					padding: styles.padding,
					paddingTop: styles.paddingTop,
					paddingRight: styles.paddingRight,
					paddingBottom: styles.paddingBottom,
					paddingLeft: styles.paddingLeft,
					borderRadius: styles.borderRadius,
					fontSize: styles.fontSize,
					fontWeight: styles.fontWeight,
					textTransform: styles.textTransform,
					border: styles.border,
					borderWidth: styles.borderWidth,
					borderStyle: styles.borderStyle,
					borderColor: styles.borderColor,
					lineHeight: styles.lineHeight,
					display: styles.display
				};
			});

			// Verify comment button colors and styles
			expect(commentButtonStyles.backgroundColor).toBe(BUTTON_COLORS.PRIMARY_BACKGROUND);
			expect(commentButtonStyles.color).toBe(BUTTON_COLORS.PRIMARY_TEXT);
			expect(commentButtonStyles.borderColor).toBe(BUTTON_COLORS.PRIMARY_BACKGROUND);
			expect(commentButtonStyles.padding).toBe(BUTTON_STYLES.PADDING);
			expect(commentButtonStyles.paddingTop).toBe('12px');
			expect(commentButtonStyles.paddingRight).toBe('35px');
			expect(commentButtonStyles.paddingBottom).toBe('12px');
			expect(commentButtonStyles.paddingLeft).toBe('35px');
			expect(commentButtonStyles.borderRadius).toBe(BUTTON_STYLES.BORDER_RADIUS);
			expect(commentButtonStyles.fontSize).toBe(BUTTON_STYLES.FONT_SIZE);
			expect(commentButtonStyles.fontWeight).toBe(BUTTON_STYLES.FONT_WEIGHT_NORMAL);
			expect(commentButtonStyles.textTransform).toBe(BUTTON_STYLES.TEXT_TRANSFORM);
			expect(commentButtonStyles.lineHeight).toBe(BUTTON_STYLES.LINE_HEIGHT);
			expect(commentButtonStyles.display).toBe('inline-block');
			expect(commentButtonStyles.borderWidth).toBe('1px');
			expect(commentButtonStyles.borderStyle).toBe('solid');

			// Test button functionality (should be clickable)
			await expect(commentButton).toBeEnabled();
		});
	});

	test.describe('Button Responsiveness', () => {
		test('should maintain button styles across different viewports', async ({ page }) => {
			const viewports = [VIEWPORTS.DESKTOP, VIEWPORTS.TABLET, VIEWPORTS.MOBILE];
			
			for (const viewport of viewports) {
				await page.setViewportSize(viewport);
				await page.goto(TEST_URLS.HOMEPAGE);
				await page.waitForLoadState('networkidle');

				// Test hero Learn More button on different viewports
				const learnMoreButton = page.locator('a').filter({ hasText: 'LEARN MORE' }).first();
				await expect(learnMoreButton).toBeVisible();

				const buttonStyles = await learnMoreButton.evaluate((button) => {
					const styles = window.getComputedStyle(button);
					return {
						backgroundColor: styles.backgroundColor,
						color: styles.color,
						textTransform: styles.textTransform,
						fontSize: styles.fontSize
					};
				});

				// Core styles should remain consistent across viewports
				expect(buttonStyles.backgroundColor).toBe(BUTTON_COLORS.PRIMARY_BACKGROUND);
				expect(buttonStyles.color).toBe(BUTTON_COLORS.PRIMARY_TEXT);
				expect(buttonStyles.textTransform).toBe(BUTTON_STYLES.TEXT_TRANSFORM);
				expect(buttonStyles.fontSize).toBe(BUTTON_STYLES.FONT_SIZE);
			}
		});
	});

	test.describe('Button Hover States', () => {
		test.beforeEach(async ({ page }) => {
			await page.setViewportSize(VIEWPORTS.DESKTOP);
		});

		test('should handle hover states for hero Learn More button', async ({ page }) => {
			await page.goto(TEST_URLS.HOMEPAGE);
			await page.waitForLoadState('networkidle');

			const learnMoreButton = page.locator('a').filter({ hasText: 'LEARN MORE' }).first();
			await expect(learnMoreButton).toBeVisible();

			// Test hover state
			await learnMoreButton.hover();
			
			// Button should remain visible and clickable after hover
			await expect(learnMoreButton).toBeVisible();
			await expect(learnMoreButton).toBeEnabled();
		});

		test('should handle hover states for post content buttons', async ({ page }) => {
			await page.goto(TEST_URLS.BLOG_POST);
			await page.waitForLoadState('networkidle');

			const postButtons = page.locator('.wp-block-button__link');
			await expect(postButtons).toHaveCount(2);

			// Test hover on both buttons
			for (let i = 0; i < 2; i++) {
				const button = postButtons.nth(i);
				await button.hover();
				
				// Button should remain visible and clickable after hover
				await expect(button).toBeVisible();
				await expect(button).toBeEnabled();
			}
		});
	});
});
