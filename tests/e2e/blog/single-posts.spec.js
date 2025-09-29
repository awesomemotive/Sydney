import { test, expect } from '@playwright/test';
import { SITE_CONFIG, VIEWPORTS } from '../utils/constants.js';

test.describe('Single Post: similique-quis-a-libero-enim-quod-corporis-3/', () => {
	test('post loads successfully and displays correct title', async ({ page }) => {
		// Navigate to the specific single post
		await page.goto(`${SITE_CONFIG.BASE_URL}2021/11/03/similique-quis-a-libero-enim-quod-corporis-3/`);

		// Check that the page title contains the expected post title
		await expect(page).toHaveTitle(/Similique quis a libero enim quod corporis/);

		// Check that the main post title is displayed
		const postTitle = page.locator('article h1');
		await expect(postTitle).toBeVisible();
		await expect(postTitle).toHaveText('Similique quis a libero enim quod corporis');
	});

	test('post metadata displays correctly', async ({ page }) => {
		// Navigate to the post
		await page.goto(`${SITE_CONFIG.BASE_URL}2021/11/03/similique-quis-a-libero-enim-quod-corporis-3/`);

		// Check author information
		const authorLink = page.locator('article .byline a');
		await expect(authorLink).toBeVisible();
		await expect(authorLink).toHaveText('vlad');
		await expect(authorLink).toHaveAttribute('href', /author\/vlad/);

		// Check publish date
		const dateLink = page.locator('article .posted-on a');
		await expect(dateLink).toBeVisible();
		await expect(dateLink).toHaveText('November 3, 2021');
		await expect(dateLink).toHaveAttribute('href', /2021\/11\/03\/similique-quis-a-libero-enim-quod-corporis-3/);

		// Check category links
		const categoryLink = page.locator('article .cat-links a');
		await expect(categoryLink).toBeVisible();
		await expect(categoryLink).toHaveText('Travel');
		await expect(categoryLink).toHaveAttribute('href', /category\/travel/);
	});

	test('post content structure is correct', async ({ page }) => {
		// Navigate to the post
		await page.goto(`${SITE_CONFIG.BASE_URL}2021/11/03/similique-quis-a-libero-enim-quod-corporis-3/`);

		// Check that the post content exists
		const postContent = page.locator('article .entry-content');
		await expect(postContent).toBeVisible();

		// Check for the correct number of paragraphs (5 paragraphs)
		const paragraphs = postContent.locator('p');
		await expect(paragraphs).toHaveCount(5);

		// Check for the correct headings (2 h3 headings)
		const headings = postContent.locator('h3');
		await expect(headings).toHaveCount(2);
		await expect(headings.first()).toHaveText('Corporis cumque sapiente');
		await expect(headings.last()).toHaveText('Ab quis dicta quod possimus');

		// Check for images in the content
		const images = postContent.locator('figure img');
		await expect(images).toHaveCount(5);

		// Verify all images have valid src attributes
		const imageCount = await images.count();
		for (let i = 0; i < imageCount; i++) {
			await expect(images.nth(i)).toHaveAttribute('src', /.+/);
		}
	});

	test('post gallery displays correctly', async ({ page }) => {
		// Navigate to the post
		await page.goto(`${SITE_CONFIG.BASE_URL}2021/11/03/similique-quis-a-libero-enim-quod-corporis-3/`);

		// Check for gallery structure
		const gallery = page.locator('figure ul');
		await expect(gallery).toBeVisible();

		// Check that the gallery contains 3 images
		const galleryImages = gallery.locator('li img');
		await expect(galleryImages).toHaveCount(3);

		// Verify each gallery image is visible and has a src
		for (let i = 0; i < 3; i++) {
			await expect(galleryImages.nth(i)).toBeVisible();
			await expect(galleryImages.nth(i)).toHaveAttribute('src', /.+/);
		}
	});

	test('post navigation works correctly', async ({ page }) => {
		// Navigate to the post
		await page.goto(`${SITE_CONFIG.BASE_URL}2021/11/03/similique-quis-a-libero-enim-quod-corporis-3/`);

		// Check that post navigation exists
		const postNavigation = page.locator('.post-navigation');
		await expect(postNavigation).toBeVisible();

		// Check for previous post link
		const prevLink = postNavigation.locator('.nav-previous a');
		await expect(prevLink).toBeVisible();
		await expect(prevLink).toHaveText('Similique quis a libero enim quod corporis');
		await expect(prevLink).toHaveAttribute('href', /similique-quis-a-libero-enim-quod-corporis-2/);

		// Check for next post link
		const nextLink = postNavigation.locator('.nav-next a');
		await expect(nextLink).toBeVisible();
		await expect(nextLink).toHaveText('Hello world!');
		await expect(nextLink).toHaveAttribute('href', /hello-world/);
	});

	test('comment form is properly displayed', async ({ page }) => {
		// Navigate to the post
		await page.goto(`${SITE_CONFIG.BASE_URL}2021/11/03/similique-quis-a-libero-enim-quod-corporis-3/`);

		// Check that comment form exists
		const commentForm = page.locator('.comment-form');
		await expect(commentForm).toBeVisible();

		// Check form heading
		const formHeading = page.locator('h3:has-text("Leave a Reply")');
		await expect(formHeading).toContainText('Leave a Reply');

		// Check required form fields
		await expect(page.locator('#comment')).toBeVisible();
		await expect(page.locator('#author')).toBeVisible();
		await expect(page.locator('#email')).toBeVisible();
		await expect(page.locator('#url')).toBeVisible();

		// Check submit button
		const submitButton = page.locator('.comment-form input[type="submit"]');
		await expect(submitButton).toBeVisible();
		await expect(submitButton).toHaveAttribute('value', 'Post Comment');

		// Check for required field indicators
		await expect(page.locator('text=Required fields are marked *')).toBeVisible();
		await expect(page.locator('text=Comment *')).toBeVisible();
		await expect(page.locator('text=Name *')).toBeVisible();
		await expect(page.locator('text=Email *')).toBeVisible();
		await expect(page.locator('label[for="url"]')).toBeVisible();
	});

	test('sidebar is displayed on single post', async ({ page }) => {
		// Navigate to the post
		await page.goto(`${SITE_CONFIG.BASE_URL}2021/11/03/similique-quis-a-libero-enim-quod-corporis-3/`);

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

	test('post is responsive across different viewports', async ({ page }) => {
		// Test on different viewports
		const viewports = [
			VIEWPORTS.MOBILE,
			VIEWPORTS.TABLET,
			VIEWPORTS.DESKTOP
		];

		for (const viewport of viewports) {
			// Set viewport size
			await page.setViewportSize({ width: viewport.width, height: viewport.height });

			// Navigate to the post
			await page.goto(`${SITE_CONFIG.BASE_URL}2021/11/03/similique-quis-a-libero-enim-quod-corporis-3/`);

			// Check that essential elements are visible
			await expect(page.locator('article h1')).toBeVisible();
			await expect(page.locator('article .entry-content')).toBeVisible();

			// Check that sidebar is visible (on larger screens)
			if (viewport.width >= VIEWPORTS.TABLET.width) {
				await expect(page.locator('#secondary, .widget-area').first()).toBeVisible();
			}
		}
	});

	test('post title colors match theme design', async ({ page }) => {
		// Navigate to the post
		await page.goto(`${SITE_CONFIG.BASE_URL}2021/11/03/similique-quis-a-libero-enim-quod-corporis-3/`);

		// Check post title color
		const postTitle = page.locator('article h1');
		const titleColor = await postTitle.evaluate(el => {
			return window.getComputedStyle(el).color;
		});

		// Test that title color is the expected dark blue: rgb(0, 16, 46)
		expect(titleColor).toBe('rgb(0, 16, 46)');
	});

	test('post content text colors match theme design', async ({ page }) => {
		// Navigate to the post
		await page.goto(`${SITE_CONFIG.BASE_URL}2021/11/03/similique-quis-a-libero-enim-quod-corporis-3/`);

		// Check paragraph text color
		const firstParagraph = page.locator('article .entry-content p').first();
		const paragraphColor = await firstParagraph.evaluate(el => {
			return window.getComputedStyle(el).color;
		});

		// Test that paragraph color is the expected dark teal: rgb(20, 46, 44)
		expect(paragraphColor).toBe('rgb(20, 46, 44)');

		// Check heading colors
		const headings = page.locator('article .entry-content h3');
		const headingCount = await headings.count();

		for (let i = 0; i < headingCount; i++) {
			const headingColor = await headings.nth(i).evaluate(el => {
				return window.getComputedStyle(el).color;
			});
			// Test that heading color is the expected dark blue: rgb(0, 16, 46)
			expect(headingColor).toBe('rgb(0, 16, 46)');
		}
	});

	test('post metadata link colors match theme design', async ({ page }) => {
		// Navigate to the post
		await page.goto(`${SITE_CONFIG.BASE_URL}2021/11/03/similique-quis-a-libero-enim-quod-corporis-3/`);

		// Check author link color
		const authorLink = page.locator('article .byline a');
		const authorLinkColor = await authorLink.evaluate(el => {
			return window.getComputedStyle(el).color;
		});

		// Test that author link color is the expected dark blue: rgb(0, 16, 46)
		expect(authorLinkColor).toBe('rgb(0, 16, 46)');

		// Check date link color
		const dateLink = page.locator('article .posted-on a');
		const dateLinkColor = await dateLink.evaluate(el => {
			return window.getComputedStyle(el).color;
		});

		// Test that date link color is the expected gray: rgb(109, 118, 133)
		expect(dateLinkColor).toBe('rgb(109, 118, 133)');

		// Check category link color
		const categoryLink = page.locator('article .cat-links a');
		const categoryLinkColor = await categoryLink.evaluate(el => {
			return window.getComputedStyle(el).color;
		});

		// Test that category link color is the expected gray: rgb(109, 118, 133)
		expect(categoryLinkColor).toBe('rgb(109, 118, 133)');
	});
});