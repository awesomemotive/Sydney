import { test, expect } from '@playwright/test';
import { SITE_CONFIG, VIEWPORTS } from '../utils/constants.js';

test.describe('Blog Archive Layout Tests', () => {
	test('blog page displays proper archive layout', async ({ page }) => {
		// Navigate to the blog page
		await page.goto(`${SITE_CONFIG.BASE_URL}my-blog-page/`);

		// Check page title
		await expect(page).toHaveTitle(/My blog page/);

		// Verify main content structure
		const mainContent = page.locator('main');
		await expect(mainContent).toBeVisible();

		// Check for articles
		const articles = page.locator('article');
		const articleCount = await articles.count();
		expect(articleCount).toBeGreaterThan(0);

		// Verify first article structure
		const firstArticle = articles.first();
		await expect(firstArticle).toBeVisible();

		// Check article has proper metadata (date and category links)
		const dateLink = firstArticle.locator('a[href*="2025"], a[href*="2021"]');
		const categoryLink = firstArticle.locator('a[href*="/category/"]');
		await expect(dateLink.first()).toBeVisible();
		await expect(categoryLink.first()).toBeVisible();

		// Check article has title (h2)
		const articleTitle = firstArticle.locator('h2');
		await expect(articleTitle.first()).toBeVisible();

		// Check article has excerpt/content
		const articleContent = firstArticle.locator('p');
		await expect(articleContent.first()).toBeVisible();

		// Check article has "Read more" link
		const readMoreLink = firstArticle.locator('a:has-text("Read more")');
		await expect(readMoreLink.first()).toBeVisible();

		// Verify sidebar exists
		const sidebar = page.locator('#secondary, .widget-area');
		await expect(sidebar.first()).toBeVisible();
	});

	test('category archive displays proper layout', async ({ page }) => {
		// Navigate to a category archive
		await page.goto(`${SITE_CONFIG.BASE_URL}category/travel/`);

		// Check page title
		await expect(page).toHaveTitle(/Travel/);

		// Check archive title
		const archiveTitle = page.locator('h1:has-text("Category: Travel")');
		await expect(archiveTitle).toBeVisible();

		// Verify main content structure
		const mainContent = page.locator('main');
		await expect(mainContent).toBeVisible();

		// Check for articles
		const articles = page.locator('article');
		const articleCount = await articles.count();
		expect(articleCount).toBeGreaterThan(0);

		// Verify articles are filtered by category
		for (let i = 0; i < Math.min(articleCount, 3); i++) {
			const article = articles.nth(i);
			const categoryLink = article.locator('a[href*="/category/travel/"]');
			await expect(categoryLink.first()).toBeVisible();
		}

		// Check article structure (same as blog page)
		const firstArticle = articles.first();

		// Check article has title (h2)
		const articleTitle = firstArticle.locator('h2');
		await expect(articleTitle.first()).toBeVisible();

		// Check article has excerpt/content
		const articleContent = firstArticle.locator('p');
		await expect(articleContent.first()).toBeVisible();

		// Check article has "Read more" link
		const readMoreLink = firstArticle.locator('a:has-text("Read more")');
		await expect(readMoreLink.first()).toBeVisible();

		// Verify sidebar exists
		const sidebar = page.locator('#secondary, .widget-area');
		await expect(sidebar.first()).toBeVisible();
	});

	test('date archive displays proper layout', async ({ page }) => {
		// Navigate to a date archive
		await page.goto(`${SITE_CONFIG.BASE_URL}2021/11/`);

		// Check page title
		await expect(page).toHaveTitle(/November 2021/);

		// Check archive title
		const archiveTitle = page.locator('h1:has-text("Month: November 2021")');
		await expect(archiveTitle).toBeVisible();

		// Verify main content structure
		const mainContent = page.locator('main');
		await expect(mainContent).toBeVisible();

		// Check for articles
		const articles = page.locator('article');
		const articleCount = await articles.count();
		expect(articleCount).toBeGreaterThan(0);

		// Verify articles are filtered by date
		for (let i = 0; i < Math.min(articleCount, 3); i++) {
			const article = articles.nth(i);
			const dateLink = article.locator('a[href*="2021/11"]');
			await expect(dateLink.first()).toBeVisible();
		}

		// Check article structure (same as other archives)
		const firstArticle = articles.first();

		// Check article has title (h2)
		const articleTitle = firstArticle.locator('h2');
		await expect(articleTitle.first()).toBeVisible();

		// Check article has excerpt/content
		const articleContent = firstArticle.locator('p');
		await expect(articleContent.first()).toBeVisible();

		// Check article has "Read more" link
		const readMoreLink = firstArticle.locator('a:has-text("Read more")');
		await expect(readMoreLink.first()).toBeVisible();

		// Verify sidebar exists
		const sidebar = page.locator('#secondary, .widget-area');
		await expect(sidebar.first()).toBeVisible();
	});

	test('blog archives are responsive', async ({ page }) => {
		const testUrls = [
			`${SITE_CONFIG.BASE_URL}my-blog-page/`,
			`${SITE_CONFIG.BASE_URL}category/travel/`,
			`${SITE_CONFIG.BASE_URL}2021/11/`
		];

		for (const testUrl of testUrls) {
			for (const viewport of [VIEWPORTS.MOBILE, VIEWPORTS.TABLET, VIEWPORTS.DESKTOP]) {
				await page.setViewportSize(viewport);
				await page.goto(testUrl);

				// Verify main content is visible
				const mainContent = page.locator('main');
				await expect(mainContent).toBeVisible();

				// Verify at least one article is visible
				const articles = page.locator('article');
				await expect(articles.first()).toBeVisible();

				// Verify sidebar exists (may be hidden on mobile but should exist in DOM)
				const sidebar = page.locator('#secondary, .widget-area').first();
				const sidebarExists = await sidebar.count() > 0;
				expect(sidebarExists).toBe(true);

				// On larger screens, sidebar should be visible
				if (viewport.width >= VIEWPORTS.TABLET.width) {
					await expect(sidebar).toBeVisible();
				}
			}
		}
	});

	test('blog archive navigation works', async ({ page }) => {
		// Navigate to the blog page
		await page.goto(`${SITE_CONFIG.BASE_URL}my-blog-page/`);

		// Check that article titles are links to individual posts
		const articleTitles = page.locator('article h2 a');
		const firstTitleLink = articleTitles.first();
		await expect(firstTitleLink).toBeVisible();

		// Verify the link has proper href
		const href = await firstTitleLink.getAttribute('href');
		expect(href).toMatch(/\/\d{4}\/\d{2}\/\d{2}\//); // Should match date-based URL pattern

		// Check that "Read more" links exist
		const readMoreLinks = page.locator('article a:has-text("Read more")');
		const readMoreCount = await readMoreLinks.count();
		expect(readMoreCount).toBeGreaterThan(0);

		// Verify read more links point to posts
		const firstReadMoreLink = readMoreLinks.first();
		const readMoreHref = await firstReadMoreLink.getAttribute('href');
		expect(readMoreHref).toMatch(/\/\d{4}\/\d{2}\/\d{2}\//);
	});

	test('archive layout consistency across page types', async ({ page }) => {
		const archivePages = [
			{ url: `${SITE_CONFIG.BASE_URL}my-blog-page/`, title: 'blog page' },
			{ url: `${SITE_CONFIG.BASE_URL}category/travel/`, title: 'category archive' },
			{ url: `${SITE_CONFIG.BASE_URL}2021/11/`, title: 'date archive' }
		];

		for (const pageData of archivePages) {
			await page.goto(pageData.url);

			// All archive pages should have consistent structure
			const mainContent = page.locator('main');
			await expect(mainContent).toBeVisible();

			// All should have articles
			const articles = page.locator('article');
			expect(await articles.count()).toBeGreaterThan(0);

			// All should have sidebar
			const sidebar = page.locator('#secondary, .widget-area');
			await expect(sidebar.first()).toBeVisible();

			// All articles should have consistent structure
			const firstArticle = articles.first();
			await expect(firstArticle.locator('h2')).toBeVisible();
			await expect(firstArticle.locator('p')).toBeVisible();
			await expect(firstArticle.locator('a:has-text("Read more")')).toBeVisible();
		}
	});

	test('primary and secondary containers have correct classes', async ({ page }) => {
		const archivePages = [
			{ url: `${SITE_CONFIG.BASE_URL}my-blog-page/`, title: 'blog page' },
			{ url: `${SITE_CONFIG.BASE_URL}category/travel/`, title: 'category archive' },
			{ url: `${SITE_CONFIG.BASE_URL}2021/11/`, title: 'date archive' }
		];

		for (const pageData of archivePages) {
			await page.goto(pageData.url);

			// Check #primary has correct classes
			const primaryElement = page.locator('#primary');
			await expect(primaryElement).toBeVisible();

			// Verify #primary has the expected classes
			const primaryClasses = await primaryElement.getAttribute('class');
			expect(primaryClasses).toContain('content-area');
			expect(primaryClasses).toContain('sidebar-right');
			expect(primaryClasses).toContain('layout3');
			expect(primaryClasses).toContain('col-md-9');

			// Check #secondary has correct classes
			const secondaryElement = page.locator('#secondary');
			await expect(secondaryElement).toBeVisible();

			// Verify #secondary has the expected classes
			const secondaryClasses = await secondaryElement.getAttribute('class');
			expect(secondaryClasses).toContain('widget-area');
			expect(secondaryClasses).toContain('col-md-3');
		}
	});

	test('article elements have correct colors', async ({ page }) => {
		const archivePages = [
			{ url: `${SITE_CONFIG.BASE_URL}my-blog-page/`, title: 'blog page' },
			{ url: `${SITE_CONFIG.BASE_URL}category/travel/`, title: 'category archive' },
			{ url: `${SITE_CONFIG.BASE_URL}2021/11/`, title: 'date archive' }
		];

		for (const pageData of archivePages) {
			await page.goto(pageData.url);

			// Get the first article for testing
			const firstArticle = page.locator('article').first();
			await expect(firstArticle).toBeVisible();

			// Test article title color (h2)
			const articleTitle = firstArticle.locator('h2');
			const titleColor = await articleTitle.evaluate(el => window.getComputedStyle(el).color);
			expect(titleColor).toBe('rgb(0, 16, 46)'); // Dark navy

			// Test article title link color
			const titleLink = articleTitle.locator('a');
			const titleLinkColor = await titleLink.evaluate(el => window.getComputedStyle(el).color);
			expect(titleLinkColor).toBe('rgb(0, 16, 46)'); // Same as title

			// Test article excerpt color (paragraph)
			const articleExcerpt = firstArticle.locator('p');
			const excerptColor = await articleExcerpt.evaluate(el => window.getComputedStyle(el).color);
			expect(excerptColor).toBe('rgb(20, 46, 44)'); // Dark teal

			// Test article "Read more" link color
			const readMoreLink = firstArticle.locator('a').filter({ hasText: 'Read more' });
			const readMoreColor = await readMoreLink.evaluate(el => window.getComputedStyle(el).color);
			expect(readMoreColor).toBe('rgb(0, 16, 46)'); // Dark navy
		}
	});

	test('archive headers are displayed correctly by archive type', async ({ page }) => {
		const archiveTests = [
			{
				url: `${SITE_CONFIG.BASE_URL}category/travel/`,
				expectedTitle: 'Category: Travel',
				description: 'Category archive should display category name'
			},
			{
				url: `${SITE_CONFIG.BASE_URL}2021/11/`,
				expectedTitle: 'Month: November 2021',
				description: 'Date archive should display month and year'
			},
			{
				url: `${SITE_CONFIG.BASE_URL}author/vlad/`,
				expectedTitle: 'Author: vlad',
				description: 'Author archive should display author name',
				hasBio: true
			}
		];

		for (const testCase of archiveTests) {
			await page.goto(testCase.url);

			// Check that the archive title is displayed correctly
			const archiveTitle = page.locator('h1').first();
			await expect(archiveTitle).toBeVisible();
			await expect(archiveTitle).toHaveText(testCase.expectedTitle);

			// For author archives, check that author bio is displayed
			if (testCase.hasBio) {
				// Author bio appears as a paragraph element after the h1 title
				const authorBio = page.locator('h1 + p, h1 + div, h1 ~ p').first();
				await expect(authorBio).toBeVisible();
				// Verify it contains some content (not empty)
				const bioText = await authorBio.textContent();
				expect(bioText && bioText.trim().length).toBeGreaterThan(0);
			}

			// Verify main content structure exists
			const mainContent = page.locator('main');
			await expect(mainContent).toBeVisible();

			// Verify articles are present
			const articles = page.locator('article');
			expect(await articles.count()).toBeGreaterThan(0);

			// Verify sidebar exists
			const sidebar = page.locator('#secondary, .widget-area');
			await expect(sidebar.first()).toBeVisible();
		}
	});

	test('blog page displays exactly 6 posts', async ({ page }) => {
		// Navigate to the blog page
		await page.goto(`${SITE_CONFIG.BASE_URL}my-blog-page/`);

		// Count the articles on the page
		const articles = page.locator('article');
		const articleCount = await articles.count();

		// Verify there are exactly 6 posts
		expect(articleCount).toBe(6);

		// Verify all 6 articles are visible
		for (let i = 0; i < 6; i++) {
			await expect(articles.nth(i)).toBeVisible();
		}
	});

	test('blog archive navigation works correctly', async ({ page }) => {
		// Navigate to the blog page
		await page.goto(`${SITE_CONFIG.BASE_URL}my-blog-page/`);

		// Verify all article titles are links that lead to individual posts
		const articleTitles = page.locator('article h2 a');
		const titleCount = await articleTitles.count();
		expect(titleCount).toBe(6); // Should match the number of posts

		// Test that each title link has a valid href pointing to a post
		for (let i = 0; i < titleCount; i++) {
			const titleLink = articleTitles.nth(i);
			const href = await titleLink.getAttribute('href');

			// Verify the link follows WordPress post URL pattern
			expect(href).toMatch(/\/\d{4}\/\d{2}\/\d{2}\//);

			// Verify the link text is not empty
			const linkText = await titleLink.textContent();
			expect(linkText && linkText.trim().length).toBeGreaterThan(0);
		}

		// Test "Read more" links
		const readMoreLinks = page.locator('article a').filter({ hasText: 'Read more' });
		const readMoreCount = await readMoreLinks.count();
		expect(readMoreCount).toBe(6); // Should match the number of posts

		// Verify each "Read more" link points to the correct post
		for (let i = 0; i < readMoreCount; i++) {
			const readMoreLink = readMoreLinks.nth(i);
			const href = await readMoreLink.getAttribute('href');

			// Verify the link follows WordPress post URL pattern
			expect(href).toMatch(/\/\d{4}\/\d{2}\/\d{2}\//);
		}

		// Test navigation between posts (if there are newer/older post links)
		// This would typically be on individual post pages, but let's check if there are any navigation elements
		const newerPostsLink = page.locator('a[href*="newer"], a[href*="next"], .nav-next a').first();
		const olderPostsLink = page.locator('a[href*="older"], a[href*="previous"], .nav-previous a').first();

		// If navigation links exist, they should be visible and have valid hrefs
		const newerExists = await newerPostsLink.count() > 0;
		const olderExists = await olderPostsLink.count() > 0;

		if (newerExists) {
			await expect(newerPostsLink).toBeVisible();
			const newerHref = await newerPostsLink.getAttribute('href');
			expect(newerHref).toBeTruthy();
		}

		if (olderExists) {
			await expect(olderPostsLink).toBeVisible();
			const olderHref = await olderPostsLink.getAttribute('href');
			expect(olderHref).toBeTruthy();
		}
	});
});