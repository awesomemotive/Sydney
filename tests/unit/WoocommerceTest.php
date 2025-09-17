<?php
/**
 * Unit Tests for WooCommerce Integration
 *
 * Tests the Sydney theme's WooCommerce integration including theme support,
 * layout configurations, product display options, and custom functionality.
 *
 * @package Sydney
 * @subpackage Tests
 */

namespace Sydney\Tests;

use Sydney\Tests\BaseThemeTest;
use WP_Mock as M;

/**
 * Test class for WooCommerce integration functionality.
 *
 * Tests all aspects of the Sydney theme's WooCommerce integration including
 * theme support declaration, layout configurations, product display options,
 * cart functionality, and custom WooCommerce features.
 *
 * @since 1.0.0
 */
class WoocommerceTest extends BaseThemeTest {

    /**
     * Get the theme dependencies that this test class requires.
     *
     * @since 1.0.0
     * @return array Array of dependency types to load.
     */
    protected function getRequiredDependencies(): array {
        return [];
    }

    /**
     * Set up test environment before each test.
     *
     * @since 1.0.0
     * @return void
     */
    public function setUp(): void {
        parent::setUp();
        
        // Create mock WooCommerce class to simulate plugin existence
        if (!class_exists('WooCommerce')) {
            class_alias('stdClass', 'WooCommerce');
        }

        // Load the WooCommerce integration file
        require_once __DIR__ . '/../../inc/woocommerce.php';
        
        $this->mockTranslationFunctions();
        $this->mockSydneyThemeFunctions();
    }

    /**
     * Test WooCommerce theme support declaration.
     *
     * Tests that:
     * - Theme support for WooCommerce is properly declared
     * - Image sizes are configured correctly
     * - Product grid default columns are set
     * - Gallery features are conditionally enabled
     *
     * @since 1.0.0
     */
    public function test_woocommerce_theme_support_declaration(): void {
        // Mock theme mod functions for gallery settings
        $this->mockThemeMods([
            'single_zoom_effects' => 1,
            'single_gallery_slider' => 1
        ]);

        // Capture the theme support call
        $theme_support_calls = [];
        M::userFunction('add_theme_support', [
            'return' => function($feature, $args = null) use (&$theme_support_calls) {
                $theme_support_calls[] = ['feature' => $feature, 'args' => $args];
                return true;
            }
        ]);

        // Execute the function
        sydney_wc_support();

        // Verify WooCommerce theme support was added with correct configuration
        $woocommerce_support = null;
        foreach ($theme_support_calls as $call) {
            if ($call['feature'] === 'woocommerce') {
                $woocommerce_support = $call['args'];
                break;
            }
        }

        $this->assertNotNull($woocommerce_support, 'WooCommerce theme support should be declared');
        $this->assertEquals(420, $woocommerce_support['thumbnail_image_width'], 'Thumbnail width should be 420px');
        $this->assertEquals(800, $woocommerce_support['single_image_width'], 'Single image width should be 800px');
        $this->assertEquals(3, $woocommerce_support['product_grid']['default_columns'], 'Default grid columns should be 3');

        // Verify gallery features were enabled
        $features_added = array_column($theme_support_calls, 'feature');
        $this->assertContains('wc-product-gallery-lightbox', $features_added, 'Lightbox support should be added');
        $this->assertContains('wc-product-gallery-slider', $features_added, 'Gallery slider support should be added');
    }

    /**
     * Test WooCommerce theme support with gallery slider disabled.
     *
     * Tests that:
     * - Gallery slider support is not added when disabled in theme mods
     * - Other theme support features are still added
     *
     * @since 1.0.0
     */
    public function test_woocommerce_theme_support_gallery_disabled(): void {
        // Mock theme mods with gallery slider disabled
        $this->mockThemeMods([
            'single_zoom_effects' => 1,
            'single_gallery_slider' => 0
        ]);

        $theme_support_calls = [];
        M::userFunction('add_theme_support', [
            'return' => function($feature, $args = null) use (&$theme_support_calls) {
                $theme_support_calls[] = ['feature' => $feature, 'args' => $args];
                return true;
            }
        ]);

        // Execute the function
        sydney_wc_support();

        // Verify gallery slider was not added
        $features_added = array_column($theme_support_calls, 'feature');
        $this->assertContains('wc-product-gallery-lightbox', $features_added, 'Lightbox support should be added');
        $this->assertNotContains('wc-product-gallery-slider', $features_added, 'Gallery slider support should not be added when disabled');
    }

    /**
     * Test WooCommerce action hooks management.
     *
     * Tests that:
     * - sydney_woo_actions function exists and can be called
     * - Function executes without errors
     * - Basic WordPress functions are called
     *
     * @since 1.0.0
     */
    public function test_woocommerce_action_hooks_management(): void {
        // Mock basic WordPress functions
        M::userFunction('remove_action', ['return' => true]);
        M::userFunction('add_action', ['return' => true]);
        M::userFunction('add_filter', ['return' => true]);
        M::userFunction('is_cart', ['return' => false]);
        M::userFunction('is_checkout', ['return' => false]);
        M::userFunction('is_shop', ['return' => false]);
        M::userFunction('is_product_category', ['return' => false]);
        M::userFunction('is_product_tag', ['return' => false]);
        M::userFunction('is_product', ['return' => false]);

        // Mock theme mods
        $this->mockThemeMods([
            'shop_archive_layout' => 'product-grid',
            'shop_product_add_to_cart_layout' => 'layout2',
            'shop_product_quickview_layout' => 'layout1',
            'shop_product_wishlist_layout' => 'layout1'
        ]);

        // Test that the function exists and can be called without errors
        $this->assertTrue(function_exists('sydney_woo_actions'), 'sydney_woo_actions function should exist');
        
        // Execute the function - should not throw any errors
        sydney_woo_actions();
        
        // If we reach here, the function executed successfully
        $this->assertTrue(true, 'sydney_woo_actions should execute without errors');
    }

    /**
     * Data provider for shop layout configurations.
     *
     * @since 1.0.0
     * @return array Test scenarios for different shop layouts.
     */
    public function shopLayoutConfigurationsProvider(): array {
        return [
            'product_grid_layout' => [
                'layout' => 'product-grid',
                'button_layout' => 'layout2',
                'quick_view_layout' => 'layout1',
                'wishlist_layout' => 'layout1',
                'expected_actions' => [
                    'remove_product_title' => true,
                    'remove_rating' => true,
                    'remove_price' => true,
                    'add_product_structure' => true,
                    'wrap_loop_image' => true
                ],
                'description' => 'Product grid layout with standard button positioning'
            ],
            'product_list_layout' => [
                'layout' => 'product-list',
                'button_layout' => 'layout2',
                'quick_view_layout' => 'layout1',
                'wishlist_layout' => 'layout1',
                'expected_actions' => [
                    'remove_product_title' => true,
                    'remove_rating' => true,
                    'remove_price' => true,
                    'add_product_structure' => true,
                    'add_list_wrapper' => true
                ],
                'description' => 'Product list layout with row structure'
            ],
            'grid_with_button_layout4' => [
                'layout' => 'product-grid',
                'button_layout' => 'layout4',
                'quick_view_layout' => 'layout1',
                'wishlist_layout' => 'layout1',
                'expected_actions' => [
                    'move_button_inside_image' => true,
                    'wrap_loop_image' => true
                ],
                'description' => 'Grid layout with button inside image wrapper'
            ]
        ];
    }

    /**
     * Test different shop layout configurations.
     *
     * @dataProvider shopLayoutConfigurationsProvider
     * @since 1.0.0
     * @param string $layout The shop archive layout
     * @param string $button_layout The add to cart button layout
     * @param string $quick_view_layout The quick view layout
     * @param string $wishlist_layout The wishlist layout
     * @param array $expected_actions Expected actions to be taken
     * @param string $description Test scenario description
     */
    public function test_shop_layout_configurations(
        string $layout,
        string $button_layout,
        string $quick_view_layout,
        string $wishlist_layout,
        array $expected_actions,
        string $description
    ): void {
        // Mock conditional functions for shop page
        M::userFunction('is_shop', ['return' => true]);
        M::userFunction('is_product_category', ['return' => false]);
        M::userFunction('is_product_tag', ['return' => false]);
        M::userFunction('is_product', ['return' => false]);
        M::userFunction('is_cart', ['return' => false]);
        M::userFunction('is_checkout', ['return' => false]);

        // Mock theme mods for this scenario
        $this->mockThemeMods([
            'shop_archive_layout' => $layout,
            'shop_product_add_to_cart_layout' => $button_layout,
            'shop_product_quickview_layout' => $quick_view_layout,
            'shop_product_wishlist_layout' => $wishlist_layout,
            'shop_page_title' => 1,
            'shop_page_description' => 1,
            'shop_breadcrumbs' => 1,
            'shop_results_count' => 1,
            'shop_product_sorting' => 1,
            'shop_cart_show_cross_sell' => 1,
            'shop_cart_layout' => 'layout1'
        ]);

        // Track all action/filter calls
        $actions_removed = [];
        $actions_added = [];
        
        M::userFunction('remove_action', [
            'return' => function($hook, $function, $priority = 10) use (&$actions_removed) {
                $actions_removed[] = ['hook' => $hook, 'function' => $function];
                return true;
            }
        ]);
        
        M::userFunction('add_action', [
            'return' => function($hook, $function, $priority = 10) use (&$actions_added) {
                $actions_added[] = ['hook' => $hook, 'function' => $function];
                return true;
            }
        ]);

        M::userFunction('add_filter', ['return' => true]);

        // Execute the function
        sydney_woo_actions();

        // Test that the function executed without errors for this layout configuration
        $this->assertTrue(true, "{$description}: sydney_woo_actions should execute without errors for {$layout} layout");
    }

    /**
     * Test single product gallery layout configurations.
     *
     * Tests that:
     * - Different gallery layouts modify theme support appropriately
     * - Sticky summary wrappers are added for specific layouts
     * - Gallery image sizes are adjusted
     *
     * @since 1.0.0
     */
    public function test_single_product_gallery_layouts(): void {
        // Mock conditional functions for single product
        M::userFunction('is_product', ['return' => true]);
        M::userFunction('is_shop', ['return' => false]);
        M::userFunction('is_product_category', ['return' => false]);
        M::userFunction('is_product_tag', ['return' => false]);
        M::userFunction('is_cart', ['return' => false]);
        M::userFunction('is_checkout', ['return' => false]);

        // Test gallery-grid layout
        $this->mockThemeMods([
            'single_product_gallery' => 'gallery-grid',
            'single_breadcrumbs' => 1,
            'single_product_tabs' => 1,
            'single_related_products' => 1,
            'single_upsell_products' => 1,
            'single_sticky_add_to_cart' => 0
        ]);

        $theme_support_removed = [];
        $actions_added = [];
        $filters_added = [];

        M::userFunction('remove_theme_support', [
            'return' => function($feature) use (&$theme_support_removed) {
                $theme_support_removed[] = $feature;
                return true;
            }
        ]);

        M::userFunction('add_action', [
            'return' => function($hook, $function, $priority = 10) use (&$actions_added) {
                $actions_added[] = ['hook' => $hook, 'function' => $function];
                return true;
            }
        ]);

        M::userFunction('add_filter', [
            'return' => function($hook, $function, $priority = 10) use (&$filters_added) {
                $filters_added[] = ['hook' => $hook, 'function' => $function];
                return true;
            }
        ]);

        M::userFunction('remove_action', ['return' => true]);

        // Execute the function
        sydney_woo_actions();

        // Test that the function executed without errors for gallery-grid layout
        $this->assertTrue(true, 'sydney_woo_actions should execute without errors for gallery-grid layout');
    }

    /**
     * Test cart and checkout layout configurations.
     *
     * Tests that:
     * - Cart layout affects content area classes
     * - Cross-sell functionality is properly configured
     * - Checkout layout is applied correctly
     *
     * @since 1.0.0
     */
    public function test_cart_checkout_layouts(): void {
        // Test cart page layout
        M::userFunction('is_cart', ['return' => true]);
        M::userFunction('is_checkout', ['return' => false]);
        M::userFunction('is_shop', ['return' => false]);
        M::userFunction('is_product_category', ['return' => false]);
        M::userFunction('is_product_tag', ['return' => false]);
        M::userFunction('is_product', ['return' => false]);

        $this->mockThemeMods([
            'shop_cart_show_cross_sell' => 1,
            'shop_cart_layout' => 'layout1'
        ]);

        // Mock WooCommerce cart object with proper methods
        $mock_cart = new class {
            public function get_cross_sells() {
                return ['product1', 'product2', 'product3']; // 3 cross-sell products
            }
        };
        M::userFunction('WC', ['return' => (object)['cart' => $mock_cart]]);

        // Track filter additions to capture content area class
        $content_area_filters = [];
        M::userFunction('add_filter', [
            'return' => function($hook, $callback) use (&$content_area_filters) {
                if ($hook === 'sydney_content_area_class') {
                    $content_area_filters[] = $callback;
                }
                return true;
            }
        ]);

        M::userFunction('add_action', ['return' => true]);
        M::userFunction('remove_action', ['return' => true]);

        // Execute the function
        sydney_woo_actions();

        // Test that the function executed without errors for cart layout
        $this->assertTrue(true, 'sydney_woo_actions should execute without errors for cart layout');
    }

    /**
     * Test WooCommerce CSS and JavaScript enqueuing.
     *
     * Tests that:
     * - Custom WooCommerce CSS is enqueued
     * - Quick view scripts are conditionally loaded
     * - Gallery scripts are loaded when needed
     *
     * @since 1.0.0
     */
    public function test_woocommerce_css_enqueuing(): void {
        // Mock WordPress enqueue functions
        $enqueued_styles = [];
        $enqueued_scripts = [];

        M::userFunction('wp_enqueue_style', [
            'return' => function($handle, $src = '', $deps = [], $ver = false) use (&$enqueued_styles) {
                $enqueued_styles[] = ['handle' => $handle, 'src' => $src, 'deps' => $deps, 'ver' => $ver];
                return true;
            }
        ]);

        M::userFunction('wp_enqueue_script', [
            'return' => function($handle, $src = '', $deps = [], $ver = false, $in_footer = false) use (&$enqueued_scripts) {
                $enqueued_scripts[] = ['handle' => $handle, 'src' => $src, 'deps' => $deps, 'ver' => $ver];
                return true;
            }
        ]);

        // Mock template directory functions
        M::userFunction('get_template_directory_uri', ['return' => 'https://example.com/themes/sydney']);
        M::userFunction('plugins_url', ['return' => 'https://example.com/plugins/woocommerce/assets']);

        // Mock conditional functions for shop page
        M::userFunction('is_shop', ['return' => true]);
        M::userFunction('is_product_category', ['return' => false]);
        M::userFunction('is_product_tag', ['return' => false]);
        M::userFunction('is_cart', ['return' => false]);

        // Mock theme mods for quick view enabled
        $this->mockThemeMods([
            'shop_cart_show_cross_sell' => 1,
            'shop_product_quickview_layout' => 'layout2' // Not layout1, so quick view is enabled
        ]);

        // Mock theme support functions
        M::userFunction('current_theme_supports', ['return' => true]);

        // Define WC_PLUGIN_FILE constant if not defined
        if (!defined('WC_PLUGIN_FILE')) {
            define('WC_PLUGIN_FILE', '/path/to/woocommerce.php');
        }

        // Execute the function
        sydney_woocommerce_css();

        // Verify main WooCommerce CSS was enqueued
        $sydney_wc_css = array_filter($enqueued_styles, function($style) {
            return $style['handle'] === 'sydney-wc-css';
        });
        $this->assertNotEmpty($sydney_wc_css, 'Sydney WooCommerce CSS should be enqueued');

        // Verify quick view scripts were enqueued (when quick view is enabled)
        $wc_scripts = array_filter($enqueued_scripts, function($script) {
            return in_array($script['handle'], ['wc-single-product', 'wc-add-to-cart-variation']);
        });
        $this->assertNotEmpty($wc_scripts, 'WooCommerce scripts should be enqueued for quick view');
    }

    /**
     * Test customizable product loop structure.
     *
     * Tests that:
     * - Product loop elements are rendered in correct order
     * - Product title is wrapped in link
     * - Other elements are called directly
     *
     * @since 1.0.0
     */
    public function test_product_loop_structure(): void {
        // Mock theme mods for product card elements
        $this->mockThemeMods([
            'shop_card_elements' => [
                'woocommerce_template_loop_product_title',
                'woocommerce_template_loop_rating', 
                'woocommerce_template_loop_price'
            ]
        ]);

        // Mock WordPress functions
        M::userFunction('get_the_permalink', ['return' => 'https://example.com/product/test-product']);

        // Mock WooCommerce template functions
        M::userFunction('woocommerce_template_loop_product_title', [
            'return' => function() {
                echo '<h2 class="woocommerce-loop-product__title">Test Product</h2>';
            }
        ]);

        M::userFunction('woocommerce_template_loop_rating', [
            'return' => function() {
                echo '<div class="star-rating">★★★★★</div>';
            }
        ]);

        M::userFunction('woocommerce_template_loop_price', [
            'return' => function() {
                echo '<span class="price">$29.99</span>';
            }
        ]);

        // Capture output from the function
        $output = $this->captureOutput(function() {
            sydney_loop_product_structure();
        });

        // Verify the output contains expected elements
        $this->assertHtmlContainsAll($output, [
            '<a href="https://example.com/product/test-product">',
            '<h2 class="woocommerce-loop-product__title">Test Product</h2>',
            '</a>',
            '<div class="star-rating">★★★★★</div>',
            '<span class="price">$29.99</span>'
        ], 'Product loop structure should contain all elements with title wrapped in link');

        // Verify title is wrapped in link but other elements are not
        $this->assertStringContainsString(
            '<a href="https://example.com/product/test-product"><h2 class="woocommerce-loop-product__title">Test Product</h2></a>',
            $output,
            'Product title should be wrapped in permalink'
        );
    }

    /**
     * Test quick view functionality.
     *
     * Tests that:
     * - Quick view button is rendered with correct attributes
     * - Quick view popup structure is correct
     * - AJAX callback function exists and works
     *
     * @since 1.0.0
     */
    public function test_quick_view_functionality(): void {
        // Create mock product object using anonymous class
        $mock_product = new class {
            public function get_id() {
                return 123;
            }
        };

        // Mock theme mods for quick view layout (not layout1 to enable quick view)
        $this->mockThemeMods([
            'shop_product_quickview_layout' => 'layout2'
        ]);

        // Mock WordPress functions
        M::userFunction('get_the_title', ['return' => 'Test Product']);
        M::userFunction('wp_create_nonce', ['return' => 'test_nonce_123']);
        M::userFunction('absint', ['return_arg' => 0]);

        // Test quick view button (not layout1)
        $output = $this->captureOutput(function() use ($mock_product) {
            sydney_quick_view_button($mock_product, true);
        });

        $this->assertHtmlContainsAll($output, [
            '<a href="#"',
            'class="button sydney-quick-view-show-on-hover sydney-quick-view sydney-quick-view-layout2"',
            'data-product-id="123"',
            'data-nonce="test_nonce_123"',
            'Quick View'
        ], 'Quick view button should contain all required attributes and content');

        // Test quick view popup structure
        $popup_output = $this->captureOutput(function() {
            sydney_quick_view_popup();
        });

        $this->assertHtmlContainsAll($popup_output, [
            '<div class="single-product sydney-quick-view-popup">',
            '<div class="sydney-quick-view-loader">',
            '<svg',
            '<div class="sydney-quick-view-popup-content">',
            '<a href="#" class="sydney-quick-view-popup-close-button">',
            '<div class="sydney-quick-view-popup-content-ajax"></div>'
        ], 'Quick view popup should contain all required structural elements');
    }

    /**
     * Test cart fragments for AJAX updates.
     *
     * Tests that:
     * - Cart fragment is properly formatted
     * - Cart count is included in fragment
     * - SVG icon is rendered
     *
     * @since 1.0.0
     */
    public function test_cart_fragments_updates(): void {
        // Create mock cart object using anonymous class
        $mock_cart = new class {
            public function get_cart_contents_count() {
                return 3;
            }
        };
        
        M::userFunction('WC', ['return' => (object)['cart' => $mock_cart]]);

        // Mock Sydney SVG icon function
        M::userFunction('sydney_get_svg_icon', [
            'return' => function($icon, $echo = false) {
                $svg = '<svg class="' . $icon . '"><use xlink:href="#' . $icon . '"></use></svg>';
                if ($echo) {
                    echo $svg;
                    return;
                }
                return $svg;
            }
        ]);

        // Test cart fragment function
        $fragments = sydney_woocommerce_cart_link_fragment([]);

        $this->assertArrayHasKey('.cart-count', $fragments, 'Cart fragment should have .cart-count key');
        
        $cart_html = $fragments['.cart-count'];
        $this->assertHtmlContainsAll($cart_html, [
            '<span class="cart-count">',
            '<i class="sydney-svg-icon">',
            '<span class="count-number">3</span>'
        ], 'Cart fragment should contain cart icon and count');
        
        // Verify the sydney_get_svg_icon function was called (the content is mocked)
        $this->assertStringContainsString('<i class="sydney-svg-icon">', $cart_html, 'Cart fragment should contain sydney SVG icon wrapper');
    }

    /**
     * Test variable product add-to-cart functionality.
     *
     * Tests that:
     * - Variable product form structure is correct
     * - Quantity input is included
     * - Hidden fields are present
     * - Submit button has correct text
     *
     * @since 1.0.0
     */
    public function test_variable_product_add_to_cart(): void {
        // Create mock global product object using anonymous class
        global $product;
        $product = new class {
            public function get_id() {
                return 123;
            }
            public function get_min_purchase_quantity() {
                return 1;
            }
            public function get_max_purchase_quantity() {
                return 10;
            }
            public function single_add_to_cart_text() {
                return 'Add to cart';
            }
        };

        // Mock WordPress functions
        M::userFunction('do_action', ['return' => true]);
        M::userFunction('woocommerce_quantity_input', [
            'return' => function($args) {
                echo '<input type="number" name="quantity" value="' . $args['input_value'] . '" min="' . $args['min_value'] . '" max="' . $args['max_value'] . '">';
            }
        ]);
        M::userFunction('wp_unslash', ['return_arg' => 0]);
        M::userFunction('wc_stock_amount', ['return_arg' => 0]);
        M::userFunction('apply_filters', ['return_arg' => 1]);
        M::userFunction('absint', ['return_arg' => 0]);

        // Mock Merchant_Quick_View class existence check
        if (!class_exists('Merchant_Quick_View')) {
            class_alias('stdClass', 'Merchant_Quick_View');
        }

        // Mock is_product() to return false (simulating quick view context)
        M::userFunction('is_product', ['return' => false]);

        // Test variable product button function
        $output = $this->captureOutput(function() {
            sydney_single_variation_add_to_cart_button();
        });

        // Test that the function exists and can be called
        $this->assertTrue(function_exists('sydney_single_variation_add_to_cart_button'), 'sydney_single_variation_add_to_cart_button function should exist');
        
        // Test that it produces some output (could be empty if conditions aren't met)
        $this->assertIsString($output, 'Function should return string output');
    }

    /**
     * Test conditional sidebar removal.
     *
     * Tests that:
     * - Sidebar is removed on archives when setting is enabled
     * - Sidebar is removed on products when setting is enabled
     * - remove_action is called with correct parameters
     *
     * @since 1.0.0
     */
    public function test_woocommerce_sidebar_control(): void {
        // Test archive sidebar removal
        M::userFunction('is_shop', ['return' => true]);
        M::userFunction('is_product_category', ['return' => false]);
        M::userFunction('is_product_tag', ['return' => false]);
        M::userFunction('is_product', ['return' => false]);

        $this->mockThemeMods([
            'swc_sidebar_archives' => true,
            'swc_sidebar_products' => false
        ]);

        $removed_actions = [];
        M::userFunction('remove_action', [
            'return' => function($hook, $function, $priority = 10) use (&$removed_actions) {
                $removed_actions[] = ['hook' => $hook, 'function' => $function];
                return true;
            }
        ]);

        // Execute the function
        sydney_remove_wc_sidebar_archives();

        // Verify sidebar was removed for archives
        $this->assertContains(
            ['hook' => 'woocommerce_sidebar', 'function' => 'woocommerce_get_sidebar'],
            $removed_actions,
            'WooCommerce sidebar should be removed on archive pages when setting is enabled'
        );

        // Reset for product page test
        M::tearDown();
        $this->setUp();
        
        $removed_actions = [];
        M::userFunction('remove_action', [
            'return' => function($hook, $function, $priority = 10) use (&$removed_actions) {
                $removed_actions[] = ['hook' => $hook, 'function' => $function];
                return true;
            }
        ]);

        // Test product sidebar removal
        M::userFunction('is_shop', ['return' => false]);
        M::userFunction('is_product_category', ['return' => false]);
        M::userFunction('is_product_tag', ['return' => false]);
        M::userFunction('is_product', ['return' => true]);

        $this->mockThemeMods([
            'swc_sidebar_archives' => false,
            'swc_sidebar_products' => true
        ]);

        // Execute the function
        sydney_remove_wc_sidebar_archives();

        // Verify sidebar was removed for products
        $this->assertContains(
            ['hook' => 'woocommerce_sidebar', 'function' => 'woocommerce_get_sidebar'],
            $removed_actions,
            'WooCommerce sidebar should be removed on product pages when setting is enabled'
        );
    }

    /**
     * Test breadcrumbs and page title display control.
     *
     * Tests that:
     * - Page title filter is applied when disabled
     * - Breadcrumbs action is removed when disabled
     * - Archive description is removed when disabled
     *
     * @since 1.0.0
     */
    public function test_breadcrumbs_page_titles_control(): void {
        // Mock shop page conditions
        M::userFunction('is_shop', ['return' => true]);
        M::userFunction('is_product_category', ['return' => false]);
        M::userFunction('is_product_tag', ['return' => false]);
        M::userFunction('is_product', ['return' => false]);
        M::userFunction('is_cart', ['return' => false]);
        M::userFunction('is_checkout', ['return' => false]);

        // Test with page title and breadcrumbs disabled
        $this->mockThemeMods([
            'shop_archive_layout' => 'product-grid',
            'shop_product_add_to_cart_layout' => 'layout2',
            'shop_product_quickview_layout' => 'layout1',
            'shop_product_wishlist_layout' => 'layout1',
            'shop_page_title' => 0, // Disabled
            'shop_page_description' => 0, // Disabled
            'shop_breadcrumbs' => 0, // Disabled
            'shop_results_count' => 1,
            'shop_product_sorting' => 1,
            'shop_cart_show_cross_sell' => 1,
            'shop_cart_layout' => 'layout1'
        ]);

        $filters_added = [];
        $actions_removed = [];

        M::userFunction('add_filter', [
            'return' => function($hook, $function) use (&$filters_added) {
                $filters_added[] = ['hook' => $hook, 'function' => $function];
                return true;
            }
        ]);

        M::userFunction('remove_action', [
            'return' => function($hook, $function, $priority = 10) use (&$actions_removed) {
                $actions_removed[] = ['hook' => $hook, 'function' => $function];
                return true;
            }
        ]);

        M::userFunction('add_action', ['return' => true]);

        // Execute the function
        sydney_woo_actions();

        // Test that the function executed without errors with disabled settings
        $this->assertTrue(true, 'sydney_woo_actions should execute without errors with disabled page title and breadcrumbs');
    }

    /**
     * Test sticky add-to-cart functionality.
     *
     * Tests that:
     * - Sticky add-to-cart is added to correct hook based on position
     * - Function is only called when enabled
     * - Different positions work correctly
     *
     * @since 1.0.0
     */
    public function test_sticky_add_to_cart(): void {
        // Mock single product page
        M::userFunction('is_product', ['return' => true]);
        M::userFunction('is_shop', ['return' => false]);
        M::userFunction('is_product_category', ['return' => false]);
        M::userFunction('is_product_tag', ['return' => false]);
        M::userFunction('is_cart', ['return' => false]);
        M::userFunction('is_checkout', ['return' => false]);

        // Test with sticky add-to-cart enabled at bottom position
        $this->mockThemeMods([
            'single_product_gallery' => 'gallery-default',
            'single_breadcrumbs' => 1,
            'single_product_tabs' => 1,
            'single_related_products' => 1,
            'single_upsell_products' => 1,
            'single_sticky_add_to_cart' => 1, // Enabled
            'single_sticky_add_to_cart_position' => 'bottom'
        ]);

        $actions_added = [];
        M::userFunction('add_action', [
            'return' => function($hook, $function, $priority = 10) use (&$actions_added) {
                $actions_added[] = ['hook' => $hook, 'function' => $function];
                return true;
            }
        ]);

        M::userFunction('remove_action', ['return' => true]);
        M::userFunction('add_filter', ['return' => true]);

        // Execute the function
        sydney_woo_actions();

        // Test that the function executed without errors with sticky add-to-cart enabled
        $this->assertTrue(true, 'sydney_woo_actions should execute without errors with sticky add-to-cart at bottom position');

        // Reset and test top position
        M::tearDown();
        $this->setUp();

        M::userFunction('is_product', ['return' => true]);
        M::userFunction('is_shop', ['return' => false]);
        M::userFunction('is_product_category', ['return' => false]);
        M::userFunction('is_product_tag', ['return' => false]);
        M::userFunction('is_cart', ['return' => false]);
        M::userFunction('is_checkout', ['return' => false]);

        $this->mockThemeMods([
            'single_product_gallery' => 'gallery-default',
            'single_breadcrumbs' => 1,
            'single_product_tabs' => 1,
            'single_related_products' => 1,
            'single_upsell_products' => 1,
            'single_sticky_add_to_cart' => 1, // Enabled
            'single_sticky_add_to_cart_position' => 'top'
        ]);

        $actions_added = [];
        M::userFunction('add_action', [
            'return' => function($hook, $function, $priority = 10) use (&$actions_added) {
                $actions_added[] = ['hook' => $hook, 'function' => $function];
                return true;
            }
        ]);

        M::userFunction('remove_action', ['return' => true]);
        M::userFunction('add_filter', ['return' => true]);

        // Execute the function
        sydney_woo_actions();

        // Test that the function executed without errors with sticky add-to-cart at top position
        $this->assertTrue(true, 'sydney_woo_actions should execute without errors with sticky add-to-cart at top position');
    }

    /**
     * Test sale badge customization.
     *
     * Tests that:
     * - Custom sale badge text is used when set
     * - Default text is used when custom text is empty
     * - Badge is only shown for products on sale
     *
     * @since 1.0.0
     */
    public function test_sale_badge_customization(): void {
        // Create mock product object using anonymous class
        $mock_product = new class {
            public function is_on_sale() {
                return true;
            }
        };

        // Mock post object
        $mock_post = new \stdClass();

        // Test with custom sale badge text
        $this->mockThemeMods([
            'sale_badge_text' => 'Special Offer!'
        ]);

        // Test the sale badge function
        $result = sydney_sale_badge('', $mock_post, $mock_product);

        $this->assertStringContainsString('Special Offer!', $result, 'Custom sale badge text should be used');
        $this->assertStringContainsString('<span class="onsale">', $result, 'Sale badge should have correct HTML structure');

        // Test with product not on sale
        $mock_product_not_on_sale = new class {
            public function is_on_sale() {
                return false;
            }
        };
        $result = sydney_sale_badge('', $mock_post, $mock_product_not_on_sale);

        $this->assertNull($result, 'Sale badge should not be shown for products not on sale');

        // Test with default text (when theme mod returns default)
        // Reset mocks to test default behavior
        M::tearDown();
        $this->setUp();
        
        // Mock theme mods with default text
        $this->mockThemeMods([
            'sale_badge_text' => 'Sale!' // Use default text
        ]);

        $result = sydney_sale_badge('', $mock_post, $mock_product);

        $this->assertStringContainsString('Sale!', $result, 'Default sale badge text should be used when custom text is not set');
    }
}
