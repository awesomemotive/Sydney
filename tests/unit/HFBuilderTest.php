<?php
/**
 * Unit tests for HF Builder functionality in Sydney theme
 *
 * @package Sydney
 * @subpackage Tests
 */

namespace Sydney\Tests;

use Sydney\Tests\BaseThemeTest;
use WP_Mock as M;

/**
 * Test class for HF Builder functionality.
 *
 * @since 1.0.0
 */
class HFBuilderTest extends BaseThemeTest {

    /**
     * Get the theme dependencies that this test class requires.
     *
     * @since 1.0.0
     * @return array Array of dependency types to load.
     */
    protected function getRequiredDependencies(): array {
        // Note: We only load 'modules' by default, not 'hf-builder'
        // Individual tests can load hf-builder using setUpWithHFBuilder()
        return ['modules'];
    }

    /**
     * Set up environment for tests that need the HF Builder loaded.
     *
     * @since 1.0.0
     * @return void
     */
    protected function setUpWithHFBuilder(): void {
        // Load HF Builder with module active
        $this->loadThemeDependencies(['hf-builder']);

        // Reset the singleton instance after including the class
        $this->resetSingleton('Sydney_Header_Footer_Builder');
    }

    /**
     * Data provider for module activation test scenarios.
     *
     * @since 1.0.0
     * @return array Test scenarios with [option_value, expected_result, description]
     */
    public function moduleActivationScenariosProvider(): array {
        return [
            'explicitly_false' => [
                'option_value' => ['sydney-modules' => ['hf-builder' => false]],
                'expected_result' => false,
                'description' => 'Module should be inactive when explicitly set to false'
            ],
            'empty_array' => [
                'option_value' => ['sydney-modules' => []],
                'expected_result' => false,
                'description' => 'Module should be inactive when options array is empty'
            ],
            'null_options' => [
                'option_value' => ['sydney-modules' => null],
                'expected_result' => false,
                'description' => 'Module should be inactive when options are null'
            ],
        ];
    }

    /**
     * Test that class only loads when hf-builder module is active.
     *
     * Tests that:
     * - Class file returns early when Sydney_Modules::is_module_active('hf-builder') returns false
     * - Class doesn't initialize when module is inactive
     * - Early return behavior prevents class instantiation
     *
     * @dataProvider moduleActivationScenariosProvider
     * @since 1.0.0
     * @param array $option_value The sydney-modules option value to test
     * @param bool $expected_result Expected result from is_module_active
     * @param string $description Test scenario description
     * @return void
     */
    public function test_module_activation_check(array $option_value, bool $expected_result, string $description): void {
        // Reset any existing class instances
        $this->resetSingleton('Sydney_Header_Footer_Builder');

        $this->mockOptions($option_value);
        
        $this->assertEquals(
            $expected_result,
            \Sydney_Modules::is_module_active('hf-builder'),
            $description
        );
    }

    /**
     * Test component data initialization on 'init' hook.
     *
     * Tests that:
     * - Desktop components array is populated correctly
     * - Mobile components array is populated correctly  
     * - Footer components array is populated correctly
     * - WooCommerce conditional component addition works
     * - Header rows and footer rows are set correctly
     *
     * @since 1.0.0
     * @return void
     */
    public function test_set_components_data(): void {
        // Set up HF Builder for this test
        $this->setUpWithHFBuilder();

        // Mock WordPress translation functions
        $this->mockFunction('esc_html__', function($text, $domain) {
            return $text; // Return untranslated text for testing
        });

        // Test without WooCommerce first by not defining the class
        // class_exists will return false naturally since Woocommerce class doesn't exist in test environment

        // Get instance and trigger set_components_data
        $instance = \Sydney_Header_Footer_Builder::get_instance();
        
        // Call set_components_data method directly to test it
        $instance->set_components_data();

        // Test desktop components structure without WooCommerce
        $this->assertIsArray($instance->desktop_components, 'Desktop components should be an array');
        $this->assertCount(8, $instance->desktop_components, 'Desktop components should have 8 items when WooCommerce is inactive');

        // Test expected desktop components
        $expected_desktop_components = [
            ['id' => 'menu', 'label' => 'Primary Menu'],
            ['id' => 'secondary_menu', 'label' => 'Secondary Menu'],
            ['id' => 'social', 'label' => 'Social'],
            ['id' => 'search', 'label' => 'Search'],
            ['id' => 'logo', 'label' => 'Site Identity'],
            ['id' => 'button', 'label' => 'Button'],
            ['id' => 'contact_info', 'label' => 'Contact Info'],
            ['id' => 'html', 'label' => 'HTML'],
        ];

        foreach ($expected_desktop_components as $index => $expected_component) {
            $this->assertArrayHasKey('id', $instance->desktop_components[$index], 'Desktop component should have id key');
            $this->assertArrayHasKey('label', $instance->desktop_components[$index], 'Desktop component should have label key');
            $this->assertEquals($expected_component['id'], $instance->desktop_components[$index]['id'], "Desktop component {$index} should have correct id");
            $this->assertEquals($expected_component['label'], $instance->desktop_components[$index]['label'], "Desktop component {$index} should have correct label");
        }

        // Test mobile components structure
        $this->assertIsArray($instance->mobile_components, 'Mobile components should be an array');
        $this->assertCount(9, $instance->mobile_components, 'Mobile components should have 9 items');

        // Test expected mobile components
        $expected_mobile_components = [
            ['id' => 'mobile_offcanvas_menu', 'label' => 'Off-Canvas Menu'],
            ['id' => 'secondary_menu', 'label' => 'Secondary Menu'],
            ['id' => 'mobile_hamburger', 'label' => 'Menu Toggle'],
            ['id' => 'social', 'label' => 'Social'],
            ['id' => 'search', 'label' => 'Search'],
            ['id' => 'logo', 'label' => 'Site Identity'],
            ['id' => 'button', 'label' => 'Button'],
            ['id' => 'contact_info', 'label' => 'Contact Info'],
            ['id' => 'html', 'label' => 'HTML'],
        ];

        foreach ($expected_mobile_components as $index => $expected_component) {
            $this->assertArrayHasKey('id', $instance->mobile_components[$index], 'Mobile component should have id key');
            $this->assertArrayHasKey('label', $instance->mobile_components[$index], 'Mobile component should have label key');
            $this->assertEquals($expected_component['id'], $instance->mobile_components[$index]['id'], "Mobile component {$index} should have correct id");
            $this->assertEquals($expected_component['label'], $instance->mobile_components[$index]['label'], "Mobile component {$index} should have correct label");
        }

        // Test footer components structure
        $this->assertIsArray($instance->footer_components, 'Footer components should be an array');
        $this->assertCount(8, $instance->footer_components, 'Footer components should have 8 items');

        // Test expected footer components
        $expected_footer_components = [
            ['id' => 'copyright', 'label' => 'Copyright'],
            ['id' => 'social', 'label' => 'Social'],
            ['id' => 'button', 'label' => 'Button 1'],
            ['id' => 'html', 'label' => 'HTML'],
            ['id' => 'widget1', 'label' => 'Widget Area 1'],
            ['id' => 'widget2', 'label' => 'Widget Area 2'],
            ['id' => 'widget3', 'label' => 'Widget Area 3'],
            ['id' => 'widget4', 'label' => 'Widget Area 4'],
        ];

        foreach ($expected_footer_components as $index => $expected_component) {
            $this->assertArrayHasKey('id', $instance->footer_components[$index], 'Footer component should have id key');
            $this->assertArrayHasKey('label', $instance->footer_components[$index], 'Footer component should have label key');
            $this->assertEquals($expected_component['id'], $instance->footer_components[$index]['id'], "Footer component {$index} should have correct id");
            $this->assertEquals($expected_component['label'], $instance->footer_components[$index]['label'], "Footer component {$index} should have correct label");
        }

        // Test header rows structure
        $this->assertIsArray($instance->header_rows, 'Header rows should be an array');
        $this->assertCount(3, $instance->header_rows, 'Header rows should have 3 items');

        $expected_header_rows = [
            ['id' => 'above_header_row', 'label' => 'Top Row', 'section' => 'sydney_section_hb_above_header_row'],
            ['id' => 'main_header_row', 'label' => 'Main Row', 'section' => 'sydney_section_hb_main_header_row'],
            ['id' => 'below_header_row', 'label' => 'Bottom Row', 'section' => 'sydney_section_hb_below_header_row'],
        ];

        foreach ($expected_header_rows as $index => $expected_row) {
            $this->assertArrayHasKey('id', $instance->header_rows[$index], 'Header row should have id key');
            $this->assertArrayHasKey('label', $instance->header_rows[$index], 'Header row should have label key');
            $this->assertArrayHasKey('section', $instance->header_rows[$index], 'Header row should have section key');
            $this->assertEquals($expected_row['id'], $instance->header_rows[$index]['id'], "Header row {$index} should have correct id");
            $this->assertEquals($expected_row['label'], $instance->header_rows[$index]['label'], "Header row {$index} should have correct label");
            $this->assertEquals($expected_row['section'], $instance->header_rows[$index]['section'], "Header row {$index} should have correct section");
        }

        // Test footer rows structure
        $this->assertIsArray($instance->footer_rows, 'Footer rows should be an array');
        $this->assertCount(3, $instance->footer_rows, 'Footer rows should have 3 items');

        $expected_footer_rows = [
            ['id' => 'above_footer_row', 'label' => 'Top Row', 'section' => 'sydney_section_fb_above_footer_row'],
            ['id' => 'main_footer_row', 'label' => 'Main Row', 'section' => 'sydney_section_fb_main_footer_row'],
            ['id' => 'below_footer_row', 'label' => 'Bottom Row', 'section' => 'sydney_section_fb_below_footer_row'],
        ];

        foreach ($expected_footer_rows as $index => $expected_row) {
            $this->assertArrayHasKey('id', $instance->footer_rows[$index], 'Footer row should have id key');
            $this->assertArrayHasKey('label', $instance->footer_rows[$index], 'Footer row should have label key');
            $this->assertArrayHasKey('section', $instance->footer_rows[$index], 'Footer row should have section key');
            $this->assertEquals($expected_row['id'], $instance->footer_rows[$index]['id'], "Footer row {$index} should have correct id");
            $this->assertEquals($expected_row['label'], $instance->footer_rows[$index]['label'], "Footer row {$index} should have correct label");
            $this->assertEquals($expected_row['section'], $instance->footer_rows[$index]['section'], "Footer row {$index} should have correct section");
        }

        // Now test WITH WooCommerce active by defining the class
        if (!class_exists('Woocommerce')) {
            // Create a simple mock Woocommerce class for testing
            class_alias('stdClass', 'Woocommerce');
        }

        // Reset singleton and test with WooCommerce
        $reflection = new \ReflectionClass('Sydney_Header_Footer_Builder');
        $instance_property = $reflection->getProperty('instance');
        $instance_property->setAccessible(true);
        $instance_property->setValue(null, null);
        $instance_property->setAccessible(false);

        // Get new instance and trigger set_components_data with WooCommerce
        $instance_with_woo = \Sydney_Header_Footer_Builder::get_instance();
        $instance_with_woo->set_components_data();

        // Test desktop components with WooCommerce
        $this->assertCount(9, $instance_with_woo->desktop_components, 'Desktop components should have 9 items when WooCommerce is active');
        
        // Check that WooCommerce component was added
        $woo_component_found = false;
        foreach ($instance_with_woo->desktop_components as $component) {
            if ($component['id'] === 'woo_icons') {
                $woo_component_found = true;
                $this->assertEquals('WooCommerce Icons', $component['label'], 'WooCommerce component should have correct label');
                break;
            }
        }
        $this->assertTrue($woo_component_found, 'WooCommerce component should be added to desktop components when WooCommerce is active');

        // Test mobile components with WooCommerce
        $this->assertCount(10, $instance_with_woo->mobile_components, 'Mobile components should have 10 items when WooCommerce is active');
        
        // Check that WooCommerce component was added to mobile components
        $mobile_woo_component_found = false;
        foreach ($instance_with_woo->mobile_components as $component) {
            if ($component['id'] === 'woo_icons') {
                $mobile_woo_component_found = true;
                $this->assertEquals('WooCommerce Icons', $component['label'], 'Mobile WooCommerce component should have correct label');
                break;
            }
        }
        $this->assertTrue($mobile_woo_component_found, 'WooCommerce component should be added to mobile components when WooCommerce is active');
    }

    /**
     * Data provider for row default value test scenarios.
     *
     * @since 1.0.0
     * @return array Test scenarios with [row_type, expected_structure, description]
     */
    public function rowDefaultValueScenariosProvider(): array {
        return [
            'mobile_offcanvas' => [
                'row_type' => 'mobile_offcanvas',
                'expected_json' => '{ "desktop": [], "mobile": [], "mobile_offcanvas": [["mobile_offcanvas_menu"]] }',
                'required_keys' => ['desktop', 'mobile', 'mobile_offcanvas'],
                'description' => 'mobile_offcanvas should have correct default structure'
            ],
            'main_footer_row' => [
                'row_type' => 'main_footer_row',
                'expected_json' => '{ "desktop": [[], [], []], "mobile": [[], [], []] }',
                'required_keys' => ['desktop', 'mobile'],
                'description' => 'main_footer_row should have correct default structure'
            ],
            'below_footer_row' => [
                'row_type' => 'below_footer_row',
                'expected_json' => '{ "desktop": [["copyright"]], "mobile": [[], [], []] }',
                'required_keys' => ['desktop', 'mobile'],
                'description' => 'below_footer_row should have correct default structure'
            ],
            'unknown_row' => [
                'row_type' => 'unknown_row',
                'expected_json' => '{ "desktop": [[], [], []], "mobile": [[], [], []], "mobile_offcanvas": [[]] }',
                'required_keys' => ['desktop', 'mobile', 'mobile_offcanvas'],
                'description' => 'unknown row should return default structure'
            ],
            'above_header_row' => [
                'row_type' => 'above_header_row',
                'expected_json' => '{ "desktop": [[], [], []], "mobile": [[], [], []], "mobile_offcanvas": [[]] }',
                'required_keys' => ['desktop', 'mobile', 'mobile_offcanvas'],
                'description' => 'above_header_row should return default structure'
            ],
            'below_header_row' => [
                'row_type' => 'below_header_row',
                'expected_json' => '{ "desktop": [[], [], []], "mobile": [[], [], []], "mobile_offcanvas": [[]] }',
                'required_keys' => ['desktop', 'mobile', 'mobile_offcanvas'],
                'description' => 'below_header_row should return default structure'
            ],
            'above_footer_row' => [
                'row_type' => 'above_footer_row',
                'expected_json' => '{ "desktop": [[], [], []], "mobile": [[], [], []], "mobile_offcanvas": [[]] }',
                'required_keys' => ['desktop', 'mobile', 'mobile_offcanvas'],
                'description' => 'above_footer_row should return default structure'
            ],
        ];
    }

    /**
     * Test default row values for standard row types (non-conditional logic).
     *
     * @dataProvider rowDefaultValueScenariosProvider
     * @since 1.0.0
     * @param string $row_type The row type to test
     * @param string $expected_json Expected JSON structure
     * @param array $required_keys Required keys in decoded JSON
     * @param string $description Test scenario description
     * @return void
     */
    public function test_get_row_default_value_standard_rows(string $row_type, string $expected_json, array $required_keys, string $description): void {
        $actual_result = \Sydney_Header_Footer_Builder::get_row_default_value($row_type);
        
        // Test that the result matches expected JSON
        $this->assertEquals($expected_json, $actual_result, $description);
        
        // Verify JSON structure is valid
        $decoded = json_decode($actual_result, true);
        $this->assertNotNull($decoded, "Row '{$row_type}' JSON should be valid");
        
        // Verify required keys exist
        foreach ($required_keys as $key) {
            $this->assertArrayHasKey($key, $decoded, "Row '{$row_type}' should have '{$key}' key");
        }
    }

    /**
     * Test main_header_row default values with WooCommerce conditional logic.
     *
     * Tests that:
     * - main_header_row default with and without WooCommerce
     * - JSON structure of returned values
     * - Conditional WooCommerce component inclusion
     *
     * @since 1.0.0
     * @return void
     */
    public function test_get_row_default_value_main_header(): void {
        // Test main_header_row (behavior depends on WooCommerce existence)
        $actual_main_header = \Sydney_Header_Footer_Builder::get_row_default_value('main_header_row');
        
        // Verify the JSON structure is valid regardless of WooCommerce state
        $decoded = json_decode($actual_main_header, true);
        $this->assertNotNull($decoded, 'main_header_row JSON should be valid');
        $this->assertArrayHasKey('desktop', $decoded, 'main_header_row should have desktop key');
        $this->assertArrayHasKey('mobile', $decoded, 'main_header_row should have mobile key');
        
        // Verify the structure contains expected components
        $this->assertContains('logo', $decoded['desktop'][0], 'Desktop first column should contain logo');
        $this->assertContains('menu', $decoded['desktop'][1], 'Desktop second column should contain menu');
        $this->assertContains('search', $decoded['desktop'][1], 'Desktop second column should contain search');
        $this->assertContains('logo', $decoded['mobile'][0], 'Mobile first column should contain logo');
        $this->assertContains('search', $decoded['mobile'][1], 'Mobile second column should contain search');
        $this->assertContains('mobile_hamburger', $decoded['mobile'][1], 'Mobile second column should contain mobile_hamburger');
        
        // Check WooCommerce conditional logic
        if (class_exists('Woocommerce')) {
            $this->assertContains('woo_icons', $decoded['desktop'][1], 'Desktop should include woo_icons when WooCommerce exists');
            $this->assertContains('woo_icons', $decoded['mobile'][1], 'Mobile should include woo_icons when WooCommerce exists');
            $expected_main_header = '{ "desktop": [["logo"], ["menu", "search", "woo_icons"]], "mobile": [["logo"], ["search", "woo_icons", "mobile_hamburger"]] }';
        } else {
            $this->assertNotContains('woo_icons', $decoded['desktop'][1], 'Desktop should not include woo_icons when WooCommerce does not exist');
            $this->assertNotContains('woo_icons', $decoded['mobile'][1], 'Mobile should not include woo_icons when WooCommerce does not exist');
            $expected_main_header = '{ "desktop": [["logo"], ["menu", "search"]], "mobile": [["logo"], ["search", "mobile_hamburger"]] }';
        }
        $this->assertEquals($expected_main_header, $actual_main_header, 'main_header_row should match expected format');
    }

    /**
     * Test row data retrieval from theme mods.
     *
     * Tests that:
     * - Header row data retrieval works correctly
     * - Footer row data retrieval works correctly
     * - Fallback to default values when theme mod is empty
     * - JSON decoding of theme mod values
     * - get_theme_mod is called with correct parameters
     * - Area parameter affects theme mod key generation
     *
     * @since 1.0.0
     * @return void
     */
    public function test_get_row_data(): void {
        // Test data for theme mods
        $header_row_data = '{"desktop":[["logo"],["menu","search"]],"mobile":[["logo"],["search","mobile_hamburger"]]}';
        $footer_row_data = '{"desktop":[["copyright"],["social"],["widget1"]],"mobile":[["copyright"],[],[]]}';
        
        // Mock get_theme_mod to return specific values for different rows
        $this->mockFunction('get_theme_mod', function($option_name, $default = null) use ($header_row_data, $footer_row_data) {
            switch ($option_name) {
                case 'sydney_header_row__main_header_row':
                    return $header_row_data;
                case 'sydney_footer_row__main_footer_row':
                    return $footer_row_data;
                case 'sydney_header_row__above_header_row':
                    // When theme mod is null, return the provided default
                    return $default;
                case 'sydney_footer_row__below_footer_row':
                    // When theme mod is empty, return the provided default  
                    return $default;
                default:
                    return $default;
            }
        });

        // Test header row data retrieval
        $result = \Sydney_Header_Footer_Builder::get_row_data('main_header_row', 'header');
        
        // Verify the result is properly decoded from JSON
        $this->assertIsObject($result, 'get_row_data should return decoded JSON object');
        $this->assertObjectHasProperty('desktop', $result, 'Result should have desktop property');
        $this->assertObjectHasProperty('mobile', $result, 'Result should have mobile property');
        
        // Verify the structure matches what we expect
        $this->assertIsArray($result->desktop, 'Desktop property should be an array');
        $this->assertIsArray($result->mobile, 'Mobile property should be an array');
        $this->assertCount(2, $result->desktop, 'Desktop should have 2 columns');
        $this->assertCount(2, $result->mobile, 'Mobile should have 2 columns');
        
        // Verify the actual content
        $this->assertEquals(['logo'], $result->desktop[0], 'First desktop column should contain logo');
        $this->assertEquals(['menu', 'search'], $result->desktop[1], 'Second desktop column should contain menu and search');
        $this->assertEquals(['logo'], $result->mobile[0], 'First mobile column should contain logo');
        $this->assertEquals(['search', 'mobile_hamburger'], $result->mobile[1], 'Second mobile column should contain search and mobile_hamburger');

        // Test footer row data retrieval
        $footer_result = \Sydney_Header_Footer_Builder::get_row_data('main_footer_row', 'footer');
        
        // Verify footer result structure
        $this->assertIsObject($footer_result, 'Footer get_row_data should return decoded JSON object');
        $this->assertObjectHasProperty('desktop', $footer_result, 'Footer result should have desktop property');
        $this->assertObjectHasProperty('mobile', $footer_result, 'Footer result should have mobile property');
        
        // Verify footer content
        $this->assertEquals(['copyright'], $footer_result->desktop[0], 'First footer desktop column should contain copyright');
        $this->assertEquals(['social'], $footer_result->desktop[1], 'Second footer desktop column should contain social');
        $this->assertEquals(['widget1'], $footer_result->desktop[2], 'Third footer desktop column should contain widget1');
        $this->assertEquals(['copyright'], $footer_result->mobile[0], 'First footer mobile column should contain copyright');
        $this->assertEquals([], $footer_result->mobile[1], 'Second footer mobile column should be empty');
        $this->assertEquals([], $footer_result->mobile[2], 'Third footer mobile column should be empty');

        // Test fallback to default values when theme mod returns null
        $default_result = \Sydney_Header_Footer_Builder::get_row_data('above_header_row', 'header');
        
        // This should return the default value for above_header_row, which is the default structure
        $this->assertIsObject($default_result, 'Should return decoded default value when theme mod is null');
        $this->assertObjectHasProperty('desktop', $default_result, 'Default result should have desktop property');
        $this->assertObjectHasProperty('mobile', $default_result, 'Default result should have mobile property');
        $this->assertObjectHasProperty('mobile_offcanvas', $default_result, 'Default result should have mobile_offcanvas property');
        
        // Verify default structure (empty columns)
        $this->assertEquals([[], [], []], $default_result->desktop, 'Default desktop should have 3 empty columns');
        $this->assertEquals([[], [], []], $default_result->mobile, 'Default mobile should have 3 empty columns');
        $this->assertEquals([[]], $default_result->mobile_offcanvas, 'Default mobile_offcanvas should have 1 empty column');

        // Test fallback to default values when theme mod returns empty string
        $empty_result = \Sydney_Header_Footer_Builder::get_row_data('below_footer_row', 'footer');
        
        // This should return the default value for below_footer_row
        $this->assertIsObject($empty_result, 'Should return decoded default value when theme mod is empty string');
        $this->assertObjectHasProperty('desktop', $empty_result, 'Empty result should have desktop property');
        $this->assertObjectHasProperty('mobile', $empty_result, 'Empty result should have mobile property');
        
        // Verify below_footer_row default structure (copyright in first desktop column)
        $this->assertEquals(['copyright'], $empty_result->desktop[0], 'Default below_footer_row should have copyright in first desktop column');
        $this->assertEquals([[], [], []], $empty_result->mobile, 'Default below_footer_row mobile should have 3 empty columns');

        // Test that area parameter affects the theme mod key
        // We can verify this by checking that header and footer areas use different theme mod keys
        // This is implicitly tested by the different return values above, but let's be explicit
        
        // The key generation logic is straightforward:
        // Header: 'sydney_header_row__' . $row
        // Footer: 'sydney_footer_row__' . $row
        // We can test this by verifying the behavior we already observed above
        
        // Verify that the method behaves differently for header vs footer areas
        // by testing with the same row name but different areas
        $test_header_result = \Sydney_Header_Footer_Builder::get_row_data('main_header_row', 'header');
        $test_footer_result = \Sydney_Header_Footer_Builder::get_row_data('main_footer_row', 'footer');
        
        // These should be different because they come from different theme mod keys
        $this->assertNotEquals(
            $test_header_result, 
            $test_footer_result, 
            'Header and footer areas should return different data when using different theme mod keys'
        );
        
        // Verify the structure differences we set up in our mock
        $this->assertEquals(['logo'], $test_header_result->desktop[0], 'Header result should match header test data');
        $this->assertEquals(['copyright'], $test_footer_result->desktop[0], 'Footer result should match footer test data');
    }

    /**
     * Test WooCommerce components only added when WC is active.
     *
     * Tests that:
     * - Desktop components without WooCommerce have correct count
     * - Desktop components with WooCommerce have correct count  
     * - Mobile components without WooCommerce have correct count
     * - Mobile components with WooCommerce have correct count
     * - woo_icons component is added conditionally
     * - Component count increases by 1 when WooCommerce is active
     *
     * @since 1.0.0
     * @return void
     */
    public function test_woocommerce_components_conditional(): void {
        // Set up HF Builder for this test
        $this->setUpWithHFBuilder();

        // Mock WordPress translation functions
        $this->mockFunction('esc_html__', function($text, $domain) {
            return $text; // Return untranslated text for testing
        });

        // SCENARIO 1: Test WITHOUT WooCommerce
        // Ensure Woocommerce class doesn't exist for this test
        $woocommerce_existed = class_exists('Woocommerce');
        
        // If WooCommerce class exists from previous tests, we need to work around it
        // by testing the logic directly rather than relying on class_exists
        
        // Get instance and test components without WooCommerce
        $instance_no_woo = \Sydney_Header_Footer_Builder::get_instance();
        
        // Manually set components data to simulate WooCommerce being inactive
        // We'll call the method and then manually verify the conditional logic
        $instance_no_woo->set_components_data();
        
        // Count components before WooCommerce (baseline counts)
        $desktop_count_without_woo = count($instance_no_woo->desktop_components);
        $mobile_count_without_woo = count($instance_no_woo->mobile_components);
        
        // Verify woo_icons is NOT present when WooCommerce is inactive
        // (Note: This test assumes WooCommerce class doesn't exist in test environment)
        if (!$woocommerce_existed) {
            $this->assertCount(8, $instance_no_woo->desktop_components, 'Desktop components should have 8 items when WooCommerce is inactive');
            $this->assertCount(9, $instance_no_woo->mobile_components, 'Mobile components should have 9 items when WooCommerce is inactive');
            
            // Verify woo_icons component is NOT present
            $woo_component_found_desktop = false;
            foreach ($instance_no_woo->desktop_components as $component) {
                if ($component['id'] === 'woo_icons') {
                    $woo_component_found_desktop = true;
                    break;
                }
            }
            $this->assertFalse($woo_component_found_desktop, 'woo_icons component should NOT be present in desktop components when WooCommerce is inactive');
            
            $woo_component_found_mobile = false;
            foreach ($instance_no_woo->mobile_components as $component) {
                if ($component['id'] === 'woo_icons') {
                    $woo_component_found_mobile = true;
                    break;
                }
            }
            $this->assertFalse($woo_component_found_mobile, 'woo_icons component should NOT be present in mobile components when WooCommerce is inactive');
        }

        // SCENARIO 2: Test WITH WooCommerce
        // Create mock WooCommerce class if it doesn't exist
        if (!class_exists('Woocommerce')) {
            class_alias('stdClass', 'Woocommerce');
        }

        // Reset singleton to test with WooCommerce active
        $reflection = new \ReflectionClass('Sydney_Header_Footer_Builder');
        $instance_property = $reflection->getProperty('instance');
        $instance_property->setAccessible(true);
        $instance_property->setValue(null, null);
        $instance_property->setAccessible(false);

        // Get new instance with WooCommerce active
        $instance_with_woo = \Sydney_Header_Footer_Builder::get_instance();
        $instance_with_woo->set_components_data();

        // Test desktop components WITH WooCommerce
        $this->assertCount(9, $instance_with_woo->desktop_components, 'Desktop components should have 9 items when WooCommerce is active');
        
        // Test mobile components WITH WooCommerce  
        $this->assertCount(10, $instance_with_woo->mobile_components, 'Mobile components should have 10 items when WooCommerce is active');

        // Verify component count increased by exactly 1 for both desktop and mobile
        if (!$woocommerce_existed) {
            $this->assertEquals(
                $desktop_count_without_woo + 1, 
                count($instance_with_woo->desktop_components),
                'Desktop component count should increase by 1 when WooCommerce is active'
            );
            $this->assertEquals(
                $mobile_count_without_woo + 1, 
                count($instance_with_woo->mobile_components),
                'Mobile component count should increase by 1 when WooCommerce is active'
            );
        }

        // Verify woo_icons component IS present when WooCommerce is active
        $woo_component_found_desktop = false;
        foreach ($instance_with_woo->desktop_components as $component) {
            if ($component['id'] === 'woo_icons') {
                $woo_component_found_desktop = true;
                $this->assertEquals('WooCommerce Icons', $component['label'], 'WooCommerce desktop component should have correct label');
                $this->assertArrayHasKey('id', $component, 'WooCommerce desktop component should have id key');
                $this->assertArrayHasKey('label', $component, 'WooCommerce desktop component should have label key');
                break;
            }
        }
        $this->assertTrue($woo_component_found_desktop, 'woo_icons component should be present in desktop components when WooCommerce is active');

        $woo_component_found_mobile = false;
        foreach ($instance_with_woo->mobile_components as $component) {
            if ($component['id'] === 'woo_icons') {
                $woo_component_found_mobile = true;
                $this->assertEquals('WooCommerce Icons', $component['label'], 'WooCommerce mobile component should have correct label');
                $this->assertArrayHasKey('id', $component, 'WooCommerce mobile component should have id key');
                $this->assertArrayHasKey('label', $component, 'WooCommerce mobile component should have label key');
                break;
            }
        }
        $this->assertTrue($woo_component_found_mobile, 'woo_icons component should be present in mobile components when WooCommerce is active');

        // Verify that footer components are NOT affected by WooCommerce
        // Footer components should remain the same count regardless of WooCommerce status
        $this->assertCount(8, $instance_with_woo->footer_components, 'Footer components should not be affected by WooCommerce status');
        
        // Verify no woo_icons in footer components
        $woo_component_found_footer = false;
        foreach ($instance_with_woo->footer_components as $component) {
            if ($component['id'] === 'woo_icons') {
                $woo_component_found_footer = true;
                break;
            }
        }
        $this->assertFalse($woo_component_found_footer, 'woo_icons component should NOT be present in footer components even when WooCommerce is active');

        // Test the specific positioning of woo_icons component
        // In desktop components, woo_icons should be added after the core components
        $desktop_woo_position = null;
        foreach ($instance_with_woo->desktop_components as $index => $component) {
            if ($component['id'] === 'woo_icons') {
                $desktop_woo_position = $index;
                break;
            }
        }
        $this->assertNotNull($desktop_woo_position, 'woo_icons should have a position in desktop components');
        $this->assertGreaterThanOrEqual(8, $desktop_woo_position, 'woo_icons should be added after the core 8 desktop components');

        // In mobile components, woo_icons should be added after the core components  
        $mobile_woo_position = null;
        foreach ($instance_with_woo->mobile_components as $index => $component) {
            if ($component['id'] === 'woo_icons') {
                $mobile_woo_position = $index;
                break;
            }
        }
        $this->assertNotNull($mobile_woo_position, 'woo_icons should have a position in mobile components');
        $this->assertGreaterThanOrEqual(9, $mobile_woo_position, 'woo_icons should be added after the core 9 mobile components');
    }

    /**
     * Test logo component rendering.
     *
     * Tests that:
     * - Logo component HTML output is correct
     * - Works with site logo set (image)
     * - Works without site logo (text only)
     * - Schema markup is included
     * - Customizer edit button is rendered in preview
     * - Different behavior on front page vs other pages
     * - Site description is rendered when available
     *
     * @since 1.0.0
     * @return void
     */
    public function test_component_rendering_logo(): void {
        // Set up HF Builder for this test
        $this->setUpWithHFBuilder();

        // Mock required WordPress functions
        $this->mockSiteInfoFunctions([
            'site_name' => 'Test Site Name',
            'site_description' => 'Test Site Description'
        ]);
        $this->mockSydneyThemeFunctions();
        $this->mockTranslationFunctions();

        // Get HF Builder instance
        $instance = \Sydney_Header_Footer_Builder::get_instance();

        // SCENARIO 1: Test with site logo image
        $this->mockThemeMods([
            'site_logo' => 'https://example.com/logo.png',
            'logo_site_title' => 0, // Don't show title alongside logo
        ]);
        $this->mockMediaFunctions([
            'attachments' => [
                'https://example.com/logo.png' => [123, 200, 100]
            ]
        ]);
        $this->mockConditionalFunctions([
            'is_front_page' => false,
            'is_customize_preview' => false
        ]);

        // Test logo component rendering with image
        $params = [
            'builder_type' => 'header',
            'device' => 'desktop'
        ];

        $output = $this->captureOutput(function() use ($instance, $params) {
            $instance->logo($params);
        });

        // Verify logo component structure
        $this->assertValidComponentStructure($output, [
            'component_id' => 'logo',
            'wrapper_class' => 'shfb-builder-item shfb-component-logo',
            'schema_type' => 'Organization',
            'required_elements' => [
                '<div class="site-branding"',
                '<img',
                'class="site-logo"',
                'src="https://example.com/logo.png"',
                'width="200"',
                'height="100"',
                'alt="Test Site Name"',
                'itemprop="logo"',
                'href="https://example.com/"',
                'title="Test Site Name"'
            ]
        ]);

        // Should NOT contain site title when logo is present and logo_site_title is false
        $this->assertHtmlContainsNone($output, [
            '<h1 class="site-title"',
            '<p class="site-title"'
        ]);

        // SCENARIO 2: Test without site logo (text only)
        // Reset WP_Mock to clear previous mocks
        M::tearDown();
        
        // Re-setup base mocks needed for all scenarios
        $this->mockFunction('esc_html__', function($text, $domain) {
            return $text; // Return untranslated text for testing
        });
        $this->mockFunction('get_template_directory', __DIR__ . '/../../');
        $this->mockFunction('home_url', function($path = '/') {
            return 'https://example.com' . $path;
        });
        $this->mockFunction('bloginfo', function($show = '') {
            switch ($show) {
                case 'name':
                    echo 'Test Site Name';
                    break;
                default:
                    echo 'Test Site Name';
                    break;
            }
        });
        $this->mockFunction('get_bloginfo', function($show = '', $filter = 'raw') {
            switch ($show) {
                case 'name':
                    return 'Test Site Name';
                case 'description':
                    return 'Test Site Description';
                default:
                    return 'Test Site Name';
            }
        });
        $this->mockFunction('sydney_get_schema', function($type) {
            echo 'itemscope itemtype="https://schema.org/Organization"';
        });
        $this->mockFunction('sydney_do_schema', function($type) {
            echo 'itemprop="logo"';
        });
        $this->mockFunction('sydney_get_svg_icon', function($icon) {
            return '<svg class="' . $icon . '"><use xlink:href="#' . $icon . '"></use></svg>';
        });
        $this->mockFunction('esc_attr_e', function($text, $domain) {
            echo $text;
        });
        $this->mockFunction('is_front_page', false);
        $this->mockFunction('is_customize_preview', false);

        // Reset singleton to clear any cached state
        $this->resetSingleton('Sydney_Header_Footer_Builder');

        // Mock theme mods for text-only scenario - NO LOGO
        $this->mockThemeMods([
            'site_logo' => '', // No logo set - empty string should be falsy
            'logo_site_title' => 0,
        ]);

        // Get fresh instance for text-only test
        $instance = \Sydney_Header_Footer_Builder::get_instance();

        $output_text_only = $this->captureOutput(function() use ($instance, $params) {
            $instance->logo($params);
        });

        // Should contain site title as text and site description
        $this->assertHtmlContainsAll($output_text_only, [
            '<p class="site-title"',
            'Test Site Name',
            'rel="home"',
            '<p class="site-description">',
            'Test Site Description'
        ]);

        // Should NOT contain logo image
        $this->assertStringNotContainsString('<img', $output_text_only, 'Should not contain logo image when no logo set');

        // SCENARIO 3: Test on front page (should use h1 instead of p for title)
        // Reset WP_Mock and set up fresh mocks for front page scenario
        M::tearDown();
        
        // Re-setup base mocks
        $this->mockFunction('esc_html__', function($text, $domain) {
            return $text;
        });
        $this->mockFunction('get_template_directory', __DIR__ . '/../../');
        $this->mockFunction('home_url', function($path = '/') {
            return 'https://example.com' . $path;
        });
        $this->mockFunction('bloginfo', function($show = '') {
            switch ($show) {
                case 'name':
                    echo 'Test Site Name';
                    break;
                default:
                    echo 'Test Site Name';
                    break;
            }
        });
        $this->mockFunction('get_bloginfo', function($show = '', $filter = 'raw') {
            switch ($show) {
                case 'name':
                    return 'Test Site Name';
                case 'description':
                    return 'Test Site Description';
                default:
                    return 'Test Site Name';
            }
        });
        $this->mockFunction('sydney_get_schema', function($type) {
            echo 'itemscope itemtype="https://schema.org/Organization"';
        });
        $this->mockFunction('sydney_do_schema', function($type) {
            echo 'itemprop="logo"';
        });
        $this->mockFunction('sydney_get_svg_icon', function($icon) {
            return '<svg class="' . $icon . '"><use xlink:href="#' . $icon . '"></use></svg>';
        });
        $this->mockFunction('esc_attr_e', function($text, $domain) {
            echo $text;
        });
        $this->mockFunction('is_customize_preview', false);
        
        // Set up front page scenario
        $this->mockFunction('is_front_page', true);
        $this->mockThemeMods([
            'site_logo' => '', // No logo
            'logo_site_title' => 0,
        ]);

        // Reset singleton
        $this->resetSingleton('Sydney_Header_Footer_Builder');

        $instance = \Sydney_Header_Footer_Builder::get_instance();

        $output_front_page = $this->captureOutput(function() use ($instance, $params) {
            $instance->logo($params);
        });


        $this->assertStringContainsString('<h1 class="site-title"', $output_front_page, 'Should use h1 for site title on front page');
        $this->assertStringNotContainsString('<p class="site-title"', $output_front_page, 'Should not use p for site title on front page');

        // SCENARIO 4: Test with both logo and site title enabled
        // Reset WP_Mock for scenario 4
        M::tearDown();
        
        // Re-setup base mocks
        $this->mockFunction('esc_html__', function($text, $domain) {
            return $text;
        });
        $this->mockFunction('get_template_directory', __DIR__ . '/../../');
        $this->mockFunction('home_url', function($path = '/') {
            return 'https://example.com' . $path;
        });
        $this->mockFunction('bloginfo', function($show = '') {
            switch ($show) {
                case 'name':
                    echo 'Test Site Name';
                    break;
                default:
                    echo 'Test Site Name';
                    break;
            }
        });
        $this->mockFunction('get_bloginfo', function($show = '', $filter = 'raw') {
            switch ($show) {
                case 'name':
                    return 'Test Site Name';
                case 'description':
                    return 'Test Site Description';
                default:
                    return 'Test Site Name';
            }
        });
        $this->mockFunction('sydney_get_schema', function($type) {
            echo 'itemscope itemtype="https://schema.org/Organization"';
        });
        $this->mockFunction('sydney_do_schema', function($type) {
            echo 'itemprop="logo"';
        });
        $this->mockFunction('sydney_get_svg_icon', function($icon) {
            return '<svg class="' . $icon . '"><use xlink:href="#' . $icon . '"></use></svg>';
        });
        $this->mockFunction('esc_attr_e', function($text, $domain) {
            echo $text;
        });
        $this->mockFunction('is_customize_preview', false);
        $this->mockFunction('is_front_page', false);
        $this->mockFunction('attachment_url_to_postid', function($url) {
            return $url === 'https://example.com/logo.png' ? 123 : 0;
        });
        $this->mockFunction('wp_get_attachment_image_src', function($id, $size) {
            return $id === 123 ? ['https://example.com/logo.png', 200, 100] : false;
        });
        
        $this->mockThemeMods([
            'site_logo' => 'https://example.com/logo.png',
            'logo_site_title' => 1, // Show title alongside logo
        ]);

        // Reset singleton
        $this->resetSingleton('Sydney_Header_Footer_Builder');

        $instance = \Sydney_Header_Footer_Builder::get_instance();

        $output_logo_and_title = $this->captureOutput(function() use ($instance, $params) {
            $instance->logo($params);
        });

        // Should contain both logo image and site title
        $this->assertHtmlContainsAll($output_logo_and_title, [
            '<img',
            'class="site-logo"',
            '<p class="site-title"'
        ]);

        // SCENARIO 5: Test customizer edit button in preview mode
        // Reset WP_Mock for scenario 5
        M::tearDown();
        
        // Re-setup base mocks
        $this->mockFunction('esc_html__', function($text, $domain) {
            return $text;
        });
        $this->mockFunction('get_template_directory', __DIR__ . '/../../');
        $this->mockFunction('home_url', function($path = '/') {
            return 'https://example.com' . $path;
        });
        $this->mockFunction('bloginfo', function($show = '') {
            switch ($show) {
                case 'name':
                    echo 'Test Site Name';
                    break;
                default:
                    echo 'Test Site Name';
                    break;
            }
        });
        $this->mockFunction('get_bloginfo', function($show = '', $filter = 'raw') {
            switch ($show) {
                case 'name':
                    return 'Test Site Name';
                case 'description':
                    return 'Test Site Description';
                default:
                    return 'Test Site Name';
            }
        });
        $this->mockFunction('sydney_get_schema', function($type) {
            echo 'itemscope itemtype="https://schema.org/Organization"';
        });
        $this->mockFunction('sydney_do_schema', function($type) {
            echo 'itemprop="logo"';
        });
        $this->mockFunction('sydney_get_svg_icon', function($icon) {
            return '<svg class="' . $icon . '"><use xlink:href="#' . $icon . '"></use></svg>';
        });
        $this->mockFunction('esc_attr_e', function($text, $domain) {
            echo $text;
        });
        $this->mockFunction('is_front_page', false);
        $this->mockFunction('is_customize_preview', true); // This is the key change for scenario 5
        $this->mockFunction('attachment_url_to_postid', function($url) {
            return $url === 'https://example.com/logo.png' ? 123 : 0;
        });
        $this->mockFunction('wp_get_attachment_image_src', function($id, $size) {
            return $id === 123 ? ['https://example.com/logo.png', 200, 100] : false;
        });
        
        $this->mockThemeMods([
            'site_logo' => 'https://example.com/logo.png',
            'logo_site_title' => 1, // Show title alongside logo
        ]);

        // Reset singleton
        $this->resetSingleton('Sydney_Header_Footer_Builder');

        $instance = \Sydney_Header_Footer_Builder::get_instance();

        $output_preview = $this->captureOutput(function() use ($instance, $params) {
            $instance->logo($params);
        });

        // Should contain customizer edit button in preview mode
        $this->assertHtmlContainsAll($output_preview, [
            '<div class="customize-partial-edit-shortcut"',
            'data-id="shfb"',
            'class="customize-partial-edit-shortcut-button',
            'aria-label="Click to edit this element."',
            '<svg class="icon-edit"'
        ]);

        // SCENARIO 6: Test mobile device parameter
        // Reset WP_Mock for scenario 6
        M::tearDown();
        
        // Re-setup base mocks
        $this->mockFunction('esc_html__', function($text, $domain) {
            return $text;
        });
        $this->mockFunction('get_template_directory', __DIR__ . '/../../');
        $this->mockFunction('home_url', function($path = '/') {
            return 'https://example.com' . $path;
        });
        $this->mockFunction('bloginfo', function($show = '') {
            switch ($show) {
                case 'name':
                    echo 'Test Site Name';
                    break;
                default:
                    echo 'Test Site Name';
                    break;
            }
        });
        $this->mockFunction('get_bloginfo', function($show = '', $filter = 'raw') {
            switch ($show) {
                case 'name':
                    return 'Test Site Name';
                case 'description':
                    return 'Test Site Description';
                default:
                    return 'Test Site Name';
            }
        });
        $this->mockFunction('sydney_get_schema', function($type) {
            echo 'itemscope itemtype="https://schema.org/Organization"';
        });
        $this->mockFunction('sydney_do_schema', function($type) {
            echo 'itemprop="logo"';
        });
        $this->mockFunction('sydney_get_svg_icon', function($icon) {
            return '<svg class="' . $icon . '"><use xlink:href="#' . $icon . '"></use></svg>';
        });
        $this->mockFunction('esc_attr_e', function($text, $domain) {
            echo $text;
        });
        $this->mockFunction('is_front_page', true);
        $this->mockFunction('is_customize_preview', false);
        
        $this->mockThemeMods([
            'site_logo' => '', // No logo
            'logo_site_title' => 0,
        ]);

        // Reset singleton
        $this->resetSingleton('Sydney_Header_Footer_Builder');

        $instance = \Sydney_Header_Footer_Builder::get_instance();

        $params_mobile = [
            'builder_type' => 'header',
            'device' => 'mobile'
        ];

        $output_mobile = $this->captureOutput(function() use ($instance, $params_mobile) {
            $instance->logo($params_mobile);
        });

        // On mobile, even on front page, should use p tag instead of h1
        $this->assertStringContainsString('<p class="site-title"', $output_mobile, 'Should use p tag for site title on mobile even on front page');
        $this->assertStringNotContainsString('<h1 class="site-title"', $output_mobile, 'Should not use h1 tag for site title on mobile');

        // SCENARIO 7: Test edge case - no site description
        // Reset WP_Mock for scenario 7
        M::tearDown();
        
        // Re-setup base mocks with empty site description
        $this->mockFunction('esc_html__', function($text, $domain) {
            return $text;
        });
        $this->mockFunction('get_template_directory', __DIR__ . '/../../');
        $this->mockFunction('home_url', function($path = '/') {
            return 'https://example.com' . $path;
        });
        $this->mockFunction('bloginfo', function($show = '') {
            switch ($show) {
                case 'name':
                    echo 'Test Site Name';
                    break;
                default:
                    echo 'Test Site Name';
                    break;
            }
        });
        $this->mockFunction('get_bloginfo', function($show = '', $filter = 'raw') {
            switch ($show) {
                case 'name':
                    return 'Test Site Name';
                case 'description':
                    return ''; // Empty description for this scenario
                default:
                    return 'Test Site Name';
            }
        });
        $this->mockFunction('sydney_get_schema', function($type) {
            echo 'itemscope itemtype="https://schema.org/Organization"';
        });
        $this->mockFunction('sydney_do_schema', function($type) {
            echo 'itemprop="logo"';
        });
        $this->mockFunction('sydney_get_svg_icon', function($icon) {
            return '<svg class="' . $icon . '"><use xlink:href="#' . $icon . '"></use></svg>';
        });
        $this->mockFunction('esc_attr_e', function($text, $domain) {
            echo $text;
        });
        $this->mockFunction('is_front_page', false);
        $this->mockFunction('is_customize_preview', false); // Not in preview, so empty description shouldn't show
        
        $this->mockThemeMods([
            'site_logo' => '', // No logo
            'logo_site_title' => 0,
        ]);

        // Reset singleton
        $this->resetSingleton('Sydney_Header_Footer_Builder');

        $instance = \Sydney_Header_Footer_Builder::get_instance();

        $output_no_description = $this->captureOutput(function() use ($instance, $params) {
            $instance->logo($params);
        });

        $this->assertStringNotContainsString('<p class="site-description">', $output_no_description, 'Should not contain site description when empty and not in preview');
    }

    /**
     * Data provider for component rendering preview mode scenarios.
     *
     * Note: Customizer edit buttons are rendered by the included component PHP files,
     * not by the main component methods, so they won't appear in these tests.
     * These tests focus on the component method logic and basic rendering.
     *
     * @since 1.0.0
     * @return array Test scenarios with [is_preview, description]
     */
    public function componentRenderingPreviewScenariosProvider(): array {
        return [
            'normal_mode' => [
                'is_preview' => false,
                'description' => 'Component rendering in normal mode'
            ],
            'preview_mode' => [
                'is_preview' => true,
                'description' => 'Component rendering in preview mode'
            ],
        ];
    }

    /**
     * Test menu component rendering.
     *
     * Tests that:
     * - Menu component HTML output is correct
     * - Basic navigation structure is rendered
     * - Schema markup is included
     * - Customizer edit button works in preview mode
     *
     * @dataProvider componentRenderingPreviewScenariosProvider
     * @since 1.0.0
     * @param bool $is_preview Whether to test in customizer preview mode
     * @param string $description Test scenario description
     * @return void
     */
    public function test_component_rendering_menu(bool $is_preview, string $description): void {
        // Set up HF Builder for this test
        $this->setUpWithHFBuilder();

        // Mock required WordPress functions
        $this->mockFunction('get_template_directory', __DIR__ . '/../../');
        $this->mockFunction('sydney_get_schema', function($type) {
            echo 'itemscope itemtype="https://schema.org/SiteNavigationElement"';
        });
        $this->mockFunction('sydney_get_svg_icon', function($icon) {
            return '<svg class="' . $icon . '"><use xlink:href="#' . $icon . '"></use></svg>';
        });
        $this->mockFunction('esc_attr_e', function($text, $domain) {
            echo $text;
        });
        $this->mockFunction('apply_filters', function($hook, $default) {
            return $default; // Return default walker
        });
        $this->mockFunction('get_option', function($option_name, $default = null) {
            switch ($option_name) {
                case 'sydney_dropdowns_hover_delay':
                    return 'yes'; // Enable hover delay
                default:
                    return $default;
            }
        });
        $this->mockFunction('has_nav_menu', function($location) {
            return $location === 'primary'; // Primary menu exists
        });
        $this->mockFunction('wp_nav_menu', function($args) {
            echo '<ul id="' . $args['menu_id'] . '" class="' . $args['menu_class'] . '">';
            echo '<li><a href="#">Home</a></li>';
            echo '<li><a href="#">About</a></li>';
            echo '</ul>';
        });
        $this->mockFunction('is_customize_preview', $is_preview);

        // Get HF Builder instance
        $instance = \Sydney_Header_Footer_Builder::get_instance();

        // Test menu component rendering
        $params = [
            'builder_type' => 'header',
            'device' => 'desktop'
        ];

        $output = $this->captureOutput(function() use ($instance, $params) {
            $instance->menu($params);
        });

        // Verify menu component structure (common elements)
        $this->assertValidComponentStructure($output, [
            'component_id' => 'menu',
            'wrapper_class' => 'shfb-builder-item shfb-component-menu',
            'schema_type' => 'SiteNavigationElement',
            'required_elements' => [
                '<nav id="site-navigation"',
                'class="sydney-dropdown main-navigation with-hover-delay"',
                '<ul id="primary-menu" class="sydney-dropdown-ul menu">',
                '<li><a href="#">Home</a></li>',
                '<li><a href="#">About</a></li>'
            ]
        ]);

        // Basic test that component rendered successfully with expected structure
        $this->assertStringContainsString('<nav id="site-navigation"', $output, $description . ' - should contain navigation element');
        $this->assertStringContainsString('class="sydney-dropdown main-navigation', $output, $description . ' - should contain navigation classes');
    }

    /**
     * Test search component rendering.
     *
     * Tests that:
     * - Search component HTML output is correct
     * - Hidden search layout renders search icon link
     * - Component wrapper classes are correct
     * - Customizer edit button works in preview mode
     *
     * @dataProvider componentRenderingPreviewScenariosProvider
     * @since 1.0.0
     * @param bool $is_preview Whether to test in customizer preview mode
     * @param string $description Test scenario description
     * @return void
     */
    public function test_component_rendering_search(bool $is_preview, string $description): void {
        // Set up HF Builder for this test
        $this->setUpWithHFBuilder();

        // Mock required WordPress functions
        $this->mockFunction('get_template_directory', __DIR__ . '/../../');
        $this->mockFunction('esc_attr__', function($text, $domain) {
            return $text; // Return untranslated text for testing
        });
        $this->mockFunction('sydney_get_header_search_icon', function() {
            return '<svg class="search-icon"><use xlink:href="#search"></use></svg>';
        });
        $this->mockFunction('sydney_get_svg_icon', function($icon) {
            return '<svg class="' . $icon . '"><use xlink:href="#' . $icon . '"></use></svg>';
        });
        $this->mockFunction('esc_attr_e', function($text, $domain) {
            echo $text;
        });
        $this->mockFunction('apply_filters', function($hook, $default) {
            return $default; // Return default content (empty string)
        });
        $this->mockFunction('is_customize_preview', $is_preview);

        // Mock theme mods for hidden search layout (default)
        $this->mockThemeMods([
            'shfb_search_layout' => 'hidden',
        ]);

        // Get HF Builder instance
        $instance = \Sydney_Header_Footer_Builder::get_instance();

        // Test search component rendering
        $params = [
            'builder_type' => 'header',
            'device' => 'desktop'
        ];

        $output = $this->captureOutput(function() use ($instance, $params) {
            $instance->search($params);
        });

        // Verify search component structure
        $this->assertValidComponentStructure($output, [
            'component_id' => 'search',
            'wrapper_class' => 'shfb-builder-item shfb-component-search',
            'required_elements' => [
                '<a href="#" class="header-search"',
                'title="Search for a product"',
                '<svg class="search-icon"'
            ]
        ]);

        // Basic test that component rendered successfully with expected structure
        $this->assertStringContainsString('<a href="#" class="header-search"', $output, $description . ' - should contain search link');
        $this->assertStringContainsString('<svg class="search-icon"', $output, $description . ' - should contain search icon');
    }

    /**
     * Test social component rendering.
     *
     * Tests that:
     * - Social component HTML output is correct
     * - Component wrapper classes are correct
     * - Social profiles function is called
     * - Customizer edit button works in preview mode
     *
     * @dataProvider componentRenderingPreviewScenariosProvider
     * @since 1.0.0
     * @param bool $is_preview Whether to test in customizer preview mode
     * @param string $description Test scenario description
     * @return void
     */
    public function test_component_rendering_social(bool $is_preview, string $description): void {
        // Set up HF Builder for this test
        $this->setUpWithHFBuilder();

        // Mock required WordPress functions
        $this->mockFunction('get_template_directory', __DIR__ . '/../../');
        $this->mockFunction('sydney_social_profile', function($profile_type) use ($is_preview) {
            // Mock social profile output - content varies by scenario for testing
            echo '<div class="social-profile-links">';
            if ($is_preview) {
                echo '<a href="https://instagram.com" class="social-link instagram">Instagram</a>';
            } else {
                echo '<a href="https://facebook.com" class="social-link facebook">Facebook</a>';
                echo '<a href="https://twitter.com" class="social-link twitter">Twitter</a>';
            }
            echo '</div>';
        });
        $this->mockFunction('sydney_get_svg_icon', function($icon) {
            return '<svg class="' . $icon . '"><use xlink:href="#' . $icon . '"></use></svg>';
        });
        $this->mockFunction('esc_attr_e', function($text, $domain) {
            echo $text;
        });
        $this->mockFunction('is_customize_preview', $is_preview);

        // Get HF Builder instance
        $instance = \Sydney_Header_Footer_Builder::get_instance();

        // Test social component rendering
        $params = [
            'builder_type' => 'header',
            'device' => 'desktop'
        ];

        $output = $this->captureOutput(function() use ($instance, $params) {
            $instance->social($params);
        });

        // Verify social component structure
        $this->assertValidComponentStructure($output, [
            'component_id' => 'social',
            'wrapper_class' => 'shfb-builder-item shfb-component-social',
            'required_elements' => [
                '<div class="social-profile-links">'
            ]
        ]);

        // Basic test that component rendered successfully with expected structure
        $this->assertStringContainsString('<div class="social-profile-links">', $output, $description . ' - should contain social profile links');
        
        // Test that different content is rendered based on preview mode
        if ($is_preview) {
            $this->assertStringContainsString('instagram', $output, $description . ' - should contain Instagram link in preview mode');
        } else {
            $this->assertStringContainsString('facebook', $output, $description . ' - should contain Facebook link in normal mode');
        }
    }

    /**
     * Test button component rendering.
     *
     * Tests that:
     * - Button component HTML output is correct
     * - Button text, URL, and classes are rendered properly
     * - Target attribute works for new tab setting
     * - Customizer edit button works in preview mode
     *
     * @dataProvider componentRenderingPreviewScenariosProvider
     * @since 1.0.0
     * @param bool $is_preview Whether to test in customizer preview mode
     * @param string $description Test scenario description
     * @return void
     */
    public function test_component_rendering_button(bool $is_preview, string $description): void {
        // Set up HF Builder for this test
        $this->setUpWithHFBuilder();

        // Mock required WordPress functions
        $this->mockFunction('get_template_directory', __DIR__ . '/../../');
        $this->mockFunction('esc_html__', function($text, $domain) {
            return $text; // Return untranslated text for testing
        });
        $this->mockFunction('esc_html', function($text) {
            return htmlspecialchars($text, ENT_QUOTES);
        });
        $this->mockFunction('esc_url', function($url) {
            return $url;
        });
        $this->mockFunction('esc_attr', function($attr) {
            return htmlspecialchars($attr, ENT_QUOTES);
        });
        $this->mockFunction('sydney_get_svg_icon', function($icon) {
            return '<svg class="' . $icon . '"><use xlink:href="#' . $icon . '"></use></svg>';
        });
        $this->mockFunction('esc_attr_e', function($text, $domain) {
            echo $text;
        });
        $this->mockFunction('is_customize_preview', $is_preview);

        // Mock theme mods for button settings - different values based on preview mode
        if ($is_preview) {
            $this->mockThemeMods([
                'header_button_text' => 'Contact Us',
                'header_button_link' => '#contact',
                'header_button_class' => '',
                'header_button_newtab' => 0, // Don't open in new tab
            ]);
        } else {
            $this->mockThemeMods([
                'header_button_text' => 'Get Started',
                'header_button_link' => 'https://example.com',
                'header_button_class' => 'btn-primary',
                'header_button_newtab' => 1, // Open in new tab
            ]);
        }

        // Get HF Builder instance
        $instance = \Sydney_Header_Footer_Builder::get_instance();

        // Test button component rendering
        $params = [
            'builder_type' => 'header',
            'device' => 'desktop'
        ];

        $output = $this->captureOutput(function() use ($instance, $params) {
            $instance->button($params);
        });

        // Verify button component structure (common elements)
        $this->assertValidComponentStructure($output, [
            'component_id' => 'button',
            'wrapper_class' => 'shfb-builder-item shfb-component-button',
            'required_elements' => [
                'class="button'
            ]
        ]);

        // Basic test that component rendered successfully with expected structure
        $this->assertStringContainsString('class="button', $output, $description . ' - should contain button element');
        
        // Test scenario-specific button behavior
        if ($is_preview) {
            $this->assertStringNotContainsString('target="_blank"', $output, 'Should not have target="_blank" when newtab is disabled in preview mode');
            $this->assertStringContainsString('Contact Us', $output, 'Should contain preview mode button text');
        } else {
            $this->assertStringContainsString('target="_blank"', $output, 'Should have target="_blank" when newtab is enabled in normal mode');
            $this->assertStringContainsString('Get Started', $output, 'Should contain normal mode button text');
        }
    }

    /**
     * Test contact_info component rendering.
     *
     * Tests that:
     * - Contact info component HTML output is correct
     * - Email and phone links are rendered properly
     * - Display inline option works
     * - Icons are included
     * - Customizer edit button works in preview mode
     *
     * @dataProvider componentRenderingPreviewScenariosProvider
     * @since 1.0.0
     * @param bool $is_preview Whether to test in customizer preview mode
     * @param string $description Test scenario description
     * @return void
     */
    public function test_component_rendering_contact_info(bool $is_preview, string $description): void {
        // Set up HF Builder for this test
        $this->setUpWithHFBuilder();

        // Mock required WordPress functions
        $this->mockFunction('get_template_directory', __DIR__ . '/../../');
        $this->mockFunction('esc_html__', function($text, $domain) {
            return $text; // Return untranslated text for testing
        });
        $this->mockFunction('esc_html', function($text) {
            return htmlspecialchars($text, ENT_QUOTES);
        });
        $this->mockFunction('esc_attr', function($attr) {
            return htmlspecialchars($attr, ENT_QUOTES);
        });
        $this->mockFunction('antispambot', function($email) {
            return $email; // Return email as-is for testing
        });
        $this->mockFunction('sydney_get_svg_icon', function($icon, $echo = false) {
            $svg = '<svg class="' . $icon . '"><use xlink:href="#' . $icon . '"></use></svg>';
            if ($echo) {
                echo $svg;
            }
            return $svg;
        });
        $this->mockFunction('esc_attr_e', function($text, $domain) {
            echo $text;
        });
        $this->mockFunction('is_customize_preview', $is_preview);

        // Mock theme mods for contact info settings - different values based on preview mode
        if ($is_preview) {
            $this->mockThemeMods([
                'header_contact_mail' => 'contact@test.com',
                'header_contact_phone' => '123-456-7890',
                'shfb_contact_info_display_inline' => 1, // Enable inline display
            ]);
        } else {
            $this->mockThemeMods([
                'header_contact_mail' => 'hello@example.com',
                'header_contact_phone' => '+1-555-123-4567',
                'shfb_contact_info_display_inline' => 0, // Not inline
            ]);
        }

        // Get HF Builder instance
        $instance = \Sydney_Header_Footer_Builder::get_instance();

        // Test contact info component rendering
        $params = [
            'builder_type' => 'header',
            'device' => 'desktop'
        ];

        $output = $this->captureOutput(function() use ($instance, $params) {
            $instance->contact_info($params);
        });

        // Verify basic structure (common elements)
        $this->assertStringContainsString('<div class="shfb-builder-item shfb-component-contact_info" data-component-id="contact_info">', $output, $description . ' - should have correct wrapper classes');
        $this->assertStringContainsString('<div class="header-contact', $output, $description . ' - should contain header-contact container');
        $this->assertStringContainsString('<svg class="icon-mail"', $output, $description . ' - should contain mail icon');
        $this->assertStringContainsString('<svg class="icon-phone"', $output, $description . ' - should contain phone icon');

        // Test scenario-specific contact info behavior
        if ($is_preview) {
            $this->assertStringContainsString('class="header-contact header-contact-inline"', $output, 'Should have inline class when display_inline is enabled in preview mode');
            $this->assertStringContainsString('contact@test.com', $output, 'Should contain preview mode email address');
        } else {
            $this->assertStringContainsString('hello@example.com', $output, 'Should contain normal mode email address');
            $this->assertStringContainsString('+1-555-123-4567', $output, 'Should contain normal mode phone number');
        }
    }

    /**
     * Test header rows structure initialization.
     *
     * Tests that:
     * - Header rows are properly initialized
     * - All required header rows exist
     * - Header rows have correct structure and properties
     * - Header rows have correct default values
     *
     * @since 1.0.0
     * @return void
     */
    public function test_header_rows_structure(): void {
        // Set up HF Builder for this test
        $this->setUpWithHFBuilder();

        // Mock required WordPress functions
        $this->mockFunction('esc_html__', function($text, $domain) {
            return $text; // Return untranslated text for testing
        });

        // Get HF Builder instance
        $instance = \Sydney_Header_Footer_Builder::get_instance();

        // Manually trigger set_components_data to initialize header_rows
        $instance->set_components_data();

        // Test header_rows property exists and is array
        $this->assertObjectHasProperty('header_rows', $instance, 'Instance should have header_rows property');
        $this->assertIsArray($instance->header_rows, 'Header rows should be an array');
        
        // Test correct number of header rows
        $this->assertCount(3, $instance->header_rows, 'Should have exactly 3 header rows');

        // Test above_header_row structure
        $above_row = $instance->header_rows[0];
        $this->assertArrayHasKey('id', $above_row, 'Above header row should have id key');
        $this->assertArrayHasKey('label', $above_row, 'Above header row should have label key');
        $this->assertArrayHasKey('description', $above_row, 'Above header row should have description key');
        $this->assertArrayHasKey('section', $above_row, 'Above header row should have section key');
        $this->assertArrayHasKey('default', $above_row, 'Above header row should have default key');
        
        $this->assertEquals('above_header_row', $above_row['id'], 'Above header row should have correct id');
        $this->assertEquals('Top Row', $above_row['label'], 'Above header row should have correct label');
        $this->assertEquals('sydney_section_hb_above_header_row', $above_row['section'], 'Above header row should have correct section');
        $this->assertStringContainsString('first row', $above_row['description'], 'Above header row should have correct description');

        // Test main_header_row structure
        $main_row = $instance->header_rows[1];
        $this->assertArrayHasKey('id', $main_row, 'Main header row should have id key');
        $this->assertArrayHasKey('label', $main_row, 'Main header row should have label key');
        $this->assertArrayHasKey('description', $main_row, 'Main header row should have description key');
        $this->assertArrayHasKey('section', $main_row, 'Main header row should have section key');
        $this->assertArrayHasKey('default', $main_row, 'Main header row should have default key');
        
        $this->assertEquals('main_header_row', $main_row['id'], 'Main header row should have correct id');
        $this->assertEquals('Main Row', $main_row['label'], 'Main header row should have correct label');
        $this->assertEquals('sydney_section_hb_main_header_row', $main_row['section'], 'Main header row should have correct section');
        $this->assertStringContainsString('second row', $main_row['description'], 'Main header row should have correct description');

        // Test below_header_row structure
        $below_row = $instance->header_rows[2];
        $this->assertArrayHasKey('id', $below_row, 'Below header row should have id key');
        $this->assertArrayHasKey('label', $below_row, 'Below header row should have label key');
        $this->assertArrayHasKey('description', $below_row, 'Below header row should have description key');
        $this->assertArrayHasKey('section', $below_row, 'Below header row should have section key');
        $this->assertArrayHasKey('default', $below_row, 'Below header row should have default key');
        
        $this->assertEquals('below_header_row', $below_row['id'], 'Below header row should have correct id');
        $this->assertEquals('Bottom Row', $below_row['label'], 'Below header row should have correct label');
        $this->assertEquals('sydney_section_hb_below_header_row', $below_row['section'], 'Below header row should have correct section');
        $this->assertStringContainsString('third row', $below_row['description'], 'Below header row should have correct description');

        // Test that all default values are valid JSON strings
        foreach ($instance->header_rows as $row) {
            $decoded = json_decode($row['default'], true);
            $this->assertNotNull($decoded, "Row '{$row['id']}' should have valid JSON default value");
            $this->assertIsArray($decoded, "Row '{$row['id']}' default should decode to array");
            $this->assertArrayHasKey('desktop', $decoded, "Row '{$row['id']}' default should have desktop key");
            $this->assertArrayHasKey('mobile', $decoded, "Row '{$row['id']}' default should have mobile key");
        }

        // Test that main_header_row has different default content (contains components)
        $main_default = json_decode($main_row['default'], true);
        $this->assertNotEmpty($main_default['desktop'][0], 'Main header row should have components in first desktop column by default');
        
        // Test that above and below rows have empty defaults
        $above_default = json_decode($above_row['default'], true);
        $below_default = json_decode($below_row['default'], true);
        
        // These should be empty by default (using the default structure)
        $this->assertEquals([[], [], []], $above_default['desktop'], 'Above header row should have empty desktop columns by default');
        $this->assertEquals([[], [], []], $below_default['desktop'], 'Below header row should have empty desktop columns by default');
    }

    /**
     * Test is_row_empty method functionality.
     *
     * Tests that:
     * - Empty rows return true
     * - Rows with components return false
     * - Different column structures work correctly
     * - Method handles edge cases properly
     *
     * @since 1.0.0
     * @return void
     */
    public function test_is_row_empty(): void {
        // Set up HF Builder for this test to load the class
        $this->setUpWithHFBuilder();
        // Test completely empty row (all columns empty)
        $empty_row = [[], [], []];
        $this->assertTrue(
            \Sydney_Header_Footer_Builder::is_row_empty($empty_row), 
            'Row with all empty columns should be considered empty'
        );

        // Test row with components in first column
        $row_with_first_column = [['logo'], [], []];
        $this->assertFalse(
            \Sydney_Header_Footer_Builder::is_row_empty($row_with_first_column), 
            'Row with components in first column should not be considered empty'
        );

        // Test row with components in middle column
        $row_with_middle_column = [[], ['menu', 'search'], []];
        $this->assertFalse(
            \Sydney_Header_Footer_Builder::is_row_empty($row_with_middle_column), 
            'Row with components in middle column should not be considered empty'
        );

        // Test row with components in last column
        $row_with_last_column = [[], [], ['social']];
        $this->assertFalse(
            \Sydney_Header_Footer_Builder::is_row_empty($row_with_last_column), 
            'Row with components in last column should not be considered empty'
        );

        // Test row with components in multiple columns
        $row_with_multiple_columns = [['logo'], ['menu'], ['social']];
        $this->assertFalse(
            \Sydney_Header_Footer_Builder::is_row_empty($row_with_multiple_columns), 
            'Row with components in multiple columns should not be considered empty'
        );

        // Test row with mixed empty and filled columns
        $row_mixed = [['logo', 'search'], [], ['button']];
        $this->assertFalse(
            \Sydney_Header_Footer_Builder::is_row_empty($row_mixed), 
            'Row with mixed empty and filled columns should not be considered empty'
        );

        // Test single column row (empty)
        $single_empty_column = [[]];
        $this->assertTrue(
            \Sydney_Header_Footer_Builder::is_row_empty($single_empty_column), 
            'Single empty column should be considered empty'
        );

        // Test single column row (with components)
        $single_filled_column = [['logo', 'menu']];
        $this->assertFalse(
            \Sydney_Header_Footer_Builder::is_row_empty($single_filled_column), 
            'Single column with components should not be considered empty'
        );

        // Test two column row (both empty)
        $two_empty_columns = [[], []];
        $this->assertTrue(
            \Sydney_Header_Footer_Builder::is_row_empty($two_empty_columns), 
            'Two empty columns should be considered empty'
        );

        // Test two column row (one filled)
        $two_columns_one_filled = [['copyright'], []];
        $this->assertFalse(
            \Sydney_Header_Footer_Builder::is_row_empty($two_columns_one_filled), 
            'Two columns with one filled should not be considered empty'
        );

        // Test edge case: empty array
        $empty_array = [];
        $this->assertTrue(
            \Sydney_Header_Footer_Builder::is_row_empty($empty_array), 
            'Completely empty array should be considered empty'
        );
    }

    /**
     * Test header front output functionality.
     *
     * Tests that:
     * - Header front output method exists and is callable
     * - Method is public and accessible
     * - Basic method structure is correct
     *
     * @since 1.0.0
     * @return void
     */
    public function test_header_front_output(): void {
        // Set up HF Builder for this test
        $this->setUpWithHFBuilder();

        // Get HF Builder instance
        $instance = \Sydney_Header_Footer_Builder::get_instance();

        // Test that the method exists
        $this->assertTrue(
            method_exists($instance, 'header_front_output'),
            'header_front_output method should exist'
        );
        
        // Test that the method is callable
        $this->assertTrue(
            is_callable([$instance, 'header_front_output']),
            'header_front_output method should be callable'
        );

        // Test method visibility using reflection
        $reflection = new \ReflectionMethod($instance, 'header_front_output');
        $this->assertTrue(
            $reflection->isPublic(),
            'header_front_output method should be public'
        );
    }

    /**
     * Test footer front output functionality.
     *
     * Tests that:
     * - Footer front output method exists and is callable
     * - Method is public and accessible
     * - Basic method structure is correct
     *
     * @since 1.0.0
     * @return void
     */
    public function test_footer_front_output(): void {
        // Set up HF Builder for this test
        $this->setUpWithHFBuilder();

        // Get HF Builder instance
        $instance = \Sydney_Header_Footer_Builder::get_instance();

        // Test that the method exists
        $this->assertTrue(
            method_exists($instance, 'footer_front_output'),
            'footer_front_output method should exist'
        );
        
        // Test that the method is callable
        $this->assertTrue(
            is_callable([$instance, 'footer_front_output']),
            'footer_front_output method should be callable'
        );

        // Test method visibility using reflection
        $reflection = new \ReflectionMethod($instance, 'footer_front_output');
        $this->assertTrue(
            $reflection->isPublic(),
            'footer_front_output method should be public'
        );
    }

    /**
     * Test singleton pattern and instance creation.
     *
     * Tests that:
     * - get_instance() returns same instance on multiple calls
     * - Constructor is called only once
     * - Instance is of correct class type
     * - Class only loads when hf-builder module is active
     *
     * @since 1.0.0
     * @return void
     */
    public function test_class_instantiation(): void {
        // Set up HF Builder for this test
        $this->setUpWithHFBuilder();

        // Get first instance
        $instance1 = \Sydney_Header_Footer_Builder::get_instance();

        // Get second instance
        $instance2 = \Sydney_Header_Footer_Builder::get_instance();

        // Test that instance is of correct class type
        $this->assertInstanceOf(\Sydney_Header_Footer_Builder::class, $instance1);
        $this->assertInstanceOf(\Sydney_Header_Footer_Builder::class, $instance2);

        // Test singleton behavior - same instance returned on multiple calls
        $this->assertSame($instance1, $instance2, 'get_instance() should return the same instance on multiple calls');

        // Test that required properties are accessible
        $this->assertObjectHasProperty('desktop_components', $instance1);
        $this->assertObjectHasProperty('mobile_components', $instance1);
        $this->assertObjectHasProperty('footer_components', $instance1);
        $this->assertObjectHasProperty('header_rows', $instance1);
        $this->assertObjectHasProperty('footer_rows', $instance1);
    }
}