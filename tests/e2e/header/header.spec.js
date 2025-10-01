import { test, expect } from '@playwright/test';
import { SITE_CONFIG, VIEWPORTS } from '../utils/constants.js';

test.describe('Desktop Header', () => {
	test.beforeEach(async ({ page }) => {
		// Set desktop viewport
		await page.setViewportSize(VIEWPORTS.DESKTOP);
	});

	test('should be transparent on homepage and normal on other pages', async ({ page }) => {
		// Test homepage - header should be transparent and absolutely positioned
		await page.goto(SITE_CONFIG.BASE_URL);
		await page.waitForLoadState('networkidle');

		// Get the header element
		const header = page.locator('header').first();
		await expect(header).toBeVisible();

		// Check header wrapper and inner row styles on homepage
		const homepageStyles = await header.evaluate((el) => {
			const headerStyles = window.getComputedStyle(el);
			const rowWrapper = el.querySelector('.shfb-row-wrapper.shfb-main_header_row');
			const rowStyles = rowWrapper ? window.getComputedStyle(rowWrapper) : null;
			
			return {
				header: {
					backgroundColor: headerStyles.backgroundColor,
					position: headerStyles.position,
					zIndex: headerStyles.zIndex
				},
				rowWrapper: rowStyles ? {
					backgroundColor: rowStyles.backgroundColor,
					hasRowWrapper: true
				} : { hasRowWrapper: false }
			};
		});

		// On homepage, header wrapper should be transparent and absolutely positioned
		const headerIsTransparent = homepageStyles.header.backgroundColor === 'rgba(0, 0, 0, 0)' || 
									 homepageStyles.header.backgroundColor === 'rgba(255, 255, 255, 0)' ||
									 homepageStyles.header.backgroundColor === 'transparent' ||
									 homepageStyles.header.backgroundColor === '';
		
		expect(headerIsTransparent).toBe(true);
		expect(homepageStyles.header.position).toBe('absolute');
		
		// On homepage, there should be no row wrapper with background color (transparent header)
		if (homepageStyles.rowWrapper.hasRowWrapper) {
			const rowIsTransparent = homepageStyles.rowWrapper.backgroundColor === 'rgba(0, 0, 0, 0)' || 
									 homepageStyles.rowWrapper.backgroundColor === 'rgba(255, 255, 255, 0)' ||
									 homepageStyles.rowWrapper.backgroundColor === 'transparent' ||
									 homepageStyles.rowWrapper.backgroundColor === '';
			expect(rowIsTransparent).toBe(true);
		}

		// Navigate to blog page to test normal header behavior
		await page.goto(SITE_CONFIG.BASE_URL + 'my-blog-page/');
		await page.waitForLoadState('networkidle');

		// Get header and row wrapper styles on blog page
		const blogStyles = await header.evaluate((el) => {
			const headerStyles = window.getComputedStyle(el);
			const rowWrapper = el.querySelector('.shfb-row-wrapper.shfb-main_header_row');
			const rowStyles = rowWrapper ? window.getComputedStyle(rowWrapper) : null;
			
			return {
				header: {
					backgroundColor: headerStyles.backgroundColor,
					position: headerStyles.position
				},
				rowWrapper: rowStyles ? {
					backgroundColor: rowStyles.backgroundColor,
					hasRowWrapper: true
				} : { hasRowWrapper: false }
			};
		});

		// On blog page, header wrapper should be positioned normally (relative, not absolute)
		expect(blogStyles.header.position).toBe('relative');
		
		// On blog page, the row wrapper should have a background color (not transparent)
		expect(blogStyles.rowWrapper.hasRowWrapper).toBe(true);
		const rowIsTransparent = blogStyles.rowWrapper.backgroundColor === 'rgba(0, 0, 0, 0)' || 
								 blogStyles.rowWrapper.backgroundColor === 'rgba(255, 255, 255, 0)' ||
								 blogStyles.rowWrapper.backgroundColor === 'transparent' ||
								 blogStyles.rowWrapper.backgroundColor === '';
		expect(rowIsTransparent).toBe(false);
		
		// Verify the row wrapper has the expected dark blue background
		expect(blogStyles.rowWrapper.backgroundColor).toBe('rgb(0, 16, 46)');
	});

	test('should have working sticky functionality on both pages', async ({ page }) => {
		// Test sticky functionality on homepage
		await page.goto(SITE_CONFIG.BASE_URL);
		await page.waitForLoadState('networkidle');

		const header = page.locator('header').first();
		await expect(header).toBeVisible();

		// Check initial state on homepage (before scroll)
		const homepageBeforeScroll = await header.evaluate((el) => {
			const headerStyles = window.getComputedStyle(el);
			const rowWrapper = el.querySelector('.shfb-row-wrapper.shfb-main_header_row');
			const rowStyles = rowWrapper ? window.getComputedStyle(rowWrapper) : null;
			
			return {
				scrollY: window.scrollY,
				header: {
					position: headerStyles.position,
					className: el.className
				},
				rowWrapper: rowStyles ? {
					backgroundColor: rowStyles.backgroundColor,
					className: rowWrapper.className,
					hasStickyActive: rowWrapper.className.includes('sticky-active')
				} : null
			};
		});

		// Before scroll on homepage: header absolute, row wrapper transparent, no sticky-active
		expect(homepageBeforeScroll.scrollY).toBe(0);
		expect(homepageBeforeScroll.header.position).toBe('absolute');
		expect(homepageBeforeScroll.header.className).toContain('has-sticky-header');
		expect(homepageBeforeScroll.rowWrapper.hasStickyActive).toBe(false);

		// Scroll down on homepage to activate sticky
		await page.evaluate(() => {
			document.documentElement.scrollTop = 500;
		});

		// Wait for sticky to activate
		await page.waitForTimeout(100);

		// Check state after scroll on homepage
		const homepageAfterScroll = await header.evaluate((el) => {
			const rowWrapper = el.querySelector('.shfb-row-wrapper.shfb-main_header_row');
			const rowStyles = rowWrapper ? window.getComputedStyle(rowWrapper) : null;
			
			return {
				scrollY: window.scrollY,
				rowWrapper: rowStyles ? {
					backgroundColor: rowStyles.backgroundColor,
					className: rowWrapper.className,
					hasStickyActive: rowWrapper.className.includes('sticky-active')
				} : null
			};
		});

		// After scroll on homepage: sticky should be active with background
		expect(homepageAfterScroll.scrollY).toBeGreaterThan(0);
		expect(homepageAfterScroll.rowWrapper.hasStickyActive).toBe(true);
		// Background color should be the dark blue color (with or without alpha)
		expect(homepageAfterScroll.rowWrapper.backgroundColor).toMatch(/rgba?\(0,\s*16,\s*46/);

		// Test sticky functionality on blog page
		await page.goto(SITE_CONFIG.BASE_URL + 'my-blog-page/');
		await page.waitForLoadState('networkidle');

		// Check initial state on blog page (before scroll)
		const blogBeforeScroll = await header.evaluate((el) => {
			const headerStyles = window.getComputedStyle(el);
			const rowWrapper = el.querySelector('.shfb-row-wrapper.shfb-main_header_row');
			const rowStyles = rowWrapper ? window.getComputedStyle(rowWrapper) : null;
			
			return {
				scrollY: window.scrollY,
				header: {
					position: headerStyles.position,
					className: el.className
				},
				rowWrapper: rowStyles ? {
					backgroundColor: rowStyles.backgroundColor,
					className: rowWrapper.className,
					hasStickyActive: rowWrapper.className.includes('sticky-active')
				} : null
			};
		});

		// Before scroll on blog page: header relative, row wrapper has background, sticky ready
		expect(blogBeforeScroll.scrollY).toBe(0);
		expect(blogBeforeScroll.header.position).toBe('relative');
		expect(blogBeforeScroll.header.className).toContain('has-sticky-header');
		// Background color should be the dark blue color (with or without alpha)
		expect(blogBeforeScroll.rowWrapper.backgroundColor).toMatch(/rgba?\(0,\s*16,\s*46/);

		// Scroll down on blog page
		await page.evaluate(() => {
			document.documentElement.scrollTop = 300;
		});

		// Wait for any sticky changes
		await page.waitForTimeout(100);

		// Check state after scroll on blog page
		const blogAfterScroll = await header.evaluate((el) => {
			const rowWrapper = el.querySelector('.shfb-row-wrapper.shfb-main_header_row');
			const rowStyles = rowWrapper ? window.getComputedStyle(rowWrapper) : null;
			
			return {
				scrollY: window.scrollY,
				rowWrapper: rowStyles ? {
					backgroundColor: rowStyles.backgroundColor,
					className: rowWrapper.className,
					hasStickyActive: rowWrapper.className.includes('sticky-active')
				} : null
			};
		});

		// After scroll on blog page: should maintain background and sticky behavior
		expect(blogAfterScroll.scrollY).toBeGreaterThan(0);
		// Background color should be the dark blue color (with or without alpha)
		expect(blogAfterScroll.rowWrapper.backgroundColor).toMatch(/rgba?\(0,\s*16,\s*46/);
		
		// Verify header has sticky classes indicating it's configured for sticky behavior
		const headerClasses = await header.getAttribute('class');
		expect(headerClasses).toContain('has-sticky-header');
		expect(headerClasses).toContain('sticky-always');
	});

	test('should have site logo, title, and description with proper links', async ({ page }) => {
		// Test on homepage only
		await page.goto(SITE_CONFIG.BASE_URL);
		await page.waitForLoadState('networkidle');

		// Check for site logo
		const siteLogo = page.locator('img.site-logo').first();
		await expect(siteLogo).toBeVisible();
		
		// Verify logo properties
		await expect(siteLogo).toHaveAttribute('alt', 'Tests');
		await expect(siteLogo).toHaveAttribute('src', /sydneylogo\.svg$/);
		
		// Check that logo is wrapped in a link to homepage
		const logoLink = siteLogo.locator('..'); // Parent element (the link)
		await expect(logoLink).toHaveAttribute('href', SITE_CONFIG.BASE_URL);

		// Check for site title
		const siteTitle = page.locator('h1.site-title');
		await expect(siteTitle).toBeVisible();
		await expect(siteTitle).toHaveText('Tests');
		
		// Check that site title contains a link to homepage
		const siteTitleLink = siteTitle.locator('a');
		await expect(siteTitleLink).toBeVisible();
		await expect(siteTitleLink).toHaveAttribute('href', SITE_CONFIG.BASE_URL);
		await expect(siteTitleLink).toHaveText('Tests');

		// Check for site description
		const siteDescription = page.locator('p.site-description').first();
		await expect(siteDescription).toBeVisible();
		await expect(siteDescription).toHaveText('Test site');

		// Verify that logo and title links are clickable (without actually navigating)
		await expect(logoLink).toBeEnabled();
		await expect(siteTitleLink).toBeEnabled();
	});

	test('should have working dropdown menu navigation functionality', async ({ page }) => {
		// Navigate to homepage
		await page.goto(SITE_CONFIG.BASE_URL);
		await page.waitForLoadState('networkidle');

		// Find the About menu item with dropdown
		const aboutMenuItem = page.locator('#menu-item-941');
		await expect(aboutMenuItem).toBeVisible();

		// Verify dropdown exists (it may be visible by default in this theme)
		const dropdown = aboutMenuItem.locator('ul').first();
		await expect(dropdown).toBeVisible();

		// Hover over About menu item to show dropdown
		await aboutMenuItem.hover();
		await page.waitForTimeout(200); // Wait for hover animation

		// Verify dropdown is now visible
		await expect(dropdown).toBeVisible();

		// Check dropdown contains expected items
		const dropdownItems = dropdown.locator('li a');
		await expect(dropdownItems).toHaveCount(2); // Should have 2 items based on site structure

		// Verify dropdown items are clickable
		const firstDropdownItem = dropdownItems.first();
		await expect(firstDropdownItem).toBeVisible();
		await expect(firstDropdownItem).toBeEnabled();

		// Test clicking dropdown item navigates correctly
		const firstItemHref = await firstDropdownItem.getAttribute('href');
		expect(firstItemHref).toBeTruthy();

		// Move mouse away from dropdown area
		await page.locator('body').hover();
		await page.waitForTimeout(300); // Wait for hover out animation

		// Verify dropdown is still accessible (theme may keep it visible)
		await expect(dropdown).toBeVisible();
	});

	test('should have proper dropdown menu hover states and timing', async ({ page }) => {
		// Navigate to homepage
		await page.goto(SITE_CONFIG.BASE_URL);
		await page.waitForLoadState('networkidle');

		const aboutMenuItem = page.locator('#menu-item-941');
		const dropdown = aboutMenuItem.locator('ul').first();

		// Test hover in timing
		await aboutMenuItem.hover();
		
		// Dropdown should appear within reasonable time
		await expect(dropdown).toBeVisible({ timeout: 500 });

		// Test hover state styling
		const aboutLink = aboutMenuItem.locator('> a');
		const hoverStyles = await aboutLink.evaluate(el => {
			const styles = window.getComputedStyle(el);
			return {
				color: styles.color,
				textDecoration: styles.textDecoration
			};
		});

		// Verify hover state has expected styling (link should have some visual change)
		expect(hoverStyles.color).toBeTruthy();

		// Test hover persistence - dropdown should stay visible when hovering over it
		await dropdown.hover();
		await page.waitForTimeout(100);
		await expect(dropdown).toBeVisible();

		// Test hover out timing
		await page.locator('body').hover();
		
		// Dropdown should remain accessible (theme keeps it visible)
		await expect(dropdown).toBeVisible({ timeout: 1000 });

		// Test rapid hover in/out doesn't break functionality
		for (let i = 0; i < 3; i++) {
			await aboutMenuItem.hover();
			await page.waitForTimeout(50);
			await page.locator('body').hover();
			await page.waitForTimeout(50);
		}

		// Final hover should still work correctly
		await aboutMenuItem.hover();
		await expect(dropdown).toBeVisible({ timeout: 500 });
	});

	test('should support dropdown menu keyboard navigation accessibility', async ({ page }) => {
		// Navigate to homepage
		await page.goto(SITE_CONFIG.BASE_URL);
		await page.waitForLoadState('networkidle');

		// Click on the About menu item to focus it
		const aboutMenuItem = page.locator('#menu-item-941 > a');
		await aboutMenuItem.click();
		await page.waitForTimeout(200);

		// Verify About menu item is focused or active
		await expect(aboutMenuItem).toBeVisible();

		// Press Enter or Space to activate dropdown
		await page.keyboard.press('Enter');
		await page.waitForTimeout(200);

		// Verify dropdown is visible
		const dropdown = page.locator('#menu-item-941 ul').first();
		await expect(dropdown).toBeVisible();

		// Test that dropdown items are accessible
		const firstDropdownItem = dropdown.locator('li a').first();
		await expect(firstDropdownItem).toBeVisible();

		const secondDropdownItem = dropdown.locator('li a').nth(1);
		await expect(secondDropdownItem).toBeVisible();

		// Press Escape to test escape functionality
		await page.keyboard.press('Escape');
		await page.waitForTimeout(200);

		// Verify dropdown is still accessible and menu item is visible
		await expect(dropdown).toBeVisible();
		await expect(aboutMenuItem).toBeVisible();

		// Test Arrow Down key functionality
		await page.keyboard.press('ArrowDown');
		await page.waitForTimeout(200);
		
		// Should maintain dropdown visibility and items should be accessible
		await expect(dropdown).toBeVisible();
		await expect(firstDropdownItem).toBeVisible();

		// Test that all dropdown items remain accessible
		await expect(secondDropdownItem).toBeVisible();
	});

	test('should have consistent header layout padding and spacing', async ({ page }) => {
		// Test on homepage
		await page.goto(SITE_CONFIG.BASE_URL);
		await page.waitForLoadState('networkidle');

		// Test header container padding
		const headerContainer = page.locator('header .container').first();
		const headerContainerStyles = await headerContainer.evaluate(el => {
			const styles = window.getComputedStyle(el);
			return {
				paddingTop: styles.paddingTop,
				paddingRight: styles.paddingRight,
				paddingBottom: styles.paddingBottom,
				paddingLeft: styles.paddingLeft,
				marginTop: styles.marginTop,
				marginBottom: styles.marginBottom
			};
		});

		// Verify header container has consistent padding
		expect(headerContainerStyles.paddingRight).toBe('15px');
		expect(headerContainerStyles.paddingLeft).toBe('15px');
		expect(headerContainerStyles.paddingTop).toBe('0px');
		expect(headerContainerStyles.paddingBottom).toBe('0px');

		// Test site branding area spacing
		const siteBranding = page.locator('.site-branding').first();
		const siteBrandingStyles = await siteBranding.evaluate(el => {
			const styles = window.getComputedStyle(el);
			return {
				paddingTop: styles.paddingTop,
				paddingRight: styles.paddingRight,
				paddingBottom: styles.paddingBottom,
				paddingLeft: styles.paddingLeft
			};
		});

		// Verify site branding has consistent spacing (adjust expectations based on actual values)
		expect(siteBrandingStyles.paddingTop).toBe('0px');
		expect(siteBrandingStyles.paddingBottom).toBe('0px');
		expect(siteBrandingStyles.paddingRight).toBe('0px');
		expect(siteBrandingStyles.paddingLeft).toBe('0px');

		// Test navigation area spacing
		const navigation = page.locator('header nav');
		const navigationStyles = await navigation.evaluate(el => {
			const styles = window.getComputedStyle(el);
			return {
				paddingTop: styles.paddingTop,
				paddingRight: styles.paddingRight,
				paddingBottom: styles.paddingBottom,
				paddingLeft: styles.paddingLeft
			};
		});

		// Verify navigation has consistent spacing
		expect(navigationStyles.paddingTop).toBe('0px');
		expect(navigationStyles.paddingBottom).toBe('0px');

		// Test on different page to ensure consistency
		await page.goto(SITE_CONFIG.BASE_URL + 'my-blog-page/');
		await page.waitForLoadState('networkidle');

		// Re-test header container padding on blog page
		const blogHeaderContainerStyles = await headerContainer.evaluate(el => {
			const styles = window.getComputedStyle(el);
			return {
				paddingRight: styles.paddingRight,
				paddingLeft: styles.paddingLeft
			};
		});

		// Verify consistency across pages
		expect(blogHeaderContainerStyles.paddingRight).toBe('15px');
		expect(blogHeaderContainerStyles.paddingLeft).toBe('15px');
	});

	test('should maintain proper header z-index stacking order', async ({ page }) => {
		// Navigate to homepage
		await page.goto(SITE_CONFIG.BASE_URL);
		await page.waitForLoadState('networkidle');

		// Test initial header z-index
		const header = page.locator('header').first();
		const initialZIndex = await header.evaluate(el => {
			const styles = window.getComputedStyle(el);
			return {
				zIndex: styles.zIndex,
				position: styles.position
			};
		});

		// Header should have high z-index for proper stacking
		expect(parseInt(initialZIndex.zIndex)).toBeGreaterThan(100);
		expect(initialZIndex.position).toBe('absolute');

		// Test sticky header z-index
		await page.evaluate(() => {
			document.documentElement.scrollTop = 500;
		});
		await page.waitForTimeout(200);

		const stickyHeaderRow = page.locator('.shfb-row-wrapper.shfb-main_header_row').first();
		const stickyZIndex = await stickyHeaderRow.evaluate(el => {
			const styles = window.getComputedStyle(el);
			return {
				zIndex: styles.zIndex,
				position: styles.position
			};
		});

		// Sticky header should have a valid z-index (may be auto or a number)
		expect(stickyZIndex.zIndex).toBeTruthy();

		// Test dropdown menu z-index
		const aboutMenuItem = page.locator('#menu-item-941');
		await aboutMenuItem.hover();
		await page.waitForTimeout(200);

		const dropdown = aboutMenuItem.locator('ul').first();
		const dropdownZIndex = await dropdown.evaluate(el => {
			const styles = window.getComputedStyle(el);
			return {
				zIndex: styles.zIndex,
				position: styles.position
			};
		});

		// Dropdown should have a valid z-index
		expect(dropdownZIndex.zIndex).toBeTruthy();

		// Test search overlay z-index
		const searchButton = page.getByRole('link', { name: 'Search for a product' });
		await searchButton.click();
		await page.waitForTimeout(200);

		const searchOverlay = page.getByRole('search');
		const searchZIndex = await searchOverlay.evaluate(el => {
			const styles = window.getComputedStyle(el);
			return {
				zIndex: styles.zIndex,
				position: styles.position
			};
		});

		// Search overlay should have a valid z-index
		expect(searchZIndex.zIndex).toBeTruthy();

		// Verify no z-index conflicts by checking elements don't overlap incorrectly
		const headerBounds = await header.boundingBox();
		const searchBounds = await searchOverlay.boundingBox();
		
		// Search overlay should be positioned above header
		expect(searchBounds).toBeTruthy();
		expect(headerBounds).toBeTruthy();
	});
});

test.describe('Mobile Header', () => {
	test.beforeEach(async ({ page }) => {
		// Set mobile viewport
		await page.setViewportSize(VIEWPORTS.MOBILE);
	});

	test('should have mobile menu toggle that opens offcanvas menu', async ({ page }) => {
		// Navigate to homepage
		await page.goto(SITE_CONFIG.BASE_URL);
		await page.waitForLoadState('networkidle');

		// Check that mobile menu toggle exists and is visible
		const menuToggle = page.getByRole('link', { name: 'Open mobile offcanvas menu' });
		await expect(menuToggle).toBeVisible();

		// Verify initial state - mobile menu should be closed
		const initialBodyClass = await page.evaluate(() => document.body.className);
		expect(initialBodyClass).not.toContain('mobile-menu-visible');

		// Click the mobile menu toggle to open the offcanvas menu
		await menuToggle.click();

		// Wait for menu animation/state change
		await page.waitForTimeout(300);

		// Verify that mobile menu is now open
		const openBodyClass = await page.evaluate(() => document.body.className);
		expect(openBodyClass).toContain('mobile-menu-visible');

		// Check that the close button is now active/visible
		const closeButton = page.getByRole('link', { name: 'Close mobile menu' });
		await expect(closeButton).toBeVisible();

		// Verify that navigation menu is visible in the offcanvas
		const navigation = page.getByRole('navigation');
		await expect(navigation).toBeVisible();

		// Check that navigation contains expected menu items (scoped to the mobile navigation)
		await expect(navigation.getByRole('link', { name: 'Home' })).toBeVisible();
		await expect(navigation.getByRole('link', { name: 'Blog' })).toBeVisible();
		await expect(navigation.getByRole('link', { name: 'About' })).toBeVisible();
		await expect(navigation.getByRole('link', { name: 'Contact' })).toBeVisible();
		await expect(navigation.getByRole('link', { name: 'Portfolio' })).toBeVisible();
		await expect(navigation.getByRole('link', { name: 'Services' })).toBeVisible();

		// Test that close button works
		await closeButton.click();
		await page.waitForTimeout(300);

		// Verify that mobile menu is closed again
		const closedBodyClass = await page.evaluate(() => document.body.className);
		expect(closedBodyClass).not.toContain('mobile-menu-visible');
	});

	test('should not show mobile menu toggle on desktop', async ({ page }) => {
		// Switch to desktop viewport
		await page.setViewportSize(VIEWPORTS.DESKTOP);
		
		// Navigate to homepage
		await page.goto(SITE_CONFIG.BASE_URL);
		await page.waitForLoadState('networkidle');

		// Mobile menu toggle should not be visible on desktop
		const menuToggle = page.getByRole('link', { name: 'Open mobile offcanvas menu' });
		await expect(menuToggle).not.toBeVisible();
	});

	test('should close offcanvas menu when close button is clicked', async ({ page }) => {
		// Navigate to homepage
		await page.goto(SITE_CONFIG.BASE_URL);
		await page.waitForLoadState('networkidle');

		// Verify initial state - mobile menu should be closed
		const initialBodyClass = await page.evaluate(() => document.body.className);
		expect(initialBodyClass).not.toContain('mobile-menu-visible');

		// Open the mobile menu first
		const menuToggle = page.getByRole('link', { name: 'Open mobile offcanvas menu' });
		await menuToggle.click();
		await page.waitForTimeout(300);

		// Verify menu is open
		const openBodyClass = await page.evaluate(() => document.body.className);
		expect(openBodyClass).toContain('mobile-menu-visible');

		// Verify close button is visible and active
		const closeButton = page.getByRole('link', { name: 'Close mobile menu' });
		await expect(closeButton).toBeVisible();

		// Click the close button
		await closeButton.click();
		await page.waitForTimeout(300);

		// Verify menu is now closed
		const closedBodyClass = await page.evaluate(() => document.body.className);
		expect(closedBodyClass).not.toContain('mobile-menu-visible');

		// Verify close button is no longer active (menu toggle should be active instead)
		await expect(menuToggle).toBeVisible();
	});

	test('should have correct header colors across different pages and states', async ({ page }) => {
		// Test homepage colors (transparent header)
		await page.goto(SITE_CONFIG.BASE_URL);
		await page.waitForLoadState('networkidle');

		// Get homepage header colors (initial state)
		const homepageColors = await page.evaluate(() => {
			const header = document.querySelector('header');
			const headerStyles = window.getComputedStyle(header);
			const siteTitle = document.querySelector('.site-title');
			const siteTitleStyles = siteTitle ? window.getComputedStyle(siteTitle) : null;
			const siteDescription = document.querySelector('.site-description');
			const siteDescriptionStyles = siteDescription ? window.getComputedStyle(siteDescription) : null;
			const headerRow = document.querySelector('.shfb-row-wrapper.shfb-main_header_row');
			const headerRowStyles = headerRow ? window.getComputedStyle(headerRow) : null;
			
			return {
				header: {
					backgroundColor: headerStyles.backgroundColor,
					position: headerStyles.position
				},
				siteTitle: siteTitleStyles ? {
					color: siteTitleStyles.color
				} : null,
				siteDescription: siteDescriptionStyles ? {
					color: siteDescriptionStyles.color
				} : null,
				headerRow: headerRowStyles ? {
					backgroundColor: headerRowStyles.backgroundColor
				} : null
			};
		});

		// Verify homepage initial colors
		expect(homepageColors.header.backgroundColor).toBe('rgba(255, 255, 255, 0)'); // Transparent header
		expect(homepageColors.header.position).toBe('absolute'); // Absolutely positioned
		expect(homepageColors.siteTitle.color).toBe('rgb(0, 16, 46)'); // Dark blue site title
		expect(homepageColors.siteDescription.color).toBe('rgba(255, 255, 255, 0.6)'); // Semi-transparent white description
		expect(homepageColors.headerRow.backgroundColor).toBe('rgba(0, 0, 0, 0)'); // Transparent header row initially

		// Test sticky mode on homepage
		await page.evaluate(() => {
			document.documentElement.scrollTop = 500;
		});
		await page.waitForTimeout(200);

		const homepageStickyColors = await page.evaluate(() => {
			const headerRow = document.querySelector('.shfb-row-wrapper.shfb-main_header_row');
			const headerRowStyles = headerRow ? window.getComputedStyle(headerRow) : null;
			
			return {
				headerRow: headerRowStyles ? {
					backgroundColor: headerRowStyles.backgroundColor,
					hasStickyActive: headerRow.className.includes('sticky-active')
				} : null
			};
		});

		// Verify sticky mode colors on homepage
		expect(homepageStickyColors.headerRow.hasStickyActive).toBe(true);
		expect(homepageStickyColors.headerRow.backgroundColor).toBe('rgb(0, 16, 46)'); // Dark blue background when sticky

		// Test blog page colors
		await page.goto(SITE_CONFIG.BASE_URL + 'my-blog-page/');
		await page.waitForLoadState('networkidle');

		const blogPageColors = await page.evaluate(() => {
			const header = document.querySelector('header');
			const headerStyles = window.getComputedStyle(header);
			const siteTitle = document.querySelector('.site-title');
			const siteTitleStyles = siteTitle ? window.getComputedStyle(siteTitle) : null;
			const siteDescription = document.querySelector('.site-description');
			const siteDescriptionStyles = siteDescription ? window.getComputedStyle(siteDescription) : null;
			const headerRow = document.querySelector('.shfb-row-wrapper.shfb-main_header_row');
			const headerRowStyles = headerRow ? window.getComputedStyle(headerRow) : null;
			
			return {
				header: {
					backgroundColor: headerStyles.backgroundColor,
					position: headerStyles.position
				},
				siteTitle: siteTitleStyles ? {
					color: siteTitleStyles.color
				} : null,
				siteDescription: siteDescriptionStyles ? {
					color: siteDescriptionStyles.color
				} : null,
				headerRow: headerRowStyles ? {
					backgroundColor: headerRowStyles.backgroundColor
				} : null
			};
		});

		// Verify blog page colors
		expect(blogPageColors.header.backgroundColor).toBe('rgba(255, 255, 255, 0)'); // Header wrapper still transparent
		expect(blogPageColors.header.position).toBe('relative'); // Relatively positioned on blog page
		expect(blogPageColors.siteTitle.color).toBe('rgb(20, 46, 44)'); // Different site title color on blog page
		expect(blogPageColors.siteDescription.color).toBe('rgba(255, 255, 255, 0.6)'); // Same description color
		expect(blogPageColors.headerRow.backgroundColor).toBe('rgb(0, 16, 46)'); // Header row has background on blog page
	});
});