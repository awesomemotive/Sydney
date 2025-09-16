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
     * Get the theme dependencies that this test class requires.
     *
     * Child classes should override this method to specify which theme
     * dependencies they need loaded. This provides a clean, declarative
     * way to manage test dependencies.
     *
     * @since 1.0.0
     * @return array Array of dependency types to load.
     *               Supported types: 'modules', 'hf-builder', 'styles', 'posts-archive'
     */
    protected function getRequiredDependencies(): array {
        return [];
    }

    /**
     * Set up the test environment before each test method.
     *
     * This method is called before each individual test method is executed.
     * It initializes WP_Mock, sets up common WordPress function mocks,
     * and loads any dependencies declared by the child class.
     *
     * @since 1.0.0
     * @return void
     */
    public function setUp(): void {
        parent::setUp();
        M::setUp();

        // Identity-mock escaping functions so HTML assertions are cleaner.
        // These functions return their input unchanged for testing purposes.
        M::userFunction('esc_url',      ['return_arg' => 0]);
        M::userFunction('esc_attr',     ['return_arg' => 0]);
        M::userFunction('esc_html',     ['return_arg' => 0]);
        M::userFunction('wp_kses_post', ['return_arg' => 0]);

        // Mock common WordPress functions used across themes
        M::userFunction('is_customize_preview', ['return' => false]);
        M::userFunction('add_action', ['return' => true]);
        M::userFunction('add_filter', ['return' => true]);
        M::userFunction('remove_all_actions', ['return' => true]);

        // Load dependencies declared by child class
        $dependencies = $this->getRequiredDependencies();
        if (!empty($dependencies)) {
            $this->loadThemeDependencies($dependencies);
        }
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
    public function tearDown(): void {
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

    /**
     * Reset a singleton instance using reflection.
     *
     * This utility method resets any singleton class instance to null, forcing
     * a fresh instance to be created on the next get_instance() call. This is
     * essential for testing singleton classes to ensure each test starts with
     * a clean state.
     *
     * @since 1.0.0
     * @param string $className The fully qualified class name of the singleton.
     * @param string $propertyName The name of the static instance property (default: 'instance').
     * @return void
     * @throws \ReflectionException If the class or property doesn't exist.
     */
    protected function resetSingleton(string $className, string $propertyName = 'instance'): void {
        if (!class_exists($className)) {
            return; // Silently return if class doesn't exist
        }

        $reflection = new \ReflectionClass($className);
        
        if (!$reflection->hasProperty($propertyName)) {
            return; // Silently return if property doesn't exist
        }

        $instance_property = $reflection->getProperty($propertyName);
        $instance_property->setAccessible(true);
        $instance_property->setValue(null, null);
        $instance_property->setAccessible(false);
    }

    /**
     * Mock common WordPress translation and escaping functions.
     *
     * Sets up mocks for frequently used translation and escaping functions
     * that return their input unchanged for cleaner test assertions.
     *
     * @since 1.0.0
     * @return void
     */
    protected function mockTranslationFunctions(): void {
        $this->mockFunction('esc_html__', function($text, $domain = null) { return $text; });
        $this->mockFunction('esc_attr__', function($text, $domain = null) { return $text; });
        $this->mockFunction('esc_html_e', function($text, $domain = null) { echo $text; });
        $this->mockFunction('esc_attr_e', function($text, $domain = null) { echo $text; });
        $this->mockFunction('__', function($text, $domain = null) { return $text; });
        $this->mockFunction('_e', function($text, $domain = null) { echo $text; });
        $this->mockFunction('_x', function($text, $context, $domain = null) { return $text; });
        $this->mockFunction('_ex', function($text, $context, $domain = null) { echo $text; });
        $this->mockFunction('_n', function($single, $plural, $number, $domain = null) { 
            return $number == 1 ? $single : $plural; 
        });
        $this->mockFunction('_nx', function($single, $plural, $number, $context, $domain = null) {
            return $number == 1 ? $single : $plural;
        });
        $this->mockFunction('number_format_i18n', function($number) { return $number; });
    }

    /**
     * Mock common WordPress site information functions.
     *
     * Sets up mocks for functions that provide site information like
     * URLs, site name, description, etc.
     *
     * @since 1.0.0
     * @param array $config Configuration array with keys:
     *                      - 'site_name' (default: 'Test Site')
     *                      - 'site_description' (default: 'Test Site Description')
     *                      - 'home_url' (default: 'https://example.com')
     *                      - 'template_directory' (default: theme directory)
     * @return void
     */
    protected function mockSiteInfoFunctions(array $config = []): void {
        $defaults = [
            'site_name' => 'Test Site',
            'site_description' => 'Test Site Description', 
            'home_url' => 'https://example.com',
            'template_directory' => __DIR__ . '/../../'
        ];
        $config = array_merge($defaults, $config);

        $this->mockFunction('get_template_directory', $config['template_directory']);
        $this->mockFunction('home_url', function($path = '/') use ($config) {
            return $config['home_url'] . $path;
        });
        $this->mockFunction('bloginfo', function($show = '') use ($config) {
            switch ($show) {
                case 'name':
                    echo $config['site_name'];
                    break;
                case 'description':
                    echo $config['site_description'];
                    break;
                default:
                    echo $config['site_name'];
                    break;
            }
        });
        $this->mockFunction('get_bloginfo', function($show = '', $filter = 'raw') use ($config) {
            switch ($show) {
                case 'name':
                    return $config['site_name'];
                case 'description':
                    return $config['site_description'];
                default:
                    return $config['site_name'];
            }
        });
    }

    /**
     * Mock common Sydney theme-specific functions.
     *
     * Sets up mocks for Sydney theme's custom functions used across
     * multiple components and templates.
     *
     * @since 1.0.0
     * @return void
     */
    protected function mockSydneyThemeFunctions(): void {
        $this->mockFunction('sydney_get_schema', function($type) {
            // Default to Organization schema for most components
            echo 'itemscope itemtype="https://schema.org/Organization"';
        });
        
        $this->mockFunction('sydney_do_schema', function($type) {
            $props = [
                'logo' => 'itemprop="logo"',
                'url' => 'itemprop="url"',
                'name' => 'itemprop="name"'
            ];
            echo $props[$type] ?? 'itemprop="' . $type . '"';
        });
        
        $this->mockFunction('sydney_get_svg_icon', function($icon) {
            return '<svg class="' . $icon . '"><use xlink:href="#' . $icon . '"></use></svg>';
        });
        
        $this->mockFunction('sydney_social_profile', function($profile_type) {
            $profiles = [
                'facebook' => '<a href="https://facebook.com" class="social-link facebook">Facebook</a>',
                'twitter' => '<a href="https://twitter.com" class="social-link twitter">Twitter</a>',
                'instagram' => '<a href="https://instagram.com" class="social-link instagram">Instagram</a>'
            ];
            echo $profiles[$profile_type] ?? '';
        });
        
        $this->mockFunction('sydney_get_header_search_icon', function() {
            return '<svg class="search-icon"><use xlink:href="#search"></use></svg>';
        });
    }

    /**
     * Mock common WordPress conditional functions.
     *
     * Sets up mocks for WordPress conditional functions that determine
     * the current page context.
     *
     * @since 1.0.0
     * @param array $conditions Array of conditions to set:
     *                         - 'is_front_page' (default: false)
     *                         - 'is_customize_preview' (default: false)
     *                         - 'is_admin' (default: false)
     * @return void
     */
    protected function mockConditionalFunctions(array $conditions = []): void {
        $defaults = [
            'is_front_page' => false,
            'is_customize_preview' => false,
            'is_admin' => false
        ];
        $conditions = array_merge($defaults, $conditions);

        $this->mockFunction('is_front_page', $conditions['is_front_page']);
        $this->mockFunction('is_customize_preview', $conditions['is_customize_preview']);
        $this->mockFunction('is_admin', $conditions['is_admin']);
    }

    /**
     * Mock common WordPress media functions.
     *
     * Sets up mocks for WordPress media and attachment functions.
     *
     * @since 1.0.0
     * @param array $media_config Array of media configurations:
     *                           - 'attachments' => array of URL => [id, width, height] mappings
     * @return void
     */
    protected function mockMediaFunctions(array $media_config = []): void {
        $defaults = [
            'attachments' => [
                'https://example.com/logo.png' => [123, 200, 100],
                'https://example.com/image.jpg' => [456, 800, 600]
            ]
        ];
        $media_config = array_merge($defaults, $media_config);

        $this->mockFunction('attachment_url_to_postid', function($url) use ($media_config) {
            return isset($media_config['attachments'][$url]) ? $media_config['attachments'][$url][0] : 0;
        });
        
        $this->mockFunction('wp_get_attachment_image_src', function($id, $size = 'full') use ($media_config) {
            foreach ($media_config['attachments'] as $url => $data) {
                if ($data[0] === $id) {
                    return [$url, $data[1], $data[2]];
                }
            }
            return false;
        });
    }

    /**
     * Set up basic comment-related WordPress function mocks.
     *
     * Provides common mocks for comment-related WordPress functions
     * used across multiple comment tests.
     *
     * @since 1.0.0
     * @return void
     */
    protected function setupBasicCommentMocks(): void {
        $this->mockFunction('wp_list_comments', '');
        $this->mockFunction('comment_form', '');
        $this->mockFunction('post_type_supports', true);
        $this->mockFunction('get_post_type', 'post');
        $this->mockFunction('previous_comments_link', '');
        $this->mockFunction('next_comments_link', '');
    }

    /**
     * Set up a specific comment scenario with pre-configured mocks.
     *
     * Provides pre-configured comment scenarios to eliminate repetitive
     * mock setup across comment tests.
     *
     * @since 1.0.0
     * @param string $scenario The comment scenario to set up
     * @param array $overrides Array of values to override scenario defaults
     * @return void
     */
    protected function setupCommentScenario(string $scenario, array $overrides = []): void {
        $scenarios = [
            'password_protected' => [
                'post_password_required' => true,
                'have_comments' => false,
                'comments_open' => false,
                'get_comments_number' => 0,
                'get_comment_pages_count' => 1,
                'get_the_title' => 'Test Post Title'
            ],
            'comments_with_pagination' => [
                'post_password_required' => false,
                'have_comments' => true,
                'comments_open' => true,
                'get_comments_number' => 15,
                'get_comment_pages_count' => 3,
                'get_the_title' => 'Test Post Title'
            ],
            'comments_open_none_exist' => [
                'post_password_required' => false,
                'have_comments' => false,
                'comments_open' => true,
                'get_comments_number' => 0,
                'get_comment_pages_count' => 1,
                'get_the_title' => 'Test Post Title'
            ],
            'comments_closed_with_existing' => [
                'post_password_required' => false,
                'have_comments' => false,
                'comments_open' => false,
                'get_comments_number' => 5,
                'get_comment_pages_count' => 1,
                'get_the_title' => 'Test Post Title'
            ],
            'comments_closed_none_exist' => [
                'post_password_required' => false,
                'have_comments' => false,
                'comments_open' => false,
                'get_comments_number' => '0', // String '0' not integer 0
                'get_comment_pages_count' => 1,
                'get_the_title' => 'Test Post Title'
            ],
            'single_comment' => [
                'post_password_required' => false,
                'have_comments' => true,
                'comments_open' => true,
                'get_comments_number' => 1,
                'get_comment_pages_count' => 1,
                'get_the_title' => 'Sample Post'
            ],
            'multiple_comments' => [
                'post_password_required' => false,
                'have_comments' => true,
                'comments_open' => true,
                'get_comments_number' => 5,
                'get_comment_pages_count' => 1,
                'get_the_title' => 'Sample Post'
            ]
        ];

        if (!isset($scenarios[$scenario])) {
            throw new \InvalidArgumentException("Unknown comment scenario: {$scenario}");
        }

        $config = array_merge($scenarios[$scenario], $overrides);

        // Apply the configuration as mocks
        foreach ($config as $function => $return_value) {
            $this->mockFunction($function, $return_value);
        }

        // Set up page_comments option based on scenario
        if ($scenario === 'comments_with_pagination') {
            $this->mockOptions(['page_comments' => true]);
        } else {
            $this->mockOptions(['page_comments' => false]);
        }

        // Set up basic comment mocks
        $this->setupBasicCommentMocks();
        
        // Set up translation functions
        $this->mockTranslationFunctions();
        
        // Override specific translation functions for comment scenarios
        if (in_array($scenario, ['single_comment', 'multiple_comments', 'comments_with_pagination'])) {
            // Override _nx for comment count formatting if not already overridden
            if (!isset($overrides['_nx_override'])) {
                $this->mockFunction('_nx', function($single, $plural, $number, $context, $domain) {
                    return $number == 1 ? $single : $plural;
                });
            }
        }
    }

    /**
     * Assert that HTML output contains all specified strings.
     *
     * This helper method checks that all provided strings are present in the HTML output,
     * providing better error messages and reducing repetitive assertion code.
     *
     * @since 1.0.0
     * @param string $html The HTML content to check
     * @param array $expectedStrings Array of strings that should be present
     * @param string $message Optional custom error message
     * @return void
     */
    protected function assertHtmlContainsAll(string $html, array $expectedStrings, string $message = ''): void {
        $missing = [];
        foreach ($expectedStrings as $expected) {
            if (strpos($html, $expected) === false) {
                $missing[] = $expected;
            }
        }
        
        if (!empty($missing)) {
            $errorMessage = $message ?: 'HTML does not contain all expected strings';
            $errorMessage .= "\n\nMissing elements:\n";
            foreach ($missing as $missingElement) {
                $errorMessage .= "- " . $missingElement . "\n";
            }
            
            // Show first 500 characters of actual HTML for debugging
            $errorMessage .= "\nActual HTML (first 500 chars):\n" . substr($html, 0, 500) . "...";
            
            $this->fail($errorMessage);
        }
    }

    /**
     * Assert that HTML output contains none of the specified strings.
     *
     * This helper method checks that none of the provided strings are present in the HTML output,
     * useful for verifying that certain elements should not be rendered.
     *
     * @since 1.0.0
     * @param string $html The HTML content to check
     * @param array $unexpectedStrings Array of strings that should not be present
     * @param string $message Optional custom error message
     * @return void
     */
    protected function assertHtmlContainsNone(string $html, array $unexpectedStrings, string $message = ''): void {
        $found = [];
        foreach ($unexpectedStrings as $unexpected) {
            if (strpos($html, $unexpected) !== false) {
                $found[] = $unexpected;
            }
        }
        
        if (!empty($found)) {
            $errorMessage = $message ?: 'HTML contains unexpected strings';
            $errorMessage .= "\n\nUnexpected elements found:\n";
            foreach ($found as $foundElement) {
                $errorMessage .= "- " . $foundElement . "\n";
            }
            
            $this->fail($errorMessage);
        }
    }

    /**
     * Assert that HTML output has correct schema markup.
     *
     * This helper method verifies that the HTML contains the specified schema.org markup,
     * which is commonly used across Sydney theme components.
     *
     * @since 1.0.0
     * @param string $html The HTML content to check
     * @param string $schemaType The schema type (e.g., 'Organization', 'ImageObject')
     * @param string $message Optional custom error message
     * @return void
     */
    protected function assertHtmlHasSchema(string $html, string $schemaType, string $message = ''): void {
        $expectedSchema = 'itemscope itemtype="https://schema.org/' . $schemaType . '"';
        $this->assertStringContainsString(
            $expectedSchema,
            $html,
            $message ?: "HTML should contain {$schemaType} schema markup: {$expectedSchema}"
        );
    }

    /**
     * Assert that HTML output has correct CSS classes.
     *
     * This helper method verifies that the HTML contains elements with the specified CSS classes,
     * useful for checking component styling and structure.
     *
     * @since 1.0.0
     * @param string $html The HTML content to check
     * @param array $expectedClasses Array of CSS classes that should be present
     * @param string $message Optional custom error message
     * @return void
     */
    protected function assertHtmlHasClasses(string $html, array $expectedClasses, string $message = ''): void {
        $missing = [];
        foreach ($expectedClasses as $class) {
            $classPattern = 'class="' . $class . '"';
            if (strpos($html, $classPattern) === false) {
                // Also try with additional classes (class="other-class target-class")
                $flexiblePattern = 'class="[^"]*' . preg_quote($class, '/') . '[^"]*"';
                if (!preg_match('/' . $flexiblePattern . '/', $html)) {
                    $missing[] = $class;
                }
            }
        }
        
        if (!empty($missing)) {
            $errorMessage = $message ?: 'HTML does not contain all expected CSS classes';
            $errorMessage .= "\n\nMissing classes:\n";
            foreach ($missing as $missingClass) {
                $errorMessage .= "- " . $missingClass . "\n";
            }
            
            $this->fail($errorMessage);
        }
    }

    /**
     * Assert that HTML output has correct attributes.
     *
     * This helper method verifies that the HTML contains elements with the specified attributes,
     * useful for checking accessibility, data attributes, and other element properties.
     *
     * @since 1.0.0
     * @param string $html The HTML content to check
     * @param array $expectedAttributes Array of attribute="value" pairs that should be present
     * @param string $message Optional custom error message
     * @return void
     */
    protected function assertHtmlHasAttributes(string $html, array $expectedAttributes, string $message = ''): void {
        $missing = [];
        foreach ($expectedAttributes as $attribute) {
            if (strpos($html, $attribute) === false) {
                $missing[] = $attribute;
            }
        }
        
        if (!empty($missing)) {
            $errorMessage = $message ?: 'HTML does not contain all expected attributes';
            $errorMessage .= "\n\nMissing attributes:\n";
            foreach ($missing as $missingAttribute) {
                $errorMessage .= "- " . $missingAttribute . "\n";
            }
            
            $this->fail($errorMessage);
        }
    }

    /**
     * Assert that HTML output represents a valid component structure.
     *
     * This helper method performs comprehensive checks for common Sydney theme component patterns,
     * including wrapper elements, schema markup, and expected content structure.
     *
     * @since 1.0.0
     * @param string $html The HTML content to check
     * @param array $config Configuration array with keys:
     *                     - 'component_id' => component identifier
     *                     - 'wrapper_class' => expected wrapper CSS class
     *                     - 'schema_type' => schema.org type
     *                     - 'required_elements' => array of required HTML elements
     *                     - 'forbidden_elements' => array of elements that should not be present
     * @param string $message Optional custom error message
     * @return void
     */
    protected function assertValidComponentStructure(string $html, array $config, string $message = ''): void {
        $errors = [];
        
        // Check component wrapper
        if (isset($config['component_id'])) {
            $expectedWrapper = 'data-component-id="' . $config['component_id'] . '"';
            if (strpos($html, $expectedWrapper) === false) {
                $errors[] = "Missing component wrapper with data-component-id=\"{$config['component_id']}\"";
            }
        }
        
        // Check wrapper class
        if (isset($config['wrapper_class'])) {
            if (strpos($html, 'class="' . $config['wrapper_class'] . '"') === false) {
                $errors[] = "Missing wrapper class: {$config['wrapper_class']}";
            }
        }
        
        // Check schema markup
        if (isset($config['schema_type'])) {
            $expectedSchema = 'itemscope itemtype="https://schema.org/' . $config['schema_type'] . '"';
            if (strpos($html, $expectedSchema) === false) {
                $errors[] = "Missing schema markup: {$expectedSchema}";
            }
        }
        
        // Check required elements
        if (isset($config['required_elements'])) {
            foreach ($config['required_elements'] as $element) {
                if (strpos($html, $element) === false) {
                    $errors[] = "Missing required element: {$element}";
                }
            }
        }
        
        // Check forbidden elements
        if (isset($config['forbidden_elements'])) {
            foreach ($config['forbidden_elements'] as $element) {
                if (strpos($html, $element) !== false) {
                    $errors[] = "Found forbidden element: {$element}";
                }
            }
        }
        
        if (!empty($errors)) {
            $errorMessage = $message ?: 'Component structure validation failed';
            $errorMessage .= "\n\nValidation errors:\n";
            foreach ($errors as $error) {
                $errorMessage .= "- " . $error . "\n";
            }
            
            $this->fail($errorMessage);
        }
    }

    /**
     * Load Sydney theme dependencies safely for testing.
     *
     * This method ensures that required theme classes and dependencies are loaded
     * in the correct order for testing purposes. It handles conditional loading
     * to prevent duplicate class declarations.
     *
     * @since 1.0.0
     * @param array $dependencies Array of dependency types to load.
     *                           Supported types: 'modules', 'hf-builder', 'styles'
     * @return void
     */
    protected function loadThemeDependencies(array $dependencies = []): void {
        // Load Sydney_Modules class first if needed
        if (in_array('modules', $dependencies) && !class_exists('Sydney_Modules')) {
            // Load the modules class (prefer the one from /inc/modules as it's loaded first in functions.php)
            require_once __DIR__ . '/../../inc/modules/class-sydney-modules.php';
        }

        // Load Header/Footer Builder if needed
        if (in_array('hf-builder', $dependencies)) {
            // Mock the module activation check to return true for testing
            if (class_exists('Sydney_Modules')) {
                M::userFunction('get_option', [
                    'return' => function($option_name, $default = null) {
                        if ($option_name === 'sydney-modules') {
                            return ['hf-builder' => true];
                        }
                        return $default;
                    }
                ]);
            }

            // Load the header-footer builder class if not already loaded
            if (!class_exists('Sydney_Header_Footer_Builder')) {
                require_once __DIR__ . '/../../inc/modules/hf-builder/class-header-footer-builder.php';
            }
        }

        // Load Sydney_Custom_CSS class if needed
        if (in_array('styles', $dependencies) && !class_exists('Sydney_Custom_CSS')) {
            require_once __DIR__ . '/../../inc/styles.php';
        }

        // Load Sydney_Posts_Archive class if needed
        if (in_array('posts-archive', $dependencies) && !class_exists('Sydney_Posts_Archive')) {
            require_once __DIR__ . '/../../inc/classes/class-sydney-posts-archive.php';
        }
    }
}