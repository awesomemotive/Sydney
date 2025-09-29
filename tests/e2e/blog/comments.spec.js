import { test, expect } from '@playwright/test';
import { SITE_CONFIG, VIEWPORTS } from '../utils/constants.js';

test.describe('Comments System: Post with Comments', () => {
	test('comments section displays correctly on post with comments', async ({ page }) => {
		// Navigate to the specific post that has comments
		await page.goto(`${SITE_CONFIG.BASE_URL}2021/11/03/perspiciatis-velit-quae-consectetur-conseq`);

		// Check that the page loaded correctly
		await expect(page).toHaveTitle(/Perspiciatis velit quae consectetur conseq/);
		const postTitle = page.locator('article h1');
		await expect(postTitle).toHaveText('Perspiciatis velit quae consectetur conseq');

		// Check that comments section exists
		const commentsSection = page.locator('main');
		await expect(commentsSection).toBeVisible();

		// Check comments title
		const commentsTitle = commentsSection.locator('h3:has-text("3 thoughts on")');
		await expect(commentsTitle).toBeVisible();
		await expect(commentsTitle).toContainText('3 thoughts on');
	});

	test('individual comments display correctly', async ({ page }) => {
		// Navigate to the post
		await page.goto(`${SITE_CONFIG.BASE_URL}2021/11/03/perspiciatis-velit-quae-consectetur-conseq`);

		// Check that all comments are displayed (in ol/li structure)
		const commentListItems = page.locator('main ol li');
		await expect(commentListItems).toHaveCount(3);

		// Check first comment content
		const firstComment = commentListItems.first();
		await expect(firstComment).toContainText('vlad says:');
		await expect(firstComment).toContainText('test comment 1');
		await expect(firstComment).toContainText('Reply');

		// Check that first comment has a nested reply
		const firstCommentNested = firstComment.locator('ol li');
		await expect(firstCommentNested).toHaveCount(1);
		await expect(firstCommentNested.first()).toContainText('vlad says:');
		await expect(firstCommentNested.first()).toContainText('reply comment 1');

		// Check second top-level comment (it's the 3rd li element due to nesting)
		const secondComment = commentListItems.nth(2);
		await expect(secondComment).toContainText('vlad says:');
		await expect(secondComment).toContainText('test comment 2');
		await expect(secondComment).toContainText('Reply');
	});

	test('comment metadata displays correctly', async ({ page }) => {
		// Navigate to the post
		await page.goto(`${SITE_CONFIG.BASE_URL}2021/11/03/perspiciatis-velit-quae-consectetur-conseq`);

		// Check that comments have dates
		const commentDates = page.locator('main ol li time');
		await expect(commentDates).toHaveCount(3);

		// All comments should have the same date
		for (let i = 0; i < 3; i++) {
			await expect(commentDates.nth(i)).toContainText('September 29, 2025 at 11:17 am');
		}

		// Check that date links have proper href attributes
		const dateLinks = page.locator('main ol li a[href*="comment-"]');
		await expect(dateLinks).toHaveCount(3);
	});

	test('comment reply functionality exists', async ({ page }) => {
		// Navigate to the post
		await page.goto(`${SITE_CONFIG.BASE_URL}2021/11/03/perspiciatis-velit-quae-consectetur-conseq`);

		// Check that reply links exist for all comments
		const replyLinks = page.locator('main ol li a:has-text("Reply")');
		await expect(replyLinks).toHaveCount(3);

		// Check that reply links have proper URLs with replytocom parameter
		for (let i = 0; i < 3; i++) {
			const replyLink = replyLinks.nth(i);
			await expect(replyLink).toHaveAttribute('href', /replytocom=\d+/);
			await expect(replyLink).toHaveAttribute('href', /#respond/);
		}
	});

	test('comment form displays correctly', async ({ page }) => {
		// Navigate to the post
		await page.goto(`${SITE_CONFIG.BASE_URL}2021/11/03/perspiciatis-velit-quae-consectetur-conseq`);

		// Check that comment form exists
		const commentForm = page.locator('main form');
		await expect(commentForm).toBeVisible();

		// Check form title
		const formTitle = page.locator('main h3:has-text("Leave a Reply")');
		await expect(formTitle).toBeVisible();

		// Check all required form fields
		await expect(page.locator('main textarea[name="comment"]')).toBeVisible();
		await expect(page.locator('main input[name="author"]')).toBeVisible();
		await expect(page.locator('main input[name="email"]')).toBeVisible();
		await expect(page.locator('main input[name="url"]')).toBeVisible();

		// Check submit button
		const submitButton = page.locator('main input[type="submit"], main button[type="submit"]');
		await expect(submitButton).toBeVisible();
		await expect(submitButton).toHaveAttribute('value', 'Post Comment');
	});

	test('comment form validation indicators are present', async ({ page }) => {
		// Navigate to the post
		await page.goto(`${SITE_CONFIG.BASE_URL}2021/11/03/perspiciatis-velit-quae-consectetur-conseq`);

		// Check for required field indicators
		await expect(page.locator('main').filter({ hasText: 'Required fields are marked *' })).toBeVisible();
		await expect(page.locator('main').filter({ hasText: 'Comment *' })).toBeVisible();
		await expect(page.locator('main').filter({ hasText: 'Name *' })).toBeVisible();
		await expect(page.locator('main').filter({ hasText: 'Email *' })).toBeVisible();
		await expect(page.locator('main').filter({ hasText: 'Website' })).toBeVisible();
	});

	test('comment form privacy notice is displayed', async ({ page }) => {
		// Navigate to the post
		await page.goto(`${SITE_CONFIG.BASE_URL}2021/11/03/perspiciatis-velit-quae-consectetur-conseq`);

		// Check for privacy notice
		await expect(page.locator('main').filter({ hasText: 'Your email address will not be published.' })).toBeVisible();
	});

	test('comment form cookie consent checkbox exists', async ({ page }) => {
		// Navigate to the post
		await page.goto(`${SITE_CONFIG.BASE_URL}2021/11/03/perspiciatis-velit-quae-consectetur-conseq`);

		// Check for cookie consent checkbox
		const cookieCheckbox = page.locator('main input[type="checkbox"][name="wp-comment-cookies-consent"]');
		await expect(cookieCheckbox).toBeVisible();

		// Check associated label
		await expect(page.locator('main').filter({ hasText: 'Save my name, email, and website in this browser' })).toBeVisible();
	});

	test('comments are responsive across different viewports', async ({ page }) => {
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
			await page.goto(`${SITE_CONFIG.BASE_URL}2021/11/03/perspiciatis-velit-quae-consectetur-conseq`);

			// Check that comments section is visible
			await expect(page.locator('main h3:has-text("3 thoughts on")')).toBeVisible();

			// Check that all comments are visible
			const comments = page.locator('main ol li');
			await expect(comments).toHaveCount(3);

			// Check that comment form is accessible
			await expect(page.locator('main form')).toBeVisible();
		}
	});

	test('comments maintain theme styling', async ({ page }) => {
		// Navigate to the post
		await page.goto(`${SITE_CONFIG.BASE_URL}2021/11/03/perspiciatis-velit-quae-consectetur-conseq`);

		// Check comment author link colors (if any)
		const commentAuthorLinks = page.locator('main ol li a:has-text("vlad")');
		const authorLinkCount = await commentAuthorLinks.count();

		if (authorLinkCount > 0) {
			for (let i = 0; i < Math.min(authorLinkCount, 2); i++) {
				const authorColor = await commentAuthorLinks.nth(i).evaluate(el => {
					return window.getComputedStyle(el).color;
				});
				// Should match theme link color (blue)
				expect(authorColor).toBe('rgb(59, 114, 208)');
			}
		}

		// Check comment text colors
		const commentContent = page.locator('main ol li p').first();
		const commentColor = await commentContent.evaluate(el => {
			return window.getComputedStyle(el).color;
		});
		// Should match theme text color (dark teal)
		expect(commentColor).toBe('rgb(20, 46, 44)');
	});

	test('comment threading displays correctly', async ({ page }) => {
		// Navigate to the post
		await page.goto(`${SITE_CONFIG.BASE_URL}2021/11/03/perspiciatis-velit-quae-consectetur-conseq`);

		// Check that the first comment has a nested reply
		const firstComment = page.locator('main ol li').first();
		const nestedComments = firstComment.locator('ol li');

		// Should have one reply to the first comment
		await expect(nestedComments).toHaveCount(1);

		// Check that the reply has proper content
		await expect(nestedComments.first()).toContainText('vlad says:');
		await expect(nestedComments.first()).toContainText('reply comment 1');
		await expect(nestedComments.first()).toContainText('Reply');
	});

	test('comments section accessibility features', async ({ page }) => {
		// Navigate to the post
		await page.goto(`${SITE_CONFIG.BASE_URL}2021/11/03/perspiciatis-velit-quae-consectetur-conseq`);

		// Check for proper heading hierarchy
		const commentsHeading = page.locator('main h3:has-text("3 thoughts on")');
		await expect(commentsHeading).toBeVisible();

		// Comments should not break the single h1 rule
		const h1Elements = page.locator('h1');
		await expect(h1Elements).toHaveCount(1); // Only the post title should be h1

		// Check that comment form has proper labels
		await expect(page.locator('main label[for="comment"]')).toBeVisible();
		await expect(page.locator('main label[for="author"]')).toBeVisible();
		await expect(page.locator('main label[for="email"]')).toBeVisible();
		await expect(page.locator('main label[for="url"]')).toBeVisible();
	});

	test('comment form submission validation', async ({ page }) => {
		// Navigate to the post
		await page.goto(`${SITE_CONFIG.BASE_URL}2021/11/03/perspiciatis-velit-quae-consectetur-conseq`);

		// Check that submit button exists
		const submitButton = page.locator('main input[type="submit"], main button[type="submit"]');
		await expect(submitButton).toBeVisible();

		// Fill out form fields to test they accept input
		const commentField = page.locator('main textarea[name="comment"]');
		const nameField = page.locator('main input[name="author"]');
		const emailField = page.locator('main input[name="email"]');

		await commentField.fill('Test comment');
		await nameField.fill('Test User');
		await emailField.fill('test@example.com');

		// Verify fields contain the entered values
		await expect(commentField).toHaveValue('Test comment');
		await expect(nameField).toHaveValue('Test User');
		await expect(emailField).toHaveValue('test@example.com');
	});
});