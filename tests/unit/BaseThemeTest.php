<?php
/**
 * Base Test Class for Sydney Theme Unit Tests
 *
 * This abstract class provides a foundation for all Sydney theme unit tests,
 * setting up common WordPress mocks and utility methods for consistent testing.
 *
 * @package Sydney
 * @subpackage Tests
 */

namespace Sydney\Tests;

use WP_Mock\Tools\TestCase as WPTestCase;
use WP_Mock as M;

/**
 * Abstract base class for Sydney theme unit tests.
 *
 * Provides common setup, teardown, and utility methods for testing WordPress
 * theme functionality using WP_Mock. This class handles the boilerplate setup
 * required for most theme tests and provides helper methods for common testing
 * scenarios.
 *
 * @since 1.0.0
 */
abstract class BaseThemeTest extends WPTestCase {
    
    /**
     * Set up the test environment before each test method.
     *
     * This method is called before each individual test method is executed.
     * It initializes WP_Mock and sets up common WordPress function mocks
     * that are used across multiple tests.
     *
     * @since 1.0.0
     * @return void
     */
    protected function setUp(): void {
        parent::setUp();
        M::setUp();

        // Identity-mock escaping functions so HTML assertions are cleaner.
        // These functions return their input unchanged for testing purposes.
        M::userFunction('esc_url',      ['return_arg' => 0]);
        M::userFunction('esc_attr',     ['return_arg' => 0]);
        M::userFunction('esc_html',     ['return_arg' => 0]);
        M::userFunction('wp_kses_post', ['return_arg' => 0]);
    }

    /**
     * Clean up the test environment after each test method.
     *
     * This method is called after each individual test method is executed.
     * It properly tears down WP_Mock to ensure a clean state for the next test.
     *
     * @since 1.0.0
     * @return void
     */
    protected function tearDown(): void {
        M::tearDown();
        parent::tearDown();
    }

    /**
     * Capture output from a callable function.
     *
     * This utility method captures any output (echo, print, etc.) generated
     * by the provided callable and returns it as a string. Useful for testing
     * functions that output HTML or other content directly.
     *
     * @since 1.0.0
     * @param callable $fn The function to execute and capture output from.
     * @return string The captured output as a string.
     * @throws \Throwable Re-throws any exception that occurs during execution.
     */
    protected function captureOutput(callable $fn): string {
        ob_start();
        try {
            $fn();
            return ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    }

    /**
     * Mock WordPress theme modification functions.
     *
     * Sets up a mock for the get_theme_mod() function that returns values
     * from the provided map. This allows tests to simulate different theme
     * customizer settings without actually modifying the database.
     *
     * @since 1.0.0
     * @param array $map Associative array mapping theme mod names to their values.
     *                   Format: ['mod_name' => 'mod_value', ...]
     * @return void
     */
    protected function mockThemeMods(array $map): void {
        M::userFunction('get_theme_mod', [
            'return' => function ($name, $default = null) use ($map) {
                return array_key_exists($name, $map) ? $map[$name] : $default;
            }
        ]);
    }

    /**
     * Mock WordPress options functions.
     *
     * Sets up a mock for the get_option() function that returns values
     * from the provided map. This allows tests to simulate different
     * WordPress options without actually modifying the database.
     *
     * @since 1.0.0
     * @param array $map Associative array mapping option names to their values.
     *                   Format: ['option_name' => 'option_value', ...]
     * @return void
     */
    protected function mockOptions(array $map): void {
        M::userFunction('get_option', [
            'return' => function ($name, $default = null) use ($map) {
                return array_key_exists($name, $map) ? $map[$name] : $default;
            }
        ]);
    }

    /**
     * Mock a WordPress function with a specific return value.
     *
     * This is a convenience method for quickly mocking any WordPress function
     * to return a specific value. Useful for simple function mocks that don't
     * require complex logic.
     *
     * @since 1.0.0
     * @param string $name   The name of the WordPress function to mock.
     * @param mixed  $return The value that the mocked function should return.
     * @return void
     */
    protected function mockFunction(string $name, $return): void {
        M::userFunction($name, ['return' => $return]);
    }
}