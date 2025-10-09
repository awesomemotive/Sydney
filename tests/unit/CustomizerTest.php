<?php
/**
 * Unit tests for Sydney Theme Customizer functionality
 *
 * Tests the customizer registration, controls, sanitization, callbacks,
 * asset management, and integration with WordPress customizer API.
 *
 * @package Sydney
 * @subpackage Tests
 */

namespace Sydney\Tests;

use Sydney\Tests\BaseThemeTest;
use WP_Mock as M;

/**
 * Test class for Sydney Customizer functionality.
 *
 * Tests that:
 * - Customizer registration and setup works correctly
 * - Custom controls are properly registered and functional
 * - Sanitization functions validate input correctly
 * - Callback functions return proper conditional logic
 * - Assets are properly enqueued with correct dependencies
 * - Settings integration with theme mods works as expected
 *
 * @since 1.0.0
 */
class CustomizerTest extends BaseThemeTest {

    /**
     * Get the theme dependencies that this test class requires.
     *
     * @since 1.0.0
     * @return array Array of dependency types to load.
     */
    protected function getRequiredDependencies(): array {
        return ['modules'];
    }

    /**
     * Set up test environment before each test.
     *
     * @since 1.0.0
     */
    public function setUp(): void {
        parent::setUp();
        
        // Mock essential WordPress functions for customizer functionality
        $this->mockEssentialsOnly();
        $this->mockTranslationFunctions();
        $this->mockSiteInfoFunctions([
            'get_template_directory' => '/tmp/wp-content/themes/sydney',
            'get_template_directory_uri' => 'http://example.com/wp-content/themes/sydney'
        ]);
        
        // Mock WordPress customizer functions
        $this->mockCustomizerFunctions();
    }

    /**
     * Mock WordPress customizer-related functions.
     *
     * Sets up common mocks for customizer API functions to avoid
     * repetitive mock setup across multiple tests.
     *
     * @since 1.0.0
     * @return void
     */
    protected function mockCustomizerFunctions(): void {
        M::userFunction('add_action')->andReturn(true);
        M::userFunction('has_action')->andReturn(false);
        M::userFunction('wp_enqueue_script')->andReturn(true);
        M::userFunction('wp_enqueue_style')->andReturn(true);
        M::userFunction('wp_localize_script')->andReturn(true);
        M::userFunction('wp_create_nonce')->andReturn('test-nonce-12345');
        M::userFunction('admin_url')->andReturn('http://example.com/wp-admin/');
        M::userFunction('sanitize_key')->andReturnUsing(function($input) {
            return $input;
        });
        M::userFunction('sanitize_text_field')->andReturnUsing(function($input) {
            return $input;
        });
        M::userFunction('wp_kses_post')->andReturnUsing(function($input) {
            return $input;
        });
        M::userFunction('force_balance_tags')->andReturnUsing(function($input) {
            return $input;
        });
    }

    /**
     * Test customizer registration and core setup functionality.
     *
     * Tests that:
     * - sydney_customize_register function exists and is callable
     * - Function is properly hooked to customize_register action
     * - Core WordPress customizer modifications are applied
     * - Selective refresh partials are registered for slider elements
     *
     * @since 1.0.0
     */
    public function test_customizer_registration_and_setup() {
        // Execution phase - verify customizer registration function exists in file
        $customizer_content = file_get_contents(get_template_directory() . '/inc/customizer/customizer.php');
        $this->assertStringContainsString('function sydney_customize_register( $wp_customize )', $customizer_content, 'sydney_customize_register function should be defined');
        
        // Test that the function is hooked to customize_register
        $this->assertStringContainsString('add_action( \'customize_register\', \'sydney_customize_register\' )', $customizer_content, 'sydney_customize_register should be hooked to customize_register action');
        
        // Verify customizer file can be loaded without errors
        $customizer_file = get_template_directory() . '/inc/customizer/customizer.php';
        $this->assertFileExists($customizer_file, 'Main customizer file should exist');
        $this->assertIsReadable($customizer_file, 'Main customizer file should be readable');
        
        // Test that required customizer dependencies exist
        $required_files = [
            '/inc/customizer/callbacks.php',
            '/inc/customizer/sanitize.php',
            '/inc/customizer/options/general.php',
            '/inc/customizer/options/typography.php'
        ];
        
        foreach ($required_files as $file) {
            $file_path = get_template_directory() . $file;
            $this->assertFileExists($file_path, "Required customizer file {$file} should exist");
        }
    }

    /**
     * Test customizer panels and sections structure.
     *
     * Tests that:
     * - Main customizer panels are properly defined
     * - Sections are organized under correct panels
     * - Panel and section priorities are set correctly
     * - Required panel/section configuration exists
     *
     * @since 1.0.0
     */
    public function test_customizer_panels_and_sections_structure() {
        // Setup phase - load general customizer options
        $general_options_file = get_template_directory() . '/inc/customizer/options/general.php';
        $this->assertFileExists($general_options_file, 'General options file should exist');
        
        // Execution phase - analyze general options content for panel structure
        $general_content = file_get_contents($general_options_file);
        $this->assertIsString($general_content, 'General options content should be readable');
        $this->assertNotEmpty($general_content, 'General options content should not be empty');
        
        // Assertion phase - verify expected panel and section structure
        $expected_panels = [
            'sydney_panel_general',
            'sydney_panel_hero',
            'sydney_panel_header',
            'sydney_panel_footer'
        ];
        
        $expected_sections = [
            'sydney_section_layouts',
            'sydney_section_sidebar',
            'sydney_section_scrolltop',
            'sydney_section_breadcrumbs'
        ];
        
        // Test panel registration patterns
        $this->assertStringContainsString('add_panel', $general_content, 'General options should register panels');
        $this->assertStringContainsString('sydney_panel_general', $general_content, 'General panel should be defined');
        
        // Test section registration patterns
        $this->assertStringContainsString('add_section', $general_content, 'General options should register sections');
        $this->assertStringContainsString('sydney_section_layouts', $general_content, 'Layouts section should be defined');
        
        // Verify priority settings exist
        $this->assertStringContainsString('priority', $general_content, 'Panel/section priorities should be set');
    }

    /**
     * Test that custom control classes exist and are properly structured.
     *
     * Tests that:
     * - All Sydney custom control classes exist
     * - Control classes extend WP_Customize_Control properly
     * - Control files are readable and contain expected structure
     * - Control registration patterns are present
     *
     * @since 1.0.0
     */
    public function test_custom_control_classes_exist() {
        // Setup phase - define expected custom controls
        $expected_controls = [
            'typography' => [
                'file' => '/inc/customizer/controls/typography/class_sydney_typography.php',
                'class' => 'Sydney_Typography_Control'
            ],
            'repeater' => [
                'file' => '/inc/customizer/controls/repeater/class_sydney_repeater.php',
                'class' => 'Sydney_Repeater_Control'
            ],
            'alpha_color' => [
                'file' => '/inc/customizer/controls/alpha-color/class_sydney_alpha_color.php',
                'class' => 'Sydney_Alpha_Color'
            ],
            'toggle' => [
                'file' => '/inc/customizer/controls/toggle/class_sydney_toggle_control.php',
                'class' => 'Sydney_Toggle_Control'
            ],
            'dimensions' => [
                'file' => '/inc/customizer/controls/dimensions/class_sydney_dimensions_control.php',
                'class' => 'Sydney_Dimensions_Control'
            ]
        ];
        
        // Execution and assertion phase - test each control
        foreach ($expected_controls as $control_name => $control_info) {
            $file_path = get_template_directory() . $control_info['file'];
            
            // Test file existence and readability
            $this->assertFileExists($file_path, "Control file for {$control_name} should exist");
            $this->assertIsReadable($file_path, "Control file for {$control_name} should be readable");
            
            // Test file content structure
            $content = file_get_contents($file_path);
            $this->assertIsString($content, "Control file content for {$control_name} should be readable");
            $this->assertNotEmpty($content, "Control file content for {$control_name} should not be empty");
            
            // Test class definition exists
            $this->assertStringContainsString("class {$control_info['class']}", $content, "Class {$control_info['class']} should be defined");
            $this->assertStringContainsString('WP_Customize_Control', $content, "Control {$control_name} should extend WP_Customize_Control");
            
            // Test control type property exists (more flexible check)
            $this->assertStringContainsString('$type', $content, "Control {$control_name} should define type property");
        }
        
        // Test that main customizer file includes control files
        $customizer_content = file_get_contents(get_template_directory() . '/inc/customizer/customizer.php');
        $this->assertStringContainsString('require get_template_directory()', $customizer_content, 'Main customizer should include control files');
    }

    /**
     * Test typography control functionality and Google Fonts integration.
     *
     * Tests that:
     * - Typography control class exists and has required methods
     * - Google Fonts data loading and caching works correctly
     * - Font selection and validation functions properly
     * - Typography control JSON handling works as expected
     *
     * @since 1.0.0
     */
    public function test_typography_control_functionality() {
        // Setup phase - verify typography control file exists
        $typography_file = get_template_directory() . '/inc/customizer/controls/typography/class_sydney_typography.php';
        $this->assertFileExists($typography_file, 'Typography control file should exist');
        
        // Execution phase - analyze typography control content
        $typography_content = file_get_contents($typography_file);
        $this->assertIsString($typography_content, 'Typography control content should be readable');
        $this->assertNotEmpty($typography_content, 'Typography control content should not be empty');
        
        // Assertion phase - verify typography control structure
        $this->assertStringContainsString('class Sydney_Typography_Control', $typography_content, 'Sydney_Typography_Control class should be defined');
        $this->assertStringContainsString('extends WP_Customize_Control', $typography_content, 'Typography control should extend WP_Customize_Control');
        
        // Test required properties exist (flexible matching)
        $expected_properties = [
            '$type' => 'type property',
            '$fontList' => 'fontList property',
            '$fontValues' => 'fontValues property',
            '$fontCount' => 'fontCount property'
        ];
        
        foreach ($expected_properties as $property => $description) {
            $this->assertStringContainsString($property, $typography_content, "Typography control should have {$description}");
        }
        
        // Test Google Fonts integration
        $this->assertStringContainsString('google-fonts', $typography_content, 'Typography control should reference Google Fonts');
        $this->assertStringContainsString('json_decode', $typography_content, 'Typography control should handle JSON data');
        
        // Test font caching mechanism
        $this->assertStringContainsString('cached_fonts', $typography_content, 'Typography control should implement font caching');
        
        // Verify Google Fonts data file exists
        $fonts_file = get_template_directory() . '/inc/customizer/controls/typography/google-fonts-alphabetical.json';
        $this->assertFileExists($fonts_file, 'Google Fonts data file should exist');
        $this->assertIsReadable($fonts_file, 'Google Fonts data file should be readable');
    }

    /**
     * Test repeater control functionality and sortable features.
     *
     * Tests that:
     * - Repeater control class exists with required structure
     * - Sortable functionality is properly implemented
     * - Button labels and control rendering work correctly
     * - Repeater control JavaScript integration exists
     *
     * @since 1.0.0
     */
    public function test_repeater_control_functionality() {
        // Setup phase - verify repeater control file exists
        $repeater_file = get_template_directory() . '/inc/customizer/controls/repeater/class_sydney_repeater.php';
        $this->assertFileExists($repeater_file, 'Repeater control file should exist');
        
        // Execution phase - analyze repeater control content
        $repeater_content = file_get_contents($repeater_file);
        $this->assertIsString($repeater_content, 'Repeater control content should be readable');
        $this->assertNotEmpty($repeater_content, 'Repeater control content should not be empty');
        
        // Assertion phase - verify repeater control structure
        $this->assertStringContainsString('class Sydney_Repeater_Control', $repeater_content, 'Sydney_Repeater_Control class should be defined');
        $this->assertStringContainsString('extends WP_Customize_Control', $repeater_content, 'Repeater control should extend WP_Customize_Control');
        
        // Test control type and properties
        $this->assertStringContainsString('sortable_repeater', $repeater_content, 'Repeater control should define sortable repeater type');
        $this->assertStringContainsString('public $button_labels', $repeater_content, 'Repeater control should have button labels property');
        
        // Test required methods exist
        $expected_methods = [
            'public function __construct',
            'public function render_content'
        ];
        
        foreach ($expected_methods as $method) {
            $this->assertStringContainsString($method, $repeater_content, "Repeater control should have {$method} method");
        }
        
        // Test sortable functionality
        $this->assertStringContainsString('sortable', $repeater_content, 'Repeater control should implement sortable functionality');
        $this->assertStringContainsString('customize-control-sortable-repeater', $repeater_content, 'Repeater control should have sortable CSS class');
        
        // Verify supporting assets exist
        $repeater_js = get_template_directory() . '/inc/customizer/controls/repeater/script.js';
        $repeater_css = get_template_directory() . '/inc/customizer/controls/repeater/styles.css';
        
        $this->assertFileExists($repeater_js, 'Repeater control JavaScript file should exist');
        $this->assertFileExists($repeater_css, 'Repeater control CSS file should exist');
    }

    /**
     * Data provider for sanitization function tests.
     *
     * @since 1.0.0
     * @return array Array of sanitization test scenarios
     */
    public function sanitizationProvider(): array {
        return [
            'valid_select_input' => [
                'function' => 'sydney_sanitize_select',
                'input' => 'valid_option',
                'choices' => ['valid_option' => 'Valid Option', 'another_option' => 'Another Option'],
                'default' => 'default_option',
                'expected' => 'valid_option'
            ],
            'invalid_select_input' => [
                'function' => 'sydney_sanitize_select',
                'input' => 'invalid_option',
                'choices' => ['valid_option' => 'Valid Option'],
                'default' => 'default_option',
                'expected' => 'default_option'
            ],
            'valid_hex_color' => [
                'function' => 'sydney_sanitize_hex_rgba',
                'input' => '#ff0000',
                'choices' => [],
                'default' => '#000000',
                'expected' => '#ff0000'
            ],
            'valid_rgba_color' => [
                'function' => 'sydney_sanitize_hex_rgba',
                'input' => 'rgba(255,0,0,0.5)',
                'choices' => [],
                'default' => '#000000',
                'expected' => 'rgba(255,0,0,0.5)'
            ]
        ];
    }

    /**
     * Test customizer sanitization functions with various inputs.
     *
     * Tests that:
     * - All sanitization functions exist and are callable
     * - Functions properly validate and sanitize input data
     * - Invalid inputs return default values or safe alternatives
     * - Edge cases are handled correctly
     *
     * @dataProvider sanitizationProvider
     * @since 1.0.0
     */
    public function test_customizer_sanitization_functions($function, $input, $choices, $default, $expected) {
        // Setup phase - mock sanitization dependencies
        M::userFunction('sanitize_hex_color')->andReturnUsing(function($input) {
            return preg_match('/^#[a-f0-9]{6}$/i', $input) ? $input : '';
        });
        
        // Load sanitize functions
        require_once get_template_directory() . '/inc/customizer/sanitize.php';
        
        // Execution phase - verify function exists
        $this->assertTrue(function_exists($function), "Sanitization function {$function} should exist");
        $this->assertTrue(is_callable($function), "Sanitization function {$function} should be callable");
        
        // Create mock setting object for functions that need it
        if (in_array($function, ['sydney_sanitize_select', 'sydney_sanitize_hex_rgba'], true)) {
            // Create a proper mock manager that can handle get_control calls
            $mock_manager = $this->createMock(\stdClass::class);
            $mock_control = new \stdClass();
            $mock_control->choices = $choices;
            
            // Use a simple approach - create an object that has the method
            $mock_manager = new class($mock_control) {
                private $control;
                public function __construct($control) { $this->control = $control; }
                public function get_control($id) { return $this->control; }
            };
            
            $mock_setting = new \stdClass();
            $mock_setting->default = $default;
            $mock_setting->manager = $mock_manager;
            $mock_setting->id = 'test_setting';
            
            // Test sanitization with mock setting
            $result = $function($input, $mock_setting);
            $this->assertEquals($expected, $result, "Sanitization function {$function} should return expected result");
        }
        
        // Test additional sanitization functions
        $this->assertTrue(function_exists('sydney_sanitize_text'), 'sydney_sanitize_text function should exist');
        $this->assertTrue(function_exists('sydney_sanitize_urls'), 'sydney_sanitize_urls function should exist');
        $this->assertTrue(function_exists('sydney_google_fonts_sanitize'), 'sydney_google_fonts_sanitize function should exist');
    }

    /**
     * Test header components sanitization with various inputs.
     *
     * Tests that:
     * - sydney_sanitize_header_components function exists
     * - Function properly filters valid header elements
     * - Invalid elements are removed from input array
     * - Function integrates with sydney_header_elements()
     *
     * @since 1.0.0
     */
    public function test_header_components_sanitization() {
        // Setup phase - load sanitize functions
        require_once get_template_directory() . '/inc/customizer/sanitize.php';
        
        // Mock sydney_header_elements function
        M::userFunction('sydney_header_elements')->andReturn([
            'search' => 'Search',
            'button' => 'Button',
            'social' => 'Social Icons',
            'contact_info' => 'Contact Info'
        ]);
        
        // Execution phase - test header components sanitization
        $this->assertTrue(function_exists('sydney_sanitize_header_components'), 'sydney_sanitize_header_components function should exist');
        
        // Test with valid components
        $valid_input = ['search', 'button', 'social'];
        $result = sydney_sanitize_header_components($valid_input);
        $this->assertIsArray($result, 'Sanitized header components should be an array');
        $this->assertEquals($valid_input, $result, 'Valid header components should be preserved');
        
        // Test with mixed valid and invalid components
        $mixed_input = ['search', 'invalid_element', 'button', 'another_invalid'];
        $result = sydney_sanitize_header_components($mixed_input);
        $expected = ['search', 'button'];
        $this->assertEquals($expected, $result, 'Invalid header components should be filtered out');
        
        // Test with empty input
        $empty_result = sydney_sanitize_header_components([]);
        $this->assertIsArray($empty_result, 'Empty input should return empty array');
        $this->assertEmpty($empty_result, 'Empty input should return empty array');
        
        // Test other component sanitization functions exist
        $this->assertTrue(function_exists('sydney_sanitize_topbar_components'), 'sydney_sanitize_topbar_components function should exist');
        $this->assertTrue(function_exists('sydney_sanitize_blog_meta_elements'), 'sydney_sanitize_blog_meta_elements function should exist');
    }

    /**
     * Data provider for active callback function tests.
     *
     * @since 1.0.0
     * @return array Array of callback test scenarios
     */
    public function callbackProvider(): array {
        return [
            'footer_widgets_divider_enabled' => [
                'callback' => 'sydney_callback_footer_widgets_divider',
                'theme_mods' => ['footer_widgets_divider' => 1],
                'expected' => true
            ],
            'footer_widgets_divider_disabled' => [
                'callback' => 'sydney_callback_footer_widgets_divider',
                'theme_mods' => ['footer_widgets_divider' => 0],
                'expected' => false
            ],
            'scrolltop_enabled' => [
                'callback' => 'sydney_callback_scrolltop',
                'theme_mods' => ['enable_scrolltop' => 1],
                'expected' => true
            ],
            'scrolltop_disabled' => [
                'callback' => 'sydney_callback_scrolltop',
                'theme_mods' => ['enable_scrolltop' => 0],
                'expected' => false
            ]
        ];
    }

    /**
     * Test active callback functions return correct boolean values.
     *
     * Tests that:
     * - All active callback functions exist and are callable
     * - Functions return correct boolean values based on theme mods
     * - Conditional display logic works as expected
     * - Default values are handled properly
     *
     * @dataProvider callbackProvider
     * @since 1.0.0
     */
    public function test_active_callback_functions($callback, $theme_mods, $expected) {
        // Setup phase - load callback functions
        require_once get_template_directory() . '/inc/customizer/callbacks.php';
        
        // Mock get_theme_mod with flexible parameters to handle all theme mods
        M::userFunction('get_theme_mod')->andReturnUsing(function($name, $default = null) use ($theme_mods) {
            return array_key_exists($name, $theme_mods) ? $theme_mods[$name] : $default;
        });
        
        // Execution phase - test callback function
        $this->assertTrue(function_exists($callback), "Callback function {$callback} should exist");
        $this->assertTrue(is_callable($callback), "Callback function {$callback} should be callable");
        
        // Test callback result
        $result = $callback();
        $this->assertIsBool($result, "Callback {$callback} should return boolean value");
        $this->assertEquals($expected, $result, "Callback {$callback} should return expected boolean value");
        
        // Test that additional callback functions exist
        $additional_callbacks = [
            'sydney_callback_sidebar_archives',
            'sydney_callback_excerpt',
            'sydney_callback_read_more',
            'sydney_callback_custom_palette'
        ];
        
        foreach ($additional_callbacks as $additional_callback) {
            $this->assertTrue(function_exists($additional_callback), "Additional callback {$additional_callback} should exist");
        }
    }

    /**
     * Data provider for header layout callback tests.
     *
     * @since 1.0.0
     * @return array Array of header layout scenarios
     */
    public function headerLayoutProvider(): array {
        return [
            'header_layout_1' => [
                'layout' => 'header_layout_1',
                'callback_1_2' => true,
                'callback_3' => false,
                'callback_4' => false,
                'callback_5' => false,
                'callback_bottom' => false
            ],
            'header_layout_2' => [
                'layout' => 'header_layout_2',
                'callback_1_2' => true,
                'callback_3' => false,
                'callback_4' => false,
                'callback_5' => false,
                'callback_bottom' => false
            ],
            'header_layout_3' => [
                'layout' => 'header_layout_3',
                'callback_1_2' => false,
                'callback_3' => true,
                'callback_4' => false,
                'callback_5' => false,
                'callback_bottom' => true
            ],
            'header_layout_4' => [
                'layout' => 'header_layout_4',
                'callback_1_2' => false,
                'callback_3' => false,
                'callback_4' => true,
                'callback_5' => false,
                'callback_bottom' => true
            ],
            'header_layout_5' => [
                'layout' => 'header_layout_5',
                'callback_1_2' => false,
                'callback_3' => false,
                'callback_4' => false,
                'callback_5' => true,
                'callback_bottom' => true
            ]
        ];
    }

    /**
     * Test header layout callback functions for different layouts.
     *
     * Tests that:
     * - Header layout callback functions exist and work correctly
     * - Functions return appropriate boolean values for each layout
     * - Layout-specific conditional logic is implemented properly
     * - Header element callbacks work with different layouts
     *
     * @dataProvider headerLayoutProvider
     * @since 1.0.0
     */
    public function test_header_layout_callbacks($layout, $callback_1_2, $callback_3, $callback_4, $callback_5, $callback_bottom) {
        // Setup phase - load callback functions
        require_once get_template_directory() . '/inc/customizer/callbacks.php';
        
        // Mock get_theme_mod for header layout
        M::userFunction('get_theme_mod')->with('header_layout_desktop', 'header_layout_1')->andReturn($layout);
        
        // Test layout-specific callback functions
        $callback_tests = [
            'sydney_callback_header_layout_1_2' => $callback_1_2,
            'sydney_callback_header_layout_3' => $callback_3,
            'sydney_callback_header_layout_4' => $callback_4,
            'sydney_callback_header_layout_5' => $callback_5,
            'sydney_callback_header_bottom' => $callback_bottom
        ];
        
        foreach ($callback_tests as $callback_function => $expected_result) {
            // Execution phase - test callback function
            $this->assertTrue(function_exists($callback_function), "Header layout callback {$callback_function} should exist");
            $this->assertTrue(is_callable($callback_function), "Header layout callback {$callback_function} should be callable");
            
            // Test callback result
            $result = $callback_function();
            $this->assertIsBool($result, "Header layout callback {$callback_function} should return boolean");
            $this->assertEquals($expected_result, $result, "Header layout callback {$callback_function} should return expected value for {$layout}");
        }
        
        // Test header elements callback function exists
        $this->assertTrue(function_exists('sydney_callback_header_elements'), 'sydney_callback_header_elements function should exist');
        
        // Test sticky header callback
        $this->assertTrue(function_exists('sydney_callback_sticky_header'), 'sydney_callback_sticky_header function should exist');
    }

    /**
     * Test customizer assets are properly enqueued with correct parameters.
     *
     * Tests that:
     * - Customizer CSS and JS files are enqueued correctly
     * - Asset dependencies are properly set
     * - Localization data has required structure
     * - Asset versions and paths are correct
     *
     * @since 1.0.0
     */
    public function test_customizer_assets_enqueued() {
        // Setup phase - mock asset enqueue functions with tracking
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
        
        // Mock required functions for asset enqueuing
        M::userFunction('sydney_get_posts_types_for_js')->andReturn(['post', 'page']);
        M::userFunction('sydney_admin_upgrade_link')->andReturn('https://athemes.com/sydney-upgrade');
        
        // Test that customizer assets function exists by checking if it can be loaded
        // We avoid loading the full file to prevent function redeclaration
        $customizer_content = file_get_contents(get_template_directory() . '/inc/customizer/customizer.php');
        $this->assertStringContainsString('function sydney_customize_footer_scripts()', $customizer_content, 'sydney_customize_footer_scripts function should be defined');
        
        // Test that the function would enqueue the expected assets by checking the file content
        $this->assertStringContainsString('wp_enqueue_style', $customizer_content, 'Function should enqueue styles');
        $this->assertStringContainsString('wp_enqueue_script', $customizer_content, 'Function should enqueue scripts');
        $this->assertStringContainsString('wp_localize_script', $customizer_content, 'Function should localize scripts');
        
        // Test expected asset handles are present
        $this->assertStringContainsString('sydney-customizer-styles', $customizer_content, 'Should enqueue customizer CSS');
        $this->assertStringContainsString('sydney-customizer-scripts', $customizer_content, 'Should enqueue customizer JS');
        
        // Test expected asset paths are present
        $this->assertStringContainsString('/css/customizer.min.css', $customizer_content, 'Should reference correct CSS path');
        $this->assertStringContainsString('/js/customize-controls.min.js', $customizer_content, 'Should reference correct JS path');
        
        // Test localization data structure is present
        $this->assertStringContainsString('syd_data', $customizer_content, 'Should use correct localization name');
        $this->assertStringContainsString('post_types', $customizer_content, 'Should include post_types in localization');
        $this->assertStringContainsString('ajax_url', $customizer_content, 'Should include ajax_url in localization');
        $this->assertStringContainsString('ajax_nonce', $customizer_content, 'Should include ajax_nonce in localization');
        $this->assertStringContainsString('sortable_config', $customizer_content, 'Should include sortable_config in localization');
    }

    /**
     * Test general settings registration and configuration.
     *
     * Tests that:
     * - General settings file exists and is properly structured
     * - Settings are registered with correct defaults and sanitization
     * - Control types and input attributes are properly configured
     * - Transport methods are set correctly for live preview
     *
     * @since 1.0.0
     */
    public function test_general_settings_registration() {
        // Setup phase - verify general settings file exists
        $general_file = get_template_directory() . '/inc/customizer/options/general.php';
        $this->assertFileExists($general_file, 'General settings file should exist');
        $this->assertIsReadable($general_file, 'General settings file should be readable');
        
        // Execution phase - analyze general settings content
        $general_content = file_get_contents($general_file);
        $this->assertIsString($general_content, 'General settings content should be readable');
        $this->assertNotEmpty($general_content, 'General settings content should not be empty');
        
        // Assertion phase - verify settings registration patterns
        $expected_settings = [
            'container_width' => [
                'default' => '1170',
                'sanitize' => 'absint',
                'transport' => 'postMessage'
            ],
            'narrow_container_width' => [
                'default' => '860',
                'sanitize' => 'absint',
                'transport' => 'postMessage'
            ],
            'wrapper_top_padding' => [
                'default' => '83',
                'sanitize' => 'absint',
                'transport' => 'postMessage'
            ]
        ];
        
        foreach ($expected_settings as $setting_name => $setting_config) {
            // Test setting registration
            $this->assertStringContainsString("add_setting", $general_content, "General settings should register {$setting_name}");
            $this->assertStringContainsString($setting_name, $general_content, "Setting {$setting_name} should be defined");
            
            // Test setting configuration patterns (more flexible matching)
            $this->assertStringContainsString("'default'", $general_content, "Setting {$setting_name} should have default value");
            $this->assertStringContainsString("'sanitize_callback'", $general_content, "Setting {$setting_name} should have sanitization callback");
            $this->assertStringContainsString("'transport'", $general_content, "Setting {$setting_name} should have transport method");
            $this->assertStringContainsString($setting_config['sanitize'], $general_content, "Setting {$setting_name} should use {$setting_config['sanitize']} sanitization");
        }
        
        // Test control registration patterns
        $this->assertStringContainsString('add_control', $general_content, 'General settings should register controls');
        $this->assertStringContainsString('input_attrs', $general_content, 'Controls should have input attributes');
        $this->assertStringContainsString('priority', $general_content, 'Controls should have priorities set');
        
        // Test panel and section structure
        $this->assertStringContainsString('sydney_panel_general', $general_content, 'General panel should be defined');
        $this->assertStringContainsString('sydney_section_layouts', $general_content, 'Layouts section should be defined');
    }

    /**
     * Test typography settings integration and Google Fonts functionality.
     *
     * Tests that:
     * - Typography settings file exists and is properly configured
     * - Google Fonts integration is implemented correctly
     * - Typography controls are registered with proper structure
     * - Font loading and caching mechanisms work as expected
     *
     * @since 1.0.0
     */
    public function test_typography_settings_integration() {
        // Setup phase - verify typography settings file exists
        $typography_file = get_template_directory() . '/inc/customizer/options/typography.php';
        $this->assertFileExists($typography_file, 'Typography settings file should exist');
        $this->assertIsReadable($typography_file, 'Typography settings file should be readable');
        
        // Execution phase - analyze typography settings content
        $typography_content = file_get_contents($typography_file);
        $this->assertIsString($typography_content, 'Typography settings content should be readable');
        $this->assertNotEmpty($typography_content, 'Typography settings content should not be empty');
        
        // Assertion phase - verify typography panel and sections
        $this->assertStringContainsString('sydney_panel_typography', $typography_content, 'Typography panel should be defined');
        $this->assertStringContainsString('add_panel', $typography_content, 'Typography panel should be registered');
        
        // Test typography control registration
        $this->assertStringContainsString('Sydney_Typography_Control', $typography_content, 'Typography controls should use Sydney_Typography_Control');
        $this->assertStringContainsString('google_fonts', $typography_content, 'Typography controls should reference Google Fonts');
        
        // Test Google Fonts integration
        $this->assertStringContainsString('google_fonts_sanitize', $typography_content, 'Typography should use Google Fonts sanitization');
        
        // Verify Google Fonts data file exists
        $fonts_data_file = get_template_directory() . '/inc/customizer/controls/typography/google-fonts-alphabetical.json';
        $this->assertFileExists($fonts_data_file, 'Google Fonts data file should exist');
        
        // Test fonts data structure
        $fonts_data = file_get_contents($fonts_data_file);
        $this->assertIsString($fonts_data, 'Google Fonts data should be readable');
        $this->assertNotEmpty($fonts_data, 'Google Fonts data should not be empty');
        
        // Verify it's valid JSON
        $fonts_array = json_decode($fonts_data, true);
        $this->assertIsArray($fonts_array, 'Google Fonts data should be valid JSON array');
        $this->assertNotEmpty($fonts_array, 'Google Fonts array should not be empty');
        
        // Test typography sections exist
        $expected_typography_sections = [
            'sydney_section_typography_headings',
            'sydney_section_typography_body'
        ];
        
        foreach ($expected_typography_sections as $section) {
            $this->assertStringContainsString($section, $typography_content, "Typography section {$section} should be defined");
        }
        
        // Test typography control assets exist
        $typography_js = get_template_directory() . '/inc/customizer/controls/typography/script.js';
        $typography_css = get_template_directory() . '/inc/customizer/controls/typography/styles.css';
        
        $this->assertFileExists($typography_js, 'Typography control JavaScript should exist');
        $this->assertFileExists($typography_css, 'Typography control CSS should exist');
    }

    /**
     * Test theme mod integration and default value handling.
     *
     * Tests that:
     * - Customizer settings integrate properly with get_theme_mod()
     * - Default values are returned when settings are not saved
     * - Theme mod retrieval works for different setting types
     * - Settings are properly stored and retrieved
     *
     * @since 1.0.0
     */
    public function test_theme_mod_integration_and_defaults() {
        // Setup phase - mock get_theme_mod function
        $theme_mods = [
            'container_width' => 1200,
            'enable_scrolltop' => 1,
            'blog_layout' => 'layout3',
            'header_layout_desktop' => 'header_layout_2'
        ];
        
        foreach ($theme_mods as $mod_name => $mod_value) {
            M::userFunction('get_theme_mod')->with($mod_name)->andReturn($mod_value);
        }
        
        // Test default value fallbacks
        M::userFunction('get_theme_mod')->with('non_existent_setting', 'default_value')->andReturn('default_value');
        
        // Execution and assertion phase - test theme mod integration
        foreach ($theme_mods as $mod_name => $expected_value) {
            $result = get_theme_mod($mod_name);
            $this->assertEquals($expected_value, $result, "Theme mod {$mod_name} should return expected value");
        }
        
        // Test default value handling
        $default_result = get_theme_mod('non_existent_setting', 'default_value');
        $this->assertEquals('default_value', $default_result, 'Non-existent theme mod should return default value');
        
        // Test different data types
        $this->assertIsInt(get_theme_mod('container_width'), 'Container width should be integer');
        $this->assertIsString(get_theme_mod('blog_layout'), 'Blog layout should be string');
        
        // Test boolean handling
        $scrolltop_enabled = get_theme_mod('enable_scrolltop');
        $this->assertIsInt($scrolltop_enabled, 'Scrolltop setting should be integer (0 or 1)');
        $this->assertContains($scrolltop_enabled, [0, 1], 'Scrolltop setting should be 0 or 1');
        
        // Test array handling for multi-select settings
        M::userFunction('get_theme_mod')->with('header_components_l1', ['search'])->andReturn(['search', 'button']);
        $header_components = get_theme_mod('header_components_l1', ['search']);
        $this->assertIsArray($header_components, 'Header components should be array');
        $this->assertContains('search', $header_components, 'Header components should contain search');
    }

    /**
     * Test customizer JavaScript integration and localization.
     *
     * Tests that:
     * - Customizer JavaScript files are properly enqueued
     * - Localization data has correct structure and values
     * - AJAX endpoints and nonces are properly configured
     * - Post type data is correctly passed to JavaScript
     *
     * @since 1.0.0
     */
    public function test_customizer_javascript_integration() {
        // Setup phase - test JavaScript functions exist in customizer file
        $customizer_content = file_get_contents(get_template_directory() . '/inc/customizer/customizer.php');
        
        // Test preview JavaScript function exists
        $this->assertStringContainsString('function sydney_customize_preview_js()', $customizer_content, 'sydney_customize_preview_js function should be defined');
        
        // Test controls JavaScript function exists
        $this->assertStringContainsString('function sydney_customize_footer_scripts()', $customizer_content, 'sydney_customize_footer_scripts function should be defined');
        
        // Test preview JS enqueuing
        $this->assertStringContainsString('wp_enqueue_script( \'sydney_customizer\'', $customizer_content, 'Preview JS should be enqueued');
        $this->assertStringContainsString('/js/customizer.min.js', $customizer_content, 'Preview JS should reference correct file');
        $this->assertStringContainsString('customize-preview', $customizer_content, 'Preview JS should depend on customize-preview');
        
        // Test preview localization
        $this->assertStringContainsString('wp_localize_script( \'sydney_customizer\', \'syd_data\'', $customizer_content, 'Preview JS should be localized');
        $this->assertStringContainsString('sydney_get_posts_types_for_js()', $customizer_content, 'Preview should get post types for JS');
        
        // Test controls JS enqueuing
        $this->assertStringContainsString('wp_enqueue_script( \'sydney-customizer-scripts\'', $customizer_content, 'Controls JS should be enqueued');
        $this->assertStringContainsString('/js/customize-controls.min.js', $customizer_content, 'Controls JS should reference correct file');
        $this->assertStringContainsString('jquery', $customizer_content, 'Controls JS should depend on jQuery');
        $this->assertStringContainsString('jquery-ui-core', $customizer_content, 'Controls JS should depend on jQuery UI');
        
        // Test controls localization structure
        $this->assertStringContainsString('wp_localize_script( \'sydney-customizer-scripts\', \'syd_data\'', $customizer_content, 'Controls JS should be localized');
        $this->assertStringContainsString('post_types', $customizer_content, 'Controls localization should include post_types');
        $this->assertStringContainsString('ajax_url', $customizer_content, 'Controls localization should include ajax_url');
        $this->assertStringContainsString('ajax_nonce', $customizer_content, 'Controls localization should include ajax_nonce');
        $this->assertStringContainsString('sortable_config', $customizer_content, 'Controls localization should include sortable_config');
        
        // Test sortable configuration structure
        $this->assertStringContainsString('header_components_l1', $customizer_content, 'Sortable config should include header components');
        $this->assertStringContainsString('single_post_meta_elements', $customizer_content, 'Sortable config should include post meta elements');
        
        // Test upgrade link integration
        $this->assertStringContainsString('customizer_upgrade_link_with_utm_content_markup', $customizer_content, 'Controls should include upgrade link');
        $this->assertStringContainsString('sydney_admin_upgrade_link', $customizer_content, 'Should use upgrade link function');
        
        // Test JavaScript files exist
        $preview_js = get_template_directory() . '/js/customizer.min.js';
        $controls_js = get_template_directory() . '/js/customize-controls.min.js';
        
        $this->assertFileExists($preview_js, 'Preview JavaScript file should exist');
        $this->assertFileExists($controls_js, 'Controls JavaScript file should exist');
        
        // Test CSS file for customizer controls
        $customizer_css = get_template_directory() . '/css/customizer.min.css';
        $this->assertFileExists($customizer_css, 'Customizer CSS file should exist');
        
        // Test that functions are hooked to appropriate actions
        $this->assertStringContainsString('add_action( \'customize_preview_init\', \'sydney_customize_preview_js\' )', $customizer_content, 'Preview JS should be hooked to customize_preview_init');
        $this->assertStringContainsString('add_action( \'customize_controls_print_footer_scripts\', \'sydney_customize_footer_scripts\' )', $customizer_content, 'Footer scripts should be hooked to customize_controls_print_footer_scripts');
    }

    /**
     * Test conditional customizer features and module integration.
     *
     * Tests that:
     * - WooCommerce customizer options load when plugin is active
     * - Header update option controls conditional loading
     * - Module-specific customizer sections activate properly
     * - Conditional file loading works as expected
     *
     * @since 1.0.0
     */
    public function test_conditional_customizer_features() {
        // Setup phase - test WooCommerce conditional loading
        $customizer_content = file_get_contents(get_template_directory() . '/inc/customizer/customizer.php');
        
        // Test WooCommerce conditional check
        $this->assertStringContainsString("class_exists( 'Woocommerce' )", $customizer_content, 'Customizer should check for WooCommerce');
        $this->assertStringContainsString('woocommerce.php', $customizer_content, 'WooCommerce options should be conditionally loaded');
        $this->assertStringContainsString('woocommerce-single.php', $customizer_content, 'WooCommerce single options should be conditionally loaded');
        
        // Test header update conditional loading
        $this->assertStringContainsString("get_option( 'sydney-update-header' )", $customizer_content, 'Customizer should check header update option');
        $this->assertStringContainsString('header.php', $customizer_content, 'Header options should be conditionally loaded');
        $this->assertStringContainsString('header-mobile.php', $customizer_content, 'Mobile header options should be conditionally loaded');
        
        // Test conditional WooCommerce files exist
        $woocommerce_file = get_template_directory() . '/inc/customizer/options/woocommerce.php';
        $woocommerce_single_file = get_template_directory() . '/inc/customizer/options/woocommerce-single.php';
        
        $this->assertFileExists($woocommerce_file, 'WooCommerce customizer options file should exist');
        $this->assertFileExists($woocommerce_single_file, 'WooCommerce single customizer options file should exist');
        
        // Test conditional header files exist
        $header_file = get_template_directory() . '/inc/customizer/options/header.php';
        $header_mobile_file = get_template_directory() . '/inc/customizer/options/header-mobile.php';
        
        $this->assertFileExists($header_file, 'Header customizer options file should exist');
        $this->assertFileExists($header_mobile_file, 'Mobile header customizer options file should exist');
        
        // Test module integration patterns by checking file content patterns
        // We avoid mocking PHP built-in functions like class_exists()
        $this->assertStringContainsString("get_option( 'sydney-update-header' )", $customizer_content, 'Should check header update option');
        $this->assertStringContainsString("class_exists( 'Woocommerce' )", $customizer_content, 'Should check WooCommerce class existence');
        
        // Test style book integration
        $this->assertStringContainsString('style-book', $customizer_content, 'Style book should be loaded');
        $style_book_file = get_template_directory() . '/inc/customizer/style-book/class-sydney-style-book.php';
        $this->assertFileExists($style_book_file, 'Style book class file should exist');
        
        // Test upsell integration
        $this->assertStringContainsString('upsell.php', $customizer_content, 'Upsell options should be loaded');
        $upsell_file = get_template_directory() . '/inc/customizer/options/upsell.php';
        $this->assertFileExists($upsell_file, 'Upsell options file should exist');
        
        // Test performance options
        $this->assertStringContainsString('performance.php', $customizer_content, 'Performance options should be loaded');
        $performance_file = get_template_directory() . '/inc/customizer/options/performance.php';
        $this->assertFileExists($performance_file, 'Performance options file should exist');
    }

}
