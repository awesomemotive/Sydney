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
		await page.waitForLoadState('networkidle');

		// Check that the post content exists - wait for element to be stable
		const postContent = page.locator('article .entry-content');
		await postContent.waitFor({ state: 'visible' });
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
		await page.waitForLoadState('networkidle');

		// Check post title color - wait for element to be visible and stable
		const postTitle = page.locator('article h1');
		await postTitle.waitFor({ state: 'visible' });
		await page.waitForTimeout(500); // Allow styles to fully compute
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
		await page.waitForLoadState('networkidle');

		// Check author link color - wait for element to be visible and stable
		const authorLink = page.locator('article .byline a');
		await authorLink.waitFor({ state: 'visible' });
		await page.waitForTimeout(500); // Allow styles to fully compute
		const authorLinkColor = await authorLink.evaluate(el => {
			return window.getComputedStyle(el).color;
		});

		// Test that author link color is the expected dark blue: rgb(0, 16, 46)
		expect(authorLinkColor).toBe('rgb(0, 16, 46)');

		// Check date link color - wait for element to be visible and stable
		const dateLink = page.locator('article .posted-on a');
		await dateLink.waitFor({ state: 'visible' });
		const dateLinkColor = await dateLink.evaluate(el => {
			return window.getComputedStyle(el).color;
		});

		// Test that date link color is the expected gray: rgb(109, 118, 133)
		expect(dateLinkColor).toBe('rgb(109, 118, 133)');

		// Check category link color - wait for element to be visible and stable
		const categoryLink = page.locator('article .cat-links a');
		await categoryLink.waitFor({ state: 'visible' });
		const categoryLinkColor = await categoryLink.evaluate(el => {
			return window.getComputedStyle(el).color;
		});

		// Test that category link color is the expected gray: rgb(109, 118, 133)
		expect(categoryLinkColor).toBe('rgb(109, 118, 133)');
	});

	test('layout paddings match theme design specifications', async ({ page }) => {
		// Navigate to the post
		await page.goto(`${SITE_CONFIG.BASE_URL}2021/11/03/similique-quis-a-libero-enim-quod-corporis-3/`);
		await page.waitForLoadState('networkidle');

		// Test container padding (main content container) - wait for element to be visible and stable
		const container = page.locator('#content .container').first();
		await container.waitFor({ state: 'visible' });
		await page.waitForTimeout(500); // Allow styles to fully compute
		const containerStyles = await container.evaluate(el => {
			const styles = window.getComputedStyle(el);
			return {
				paddingTop: styles.paddingTop,
				paddingRight: styles.paddingRight,
				paddingBottom: styles.paddingBottom,
				paddingLeft: styles.paddingLeft
			};
		});

		expect(containerStyles.paddingTop).toBe('0px');
		expect(containerStyles.paddingRight).toBe('15px');
		expect(containerStyles.paddingBottom).toBe('0px');
		expect(containerStyles.paddingLeft).toBe('15px');

		// Test primary content area padding
		const primary = page.locator('#primary');
		const primaryStyles = await primary.evaluate(el => {
			const styles = window.getComputedStyle(el);
			return {
				paddingTop: styles.paddingTop,
				paddingRight: styles.paddingRight,
				paddingBottom: styles.paddingBottom,
				paddingLeft: styles.paddingLeft
			};
		});

		expect(primaryStyles.paddingTop).toBe('0px');
		expect(primaryStyles.paddingRight).toBe('15px');
		expect(primaryStyles.paddingBottom).toBe('0px');
		expect(primaryStyles.paddingLeft).toBe('15px');

		// Test sidebar padding
		const secondary = page.locator('#secondary');
		const secondaryStyles = await secondary.evaluate(el => {
			const styles = window.getComputedStyle(el);
			return {
				paddingTop: styles.paddingTop,
				paddingRight: styles.paddingRight,
				paddingBottom: styles.paddingBottom,
				paddingLeft: styles.paddingLeft
			};
		});

		expect(secondaryStyles.paddingTop).toBe('30px');
		expect(secondaryStyles.paddingRight).toBe('30px');
		expect(secondaryStyles.paddingBottom).toBe('30px');
		expect(secondaryStyles.paddingLeft).toBe('30px');

		// Test article spacing
		const article = page.locator('article');
		const articleStyles = await article.evaluate(el => {
			const styles = window.getComputedStyle(el);
			return {
				paddingTop: styles.paddingTop,
				paddingRight: styles.paddingRight,
				paddingBottom: styles.paddingBottom,
				paddingLeft: styles.paddingLeft,
				marginBottom: styles.marginBottom
			};
		});

		expect(articleStyles.paddingTop).toBe('0px');
		expect(articleStyles.paddingRight).toBe('0px');
		expect(articleStyles.paddingBottom).toBe('0px');
		expect(articleStyles.paddingLeft).toBe('0px');
		expect(articleStyles.marginBottom).toBe('60px');

		// Test entry content padding
		const entryContent = page.locator('article .entry-content');
		const entryContentStyles = await entryContent.evaluate(el => {
			const styles = window.getComputedStyle(el);
			return {
				paddingTop: styles.paddingTop,
				paddingRight: styles.paddingRight,
				paddingBottom: styles.paddingBottom,
				paddingLeft: styles.paddingLeft
			};
		});

		expect(entryContentStyles.paddingTop).toBe('0px');
		expect(entryContentStyles.paddingRight).toBe('0px');
		expect(entryContentStyles.paddingBottom).toBe('0px');
		expect(entryContentStyles.paddingLeft).toBe('0px');
	});

	test('post navigation and comment form layout spacing', async ({ page }) => {
		// Navigate to the post
		await page.goto(`${SITE_CONFIG.BASE_URL}2021/11/03/similique-quis-a-libero-enim-quod-corporis-3/`);
		await page.waitForLoadState('networkidle');

		// Test post navigation padding - wait for element to be visible and stable
		const postNavigation = page.locator('.post-navigation');
		await postNavigation.waitFor({ state: 'visible' });
		await page.waitForTimeout(500); // Allow styles to fully compute
		const postNavStyles = await postNavigation.evaluate(el => {
			const styles = window.getComputedStyle(el);
			return {
				paddingTop: styles.paddingTop,
				paddingRight: styles.paddingRight,
				paddingBottom: styles.paddingBottom,
				paddingLeft: styles.paddingLeft
			};
		});

		expect(postNavStyles.paddingTop).toBe('0px');
		expect(postNavStyles.paddingRight).toBe('0px');
		expect(postNavStyles.paddingBottom).toBe('0px');
		expect(postNavStyles.paddingLeft).toBe('0px');

		// Test comment form padding
		const commentForm = page.locator('.comment-form');
		const commentFormStyles = await commentForm.evaluate(el => {
			const styles = window.getComputedStyle(el);
			return {
				paddingTop: styles.paddingTop,
				paddingRight: styles.paddingRight,
				paddingBottom: styles.paddingBottom,
				paddingLeft: styles.paddingLeft
			};
		});

		expect(commentFormStyles.paddingTop).toBe('0px');
		expect(commentFormStyles.paddingRight).toBe('0px');
		expect(commentFormStyles.paddingBottom).toBe('0px');
		expect(commentFormStyles.paddingLeft).toBe('0px');
	});

	test('layout paddings are consistent across different viewports', async ({ page }) => {
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

			// Test container padding consistency
			const container = page.locator('#content .container').first();
			const containerStyles = await container.evaluate(el => {
				const styles = window.getComputedStyle(el);
				return {
					paddingRight: styles.paddingRight,
					paddingLeft: styles.paddingLeft
				};
			});

			// Container should maintain 15px horizontal padding across all viewports
			expect(containerStyles.paddingRight).toBe('15px');
			expect(containerStyles.paddingLeft).toBe('15px');

			// Test primary content area padding consistency
			const primary = page.locator('#primary');
			const primaryStyles = await primary.evaluate(el => {
				const styles = window.getComputedStyle(el);
				return {
					paddingRight: styles.paddingRight,
					paddingLeft: styles.paddingLeft
				};
			});

			// Primary should maintain 15px horizontal padding across all viewports
			expect(primaryStyles.paddingRight).toBe('15px');
			expect(primaryStyles.paddingLeft).toBe('15px');

			// Test sidebar padding consistency (when visible)
			const secondary = page.locator('#secondary');
			if (viewport.width >= VIEWPORTS.TABLET.width) {
				const secondaryStyles = await secondary.evaluate(el => {
					const styles = window.getComputedStyle(el);
					return {
						paddingTop: styles.paddingTop,
						paddingRight: styles.paddingRight,
						paddingBottom: styles.paddingBottom,
						paddingLeft: styles.paddingLeft
					};
				});

				// Sidebar should maintain 30px padding on larger screens
				expect(secondaryStyles.paddingTop).toBe('30px');
				expect(secondaryStyles.paddingRight).toBe('30px');
				expect(secondaryStyles.paddingBottom).toBe('30px');
				expect(secondaryStyles.paddingLeft).toBe('30px');
			}
		}
	});
});