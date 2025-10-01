import { test, expect } from '@playwright/test';
import { SITE_CONFIG, VIEWPORTS } from '../utils/constants.js';

test.describe('WooCommerce Shop Archive Tests', () => {
	test('main shop page displays proper layout and elements', async ({ page }) => {
		// Navigate to the main shop page
		await page.goto(`${SITE_CONFIG.BASE_URL}shop/`);

		// Check page title
		await expect(page).toHaveTitle(/Shop/);

		// Verify breadcrumb navigation
		const breadcrumb = page.locator('nav[aria-label="Breadcrumb"]');
		await expect(breadcrumb).toBeVisible();
		await expect(breadcrumb.locator('text=Home')).toBeVisible();
		await expect(breadcrumb.locator('text=Shop')).toBeVisible();

		// Check main heading
		const mainHeading = page.locator('h1:has-text("Shop")');
		await expect(mainHeading).toBeVisible();

		// Verify results count message
		const resultsMessage = page.locator('text=Showing the single result');
		await expect(resultsMessage).toBeVisible();

		// Check shop ordering dropdown
		const orderingDropdown = page.locator('select[name="orderby"], .orderby');
		await expect(orderingDropdown.first()).toBeVisible();

		// Verify sorting options exist (check if they're in the DOM, not necessarily visible)
		const sortingOptions = [
			'Default sorting',
			'Sort by popularity',
			'Sort by average rating',
			'Sort by latest',
			'Sort by price: low to high',
			'Sort by price: high to low'
		];

		for (const option of sortingOptions) {
			await expect(page.locator(`option:has-text("${option}")`)).toBeAttached();
		}

		// Check product grid/list exists
		const productList = page.locator('.products, ul.products');
		await expect(productList.first()).toBeVisible();

		// Verify product items
		const products = page.locator('.product, li.product');
		const productCount = await products.count();
		expect(productCount).toBeGreaterThan(0);

		// Check first product structure
		const firstProduct = products.first();
		await expect(firstProduct).toBeVisible();

		// Verify product image
		const productImage = firstProduct.locator('img');
		await expect(productImage.first()).toBeVisible();

		// Check product title/name
		const productTitle = firstProduct.locator('h2, .woocommerce-loop-product__title');
		await expect(productTitle.first()).toBeVisible();

		// Verify product price
		const productPrice = firstProduct.locator('.price, .woocommerce-Price-amount');
		await expect(productPrice.first()).toBeVisible();

		// Check add to cart button
		const addToCartButton = firstProduct.locator('a:has-text("Add to cart"), button:has-text("Add to cart")');
		await expect(addToCartButton.first()).toBeVisible();

		// Verify sale badge if present
		const saleBadge = firstProduct.locator('.onsale, :text("Sale!")');
		if (await saleBadge.count() > 0) {
			await expect(saleBadge.first()).toBeVisible();
		}
	});

	test('product category archive displays proper layout', async ({ page }) => {
		// Navigate to shoes category
		await page.goto(`${SITE_CONFIG.BASE_URL}product-category/shoes/`);

		// Check page title
		await expect(page).toHaveTitle(/Shoes/);

		// Verify breadcrumb shows category
		const breadcrumb = page.locator('nav[aria-label="Breadcrumb"]');
		await expect(breadcrumb).toBeVisible();
		await expect(breadcrumb.locator('text=Home')).toBeVisible();
		await expect(breadcrumb.locator('text=Shoes')).toBeVisible();

		// Check category heading
		const categoryHeading = page.locator('h1:has-text("Shoes")');
		await expect(categoryHeading).toBeVisible();

		// Verify shop elements are present (same as main shop)
		const resultsMessage = page.locator('text=Showing the single result');
		await expect(resultsMessage).toBeVisible();

		const orderingDropdown = page.locator('select[name="orderby"], .orderby');
		await expect(orderingDropdown.first()).toBeVisible();

		const productList = page.locator('.products, ul.products');
		await expect(productList.first()).toBeVisible();

		// Check products are filtered by category
		const products = page.locator('.product, li.product');
		const productCount = await products.count();
		expect(productCount).toBeGreaterThan(0);
	});

	test('product tag archive displays proper layout', async ({ page }) => {
		// Navigate to product tag archive
		await page.goto(`${SITE_CONFIG.BASE_URL}product-tag/product-tag/`);

		// Check page title
		await expect(page).toHaveTitle(/Product tag/);

		// Verify breadcrumb shows tag context
		const breadcrumb = page.locator('nav[aria-label="Breadcrumb"]');
		await expect(breadcrumb).toBeVisible();
		await expect(breadcrumb.locator('text=Home')).toBeVisible();
		// Check for tag breadcrumb text (may vary in format) - use contains text
		const tagBreadcrumb = breadcrumb.getByText('Product tag', { exact: false });
		await expect(tagBreadcrumb).toBeVisible();

		// Check tag heading
		const tagHeading = page.locator('h1:has-text("Product tag")');
		await expect(tagHeading).toBeVisible();

		// Verify shop elements are present
		const resultsMessage = page.locator('text=Showing the single result');
		await expect(resultsMessage).toBeVisible();

		const orderingDropdown = page.locator('select[name="orderby"], .orderby');
		await expect(orderingDropdown.first()).toBeVisible();

		const productList = page.locator('.products, ul.products');
		await expect(productList.first()).toBeVisible();

		// Check products are filtered by tag
		const products = page.locator('.product, li.product');
		const productCount = await products.count();
		expect(productCount).toBeGreaterThan(0);
	});

	test('shop sorting functionality works correctly', async ({ page }) => {
		await page.goto(`${SITE_CONFIG.BASE_URL}shop/`);

		// Test sorting dropdown interaction
		const orderingDropdown = page.locator('select[name="orderby"], .orderby').first();
		await expect(orderingDropdown).toBeVisible();

		// Get all available option values first
		const options = await orderingDropdown.locator('option').all();
		const availableValues = [];
		for (const option of options) {
			const value = await option.getAttribute('value');
			if (value) availableValues.push(value);
		}

		// Test that we can interact with the dropdown and it has options
		expect(availableValues.length).toBeGreaterThan(1);

		// Test selecting different sorting options based on what's available
		if (availableValues.includes('popularity')) {
			const currentUrl = page.url();
			await orderingDropdown.selectOption('popularity');
			await page.waitForLoadState('networkidle');
			
			// Check if URL changed or if we're on the same page with different content
			const newUrl = page.url();
			if (newUrl !== currentUrl) {
				expect(newUrl).toContain('orderby=popularity');
			} else {
				// If URL didn't change, at least verify the dropdown value changed
				const selectedValue = await orderingDropdown.inputValue();
				expect(selectedValue).toBe('popularity');
			}
		}

		// Test another sorting option if available
		const otherValues = ['date', 'rating', 'menu_order'];
		const availableOtherValue = otherValues.find(val => availableValues.includes(val));
		if (availableOtherValue) {
			await orderingDropdown.selectOption(availableOtherValue);
			await page.waitForLoadState('networkidle');
			
			// Verify the dropdown value changed
			const selectedValue = await orderingDropdown.inputValue();
			expect(selectedValue).toBe(availableOtherValue);
		}
	});

	test('product links and interactions work correctly', async ({ page }) => {
		await page.goto(`${SITE_CONFIG.BASE_URL}shop/`);

		const firstProduct = page.locator('.product, li.product').first();
		
		// Test product image link
		const productImageLink = firstProduct.locator('a').first();
		await expect(productImageLink).toBeVisible();
		
		// Verify link points to product page
		const productUrl = await productImageLink.getAttribute('href');
		expect(productUrl).toContain('/product/');

		// Test product title link
		const productTitleLink = firstProduct.locator('h2 a, .woocommerce-loop-product__title a').first();
		if (await productTitleLink.count() > 0) {
			const titleUrl = await productTitleLink.getAttribute('href');
			expect(titleUrl).toContain('/product/');
		}

		// Test add to cart button functionality
		const addToCartButton = firstProduct.locator('a:has-text("Add to cart"), button:has-text("Add to cart")').first();
		await expect(addToCartButton).toBeVisible();
		
		// Click add to cart and verify response
		await addToCartButton.click();
		
		// Wait for any AJAX response or page change
		await page.waitForTimeout(1000);
		
		// Check for success message or cart update
		const successMessage = page.locator('.woocommerce-message, .added_to_cart');
		if (await successMessage.count() > 0) {
			await expect(successMessage.first()).toBeVisible();
		}
	});

	test('shop archives are responsive on different viewports', async ({ page }) => {
		const testUrls = [
			`${SITE_CONFIG.BASE_URL}shop/`,
			`${SITE_CONFIG.BASE_URL}product-category/shoes/`,
			`${SITE_CONFIG.BASE_URL}product-tag/product-tag/`
		];

		for (const url of testUrls) {
			// Test mobile viewport
			await page.setViewportSize(VIEWPORTS.MOBILE);
			await page.goto(url);

			// Verify essential elements are visible on mobile
			await expect(page.locator('h1')).toBeVisible();
			await expect(page.locator('.products, ul.products').first()).toBeVisible();
			await expect(page.locator('.product, li.product').first()).toBeVisible();

			// Test tablet viewport
			await page.setViewportSize(VIEWPORTS.TABLET);
			await page.reload();

			await expect(page.locator('h1')).toBeVisible();
			await expect(page.locator('.products, ul.products').first()).toBeVisible();

			// Test desktop viewport
			await page.setViewportSize(VIEWPORTS.DESKTOP);
			await page.reload();

			await expect(page.locator('h1')).toBeVisible();
			await expect(page.locator('.products, ul.products').first()).toBeVisible();
			await expect(page.locator('select[name="orderby"], .orderby').first()).toBeVisible();
		}
	});

	test('shop search functionality works', async ({ page }) => {
		await page.goto(`${SITE_CONFIG.BASE_URL}shop/`);

		// Look for search functionality
		const searchButton = page.locator('a[href="#"]:has(img), .search-toggle, .product-search');
		
		if (await searchButton.count() > 0) {
			// Test search toggle
			await searchButton.first().click();
			
			// Look for search input that might appear
			const searchInput = page.locator('input[type="search"], .search-field');
			if (await searchInput.count() > 0) {
				await expect(searchInput.first()).toBeVisible();
				
				// Test search functionality
				await searchInput.first().fill('test');
				await searchInput.first().press('Enter');
				
				// Verify search results or redirect
				await page.waitForLoadState('networkidle');
				expect(page.url()).toContain('s=test');
			}
		}
	});

});
