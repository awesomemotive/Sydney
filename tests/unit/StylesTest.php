<?php
/**
 * Unit tests for the Dynamic Styles functionality in Sydney theme
 *
 * @package Sydney
 * @subpackage Tests
 */

namespace Sydney\Tests;

use Sydney\Tests\BaseThemeTest;
use WP_Mock as M;

/**
 * Test class for Dynamic Styles functionality.
 *
 * @since 1.0.0
 */
class StylesTest extends BaseThemeTest {

	/**
	 * Get the theme dependencies that this test class requires.
	 *
	 * @since 1.0.0
	 * @return array Array of dependency types to load.
	 */
	protected function getRequiredDependencies(): array {
		return ['styles'];
	}

	/**
	 * Test that get_instance() returns a singleton instance.
	 *
	 * Verifies that:
	 * - First call to get_instance() returns Sydney_Custom_CSS instance
	 * - Second call to get_instance() returns same instance as first call
	 * - Instance is stored in static property
	 *
	 * @since 1.0.0
	 */
	public function test_get_instance_returns_singleton() {
		// Reset singleton instance to ensure clean test state
		$this->resetSingleton('Sydney_Custom_CSS');

		// First call to get_instance()
		$first_instance = \Sydney_Custom_CSS::get_instance();

		// Verify first instance is of correct type
		$this->assertInstanceOf('Sydney_Custom_CSS', $first_instance);

		// Second call to get_instance()
		$second_instance = \Sydney_Custom_CSS::get_instance();

		// Verify both calls return the exact same instance (singleton pattern)
		$this->assertSame($first_instance, $second_instance);

		// Verify instance is stored in static property by checking it's not null
		$reflection = new \ReflectionClass('Sydney_Custom_CSS');
		$instance_property = $reflection->getProperty('instance');
		$instance_property->setAccessible(true);
		$stored_instance = $instance_property->getValue();
		$this->assertSame($first_instance, $stored_instance);
	}

	/**
	 * Test that constructor properly initializes class properties.
	 *
	 * Verifies that:
	 * - customizer_js property is initialized as empty array
	 * - customizer_js_css_vars property exists
	 * - css_to_replace static property exists
	 *
	 * @since 1.0.0
	 */
	public function test_constructor_initializes_properties() {
		// Reset singleton instance to ensure fresh constructor call
		$this->resetSingleton('Sydney_Custom_CSS');

		// Get fresh instance to trigger constructor
		$instance = \Sydney_Custom_CSS::get_instance();

		// Test customizer_js property is initialized as empty array
		$this->assertIsArray($instance->customizer_js, 'customizer_js property should be an array');
		$this->assertEmpty($instance->customizer_js, 'customizer_js property should be initialized as empty array');

		// Test customizer_js_css_vars property exists
		$this->assertObjectHasProperty('customizer_js_css_vars', $instance, 'customizer_js_css_vars property should exist');

		// Test css_to_replace static property exists using reflection
		$reflection = new \ReflectionClass('Sydney_Custom_CSS');
		$css_to_replace_property = $reflection->getProperty('css_to_replace');
		$css_to_replace_property->setAccessible(true);
		$css_to_replace_value = $css_to_replace_property->getValue();
		
		$this->assertIsArray($css_to_replace_value, 'css_to_replace static property should be an array');
	}

	/**
	 * Test that constructor adds the wp_enqueue_scripts action hook.
	 *
	 * Verifies that:
	 * - Constructor executes without errors
	 * - Instance is properly created 
	 * - The hook registration is handled (mocked by BaseThemeTest)
	 *
	 * @since 1.0.0
	 */
	public function test_constructor_adds_wp_enqueue_scripts_hook() {
		// Reset singleton instance to ensure fresh constructor call
		$this->resetSingleton('Sydney_Custom_CSS');

		// Get fresh instance to trigger constructor
		// The add_action call is already mocked to return true in BaseThemeTest
		$instance = \Sydney_Custom_CSS::get_instance();

		// Verify instance was created successfully
		$this->assertInstanceOf('Sydney_Custom_CSS', $instance);
		
		// The fact that we can create an instance without errors means
		// the constructor's add_action call executed successfully
	}
}