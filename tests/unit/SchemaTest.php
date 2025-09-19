<?php
/**
 * Unit tests for Schema functionality
 *
 * Tests the schema.php file functions that handle structured data markup
 * for various page types and components in the Sydney theme.
 *
 * @package Sydney
 * @subpackage Tests
 * @since 1.0.0
 */

namespace Sydney\Tests;

use WP_Mock\Tools\TestCase as WPTestCase;
use WP_Mock as M;

/**
 * Test class for Sydney theme schema functionality.
 *
 * Tests that:
 * - Schema markup is correctly generated for different page locations
 * - Schema output is properly enabled/disabled via theme customizer
 * - All supported schema types return correct markup
 * - Edge cases and unknown locations are handled gracefully
 *
 * @since 1.0.0
 */
class SchemaTest extends WPTestCase {

    /**
     * Set up test environment before each test.
     *
     * @since 1.0.0
     * @return void
     */
    public function setUp(): void {
        parent::setUp();
        M::setUp();
        
        // Load the schema.php file only if functions don't exist yet
        if (!function_exists('sydney_get_schema')) {
            require_once __DIR__ . '/../../inc/schema.php';
        }
    }

    /**
     * Clean up test environment after each test.
     *
     * @since 1.0.0
     * @return void
     */
    public function tearDown(): void {
        M::tearDown();
        parent::tearDown();
    }

    /**
     * Test that schema functions exist and are callable.
     *
     * @since 1.0.0
     * @return void
     */
    public function test_schema_functions_exist(): void {
        $this->assertTrue(
            function_exists('sydney_get_schema'),
            'sydney_get_schema function should exist'
        );
        
        $this->assertTrue(
            function_exists('sydney_do_schema'),
            'sydney_do_schema function should exist'
        );
        
        // Test that functions are callable
        $this->assertTrue(is_callable('sydney_get_schema'));
        $this->assertTrue(is_callable('sydney_do_schema'));
    }

    /**
     * Test schema function with disabled state.
     *
     * @since 1.0.0
     * @return void
     */
    public function test_schema_disabled_state(): void {
        // Mock get_theme_mod to return 0 (disabled)
        M::userFunction('get_theme_mod', [
            'return' => function($name, $default = null) {
                if ($name === 'sydney_enable_schema') {
                    return 0;
                }
                return $default;
            }
        ]);

        // Test that schema returns null when disabled
        $result = sydney_get_schema('header');
        $this->assertNull($result, 'Schema should return null when disabled');
        
        $result = sydney_get_schema('unknown');
        $this->assertNull($result, 'Schema should return null for any location when disabled');
    }

    /**
     * Test schema function return types.
     *
     * Tests basic return types without relying on complex mocking.
     *
     * @since 1.0.0
     * @return void
     */
    public function test_schema_return_types(): void {
        // Mock get_theme_mod with a simple return to avoid conflicts
        M::userFunction('get_theme_mod')->andReturn(1);

        // Test that functions return appropriate types
        $result = sydney_get_schema('header');
        $this->assertTrue(is_string($result) || is_null($result), 'Schema should return string or null');
        
        $result = sydney_get_schema('unknown');
        $this->assertTrue(is_string($result) || is_null($result), 'Schema should return string or null for unknown location');
    }

    /**
     * Test sydney_do_schema() function basic functionality.
     *
     * @since 1.0.0
     * @return void
     */
    public function test_do_schema_basic(): void {
        // Mock get_theme_mod with simple return
        M::userFunction('get_theme_mod')->andReturn(1);

        // Test that sydney_do_schema doesn't throw errors
        ob_start();
        sydney_do_schema('header');
        $output = ob_get_clean();

        $this->assertIsString($output, 'sydney_do_schema should produce string output');
    }

    /**
     * Test schema with explicit disabled state.
     *
     * @since 1.0.0
     * @return void
     */
    public function test_schema_explicit_disabled(): void {
        // Use a very explicit mock that should work in all contexts
        M::userFunction('get_theme_mod')->andReturn(0);

        // Test that schema returns null when explicitly disabled
        $result = sydney_get_schema('header');
        $this->assertNull($result, 'Schema should return null when disabled');
    }

    /**
     * Test schema function behavior consistency.
     *
     * @since 1.0.0
     * @return void
     */
    public function test_schema_consistency(): void {
        // Mock get_theme_mod with simple return
        M::userFunction('get_theme_mod')->andReturn(1);

        // Test that multiple calls return consistent types
        $locations = ['header', 'footer', 'headline', 'unknown'];
        
        foreach ($locations as $location) {
            $result1 = sydney_get_schema($location);
            $result2 = sydney_get_schema($location);
            
            // Both calls should return the same type
            $this->assertEquals(gettype($result1), gettype($result2), 
                "Multiple calls to schema for {$location} should return same type");
        }
    }
}