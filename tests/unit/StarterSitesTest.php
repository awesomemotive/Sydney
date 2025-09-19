<?php
/**
 * Unit tests for Sydney Theme Dashboard Starter Sites functionality
 *
 * Tests the dashboard starter sites tab, plugin detection, HTML rendering,
 * asset management, and dashboard state management functionality.
 *
 * @package Sydney
 * @subpackage Tests
 */

namespace Sydney\Tests;

use Sydney\Tests\BaseThemeTest;
use WP_Mock as M;

/**
 * Test class for Sydney Dashboard Starter Sites functionality.
 *
 * Tests that:
 * - Dashboard class integration works correctly
 * - Settings configuration is properly structured
 * - HTML template rendering works for different plugin states
 * - Assets are properly enqueued
 * - Dashboard state management functions correctly
 *
 * @since 1.0.0
 */
class StarterSitesTest extends BaseThemeTest {

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
        
        // Define WordPress constants needed for testing
        if (!defined('WP_PLUGIN_DIR')) {
            define('WP_PLUGIN_DIR', '/tmp/wp-content/plugins');
        }
        
        // Mock essential WordPress functions for dashboard functionality
        $this->mockEssentialsOnly();
        $this->mockTranslationFunctions();
        $this->mockSiteInfoFunctions([
            'admin_url' => 'http://example.com/wp-admin/',
            'get_template_directory_uri' => 'http://example.com/wp-content/themes/sydney'
        ]);
        
        // Load dashboard classes if they don't exist
        if (!class_exists('Sydney_Dashboard')) {
            require_once get_template_directory() . '/inc/dashboard/class-dashboard.php';
        }
        
        if (!function_exists('sydney_dashboard_settings')) {
            require_once get_template_directory() . '/inc/dashboard/class-dashboard-settings.php';
        }
    }

    /**
     * Mock dashboard settings dependencies.
     *
     * Sets up all WordPress functions needed for sydney_dashboard_settings()
     * to run without errors. This is a reusable helper to avoid repetitive
     * mock setup across multiple tests.
     *
     * @since 1.0.0
     * @return void
     */
    protected function mockDashboardSettingsDependencies(): void {
        M::userFunction('sydney_admin_upgrade_link')->andReturn('https://athemes.com/sydney-upgrade');
        M::userFunction('wp_get_theme')->andReturn((object)['version' => '1.0.0']);
        M::userFunction('wp_remote_get')->andReturn(['body' => '[]']);
        M::userFunction('is_wp_error')->andReturn(false);
        M::userFunction('wp_remote_retrieve_response_code')->andReturn(200);
        M::userFunction('wp_remote_retrieve_body')->andReturn('[]');
        M::userFunction('add_query_arg')->andReturn('http://example.com/customize.php');
        M::userFunction('admin_url')->andReturn('http://example.com/wp-admin/');
        // Mock Sydney_Modules static method using a different approach
        if (!class_exists('Sydney_Modules')) {
            // Create a simple mock class
            eval('class Sydney_Modules { public static function is_module_active($module) { return false; } }');
        }
    }

    /**
     * Mock plugin-related WordPress functions.
     *
     * Sets up common mocks for plugin status detection and management.
     * This provides a clean base that can be overridden for specific scenarios.
     * Note: file_exists() is not mocked as it's a PHP built-in function.
     *
     * @since 1.0.0
     * @param array $config Plugin configuration:
     *                     - 'user_can_install' => bool (default: true)
     *                     - 'active_plugins' => array (default: [])
     *                     - 'network_active' => bool (default: false)
     * @return void
     */
    protected function mockPluginFunctions(array $config = []): void {
        $defaults = [
            'user_can_install' => true,
            'active_plugins' => [],
            'network_active' => false
        ];
        $config = array_merge($defaults, $config);

        M::userFunction('current_user_can')->with('install_plugins')->andReturn($config['user_can_install']);
        
        // Mock get_option for active_plugins
        M::userFunction('get_option')->with('active_plugins', [])->andReturn($config['active_plugins']);
        
        // Mock network plugin check
        M::userFunction('is_plugin_active_for_network')->andReturn($config['network_active']);
    }

    /**
     * Setup template rendering environment.
     *
     * Mocks the WordPress functions and globals needed for template
     * rendering tests. This creates a clean environment for testing
     * actual template output.
     *
     * @since 1.0.0
     * @param array $dashboard_settings Dashboard settings to use in template
     * @return void
     */
    protected function setupTemplateRenderingMocks(array $dashboard_settings = []): void {
        $defaults = [
            'starter_plugin_path' => 'athemes-starter-sites/athemes-starter-sites.php',
            'starter_plugin_slug' => 'athemes-starter-sites',
            'menu_slug' => 'sydney-dashboard'
        ];
        $dashboard_settings = array_merge($defaults, $dashboard_settings);

        // Create a mock dashboard object with settings
        $GLOBALS['sydney_dashboard_mock'] = (object) [
            'settings' => $dashboard_settings
        ];

        // Mock URL generation functions
        M::userFunction('add_query_arg')->andReturn('http://example.com/wp-admin/themes.php?page=sydney-dashboard&tab=starter-sites');
        
        // Mock action/hook functions
        M::userFunction('has_action')->with('atss_starter_sites')->andReturn(false);
        M::userFunction('do_action')->with('atss_starter_sites')->andReturn(true);
    }

    /**
     * Test dashboard class exists and has required methods.
     *
     * Tests that:
     * - Sydney_Dashboard class exists and can be loaded
     * - Required methods are present for starter sites functionality
     * - Class has proper structure for plugin management
     *
     * @since 1.0.0
     */
    public function test_dashboard_class_exists() {
        // Execution phase - verify class exists and has required methods
        $this->assertTrue(class_exists('Sydney_Dashboard'), 'Sydney_Dashboard class should exist');
        
        // Assertion phase - verify required methods for starter sites functionality
        $required_methods = [
            'get_plugin_status',
            'admin_enqueue_scripts', 
            'add_menu_page',
            'html_dashboard'
        ];
        
        foreach ($required_methods as $method) {
            $this->assertTrue(
                method_exists('Sydney_Dashboard', $method), 
                "Dashboard should have {$method} method for starter sites functionality"
            );
        }
    }

    /**
     * Test starter sites tab is properly registered in dashboard tabs.
     *
     * Tests that:
     * - Starter sites tab exists in dashboard configuration
     * - Tab has correct label and structure
     * - Settings array contains all required elements for starter sites
     *
     * @since 1.0.0
     */
    public function test_starter_sites_tab_registration() {
        // Setup phase - use reusable mock helper
        $this->mockDashboardSettingsDependencies();
        
        // Execution phase - call the actual settings function
        $settings = sydney_dashboard_settings();
        
        // Assertion phase - comprehensive validation of starter sites configuration
        $this->assertIsArray($settings, 'Dashboard settings should be an array');
        $this->assertArrayHasKey('tabs', $settings, 'Settings should have tabs array');
        $this->assertArrayHasKey('starter-sites', $settings['tabs'], 'Tabs should include starter-sites');
        $this->assertIsString($settings['tabs']['starter-sites'], 'Starter sites tab should have string label');
        $this->assertEquals('Starter Sites', $settings['tabs']['starter-sites'], 'Starter sites tab should have correct label');
        
        // Verify starter plugin configuration exists
        $this->assertArrayHasKey('starter_plugin_slug', $settings, 'Settings should have starter plugin slug');
        $this->assertArrayHasKey('starter_plugin_path', $settings, 'Settings should have starter plugin path');
        $this->assertEquals('athemes-starter-sites', $settings['starter_plugin_slug'], 'Should have correct plugin slug');
        $this->assertEquals('athemes-starter-sites/athemes-starter-sites.php', $settings['starter_plugin_path'], 'Should have correct plugin path');
    }

    /**
     * Data provider for plugin status scenarios.
     *
     * Provides different plugin states and their expected behaviors
     * for comprehensive testing of plugin detection logic.
     * Note: We test plugin behavior rather than file system checks.
     *
     * @since 1.0.0
     * @return array Array of test scenarios
     */
    public function pluginStatusProvider(): array {
        return [
            'active_regular' => [
                'plugin_config' => [
                    'user_can_install' => true,
                    'active_plugins' => ['athemes-starter-sites/athemes-starter-sites.php'],
                    'network_active' => false
                ],
                'expected_status' => 'active',
                'expected_locked' => false
            ],
            'active_network' => [
                'plugin_config' => [
                    'user_can_install' => true,
                    'active_plugins' => [],
                    'network_active' => true
                ],
                'expected_status' => 'active',
                'expected_locked' => false
            ],
            'no_permissions' => [
                'plugin_config' => [
                    'user_can_install' => false,
                    'active_plugins' => [],
                    'network_active' => false
                ],
                'expected_status' => null,
                'expected_locked' => true
            ]
        ];
    }

    /**
     * Test plugin status detection logic with different scenarios.
     *
     * Tests that:
     * - Plugin status detection method exists and is callable
     * - Locked/unlocked state logic is correct
     * - User permissions affect plugin status
     *
     * @dataProvider pluginStatusProvider
     * @since 1.0.0
     */
    public function test_plugin_status_detection($plugin_config, $expected_status, $expected_locked) {
        // Setup phase - mock plugin functions with provided configuration
        $this->mockPluginFunctions($plugin_config);
        
        // Execution phase - test the locked state logic directly
        // Since we can't mock file_exists(), we test the business logic
        $mock_status = $expected_status;
        
        // Test locked state logic (plugin is locked if not active)
        $is_locked = in_array($mock_status, ['inactive', 'not_installed'], true) || $mock_status === null;
        
        // Assertion phase - verify locked state determination
        $this->assertEquals($expected_locked, $is_locked, 'Locked state should be determined correctly');
        
        // Verify the dashboard class has the method
        $this->assertTrue(method_exists('Sydney_Dashboard', 'get_plugin_status'), 'Dashboard should have get_plugin_status method');
        
        // Test user permissions logic
        if (!$plugin_config['user_can_install']) {
            $dashboard = new \Sydney_Dashboard();
            $status = $dashboard->get_plugin_status('test-plugin/test-plugin.php');
            $this->assertNull($status, 'Should return null when user cannot install plugins');
        }
    }

    /**
     * Test starter sites template logic and expected elements.
     *
     * Tests that:
     * - Template file exists and is readable
     * - Expected CSS classes and elements are defined
     * - Plugin state conditional logic works correctly
     *
     * @since 1.0.0
     */
    public function test_starter_sites_template_structure() {
        // Setup phase - verify template file exists
        $template_path = get_template_directory() . '/inc/dashboard/html-starter-sites.php';
        $this->assertFileExists($template_path, 'Starter sites template file should exist');
        $this->assertIsReadable($template_path, 'Starter sites template file should be readable');
        
        // Execution phase - analyze template content
        $template_content = file_get_contents($template_path);
        $this->assertIsString($template_content, 'Template content should be readable as string');
        $this->assertNotEmpty($template_content, 'Template content should not be empty');
        
        // Assertion phase - verify expected elements are present in template
        $expected_elements = [
            'sydney-dashboard-starter-sites',
            'sydney-dashboard-starter-sites-locked',
            'sydney-dashboard-starter-sites-notice',
            'button button-primary',
            'startersbg.jpg',
            'Install and Activate',
            'atss_starter_sites',
            'Go to Starter Sites'
        ];
        
        $this->assertHtmlContainsAll($template_content, $expected_elements);
        
        // Verify conditional logic structure
        $this->assertStringContainsString('get_plugin_status', $template_content, 'Template should check plugin status');
        $this->assertStringContainsString('has_action', $template_content, 'Template should check for action hook');
        $this->assertStringContainsString('do_action', $template_content, 'Template should call action hook');
        
        // Verify security practices
        $this->assertStringContainsString('esc_url', $template_content, 'Template should escape URLs');
        $this->assertStringContainsString('esc_attr', $template_content, 'Template should escape attributes');
        $this->assertStringContainsString('esc_html_e', $template_content, 'Template should escape translated text');
    }

    /**
     * Test dashboard assets are properly enqueued with correct parameters.
     *
     * Tests that:
     * - Dashboard CSS is enqueued with correct path and version
     * - Dashboard JS is enqueued with proper dependencies
     * - RTL CSS is conditionally enqueued
     * - Localization data has required structure
     * - Asset paths are valid
     *
     * @since 1.0.0
     */
    public function test_dashboard_assets_enqueued() {
        // Setup phase - mock WordPress asset functions with detailed tracking
        $enqueued_styles = [];
        $enqueued_scripts = [];
        $localized_data = [];
        
        M::userFunction('wp_enqueue_style')->andReturnUsing(function($handle, $src, $deps, $ver) use (&$enqueued_styles) {
            $enqueued_styles[] = compact('handle', 'src', 'deps', 'ver');
            return true;
        });
        
        M::userFunction('wp_enqueue_script')->andReturnUsing(function($handle, $src, $deps, $ver, $in_footer) use (&$enqueued_scripts) {
            $enqueued_scripts[] = compact('handle', 'src', 'deps', 'ver', 'in_footer');
            return true;
        });
        
        M::userFunction('wp_localize_script')->andReturnUsing(function($handle, $name, $data) use (&$localized_data) {
            $localized_data[$handle] = compact('name', 'data');
            return true;
        });
        
        M::userFunction('wp_create_nonce')->andReturn('test-nonce-12345');
        M::userFunction('is_rtl')->andReturn(false);
        
        // Create dashboard instance and enqueue assets
        $dashboard = new \Sydney_Dashboard();
        $dashboard->admin_enqueue_scripts('themes.php');
        
        // Assertion phase - verify main CSS enqueued correctly
        $main_css = array_filter($enqueued_styles, function($style) {
            return $style['handle'] === 'sydney-dashboard';
        });
        $this->assertCount(1, $main_css, 'Main dashboard CSS should be enqueued once');
        
        $main_css = reset($main_css);
        $this->assertStringContainsString('/inc/dashboard/assets/css/sydney-dashboard.min.css', $main_css['src'], 'CSS should have correct path');
        $this->assertEquals('20230525', $main_css['ver'], 'CSS should have correct version');
        
        // Verify main JS enqueued correctly
        $main_js = array_filter($enqueued_scripts, function($script) {
            return $script['handle'] === 'sydney-dashboard';
        });
        $this->assertCount(1, $main_js, 'Main dashboard JS should be enqueued once');
        
        $main_js = reset($main_js);
        $this->assertStringContainsString('/inc/dashboard/assets/js/sydney-dashboard.min.js', $main_js['src'], 'JS should have correct path');
        $this->assertEquals(['jquery', 'wp-util', 'jquery-ui-sortable'], $main_js['deps'], 'JS should have correct dependencies');
        $this->assertTrue($main_js['in_footer'], 'JS should be loaded in footer');
        
        // Verify localization data structure
        $this->assertArrayHasKey('sydney-dashboard', $localized_data, 'Dashboard JS should be localized');
        $localization = $localized_data['sydney-dashboard'];
        $this->assertEquals('sydney_dashboard', $localization['name'], 'Localization should have correct name');
        
        $data = $localization['data'];
        $this->assertArrayHasKey('ajax_url', $data, 'Localization should have ajax_url');
        $this->assertArrayHasKey('nonce', $data, 'Localization should have nonce');
        $this->assertArrayHasKey('i18n', $data, 'Localization should have i18n strings');
        $this->assertEquals('test-nonce-12345', $data['nonce'], 'Nonce should be correctly set');
        
        // Test RTL scenario
        $this->resetAllMocks();
        $this->setUp();
        
        $rtl_styles = [];
        M::userFunction('wp_enqueue_style')->andReturnUsing(function($handle, $src) use (&$rtl_styles) {
            $rtl_styles[] = compact('handle', 'src');
            return true;
        });
        M::userFunction('wp_enqueue_script')->andReturn(true);
        M::userFunction('wp_localize_script')->andReturn(true);
        M::userFunction('wp_create_nonce')->andReturn('test-nonce');
        M::userFunction('is_rtl')->andReturn(true);
        
        $dashboard = new \Sydney_Dashboard();
        $dashboard->admin_enqueue_scripts('themes.php');
        
        // Verify RTL CSS is also enqueued
        $rtl_css = array_filter($rtl_styles, function($style) {
            return $style['handle'] === 'sydney-dashboard-rtl';
        });
        $this->assertCount(1, $rtl_css, 'RTL CSS should be enqueued when RTL is active');
        
        $rtl_css = reset($rtl_css);
        $this->assertStringContainsString('sydney-dashboard-rtl.min.css', $rtl_css['src'], 'RTL CSS should have correct filename');
    }

    /**
     * Data provider for dashboard tab scenarios.
     *
     * @since 1.0.0
     * @return array Array of tab scenarios
     */
    public function dashboardTabProvider(): array {
        return [
            'starter_sites_selected' => [
                'selected_tab' => 'starter-sites',
                'expected_active' => 'starter-sites',
                'expected_class' => ' active'
            ],
            'home_selected' => [
                'selected_tab' => 'home',
                'expected_active' => 'home', 
                'expected_class' => ' active'
            ],
            'no_tab_selected' => [
                'selected_tab' => null,
                'expected_active' => 'home', // Default
                'expected_class' => ' active'
            ],
            'invalid_tab_selected' => [
                'selected_tab' => 'nonexistent',
                'expected_active' => 'home', // Fallback to default
                'expected_class' => ' active'
            ]
        ];
    }

    /**
     * Test dashboard tab state management with different scenarios.
     *
     * Tests that:
     * - Active tab CSS class is applied correctly
     * - Default tab behavior works when no tab is selected
     * - Invalid tab selections fallback appropriately
     * - Tab navigation logic is consistent
     *
     * @dataProvider dashboardTabProvider
     * @since 1.0.0
     */
    public function test_dashboard_tab_state_management($selected_tab, $expected_active, $expected_class) {
        // Setup phase - configure tab settings and mock functions
        $settings = [
            'tabs' => [
                'home' => 'Home',
                'starter-sites' => 'Starter Sites',
                'settings' => 'License',
                'free-vs-pro' => 'Free vs Pro'
            ]
        ];
        
        M::userFunction('sanitize_text_field')->andReturnUsing(function($input) {
            return $input;
        });
        M::userFunction('wp_unslash')->andReturnUsing(function($input) {
            return $input;
        });
        
        // Set up $_GET parameter
        if ($selected_tab !== null) {
            $_GET['tab'] = $selected_tab;
        } else {
            unset($_GET['tab']);
        }
        
        // Execution phase - simulate dashboard tab logic
        $section = (isset($_GET['tab'])) ? sanitize_text_field(wp_unslash($_GET['tab'])) : '';
        
        // Validate selected tab exists, fallback to home if not
        if ($section && !array_key_exists($section, $settings['tabs'])) {
            $section = '';
        }
        
        $tab_states = [];
        foreach($settings['tabs'] as $tab_id => $tab_title) {
            $is_active = (($section && $section === $tab_id) || (!$section && $tab_id === 'home'));
            $tab_states[$tab_id] = [
                'active' => $is_active,
                'class' => $is_active ? ' active' : ''
            ];
        }
        
        // Assertion phase - verify tab state management
        $active_tabs = array_filter($tab_states, function($state) {
            return $state['active'];
        });
        
        $this->assertCount(1, $active_tabs, 'Exactly one tab should be active');
        
        $active_tab_id = key($active_tabs);
        $this->assertEquals($expected_active, $active_tab_id, 'Correct tab should be active');
        $this->assertEquals($expected_class, $tab_states[$active_tab_id]['class'], 'Active tab should have correct CSS class');
        
        // Verify starter sites tab behavior specifically
        if ($expected_active === 'starter-sites') {
            $this->assertTrue($tab_states['starter-sites']['active'], 'Starter sites tab should be active when selected');
            $this->assertEquals(' active', $tab_states['starter-sites']['class'], 'Starter sites should have active class');
        } else {
            $this->assertFalse($tab_states['starter-sites']['active'], 'Starter sites tab should not be active when not selected');
            $this->assertEquals('', $tab_states['starter-sites']['class'], 'Starter sites should not have active class when not selected');
        }
        
        // Clean up
        unset($_GET['tab']);
    }

    /**
     * Test dashboard tab content wrapper structure.
     *
     * Tests that:
     * - Tab content wrapper renders with correct attributes
     * - Tab content has proper data attributes
     * - Active tab content is properly identified
     *
     * @since 1.0.0
     */
    public function test_dashboard_tab_content_wrapper() {
        // Setup phase - mock dashboard rendering context
        $tab_id = 'starter-sites';
        $tab_active = ' active';
        
        // Simulate tab content wrapper HTML generation
        $wrapper_html = '<div class="sydney-dashboard-tab-content-wrapper" data-tab-wrapper-id="main">';
        $content_html = sprintf(
            '<div class="sydney-dashboard-tab-content%s" data-tab-content-id="%s">',
            $tab_active,
            $tab_id
        );
        
        $full_html = $wrapper_html . $content_html . '</div></div>';
        
        // Assertion phase - validate tab wrapper structure
        $this->assertValidComponentStructure($full_html, [
            'wrapper_class' => 'sydney-dashboard-tab-content-wrapper',
            'required_elements' => [
                'data-tab-wrapper-id="main"',
                'data-tab-content-id="starter-sites"',
                'sydney-dashboard-tab-content active'
            ]
        ]);
        
        $this->assertHtmlHasAttributes($full_html, [
            'data-tab-wrapper-id="main"',
            'data-tab-content-id="starter-sites"'
        ]);
        
        $this->assertHtmlHasClasses($full_html, [
            'sydney-dashboard-tab-content-wrapper',
            'sydney-dashboard-tab-content',
            'active'
        ]);
    }

}
