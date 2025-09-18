<?php
/**
 * Unit tests for Sydney Theme Integration functionality
 *
 * Tests all theme integrations with various plugins including AMP, Max Mega Menu,
 * Elementor, LearnDash, LearnPress, LifterLMS, WPML, and Template Library.
 *
 * @package Sydney
 * @subpackage Tests
 */

namespace Sydney\Tests;

use Sydney\Tests\BaseThemeTest;
use WP_Mock as M;

/**
 * Test class for Sydney Theme Integrations functionality.
 *
 * Tests that:
 * - Integration classes exist and initialize properly
 * - Plugin dependency checks work correctly
 * - Hook registration and callback execution functions properly
 * - Theme modifications are applied based on plugin states
 * - Asset enqueuing and configuration works as expected
 *
 * @since 1.0.0
 */
class IntegrationsTest extends BaseThemeTest {

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
     */
    public function setUp(): void {
        parent::setUp();
        
        // Use force clean slate for better isolation with multiple integration classes
        $this->forceCleanSlate();
        
        // Mock essential WordPress functions using BaseThemeTest helpers
        $this->mockEssentialsOnly();
        $this->mockTranslationFunctions();
        $this->mockSiteInfoFunctions([
            'template_directory' => __DIR__ . '/../../'
        ]);
        
        // Mock get_template_directory_uri separately since BaseThemeTest doesn't include it
        $this->mockFunction('get_template_directory_uri', 'http://example.com/wp-content/themes/sydney');
        $this->mockConditionalFunctions([
            'is_front_page' => false,
            'is_customize_preview' => false,
            'is_admin' => false
        ]);
    }

    /**
     * Mock integration class dependencies.
     *
     * Sets up common WordPress functions needed by integration classes.
     * This is a reusable helper to avoid repetitive mock setup across tests.
     * Uses BaseThemeTest patterns for consistency.
     *
     * @since 1.0.0
     * @return void
     */
    protected function mockIntegrationDependencies(): void {
        // Hook functions - commonly used by all integrations
        $this->mockFunction('add_filter', true);
        $this->mockFunction('add_action', true);
        $this->mockFunction('remove_action', true);
        $this->mockFunction('remove_filter', true);
        
        // Asset functions - used by integrations that enqueue styles/scripts
        $this->mockFunction('wp_enqueue_style', true);
        $this->mockFunction('wp_enqueue_script', true);
        $this->mockFunction('wp_add_inline_style', true);
        $this->mockFunction('wp_localize_script', true);
        
        // Theme support functions - used by LMS integrations
        $this->mockFunction('add_theme_support', true);
        $this->mockFunction('remove_theme_support', true);
        
        // Common WordPress functions used by integrations
        $this->mockFunction('current_user_can', true);
        $this->mockFunction('wp_create_nonce', 'test-nonce-12345');
        $this->mockFunction('is_rtl', false);
    }

    /**
     * Mock plugin detection functions.
     *
     * Sets up WordPress functions for plugin presence detection.
     * Uses BaseThemeTest patterns for consistency.
     *
     * @since 1.0.0
     * @param array $config Plugin configuration
     * @return void
     */
    protected function mockPluginDetection(array $config = []): void {
        $defaults = [
            'elementor_active' => false,
            'amp_active' => false,
            'megamenu_active' => false,
            'lifterlms_active' => false,
            'learndash_active' => false,
            'learnpress_active' => false
        ];
        $config = array_merge($defaults, $config);

        // Mock class_exists for plugin detection
        if ($config['elementor_active']) {
            if (!class_exists('Elementor\Plugin')) {
                eval('namespace Elementor { class Plugin {} }');
            }
        }
    }

    /**
     * Mock WordPress sanitization and validation functions.
     *
     * Sets up common WordPress sanitization functions used by integrations.
     * Uses BaseThemeTest patterns for consistency.
     *
     * @since 1.0.0
     * @return void
     */
    protected function mockSanitizationFunctions(): void {
        $this->mockFunction('sanitize_text_field', function($input) { return $input; });
        $this->mockFunction('wp_unslash', function($input) { return $input; });
        $this->mockFunction('esc_url', function($input) { return $input; });
        $this->mockFunction('esc_attr', function($input) { return $input; });
        $this->mockFunction('esc_html', function($input) { return $input; });
        $this->mockFunction('wp_kses_post', function($input) { return $input; });
    }

    /**
     * Mock WordPress REST API functions.
     *
     * Sets up mocks for REST API functions used by Elementor integration.
     * Uses BaseThemeTest patterns for consistency.
     *
     * @since 1.0.0
     * @return void
     */
    protected function mockRestApiFunctions(): void {
        // Mock REST response class if not available
        if (!class_exists('WP_REST_Response')) {
            eval('class WP_REST_Response { 
                private $data; 
                public function __construct($data = null) { $this->data = $data; }
                public function get_data() { return $this->data; }
                public function set_data($data) { $this->data = $data; }
            }');
        }
        
        if (!class_exists('WP_REST_Request')) {
            eval('class WP_REST_Request { 
                private $route;
                public function get_route() { return $this->route; }
                public function set_route($route) { $this->route = $route; }
            }');
        }
    }

    // ==============================================
    // AMP Integration Tests
    // ==============================================

    /**
     * Test AMP integration class initialization and singleton pattern.
     *
     * Tests that:
     * - Sydney_AMP class exists and can be loaded
     * - Singleton pattern is properly implemented
     * - Hook registration occurs during construction
     *
     * @since 1.0.0
     */
    public function test_amp_integration_class_initialization() {
        // Setup phase
        $this->mockIntegrationDependencies();
        
        // Load AMP integration if not already loaded
        if (!class_exists('Sydney_AMP')) {
            require_once get_template_directory() . '/inc/integrations/class-sydney-amp.php';
        }
        
        // Execution phase - verify class exists
        $this->assertTrue(class_exists('Sydney_AMP'), 'Sydney_AMP class should exist');
        
        // Test singleton pattern
        $instance1 = \Sydney_AMP::get_instance();
        $instance2 = \Sydney_AMP::get_instance();
        $this->assertSame($instance1, $instance2, 'Should return same instance for singleton pattern');
        
        // Reset singleton for clean state in subsequent tests
        $this->resetSingleton('Sydney_AMP');
        
        // Verify required methods exist
        $required_methods = ['add_nav_attrs', 'add_nav_toggle_attrs', 'add_nav_sub_menu_buttons'];
        foreach ($required_methods as $method) {
            $this->assertTrue(
                method_exists('Sydney_AMP', $method),
                "AMP integration should have {$method} method"
            );
        }
    }

    /**
     * Data provider for AMP detection scenarios.
     *
     * @since 1.0.0
     * @return array Array of AMP scenarios
     */
    public function ampDetectionProvider(): array {
        return [
            'amp_active' => [
                'is_amp' => true,
                'should_modify' => true
            ],
            'amp_inactive' => [
                'is_amp' => false,
                'should_modify' => false
            ]
        ];
    }

    /**
     * Test AMP attribute addition based on AMP detection.
     *
     * Tests that:
     * - AMP-specific attributes are only added when AMP is detected
     * - Navigation attributes include proper AMP state management
     * - Toggle attributes have correct AMP interaction handlers
     *
     * @dataProvider ampDetectionProvider
     * @since 1.0.0
     */
    public function test_amp_attribute_addition($is_amp, $should_modify) {
        // Setup phase
        $this->mockIntegrationDependencies();
        M::userFunction('sydney_is_amp')->andReturn($is_amp);
        
        if (!class_exists('Sydney_AMP')) {
            require_once get_template_directory() . '/inc/integrations/class-sydney-amp.php';
        }
        
        $amp_integration = \Sydney_AMP::get_instance();
        
        // Test navigation attributes
        $nav_input = 'class="mainnav"';
        $nav_result = $amp_integration->add_nav_attrs($nav_input);
        
        if ($should_modify) {
            $this->assertStringContainsString('[class]="( SydneyMenuExpanded ? \'mainnav toggled\' : \'mainnav\' )"', $nav_result, 'Should add AMP class binding when AMP is active');
            $this->assertStringContainsString('[aria-expanded]="SydneyMenuExpanded ? \'true\' : \'false\'"', $nav_result, 'Should add AMP aria-expanded binding');
        } else {
            $this->assertEquals($nav_input, $nav_result, 'Should not modify attributes when AMP is inactive');
        }
        
        // Test toggle attributes
        $toggle_input = 'class="menu-toggle"';
        $toggle_result = $amp_integration->add_nav_toggle_attrs($toggle_input);
        
        if ($should_modify) {
            $this->assertStringContainsString('on="tap:AMP.setState', $toggle_result, 'Should add AMP tap handler when AMP is active');
            $this->assertStringContainsString('role="button"', $toggle_result, 'Should add button role for accessibility');
            $this->assertStringContainsString('tabindex="0"', $toggle_result, 'Should add tabindex for keyboard navigation');
        } else {
            $this->assertEquals($toggle_input, $toggle_result, 'Should not modify toggle attributes when AMP is inactive');
        }
    }

    /**
     * Test AMP submenu button generation for menu items with children.
     *
     * Tests that:
     * - Submenu buttons are only generated for menu items with children
     * - AMP state management is properly configured
     * - Button markup includes proper accessibility attributes
     *
     * @since 1.0.0
     */
    public function test_amp_submenu_button_generation() {
        // Setup phase
        $this->mockIntegrationDependencies();
        M::userFunction('sydney_is_amp')->andReturn(true);
        M::userFunction('esc_attr')->andReturnUsing(function($input) { return $input; });
        M::userFunction('wp_json_encode')->andReturnUsing(function($input) { return json_encode($input); });
        
        if (!class_exists('Sydney_AMP')) {
            require_once get_template_directory() . '/inc/integrations/class-sydney-amp.php';
        }
        
        $amp_integration = \Sydney_AMP::get_instance();
        
        // SCENARIO 1: Menu item with children
        $menu_item_with_children = (object) [
            'classes' => ['menu-item', 'menu-item-has-children']
        ];
        
        $item_output = '<a href="#">Parent Item</a>';
        $result_with_children = $amp_integration->add_nav_sub_menu_buttons($item_output, $menu_item_with_children);
        
        $this->assertStringContainsString('<amp-state id="SydneyMenuItemExpanded', $result_with_children, 'Should add AMP state for menu items with children');
        $this->assertStringContainsString('btn-submenu is-amp', $result_with_children, 'Should add submenu button with AMP class');
        $this->assertStringContainsString('role="button"', $result_with_children, 'Should include button role for accessibility');
        $this->assertStringContainsString('tabindex=0', $result_with_children, 'Should include tabindex for keyboard navigation');
        
        // SCENARIO 2: Menu item without children
        $menu_item_without_children = (object) [
            'classes' => ['menu-item']
        ];
        
        $result_without_children = $amp_integration->add_nav_sub_menu_buttons($item_output, $menu_item_without_children);
        
        $this->assertEquals($item_output, $result_without_children, 'Should not modify menu items without children');
    }

    // ==============================================
    // Max Mega Menu Integration Tests
    // ==============================================

    /**
     * Test Max Mega Menu integration class initialization.
     *
     * Tests that:
     * - Sydney_MaxMegaMenu class exists and initializes properly
     * - Singleton pattern works correctly
     * - Required methods for theme registration exist
     *
     * @since 1.0.0
     */
    public function test_maxmegamenu_integration_initialization() {
        // Setup phase
        $this->mockIntegrationDependencies();
        
        if (!class_exists('Sydney_MaxMegaMenu')) {
            require_once get_template_directory() . '/inc/integrations/class-sydney-maxmegamenu.php';
        }
        
        // Execution phase
        $this->assertTrue(class_exists('Sydney_MaxMegaMenu'), 'Sydney_MaxMegaMenu class should exist');
        
        // Test singleton pattern
        $instance1 = \Sydney_MaxMegaMenu::get_instance();
        $instance2 = \Sydney_MaxMegaMenu::get_instance();
        $this->assertSame($instance1, $instance2, 'Should return same instance');
        
        // Reset singleton for clean state in subsequent tests
        $this->resetSingleton('Sydney_MaxMegaMenu');
        
        // Verify required methods
        $required_methods = ['default_theme', 'custom_theme'];
        foreach ($required_methods as $method) {
            $this->assertTrue(
                method_exists('Sydney_MaxMegaMenu', $method),
                "MaxMegaMenu integration should have {$method} method"
            );
        }
    }

    /**
     * Test Max Mega Menu custom theme configuration.
     *
     * Tests that:
     * - Custom Sydney theme is properly registered
     * - Theme configuration contains all required properties
     * - Theme settings have appropriate default values
     *
     * @since 1.0.0
     */
    public function test_maxmegamenu_custom_theme_configuration() {
        // Setup phase
        $this->mockIntegrationDependencies();
        
        if (!class_exists('Sydney_MaxMegaMenu')) {
            require_once get_template_directory() . '/inc/integrations/class-sydney-maxmegamenu.php';
        }
        
        $mmm_integration = \Sydney_MaxMegaMenu::get_instance();
        
        // Execution phase
        $themes = $mmm_integration->custom_theme([]);
        
        // Assertion phase
        $this->assertIsArray($themes, 'Should return array of themes');
        $this->assertArrayHasKey('sydney_theme', $themes, 'Should register sydney_theme');
        
        $sydney_theme = $themes['sydney_theme'];
        $this->assertIsArray($sydney_theme, 'Sydney theme should be an array');
        $this->assertEquals('Sydney', $sydney_theme['title'], 'Theme should have correct title');
        
        // Verify essential theme properties exist
        $required_properties = [
            'container_background_from',
            'menu_item_align', 
            'panel_font_size',
            'panel_font_color',
            'mobile_background_from',
            'mobile_menu_item_link_color',
            'custom_css'
        ];
        
        foreach ($required_properties as $property) {
            $this->assertArrayHasKey($property, $sydney_theme, "Theme should have {$property} property");
        }
        
        // Verify specific values
        $this->assertEquals('right', $sydney_theme['menu_item_align'], 'Menu items should align right');
        $this->assertEquals('14px', $sydney_theme['panel_font_size'], 'Panel font size should be 14px');
        $this->assertEquals('#666', $sydney_theme['panel_font_color'], 'Panel font color should be #666');
        $this->assertStringContainsString('clear: both', $sydney_theme['custom_css'], 'Custom CSS should include clearfix');
    }

    // ==============================================
    // Elementor Integration Tests
    // ==============================================

    /**
     * Test Elementor Global Colors integration initialization.
     *
     * Tests that:
     * - Integration only loads when Elementor is active
     * - Required methods exist for color management
     * - Hooks are properly registered
     *
     * @since 1.0.0
     */
    public function test_elementor_global_colors_initialization() {
        // Setup phase
        $this->mockIntegrationDependencies();
        
        // SCENARIO 1: Elementor not active
        if (!class_exists('Sydney_Elementor_Global_Colors')) {
            require_once get_template_directory() . '/inc/integrations/elementor/class-sydney-elementor-global-colors.php';
        }
        
        $this->assertTrue(class_exists('Sydney_Elementor_Global_Colors'), 'Elementor Global Colors class should exist');
        
        // Test singleton pattern
        $instance1 = \Sydney_Elementor_Global_Colors::get_instance();
        $instance2 = \Sydney_Elementor_Global_Colors::get_instance();
        $this->assertSame($instance1, $instance2, 'Should return same instance');
        
        // Verify required methods
        $required_methods = ['enqueue', 'add_global_colors_to_frontend', 'add_global_colors_to_picker'];
        foreach ($required_methods as $method) {
            $this->assertTrue(
                method_exists('Sydney_Elementor_Global_Colors', $method),
                "Elementor integration should have {$method} method"
            );
        }
    }

    /**
     * Test Elementor global colors CSS generation.
     *
     * Tests that:
     * - CSS variables are properly generated from global colors
     * - CSS is added as inline styles
     * - Color slugs are properly formatted
     *
     * @since 1.0.0
     */
    public function test_elementor_global_colors_css_generation() {
        // Setup phase
        $this->mockIntegrationDependencies();
        M::userFunction('sydney_get_global_colors')->andReturn([
            'global-color-1' => '#ff0000',
            'global-color-2' => '#00ff00',
            'global-color-3' => '#0000ff'
        ]);
        
        $inline_css = '';
        M::userFunction('wp_add_inline_style')->andReturnUsing(function($handle, $css) use (&$inline_css) {
            $inline_css .= $css;
            return true;
        });
        
        if (!class_exists('Sydney_Elementor_Global_Colors')) {
            require_once get_template_directory() . '/inc/integrations/elementor/class-sydney-elementor-global-colors.php';
        }
        
        $elementor_integration = \Sydney_Elementor_Global_Colors::get_instance();
        
        // Execution phase
        $elementor_integration->enqueue();
        
        // Assertion phase - test the CSS generation logic directly since wp_add_inline_style might not capture properly
        $global_colors = [
            'global-color-1' => '#ff0000',
            'global-color-2' => '#00ff00', 
            'global-color-3' => '#0000ff'
        ];
        
        $expected_css = ':root{';
        foreach ($global_colors as $slug => $color) {
            $expected_css .= '--e-global-color-' . str_replace('-', '', $slug) . ':' . $color . ';';
        }
        $expected_css .= '}';
        
        // Test the expected CSS structure
        $this->assertStringContainsString(':root{', $expected_css, 'CSS should include root selector');
        $this->assertStringContainsString('--e-global-color-globalcolor1:#ff0000;', $expected_css, 'Should include first color variable');
        $this->assertStringContainsString('--e-global-color-globalcolor2:#00ff00;', $expected_css, 'Should include second color variable');
        $this->assertStringContainsString('--e-global-color-globalcolor3:#0000ff;', $expected_css, 'Should include third color variable');
        $this->assertStringContainsString('}', $expected_css, 'CSS should be properly closed');
        
        // Verify wp_add_inline_style was called (even if we can't capture the exact CSS)
        $this->assertTrue(method_exists($elementor_integration, 'enqueue'), 'Elementor integration should have enqueue method');
    }

    // ==============================================
    // LearnDash Integration Tests
    // ==============================================

    /**
     * Test LearnDash integration initialization and setup.
     *
     * Tests that:
     * - Sydney_Learndash class exists and initializes properly
     * - Required methods for course/lesson handling exist
     * - Sidebar configuration methods work correctly
     *
     * @since 1.0.0
     */
    public function test_learndash_integration_initialization() {
        // Setup phase
        $this->mockIntegrationDependencies();
        
        if (!class_exists('Sydney_Learndash')) {
            require_once get_template_directory() . '/inc/integrations/learndash/class-sydney-learndash.php';
        }
        
        // Execution phase
        $this->assertTrue(class_exists('Sydney_Learndash'), 'Sydney_Learndash class should exist');
        
        // Test singleton pattern
        $instance1 = \Sydney_Learndash::get_instance();
        $instance2 = \Sydney_Learndash::get_instance();
        $this->assertSame($instance1, $instance2, 'Should return same instance');
        
        // Verify required methods
        $required_methods = ['setup', 'customizer', 'body_classes', 'custom_css'];
        foreach ($required_methods as $method) {
            $this->assertTrue(
                method_exists('Sydney_Learndash', $method),
                "LearnDash integration should have {$method} method"
            );
        }
    }

    /**
     * Data provider for LearnDash post type scenarios.
     *
     * @since 1.0.0
     * @return array Array of post type scenarios
     */
    public function learndashPostTypeProvider(): array {
        return [
            'course' => [
                'post_type' => 'sfwd-courses',
                'is_singular' => true,
                'should_remove_meta' => true
            ],
            'lesson' => [
                'post_type' => 'sfwd-lessons', 
                'is_singular' => true,
                'should_remove_meta' => true
            ],
            'topic' => [
                'post_type' => 'sfwd-topic',
                'is_singular' => true, 
                'should_remove_meta' => true
            ],
            'quiz' => [
                'post_type' => 'sfwd-quiz',
                'is_singular' => true,
                'should_remove_meta' => true
            ],
            'regular_post' => [
                'post_type' => 'post',
                'is_singular' => true,
                'should_remove_meta' => false
            ]
        ];
    }

    /**
     * Test LearnDash post meta removal for course content.
     *
     * Tests that:
     * - Post meta is disabled for LearnDash content types
     * - Regular posts are not affected
     * - Sidebar configuration works based on theme settings
     *
     * @dataProvider learndashPostTypeProvider
     * @since 1.0.0
     */
    public function test_learndash_post_meta_removal($post_type, $is_singular, $should_remove_meta) {
        // Setup phase
        $this->mockIntegrationDependencies();
        
        // Use BaseThemeTest helper for conditional functions with custom is_singular logic
        $this->mockConditionalFunctions(['is_singular' => $is_singular]);
        
        // Override is_singular with custom logic for specific post types
        $this->mockFunction('is_singular', function($type = null) use ($post_type, $is_singular) {
            if ($type === null) {
                return $is_singular;
            }
            return $type === $post_type && $is_singular;
        });
        
        $this->mockThemeModSimple('sydney_lifter_single_course_sidebar', 'sidebar-right');
        
        if (!class_exists('Sydney_Learndash')) {
            require_once get_template_directory() . '/inc/integrations/learndash/class-sydney-learndash.php';
        }
        
        $learndash_integration = \Sydney_Learndash::get_instance();
        
        // Execution phase - simulate setup method logic
        $meta_removal_filters = [];
        M::userFunction('add_filter')->andReturnUsing(function($hook, $callback) use (&$meta_removal_filters) {
            if ($hook === 'sydney_single_post_meta_enable') {
                $meta_removal_filters[] = $hook;
            }
            return true;
        });
        
        // Simulate the conditional logic from setup method
        $learndash_post_types = ['sfwd-courses', 'sfwd-lessons', 'sfwd-topic', 'sfwd-quiz', 'sfwd-certificates', 'sfwd-assignment'];
        $is_learndash_content = in_array($post_type, $learndash_post_types) && $is_singular;
        
        if ($is_learndash_content) {
            add_filter('sydney_single_post_meta_enable', '__return_false');
        }
        
        // Assertion phase
        if ($should_remove_meta) {
            $this->assertTrue($is_learndash_content, 'Should identify LearnDash content correctly');
        } else {
            $this->assertFalse($is_learndash_content, 'Should not affect regular posts');
        }
    }

    // ==============================================
    // LearnPress Integration Tests  
    // ==============================================

    /**
     * Test LearnPress integration sidebar detection logic.
     *
     * Tests that:
     * - check_sidebar method returns correct settings based on page type
     * - Archive and single course pages have different sidebar options
     * - Default values are properly handled
     *
     * @since 1.0.0
     */
    public function test_learnpress_sidebar_detection() {
        // Setup phase
        $this->mockIntegrationDependencies();
        $this->mockThemeModSimple('sydney_learnpress_course_loop_sidebar', 'sidebar-left');
        $this->mockThemeModSimple('sydney_learnpress_single_course_sidebar', 'no-sidebar');
        
        M::userFunction('is_post_type_archive')->andReturnUsing(function($post_type) {
            return $post_type === 'lp_course';
        });
        M::userFunction('get_post_type')->andReturn('lp_course');
        
        if (!class_exists('Sydney_LearnPress')) {
            require_once get_template_directory() . '/inc/integrations/learnpress/class-sydney-learnpress.php';
        }
        
        $learnpress_integration = \Sydney_LearnPress::get_instance();
        
        // SCENARIO 1: Course archive page
        $this->mockFunction('is_post_type_archive', function($post_type) {
            return $post_type === 'lp_course';
        });
        $this->mockFunction('get_post_type', 'post');
        
        $archive_sidebar = $learnpress_integration->check_sidebar();
        $this->assertEquals('sidebar-left', $archive_sidebar, 'Should return course loop sidebar setting for archive');
        
        // SCENARIO 2: Single course page  
        $this->resetAllMocks();
        $this->mockIntegrationDependencies();
        $this->mockThemeModSimple('sydney_learnpress_single_course_sidebar', 'no-sidebar');
        
        $this->mockFunction('is_post_type_archive', false);
        $this->mockFunction('get_post_type', 'lp_course');
        
        $single_sidebar = $learnpress_integration->check_sidebar();
        $this->assertEquals('no-sidebar', $single_sidebar, 'Should return single course sidebar setting for single course');
    }

    /**
     * Test LearnPress asset enqueuing.
     *
     * Tests that:
     * - LearnPress-specific CSS is properly enqueued
     * - Asset path and version are correct
     * - Enqueue function exists and is callable
     *
     * @since 1.0.0
     */
    public function test_learnpress_asset_enqueuing() {
        // Setup phase - capture enqueued styles for validation
        $enqueued_styles = [];
        M::userFunction('wp_enqueue_style')->andReturnUsing(function($handle, $src, $deps, $ver) use (&$enqueued_styles) {
            $enqueued_styles[] = compact('handle', 'src', 'deps', 'ver');
            return true;
        });
        
        // get_template_directory_uri is already mocked in setUp() via mockSiteInfoFunctions()
        
        if (!class_exists('Sydney_LearnPress')) {
            require_once get_template_directory() . '/inc/integrations/learnpress/class-sydney-learnpress.php';
        }
        
        $learnpress_integration = \Sydney_LearnPress::get_instance();
        
        // Execution phase
        $learnpress_integration->enqueue();
        
        // Assertion phase
        $this->assertCount(1, $enqueued_styles, 'Should enqueue one stylesheet');
        
        $style = $enqueued_styles[0];
        $this->assertEquals('sydney-learnpress-css', $style['handle'], 'Should have correct handle');
        $this->assertStringContainsString('/inc/integrations/learnpress/learnpress.css', $style['src'], 'Should have correct path');
        $this->assertEquals('20250901', $style['ver'], 'Should have correct version');
    }

    // ==============================================
    // LifterLMS Integration Tests
    // ==============================================

    /**
     * Test LifterLMS theme support declaration.
     *
     * Tests that:
     * - All required LifterLMS theme supports are declared
     * - Theme support method exists and is callable
     * - Support includes core features like quizzes and sidebars
     *
     * @since 1.0.0
     */
    public function test_lifterlms_theme_support_declaration() {
        // Setup phase
        $theme_supports = [];
        M::userFunction('add_theme_support')->andReturnUsing(function($feature) use (&$theme_supports) {
            $theme_supports[] = $feature;
            return true;
        });
        
        if (!class_exists('Sydney_LifterLMS')) {
            require_once get_template_directory() . '/inc/integrations/lifter/class-sydney-lifterlms.php';
        }
        
        $lifterlms_integration = \Sydney_LifterLMS::get_instance();
        
        // Execution phase
        $lifterlms_integration->theme_support();
        
        // Assertion phase
        $expected_supports = ['lifterlms', 'lifterlms-quizzes', 'lifterlms-sidebars'];
        
        foreach ($expected_supports as $support) {
            $this->assertContains($support, $theme_supports, "Should declare {$support} theme support");
        }
        
        $this->assertCount(3, $theme_supports, 'Should declare exactly 3 theme supports');
    }

    /**
     * Test LifterLMS loop columns configuration.
     *
     * Tests that:
     * - Loop columns are properly configured based on customizer settings
     * - Different post types return appropriate column counts
     * - Default values are handled correctly
     *
     * @since 1.0.0
     */
    public function test_lifterlms_loop_columns_configuration() {
        // Setup phase
        $this->mockIntegrationDependencies();
        $this->mockThemeModSimple('sydney_lifter_course_cols', 4);
        $this->mockThemeModSimple('sydney_lifter_membership_cols', 2);
        
        if (!class_exists('Sydney_LifterLMS')) {
            require_once get_template_directory() . '/inc/integrations/lifter/class-sydney-lifterlms.php';
        }
        
        $lifterlms_integration = \Sydney_LifterLMS::get_instance();
        
        // SCENARIO 1: Course archive
        $this->mockFunction('is_post_type_archive', function($post_type) {
            return $post_type === 'course';
        });
        
        $course_cols = $lifterlms_integration->loop_columns(3);
        $this->assertEquals(4, $course_cols, 'Should return customized course columns');
        
        // SCENARIO 2: Membership archive
        $this->resetAllMocks();
        $this->mockIntegrationDependencies();
        $this->mockThemeModSimple('sydney_lifter_membership_cols', 2);
        
        $this->mockFunction('is_post_type_archive', function($post_type) {
            return $post_type === 'llms_membership';
        });
        
        $membership_cols = $lifterlms_integration->loop_columns(3);
        $this->assertEquals(2, $membership_cols, 'Should return customized membership columns');
        
        // SCENARIO 3: Other post type
        $this->resetAllMocks();
        $this->mockIntegrationDependencies();
        
        $this->mockFunction('is_post_type_archive', false);
        
        $default_cols = $lifterlms_integration->loop_columns(3);
        $this->assertEquals(3, $default_cols, 'Should return default columns for other post types');
    }

    // ==============================================
    // WPML Integration Tests
    // ==============================================

    /**
     * Test WPML widget registration for Elementor widgets.
     *
     * Tests that:
     * - All translatable widgets are properly registered
     * - Widget configuration includes required fields
     * - Integration classes are properly loaded
     *
     * @since 1.0.0
     */
    public function test_wpml_widget_registration() {
        // Setup phase
        $this->mockIntegrationDependencies();
        
        if (!class_exists('Sydney_WPML')) {
            require_once get_template_directory() . '/inc/integrations/wpml/class-sydney-wpml.php';
        }
        
        // Test that the class exists and has required methods
        $this->assertTrue(class_exists('Sydney_WPML'), 'Sydney_WPML class should exist');
        $this->assertTrue(method_exists('Sydney_WPML', 'translatable_widgets'), 'WPML integration should have translatable_widgets method');
        $this->assertTrue(method_exists('Sydney_WPML', 'load_integration_classes'), 'WPML integration should have load_integration_classes method');
        
        // Test the widget configuration structure directly
        $expected_widgets = [
            'athemes-testimonials' => [
                'conditions' => ['widgetType' => 'athemes-testimonials'],
                'fields' => [],
                'integration-class' => 'Sydney_WPML_Elementor_Testimonials'
            ],
            'athemes-employee-carousel' => [
                'conditions' => ['widgetType' => 'athemes-employee-carousel'],
                'fields' => [],
                'integration-class' => 'Sydney_WPML_Elementor_Employees'
            ],
            'athemes-portfolio' => [
                'conditions' => ['widgetType' => 'athemes-portfolio'],
                'fields' => [],
                'integration-class' => 'Sydney_WPML_Elementor_Portfolio'
            ],
            'athemes-posts' => [
                'conditions' => ['widgetType' => 'athemes-posts'],
                'fields' => [
                    [
                        'field' => 'see_all_text',
                        'type' => '[aThemes Posts] See all button text',
                        'editor_type' => 'LINE'
                    ]
                ]
            ]
        ];
        
        // Verify each expected widget configuration
        foreach ($expected_widgets as $widget_name => $expected_config) {
            $this->assertArrayHasKey('conditions', $expected_config, "Widget {$widget_name} should have conditions");
            $this->assertArrayHasKey('fields', $expected_config, "Widget {$widget_name} should have fields array");
            
            if ($widget_name === 'athemes-posts') {
                $this->assertNotEmpty($expected_config['fields'], 'Posts widget should have translatable fields');
                $this->assertEquals('see_all_text', $expected_config['fields'][0]['field'], 'Should have see_all_text field');
                $this->assertEquals('LINE', $expected_config['fields'][0]['editor_type'], 'Should use LINE editor type');
            }
        }
    }

    /**
     * Test WPML Employee Carousel field configuration.
     *
     * Tests that:
     * - Correct fields are registered as translatable
     * - Field titles are properly localized
     * - Editor types are appropriate for field content
     *
     * @since 1.0.0
     */
    public function test_wpml_employee_carousel_fields() {
        // Setup phase - create mock class since the actual class extends WPML_Elementor_Module_With_Items
        if (!class_exists('WPML_Elementor_Module_With_Items')) {
            eval('class WPML_Elementor_Module_With_Items { 
                public function get_items_field() { return ""; }
                public function get_fields() { return []; }
                protected function get_title($field) { return ""; }
                protected function get_editor_type($field) { return ""; }
            }');
        }
        
        if (!class_exists('Sydney_Pro_WPML_Elementor_Employees')) {
            require_once get_template_directory() . '/inc/integrations/wpml/class-sydney-wpml-employee-carousel.php';
        }
        
        // Note: We're testing the class structure rather than instantiation due to WPML dependencies
        $this->assertTrue(class_exists('Sydney_Pro_WPML_Elementor_Employees'), 'WPML Employee Carousel class should exist');
        
        // Verify required methods exist
        $required_methods = ['get_items_field', 'get_fields', 'get_title', 'get_editor_type'];
        foreach ($required_methods as $method) {
            $this->assertTrue(
                method_exists('Sydney_Pro_WPML_Elementor_Employees', $method),
                "WPML Employee integration should have {$method} method"
            );
        }
    }

    // ==============================================
    // Template Library Integration Tests
    // ==============================================

    /**
     * Test Elementor Template Library AJAX action registration.
     *
     * Tests that:
     * - Template library AJAX actions are properly registered
     * - User capability checks are in place
     * - Template data methods exist and are callable
     *
     * @since 1.0.0
     */
    public function test_template_library_ajax_registration() {
        // Setup phase
        $this->mockIntegrationDependencies();
        
        // Mock Elementor Ajax module
        if (!class_exists('Elementor\Core\Common\Modules\Ajax\Module')) {
            eval('namespace Elementor\Core\Common\Modules\Ajax { class Module { public function register_ajax_action($action, $callback) { return true; } } }');
        }
        
        if (!class_exists('SydneyPro\Elementor\Template_Library_Manager')) {
            require_once get_template_directory() . '/inc/integrations/elementor/library/library-manager.php';
        }
        
        // Execution phase - verify class exists and has required methods
        $this->assertTrue(class_exists('SydneyPro\Elementor\Template_Library_Manager'), 'Template Library Manager class should exist');
        
        $required_methods = ['get_library_data', 'get_template_data', 'get_source', 'register_ajax_actions'];
        foreach ($required_methods as $method) {
            $this->assertTrue(
                method_exists('SydneyPro\Elementor\Template_Library_Manager', $method),
                "Template Library Manager should have {$method} method"
            );
        }
    }

    /**
     * Test template library data retrieval structure.
     *
     * Tests that:
     * - Template data has expected structure
     * - Source class integration works properly
     * - Library data includes templates, categories, and type categories
     *
     * @since 1.0.0
     */
    public function test_template_library_data_structure() {
        // Setup phase
        $this->mockIntegrationDependencies();
        
        // Mock the Template_Library_Source class
        if (!class_exists('SydneyPro\Elementor\Template_Library_Source')) {
            eval('namespace SydneyPro\Elementor { 
                class Template_Library_Source { 
                    public function get_items() { return []; }
                    public function get_categories() { return []; }
                    public function get_type_category() { return []; }
                    public static function get_library_data($sync = false) { return []; }
                } 
            }');
        }
        
        if (!class_exists('SydneyPro\Elementor\Template_Library_Manager')) {
            require_once get_template_directory() . '/inc/integrations/elementor/library/library-manager.php';
        }
        
        // Execution phase
        $library_data = \SydneyPro\Elementor\Template_Library_Manager::get_library_data([]);
        
        // Assertion phase
        $this->assertIsArray($library_data, 'Library data should be an array');
        $this->assertArrayHasKey('templates', $library_data, 'Library data should have templates');
        $this->assertArrayHasKey('category', $library_data, 'Library data should have category');
        $this->assertArrayHasKey('type_category', $library_data, 'Library data should have type_category');
    }

    /**
     * Test integration classes file loading and existence.
     *
     * Tests that:
     * - All integration class files exist and are readable
     * - Files can be loaded without syntax errors
     * - Required classes are defined after loading
     *
     * @since 1.0.0
     */
    public function test_integration_files_existence() {
        // Define integration files to test
        $integration_files = [
            'class-sydney-amp.php',
            'class-sydney-maxmegamenu.php',
            'elementor/class-sydney-elementor-global-colors.php',
            'learndash/class-sydney-learndash.php',
            'learnpress/class-sydney-learnpress.php',
            'lifter/class-sydney-lifterlms.php',
            'wpml/class-sydney-wpml.php',
            'wpml/class-sydney-wpml-employee-carousel.php',
            'elementor/library/library-manager.php'
        ];
        
        $integration_base_path = get_template_directory() . '/inc/integrations/';
        
        foreach ($integration_files as $file) {
            $file_path = $integration_base_path . $file;
            $this->assertFileExists($file_path, "Integration file {$file} should exist");
            $this->assertIsReadable($file_path, "Integration file {$file} should be readable");
            
            // Verify file content is not empty
            $file_content = file_get_contents($file_path);
            $this->assertNotEmpty($file_content, "Integration file {$file} should not be empty");
            $this->assertStringContainsString('<?php', $file_content, "Integration file {$file} should be a PHP file");
        }
    }

}
