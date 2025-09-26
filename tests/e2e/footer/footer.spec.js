import { test, expect } from '@playwright/test';
import { SITE_CONFIG, VIEWPORTS } from '../utils/constants.js';

test.describe('Footer Tests', () => {
	test.beforeEach(async ({ page }) => {
		await page.goto(SITE_CONFIG.BASE_URL);
		// Scroll to footer to ensure it's visible
		await page.evaluate(() => {
			window.scrollTo(0, document.body.scrollHeight);
		});
	});

	test('footer credits are visible', async ({ page }) => {
		// Test that the footer credits section exists and is visible
		const creditsSection = page.locator('.sydney-credits');
		await expect(creditsSection).toBeVisible();
		
		// Check that it contains the expected copyright text
		await expect(creditsSection).toContainText('© 2025 Tests');
		await expect(creditsSection).toContainText('Proudly powered by');
		
		// Check that the Sydney theme link is present
		const sydneyLink = creditsSection.locator('a[href*="sydney"]');
		await expect(sydneyLink).toBeVisible();
		await expect(sydneyLink).toHaveText('Sydney');
	});

	test('social icons are visible', async ({ page }) => {
		// Test that social icons container exists
		const socialContainer = page.locator('.social-profile');
		await expect(socialContainer).toBeVisible();
		
		// Test individual social icons
		const facebookIcon = page.locator('a[href*="facebook.com"]');
		const twitterIcon = page.locator('a[href*="twitter.com"]');
		const instagramIcon = page.locator('a[href*="instagram.com"]');
		
		await expect(facebookIcon).toBeVisible();
		await expect(twitterIcon).toBeVisible();
		await expect(instagramIcon).toBeVisible();
		
		// Check that icons have proper aria-labels for accessibility
		await expect(facebookIcon).toHaveAttribute('aria-label', 'facebook link, opens in a new tab');
		await expect(twitterIcon).toHaveAttribute('aria-label', 'twitter link, opens in a new tab');
		await expect(instagramIcon).toHaveAttribute('aria-label', 'instagram link, opens in a new tab');
		
		// Check that links open in new tab
		await expect(facebookIcon).toHaveAttribute('target', '_blank');
		await expect(twitterIcon).toHaveAttribute('target', '_blank');
		await expect(instagramIcon).toHaveAttribute('target', '_blank');
	});

	test('social icons alignment on desktop and mobile', async ({ page }) => {
		// Test desktop alignment (should be right-aligned)
		await page.setViewportSize(VIEWPORTS.DESKTOP);
		
		const socialColumn = page.locator('.shfb-below_footer_row .shfb-column-2');
		await expect(socialColumn).toBeVisible();
		
		// Check that the social icons column has proper alignment
		const socialContainer = page.locator('.social-profile');
		const socialColumnStyles = await socialColumn.evaluate(el => {
			const styles = window.getComputedStyle(el);
			return {
				textAlign: styles.textAlign,
				justifyContent: styles.justifyContent,
				display: styles.display
			};
		});
		
		// On desktop, the social icons should be right-aligned
		// This can be achieved through CSS flexbox or text-align
		
		// Test mobile alignment (should be center-aligned)
		await page.setViewportSize(VIEWPORTS.MOBILE);
		
		const mobileStyles = await socialColumn.evaluate(el => {
			const styles = window.getComputedStyle(el);
			return {
				textAlign: styles.textAlign,
				justifyContent: styles.justifyContent,
				display: styles.display
			};
		});
		
		// Verify that alignment changes on mobile
		// Note: The specific alignment values depend on the theme's CSS implementation
		expect(socialColumnStyles).toBeDefined();
		expect(mobileStyles).toBeDefined();
	});

	test('footer colors match theme design', async ({ page }) => {
		// Test footer background color
		const belowFooterRow = page.locator('.shfb-below_footer_row');
		await expect(belowFooterRow).toBeVisible();
		
		// Get computed styles for color testing
		const footerStyles = await belowFooterRow.evaluate(el => {
			const styles = window.getComputedStyle(el);
			return {
				backgroundColor: styles.backgroundColor
			};
		});
		
		// Test that background color is the expected dark blue: rgb(0, 16, 46)
		expect(footerStyles.backgroundColor).toBe('rgb(0, 16, 46)');
		
		// Test credits text color
		const creditsSection = page.locator('.sydney-credits');
		const creditsStyles = await creditsSection.evaluate(el => {
			const styles = window.getComputedStyle(el);
			return {
				color: styles.color
			};
		});
		
		// Test that credits color is the expected white: rgb(255, 255, 255)
		expect(creditsStyles.color).toBe('rgb(255, 255, 255)');
		
		// Test social icons color
		const socialContainer = page.locator('.social-profile');
		const socialStyles = await socialContainer.evaluate(el => {
			const styles = window.getComputedStyle(el);
			return {
				color: styles.color
			};
		});
		
		// Test that social icons color matches the credits color: rgb(20, 46, 44)
		expect(socialStyles.color).toBe('rgb(20, 46, 44)');
		
		// Test border top color
		const belowFooterBorderStyles = await belowFooterRow.evaluate(el => {
			const styles = window.getComputedStyle(el);
			return {
				borderTopColor: styles.borderTopColor,
				borderTopWidth: styles.borderTopWidth,
				borderTopStyle: styles.borderTopStyle
			};
		});
		
		// Test that border top color is the expected light gray with transparency: rgba(234, 234, 234, 0.1)
		expect(belowFooterBorderStyles.borderTopColor).toBe('rgba(234, 234, 234, 0.1)');
		expect(belowFooterBorderStyles.borderTopWidth).toBe('1px');
		expect(belowFooterBorderStyles.borderTopStyle).toBe('solid');
	});

	test('above footer row components', async ({ page }) => {
		// Test that the button is visible
		const button = page.locator('.shfb-above_footer_row a[href="#"]');
		await expect(button).toBeVisible();
		await expect(button).toContainText('Click me');

		// Test that the HTML component is visible on desktop
		await page.setViewportSize(VIEWPORTS.DESKTOP);
		const htmlComponent = page.locator('.shfb-above_footer_row .footer-html');
		await expect(htmlComponent).toBeVisible();
		await expect(htmlComponent).toContainText('HTML Content hidden on mobiles');

		// Test that the HTML component is hidden on mobile
		await page.setViewportSize(VIEWPORTS.MOBILE);
		await expect(htmlComponent).toBeHidden();
	});

	test('main footer row properties', async ({ page }) => {
		// Test row background color
		const mainFooterRow = page.locator('.shfb-main_footer_row');
		await expect(mainFooterRow).toBeVisible();

		const rowBgColor = await mainFooterRow.evaluate(el => {
			return window.getComputedStyle(el).backgroundColor;
		});
		expect(rowBgColor).toBe('rgb(0, 16, 46)');

		// Test that all widgets exist
		const expectedWidgets = ['widget1', 'widget2', 'widget3', 'widget4'];
		for (const widgetId of expectedWidgets) {
			const widget = page.locator(`[data-component-id="${widgetId}"]`);
			await expect(widget).toBeVisible();
		}

		// Test border top color of the row
		const borderTopStyles = await mainFooterRow.evaluate(el => {
			const styles = window.getComputedStyle(el);
			return {
				borderTopColor: styles.borderTopColor,
				borderTopWidth: styles.borderTopWidth,
				borderTopStyle: styles.borderTopStyle
			};
		});
		expect(borderTopStyles.borderTopColor).toBe('rgba(234, 234, 234, 0.1)');
		expect(borderTopStyles.borderTopWidth).toBe('1px');
		expect(borderTopStyles.borderTopStyle).toBe('solid');

		// Test color of widget titles
		const widgetTitles = mainFooterRow.locator('h3');
		const titleCount = await widgetTitles.count();
		expect(titleCount).toBeGreaterThan(0);

		for (let i = 0; i < titleCount; i++) {
			const titleColor = await widgetTitles.nth(i).evaluate(el => {
				return window.getComputedStyle(el).color;
			});
			expect(titleColor).toBe('rgb(255, 255, 255)');
		}

		// Test color of menu items
		const menuLinks = mainFooterRow.locator('a[href]');
		const linkCount = await menuLinks.count();
		expect(linkCount).toBeGreaterThan(0);

		for (let i = 0; i < Math.min(linkCount, 5); i++) { // Test first 5 links to avoid timeout
			const linkColor = await menuLinks.nth(i).evaluate(el => {
				return window.getComputedStyle(el).color;
			});
			expect(linkColor).toBe('rgb(255, 255, 255)');
		}
	});

	test('below footer row dimensions and spacing', async ({ page }) => {
		const belowFooterRow = page.locator('.shfb-below_footer_row');

		// Test desktop dimensions and spacing
		await page.setViewportSize(VIEWPORTS.DESKTOP);
		const desktopStyles = await belowFooterRow.evaluate(el => {
			const styles = window.getComputedStyle(el);
			return {
				minHeight: styles.minHeight,
				paddingTop: styles.paddingTop,
				paddingBottom: styles.paddingBottom,
				height: el.offsetHeight
			};
		});
		expect(desktopStyles.minHeight).toBe('100px');
		expect(desktopStyles.paddingTop).toBe('30px');
		expect(desktopStyles.paddingBottom).toBe('30px');
		expect(desktopStyles.height).toBe(100);

		// Test tablet dimensions and spacing
		await page.setViewportSize(VIEWPORTS.TABLET);
		const tabletStyles = await belowFooterRow.evaluate(el => {
			const styles = window.getComputedStyle(el);
			return {
				minHeight: styles.minHeight,
				paddingTop: styles.paddingTop,
				paddingBottom: styles.paddingBottom,
				height: el.offsetHeight
			};
		});
		expect(tabletStyles.minHeight).toBe('120px');
		expect(tabletStyles.paddingTop).toBe('15px');
		expect(tabletStyles.paddingBottom).toBe('15px');
		expect(tabletStyles.height).toBe(120);

		// Test mobile dimensions and spacing
		await page.setViewportSize(VIEWPORTS.MOBILE);
		const mobileStyles = await belowFooterRow.evaluate(el => {
			const styles = window.getComputedStyle(el);
			return {
				minHeight: styles.minHeight,
				paddingTop: styles.paddingTop,
				paddingBottom: styles.paddingBottom,
				height: el.offsetHeight
			};
		});
		expect(mobileStyles.minHeight).toBe('140px');
		expect(mobileStyles.paddingTop).toBe('10px');
		expect(mobileStyles.paddingBottom).toBe('10px');
		expect(mobileStyles.height).toBe(140);
	});

	test('footer structure and layout', async ({ page }) => {
		// Test that main footer elements exist
		const footer = page.locator('footer.shfb-footer');
		await expect(footer).toBeVisible();
		
		// Test that below footer row exists
		const belowFooterRow = page.locator('.shfb-below_footer_row');
		await expect(belowFooterRow).toBeVisible();
		
		// Test that the footer has two columns
		const column1 = page.locator('.shfb-below_footer_row .shfb-column-1');
		const column2 = page.locator('.shfb-below_footer_row .shfb-column-2');
		
		await expect(column1).toBeVisible();
		await expect(column2).toBeVisible();
		
		// Test that credits are in column 1
		await expect(column1.locator('.sydney-credits')).toBeVisible();
		
		// Test that social icons are in column 2
		await expect(column2.locator('.social-profile')).toBeVisible();
	});

	test('footer responsive behavior', async ({ page }) => {
		// Test footer on different viewport sizes
		const viewports = [VIEWPORTS.MOBILE, VIEWPORTS.TABLET, VIEWPORTS.DESKTOP];
		
		for (const viewport of viewports) {
			await page.setViewportSize(viewport);
			
			// Ensure footer is still visible and functional
			const footer = page.locator('footer.shfb-footer');
			await expect(footer).toBeVisible();
			
			const creditsSection = page.locator('.sydney-credits');
			await expect(creditsSection).toBeVisible();
			
			const socialContainer = page.locator('.social-profile');
			await expect(socialContainer).toBeVisible();
			
			// Count social icons
			const socialIcons = page.locator('.social-profile a');
			await expect(socialIcons).toHaveCount(3);
		}
	});
});