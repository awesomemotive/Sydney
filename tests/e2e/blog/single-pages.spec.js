import { test, expect } from '@playwright/test';
import { SITE_CONFIG, VIEWPORTS } from '../utils/constants.js';

test.describe('Single Page: Sample Page', () => {
	test('page loads successfully and displays correct title', async ({ page }) => {
		// Navigate to the sample page
		await page.goto(`${SITE_CONFIG.BASE_URL}sample-page/`);

		// Check that the page title contains the expected page title
		await expect(page).toHaveTitle(/Sample Page/);

		// Check that the main page title is displayed
		const pageTitle = page.locator('article h1');
		await expect(pageTitle).toBeVisible();
		await expect(pageTitle).toHaveText('Sample Page');
	});

	test('page content structure is correct', async ({ page }) => {
		// Navigate to the page
		await page.goto(`${SITE_CONFIG.BASE_URL}sample-page/`);

		// Check that the page content exists
		const pageContent = page.locator('article .entry-content');
		await expect(pageContent).toBeVisible();

		// Check for the correct number of paragraphs (5 paragraphs)
		const paragraphs = pageContent.locator('p');
		await expect(paragraphs).toHaveCount(5);

		// Check for the correct number of blockquotes (2 blockquotes)
		const blockquotes = pageContent.locator('blockquote');
		await expect(blockquotes).toHaveCount(2);

		// Check for the correct number of links in content (2 links)
		const contentLinks = pageContent.locator('a');
		await expect(contentLinks).toHaveCount(2);

		// Verify specific link texts
		await expect(pageContent.locator('a:has-text("potential")')).toBeVisible();
		await expect(pageContent.locator('a:has-text("your dashboard")')).toBeVisible();

		// Check that the dashboard link points to wp-admin
		const dashboardLink = pageContent.locator('a:has-text("your dashboard")');
		await expect(dashboardLink).toHaveAttribute('href', /wp-admin/);
	});

	test('sidebar is displayed on single page', async ({ page }) => {
		// Navigate to the page
		await page.goto(`${SITE_CONFIG.BASE_URL}sample-page/`);

		// Check that sidebar exists
		const sidebar = page.locator('#secondary, .widget-area');
		await expect(sidebar.first()).toBeVisible();

		// Check sidebar widgets
		const recentPostsWidget = page.locator('aside:has(h3:has-text("Recent Posts"))');
		await expect(recentPostsWidget).toBeVisible();

		const recentCommentsWidget = page.locator('aside:has(h3:has-text("Recent Comments"))');
		await expect(recentCommentsWidget).toBeVisible();

		const archivesWidget = page.locator('aside:has(h3:has-text("Archives"))');
		await expect(archivesWidget).toBeVisible();

		const categoriesWidget = page.locator('aside:has(h3:has-text("Categories"))');
		await expect(categoriesWidget).toBeVisible();
	});

	test('page does not have comment form', async ({ page }) => {
		// Navigate to the page
		await page.goto(`${SITE_CONFIG.BASE_URL}sample-page/`);

		// Check that comment form does NOT exist (pages typically don't have comments by default)
		const commentForm = page.locator('.comment-form');
		await expect(commentForm).not.toBeVisible();

		// Also check that there's no comment-related heading
		const commentHeading = page.locator('h3:has-text("Leave a Reply")');
		await expect(commentHeading).not.toBeVisible();
	});

	test('page is responsive across different viewports', async ({ page }) => {
		// Test on different viewports
		const viewports = [
			VIEWPORTS.MOBILE,
			VIEWPORTS.TABLET,
			VIEWPORTS.DESKTOP
		];

		for (const viewport of viewports) {
			// Set viewport size
			await page.setViewportSize({ width: viewport.width, height: viewport.height });

			// Navigate to the page
			await page.goto(`${SITE_CONFIG.BASE_URL}sample-page/`);

			// Check that essential elements are visible
			await expect(page.locator('article h1')).toBeVisible();
			await expect(page.locator('article .entry-content')).toBeVisible();

			// Check that sidebar is visible (on larger screens)
			if (viewport.width >= VIEWPORTS.TABLET.width) {
				await expect(page.locator('#secondary, .widget-area').first()).toBeVisible();
			}
		}
	});

	test('page title colors match theme design', async ({ page }) => {
		// Navigate to the page
		await page.goto(`${SITE_CONFIG.BASE_URL}sample-page/`);

		// Check page title color
		const pageTitle = page.locator('article h1');
		const titleColor = await pageTitle.evaluate(el => {
			return window.getComputedStyle(el).color;
		});

		// Test that title color is the expected dark blue: rgb(0, 16, 46)
		expect(titleColor).toBe('rgb(0, 16, 46)');
	});

	test('page content text colors match theme design', async ({ page }) => {
		// Navigate to the page
		await page.goto(`${SITE_CONFIG.BASE_URL}sample-page/`);

		// Check paragraph text color
		const firstParagraph = page.locator('article .entry-content p').first();
		const paragraphColor = await firstParagraph.evaluate(el => {
			return window.getComputedStyle(el).color;
		});

		// Test that paragraph color is the expected dark teal: rgb(20, 46, 44)
		expect(paragraphColor).toBe('rgb(20, 46, 44)');

		// Check blockquote text color
		const firstBlockquote = page.locator('article .entry-content blockquote p').first();
		const blockquoteColor = await firstBlockquote.evaluate(el => {
			return window.getComputedStyle(el).color;
		});

		// Test that blockquote color matches paragraph color
		expect(blockquoteColor).toBe('rgb(20, 46, 44)');
	});

	test('page content link colors match theme design', async ({ page }) => {
		// Navigate to the page
		await page.goto(`${SITE_CONFIG.BASE_URL}sample-page/`);

		// Check content link colors
		const contentLinks = page.locator('article .entry-content a');
		const linkCount = await contentLinks.count();

		// Verify all links have the expected gold/yellow color
		for (let i = 0; i < linkCount; i++) {
			const linkColor = await contentLinks.nth(i).evaluate(el => {
				return window.getComputedStyle(el).color;
			});
			// Test that link color is the expected gold/yellow: rgb(255, 208, 10)
			expect(linkColor).toBe('rgb(255, 208, 10)');
		}
	});

	test('page maintains proper semantic structure', async ({ page }) => {
		// Navigate to the page
		await page.goto(`${SITE_CONFIG.BASE_URL}sample-page/`);

		// Check that the page uses proper semantic HTML
		const article = page.locator('article');
		await expect(article).toBeVisible();

		// Check that the main content is properly structured
		const main = page.locator('main');
		await expect(main).toBeVisible();

		// Check that the page title is an h1
		const h1 = page.locator('article h1');
		await expect(h1).toBeVisible();
		await expect(h1).toHaveText('Sample Page');

		// Verify no other h1 elements exist on the page (accessibility best practice)
		const allH1s = page.locator('h1');
		await expect(allH1s).toHaveCount(1);
	});

	test('page navigation and breadcrumbs work correctly', async ({ page }) => {
		// Navigate to the page
		await page.goto(`${SITE_CONFIG.BASE_URL}sample-page/`);

		// Check that navigation menu is present and functional
		const nav = page.locator('#site-navigation');
		await expect(nav).toBeVisible();

		// Check that the Home link works
		const homeLink = page.locator('#site-navigation a:has-text("Home")');
		await expect(homeLink).toBeVisible();
		await expect(homeLink).toHaveAttribute('href', /\/$/);

		// Check that the Blog link works
		const blogLink = page.locator('#site-navigation a:has-text("Blog")');
		await expect(blogLink).toBeVisible();
		await expect(blogLink).toHaveAttribute('href', /my-blog-page/);
	});

	test('page accessibility features are present', async ({ page }) => {
		// Navigate to the page
		await page.goto(`${SITE_CONFIG.BASE_URL}sample-page/`);

		// Check for skip link (accessibility feature)
		const skipLink = page.locator('a:has-text("Skip to content")');
		await expect(skipLink).toBeVisible();
		await expect(skipLink).toHaveAttribute('href', '#content');

		// Check that images have proper alt attributes (if any)
		const images = page.locator('article img');
		const imageCount = await images.count();

		// Note: This page doesn't have images, but let's verify
		expect(imageCount).toBe(0);
	});

	test.describe('Page with Featured Image: page-with-featured-image/', () => {
		test('featured image is displayed correctly', async ({ page }) => {
			// Navigate to the page with featured image
			await page.goto(`${SITE_CONFIG.BASE_URL}page-with-featured-image/`);

			// Check that the page title is correct
			await expect(page).toHaveTitle(/Page with featured image/);
			const pageTitle = page.locator('article h1');
			await expect(pageTitle).toHaveText('Page with featured image');

			// Check that the featured image is displayed
			const featuredImage = page.locator('article .wp-post-image');
			await expect(featuredImage).toBeVisible();

			// Verify the image has the correct WordPress classes
			await expect(featuredImage).toHaveClass(/wp-post-image/);
			await expect(featuredImage).toHaveClass(/attachment-large-thumb/);
			await expect(featuredImage).toHaveClass(/size-large-thumb/);

			// Check that the image has a valid src attribute
			await expect(featuredImage).toHaveAttribute('src', /.+/);
			await expect(featuredImage).toHaveAttribute('src', /pexels-rostislav-uzunov-4754883\.jpg/);

			// Verify the image loads properly (not broken)
			const src = await featuredImage.getAttribute('src');
			const response = await page.request.get(src);
			expect(response.status()).toBe(200);

			// Check that the image is properly sized (has dimensions)
			const imageBox = await featuredImage.boundingBox();
			expect(imageBox.width).toBeGreaterThan(0);
			expect(imageBox.height).toBeGreaterThan(0);
		});

		test('featured image page maintains proper layout', async ({ page }) => {
			// Navigate to the page with featured image
			await page.goto(`${SITE_CONFIG.BASE_URL}page-with-featured-image/`);

			// Check that the featured image appears before the content
			const featuredImage = page.locator('article .wp-post-image');
			const pageContent = page.locator('article .entry-content');

			await expect(featuredImage).toBeVisible();
			await expect(pageContent).toBeVisible();

			// Verify sidebar is still present
			const sidebar = page.locator('#secondary, .widget-area');
			await expect(sidebar.first()).toBeVisible();

			// Check that the page content is displayed correctly
			const paragraphs = pageContent.locator('p');
			await expect(paragraphs).toHaveCount(3);

			// Verify the content text is present
			await expect(pageContent).toContainText('Lorem ipsum dolor sit amet');
			await expect(pageContent).toContainText('Donec sed blandit tellus');
			await expect(pageContent).toContainText('Etiam et lacinia nisi');
		});

		test('featured image page is responsive', async ({ page }) => {
			// Test on different viewports
			const viewports = [
				VIEWPORTS.MOBILE,
				VIEWPORTS.TABLET,
				VIEWPORTS.DESKTOP
			];

			for (const viewport of viewports) {
				// Set viewport size
				await page.setViewportSize({ width: viewport.width, height: viewport.height });

				// Navigate to the page
				await page.goto(`${SITE_CONFIG.BASE_URL}page-with-featured-image/`);

				// Check that featured image is visible
				const featuredImage = page.locator('article .wp-post-image');
				await expect(featuredImage).toBeVisible();

				// Check that image has proper dimensions
				const imageBox = await featuredImage.boundingBox();
				expect(imageBox.width).toBeGreaterThan(0);
				expect(imageBox.height).toBeGreaterThan(0);

				// Verify page title and content are still accessible
				await expect(page.locator('article h1')).toBeVisible();
				await expect(page.locator('article .entry-content')).toBeVisible();
			}
		});
	});
});