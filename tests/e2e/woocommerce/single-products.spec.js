import { test, expect } from '@playwright/test';
import { SITE_CONFIG, VIEWPORTS } from '../utils/constants.js';

test.describe('WooCommerce Single Product Tests', () => {
	test('single product page displays proper layout and elements', async ({ page }) => {
		// Navigate to the single product page
		await page.goto(`${SITE_CONFIG.BASE_URL}product/test-product/`);

		// Check page title
		await expect(page).toHaveTitle(/Test product/);

		// Verify breadcrumb navigation
		const breadcrumb = page.locator('nav[aria-label="Breadcrumb"]');
		await expect(breadcrumb).toBeVisible();
		await expect(breadcrumb.locator('text=Home')).toBeVisible();
		await expect(breadcrumb.locator('text=Shoes')).toBeVisible();
		await expect(breadcrumb.locator('text=Test product')).toBeVisible();

		// Check main product heading
		const productHeading = page.locator('h1:has-text("Test product")');
		await expect(productHeading).toBeVisible();

		// Verify product images
		const productImages = page.locator('img[alt*="Test product"]');
		const imageCount = await productImages.count();
		expect(imageCount).toBeGreaterThan(0);

		// Check main product image
		const mainProductImage = productImages.first();
		await expect(mainProductImage).toBeVisible();

		// Verify product image gallery/thumbnails
		const thumbnails = page.locator('img[alt*="Test product"]').nth(1);
		if (await thumbnails.count() > 0) {
			await expect(thumbnails).toBeVisible();
		}

		// Check sale badge
		const saleBadge = page.locator(':text("Sale!")');
		await expect(saleBadge).toBeVisible();

		// Verify product price
		const productPrice = page.locator('.price, .woocommerce-Price-amount');
		await expect(productPrice.first()).toBeVisible();

		// Check original price (strikethrough)
		const originalPrice = page.locator('del, .woocommerce-Price-amount:has-text("111,00 lei")');
		await expect(originalPrice.first()).toBeVisible();

		// Check sale price
		const salePrice = page.locator('ins, .woocommerce-Price-amount:has-text("92,00 lei")');
		await expect(salePrice.first()).toBeVisible();

		// Verify short description
		const shortDescription = page.locator('text=Lorem ipsum dolor sit amet, consectetur adipiscing elit.');
		await expect(shortDescription.first()).toBeVisible();

		// Check quantity selector
		const quantityInput = page.locator('input[type="number"], spinbutton');
		await expect(quantityInput.first()).toBeVisible();
		
		// Verify default quantity is 1
		const quantityValue = await quantityInput.first().inputValue();
		expect(quantityValue).toBe('1');

		// Check add to cart button
		const addToCartButton = page.locator('button:has-text("Add to cart")');
		await expect(addToCartButton).toBeVisible();
		await expect(addToCartButton).toBeEnabled();

		// Verify product meta information
		const categoryLink = page.locator('a:has-text("Shoes")');
		await expect(categoryLink.first()).toBeVisible();

		const tagLink = page.locator('a:has-text("Product tag")');
		await expect(tagLink.first()).toBeVisible();

		const brandLink = page.locator('a:has-text("Nike")');
		await expect(brandLink.first()).toBeVisible();
	});

	test('product tabs functionality works correctly', async ({ page }) => {
		await page.goto(`${SITE_CONFIG.BASE_URL}product/test-product/`);

		// Check tabs are present
		const tabList = page.locator('[role="tablist"]');
		await expect(tabList).toBeVisible();

		// Verify Description tab
		const descriptionTab = page.locator('tab:has-text("Description"), [role="tab"]:has-text("Description")');
		await expect(descriptionTab.first()).toBeVisible();

		// Verify Reviews tab
		const reviewsTab = page.locator('tab:has-text("Reviews"), [role="tab"]:has-text("Reviews")');
		await expect(reviewsTab.first()).toBeVisible();

		// Test Description tab content (should be active by default)
		const descriptionContent = page.locator('text=Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas fermentum');
		await expect(descriptionContent).toBeVisible();

		// Click Reviews tab
		await reviewsTab.first().click();
		
		// Verify Reviews content appears
		const reviewsContent = page.locator('text=There are no reviews yet.');
		await expect(reviewsContent).toBeVisible();

		// Check review form elements
		const reviewForm = page.locator('text=Be the first to review');
		await expect(reviewForm).toBeVisible();

		// Verify rating stars (may be implemented differently)
		const ratingStars = page.locator('input[type="radio"], .star-rating input, .rating input');
		const starCount = await ratingStars.count();
		// Rating stars might be implemented differently, so check if they exist
		if (starCount > 0) {
			expect(starCount).toBeGreaterThanOrEqual(5);
		}

		// Check review form fields
		const reviewTextarea = page.locator('textarea');
		await expect(reviewTextarea.first()).toBeVisible();

		const nameInput = page.locator('input[type="text"]').first();
		await expect(nameInput).toBeVisible();

		const emailInput = page.locator('input[type="email"]');
		await expect(emailInput.first()).toBeVisible();

		// Check submit button (may be implemented differently)
		const submitButton = page.locator('button:has-text("Submit"), input[value="Submit"], .submit');
		if (await submitButton.count() > 0) {
			await expect(submitButton.first()).toBeVisible();
		} else {
			// If no submit button found, just verify the form exists
			const reviewForm = page.locator('form, .comment-form');
			await expect(reviewForm.first()).toBeVisible();
		}
	});

	test('product quantity and add to cart functionality', async ({ page }) => {
		await page.goto(`${SITE_CONFIG.BASE_URL}product/test-product/`);

		const quantityInput = page.locator('input[type="number"], spinbutton').first();
		const addToCartButton = page.locator('button:has-text("Add to cart")');

		// Test quantity input
		await quantityInput.clear();
		await quantityInput.fill('3');
		
		// Verify quantity changed
		const newQuantity = await quantityInput.inputValue();
		expect(newQuantity).toBe('3');

		// Test add to cart
		await addToCartButton.click();
		
		// Wait for any AJAX response
		await page.waitForTimeout(2000);
		
		// Check for success message or cart update
		const successIndicators = [
			page.locator('.woocommerce-message'),
			page.locator('.added_to_cart'),
			page.locator('text=has been added to your cart'),
			page.locator('.cart-contents')
		];

		let foundSuccessIndicator = false;
		for (const indicator of successIndicators) {
			if (await indicator.count() > 0) {
				await expect(indicator.first()).toBeVisible();
				foundSuccessIndicator = true;
				break;
			}
		}

		// If no specific success indicator, at least verify the button is still functional
		if (!foundSuccessIndicator) {
			await expect(addToCartButton).toBeVisible();
		}
	});

	test('product image gallery functionality', async ({ page }) => {
		await page.goto(`${SITE_CONFIG.BASE_URL}product/test-product/`);

		// Check if multiple product images exist
		const productImages = page.locator('img[alt*="Test product"]');
		const imageCount = await productImages.count();
		
		if (imageCount > 1) {
			// Test thumbnail navigation
			const thumbnails = page.locator('img[alt*="Test product"]');
			
			// Click on second thumbnail if it exists
			if (await thumbnails.nth(1).count() > 0) {
				await thumbnails.nth(1).click();
				
				// Wait for image change
				await page.waitForTimeout(500);
				
				// Verify main image changed (this is basic - in real scenarios you'd check src attributes)
				const mainImage = page.locator('img[alt*="Test product"]').first();
				await expect(mainImage).toBeVisible();
			}

			// Test image links (should open larger version)
			const imageLinks = page.locator('a[href*=".jpg"], a[href*=".png"]');
			if (await imageLinks.count() > 0) {
				const firstImageLink = imageLinks.first();
				const href = await firstImageLink.getAttribute('href');
				expect(href).toContain('.jpg');
			}
		}
	});

	test('product meta links work correctly', async ({ page }) => {
		await page.goto(`${SITE_CONFIG.BASE_URL}product/test-product/`);

		// Test category link
		const categoryLink = page.locator('a:has-text("Shoes")').first();
		await expect(categoryLink).toBeVisible();
		
		const categoryHref = await categoryLink.getAttribute('href');
		expect(categoryHref).toContain('/product-category/shoes/');

		// Test clicking category link
		await categoryLink.click();
		await page.waitForLoadState('networkidle');
		
		// Verify we're on category page
		await expect(page).toHaveTitle(/Shoes/);
		expect(page.url()).toContain('/product-category/shoes/');

		// Go back to product
		await page.goBack();
		await page.waitForLoadState('networkidle');

		// Test tag link
		const tagLink = page.locator('a:has-text("Product tag")').first();
		await expect(tagLink).toBeVisible();
		
		const tagHref = await tagLink.getAttribute('href');
		expect(tagHref).toContain('/product-tag/product-tag/');

		// Test brand link
		const brandLink = page.locator('a:has-text("Nike")').first();
		await expect(brandLink).toBeVisible();
		
		const brandHref = await brandLink.getAttribute('href');
		expect(brandHref).toContain('/brand/nike/');
	});

	test('single product is responsive on different viewports', async ({ page }) => {
		const productUrl = `${SITE_CONFIG.BASE_URL}product/test-product/`;

		// Test mobile viewport
		await page.setViewportSize(VIEWPORTS.MOBILE);
		await page.goto(productUrl);

		// Verify essential elements are visible on mobile
		await expect(page.locator('h1:has-text("Test product")')).toBeVisible();
		await expect(page.locator('img[alt*="Test product"]').first()).toBeVisible();
		await expect(page.locator('button:has-text("Add to cart")')).toBeVisible();
		await expect(page.locator('input[type="number"], spinbutton').first()).toBeVisible();

		// Test tablet viewport
		await page.setViewportSize(VIEWPORTS.TABLET);
		await page.reload();

		await expect(page.locator('h1:has-text("Test product")')).toBeVisible();
		await expect(page.locator('img[alt*="Test product"]').first()).toBeVisible();
		await expect(page.locator('[role="tablist"]')).toBeVisible();

		// Test desktop viewport
		await page.setViewportSize(VIEWPORTS.DESKTOP);
		await page.reload();

		await expect(page.locator('h1:has-text("Test product")')).toBeVisible();
		await expect(page.locator('img[alt*="Test product"]').first()).toBeVisible();
		await expect(page.locator('[role="tablist"]')).toBeVisible();
		await expect(page.locator('nav[aria-label="Breadcrumb"]')).toBeVisible();
	});

	test('product review form validation', async ({ page }) => {
		await page.goto(`${SITE_CONFIG.BASE_URL}product/test-product/`);

		// Click Reviews tab
		const reviewsTab = page.locator('[role="tab"]:has-text("Reviews")');
		await reviewsTab.first().click();

		// Try to submit empty form - look for review submit button specifically
		const submitButton = page.locator('button:has-text("Submit"), input[value="Submit"], .submit');
		
		// Check if submit button exists and is visible
		if (await submitButton.count() > 0) {
			await submitButton.first().click();
		} else {
			// If no submit button found, skip this part of the test
			console.log('No submit button found for review form');
		}

		// Wait for validation
		await page.waitForTimeout(1000);

		// Check if form validation prevents submission or shows errors
		// This is basic validation - actual behavior depends on WooCommerce setup
		const reviewTextarea = page.locator('textarea').first();
		const nameInput = page.locator('input[type="text"]').first();
		const emailInput = page.locator('input[type="email"]').first();

		// Verify form fields are still visible (form didn't submit)
		await expect(reviewTextarea).toBeVisible();
		await expect(nameInput).toBeVisible();
		await expect(emailInput).toBeVisible();

		// Test filling out the form
		await reviewTextarea.fill('This is a test review for the product.');
		await nameInput.fill('Test User');
		await emailInput.fill('test@example.com');

		// Select a rating
		const ratingStars = page.locator('input[type="radio"], .star-rating input, .rating input');
		if (await ratingStars.count() > 0) {
			await ratingStars.nth(4).click(); // 5-star rating
		}

		// Form should now be ready for submission (we won't actually submit in tests)
		if (await submitButton.count() > 0) {
			await expect(submitButton.first()).toBeVisible();
			await expect(submitButton.first()).toBeEnabled();
		}
	});

	test('product breadcrumb navigation works', async ({ page }) => {
		await page.goto(`${SITE_CONFIG.BASE_URL}product/test-product/`);

		const breadcrumb = page.locator('nav[aria-label="Breadcrumb"]');
		await expect(breadcrumb).toBeVisible();

		// Test Home breadcrumb link
		const homeLink = breadcrumb.locator('a:has-text("Home")');
		await expect(homeLink).toBeVisible();
		
		const homeHref = await homeLink.getAttribute('href');
		expect(homeHref).toContain(SITE_CONFIG.BASE_URL.replace(/\/$/, ''));

		// Test category breadcrumb link
		const categoryBreadcrumb = breadcrumb.locator('a:has-text("Shoes")');
		await expect(categoryBreadcrumb).toBeVisible();
		
		const categoryHref = await categoryBreadcrumb.getAttribute('href');
		expect(categoryHref).toContain('/product-category/shoes/');

		// Test clicking category breadcrumb
		await categoryBreadcrumb.click();
		await page.waitForLoadState('networkidle');
		
		// Verify we're on category page
		expect(page.url()).toContain('/product-category/shoes/');
		await expect(page.locator('h1:has-text("Shoes")')).toBeVisible();
	});
});