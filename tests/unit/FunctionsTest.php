<?php
/**
 * Unit tests for the functions.php file in Sydney theme
 *
 * @package Sydney
 * @subpackage Tests
 */

namespace Sydney\Tests;

use Sydney\Tests\BaseThemeTest;
use WP_Mock as M;

/**
 * Test class for the functions.php file.
 *
 * @since 1.0.0
 */
class FunctionsTest extends BaseThemeTest {

	/**
	 * Test constants for consistent URL usage
	 */
	private const TEST_THEME_URL = 'https://example.com/wp-content/themes/sydney';
	private const TEST_STYLESHEET_URL = 'https://example.com/wp-content/themes/sydney/style.css';
	private const TEST_GOOGLE_FONTS_URL = 'https://fonts.googleapis.com/css?family=Open+Sans';

	/**
	 * Get required dependencies for this test class.
	 *
	 * @return array
	 */
	protected function getRequiredDependencies(): array {
		return ['modules'];
	}

	/**
	 * Load a function from functions.php file for testing.
	 *
	 * @param string $functionName The name of the function to load
	 * @param string $endPattern The pattern to find the end of the function
	 * @return void
	 */
	private function loadFunctionFromFile(string $functionName, string $endPattern): void {
		if (!function_exists($functionName)) {
			$functions_content = file_get_contents(__DIR__ . '/../../functions.php');
			$start = strpos($functions_content, "function {$functionName}() {");
			$end = strpos($functions_content, $endPattern);
			$function_code = substr($functions_content, $start, $end - $start);
			eval($function_code);
		}
	}

	/**
	 * Set up basic WordPress mocks used across multiple tests.
	 *
	 * @return void
	 */
	private function setupBasicWordPressMocks(): void {
		$this->mockFunction('sydney_is_amp', false);
		$this->mockFunction('wp_style_add_data', true);
		
		// Use BaseThemeTest helper for site info functions
		$this->mockSiteInfoFunctions([
			'template_directory' => self::TEST_THEME_URL
		]);
		
		// Mock get_template_directory_uri separately as it's not in the helper
		$this->mockFunction('get_template_directory_uri', self::TEST_THEME_URL);
		
		$this->mockFunction('is_singular', false);
		$this->mockFunction('comments_open', false);
		$this->mockFunction('get_comments_number', '0');
		$this->mockFunction('get_option', false);
		$this->mockFunction('get_stylesheet_uri', self::TEST_STYLESHEET_URL);
	}

	/**
	 * Set up enqueue tracking arrays for scripts and styles.
	 *
	 * @param array $enqueued_styles Reference to styles array
	 * @param array $enqueued_scripts Reference to scripts array
	 * @return void
	 */
	private function setupEnqueueTracking(array &$enqueued_styles, array &$enqueued_scripts): void {
		M::userFunction('wp_enqueue_style', [
			'return' => function($handle, $src = '', $deps = [], $ver = false, $media = 'all') use (&$enqueued_styles) {
				$enqueued_styles[$handle] = [
					'src' => $src,
					'deps' => $deps,
					'ver' => $ver,
					'media' => $media
				];
				return true;
			}
		]);
		
		M::userFunction('wp_enqueue_script', [
			'return' => function($handle, $src = '', $deps = [], $ver = false, $in_footer = false) use (&$enqueued_scripts) {
				$enqueued_scripts[$handle] = [
					'src' => $src,
					'deps' => $deps,
					'ver' => $ver,
					'in_footer' => $in_footer
				];
				return true;
			}
		]);
	}

	/**
	 * Set up the test environment before each test method.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
	}

	/**
	 * Test Sydney theme helper functions.
	 *
	 * Tests that:
	 * - mockSydneyThemeFunctions helper works correctly
	 * - SVG icon generation is mocked
	 *
	 * @since 1.0.0
	 */
	public function test_sydney_helper_functions() {
		$this->mockSydneyThemeFunctions();
		
		// Test SVG icon generation
		$svg_output = sydney_get_svg_icon('test-icon');
		$this->assertStringContainsString('<svg', $svg_output);
		$this->assertStringContainsString('test-icon', $svg_output);
		
		// Test schema function
		ob_start();
		sydney_get_schema('Organization');
		$schema_output = ob_get_clean();
		$this->assertStringContainsString('itemscope', $schema_output);
		$this->assertStringContainsString('Organization', $schema_output);
	}

	/**
	 * Test sydney_scripts function enqueue behavior.
	 *
	 * Tests that:
	 * - Google Fonts are enqueued when google fonts URL is available
	 * - Main Functions Script is enqueued when not AMP
	 *
	 * @since 1.0.0
	 */
	public function test_sydney_scripts_google_fonts_enqueue() {
		// Load the sydney_scripts function
		$this->loadFunctionFromFile('sydney_scripts', 'add_action( \'wp_enqueue_scripts\', \'sydney_scripts\' );');

		// Set up basic WordPress mocks
		$this->setupBasicWordPressMocks();
		$this->mockFunction('sydney_google_fonts_url', self::TEST_GOOGLE_FONTS_URL);
		
		// Use BaseThemeTest helper for theme mods
		$this->mockThemeMods([
			'front_header_type' => 'nothing',
			'site_header_type' => 'nothing'
		]);
		
		// Use BaseThemeTest helper for conditional functions
		$this->mockConditionalFunctions([
			'is_front_page' => false
		]);

		// Track enqueued styles and scripts
		$enqueued_styles = [];
		$enqueued_scripts = [];
		$this->setupEnqueueTracking($enqueued_styles, $enqueued_scripts);

		// Execute the function
		sydney_scripts();

		// ASSERTION 1: Verify Google Fonts are enqueued when URL is available
		$this->assertArrayHasKey('sydney-google-fonts', $enqueued_styles, 'Google Fonts should be enqueued when URL is available');
		$this->assertEquals(self::TEST_GOOGLE_FONTS_URL, $enqueued_styles['sydney-google-fonts']['src']);
		
		// ASSERTION 3: Verify Main Functions Script is enqueued when not AMP
		$this->assertArrayHasKey('sydney-functions', $enqueued_scripts, 'Main functions script should be enqueued when not AMP');
		$this->assertEquals(self::TEST_THEME_URL . '/js/functions.min.js', $enqueued_scripts['sydney-functions']['src']);
		$this->assertEquals([], $enqueued_scripts['sydney-functions']['deps']);
		$this->assertTrue($enqueued_scripts['sydney-functions']['in_footer'], 'Functions script should be loaded in footer');
	}

	/**
	 * Data provider for hero slider test scenarios.
	 *
	 * @return array Test scenarios with slider configuration and expected results
	 */
	public function heroSliderScenariosProvider(): array {
		return [
			'slider_on_front_page' => [
				'front_header_type' => 'slider',
				'site_header_type' => 'nothing',
				'is_front_page' => true,
				'should_enqueue_slider' => true,
				'description' => 'Hero slider should be enqueued when slider is active on front page'
			],
			'slider_on_site_pages' => [
				'front_header_type' => 'nothing',
				'site_header_type' => 'slider',
				'is_front_page' => false,
				'should_enqueue_slider' => true,
				'description' => 'Hero slider should be enqueued when slider is active on site pages'
			],
			'no_slider_front_page' => [
				'front_header_type' => 'nothing',
				'site_header_type' => 'nothing',
				'is_front_page' => true,
				'should_enqueue_slider' => false,
				'description' => 'Hero slider should not be enqueued when no slider is set'
			],
			'wrong_page_type' => [
				'front_header_type' => 'slider',
				'site_header_type' => 'nothing',
				'is_front_page' => false,
				'should_enqueue_slider' => false,
				'description' => 'Hero slider should not be enqueued when front slider is set but not on front page'
			],
		];
	}

	/**
	 * Test sydney_scripts function hero slider enqueue behavior with various scenarios.
	 *
	 * @dataProvider heroSliderScenariosProvider
	 * @param string $front_header_type Front page header type
	 * @param string $site_header_type Site pages header type
	 * @param bool $is_front_page Whether current page is front page
	 * @param bool $should_enqueue_slider Whether slider assets should be enqueued
	 * @param string $description Test scenario description
	 *
	 * @since 1.0.0
	 */
	public function test_sydney_scripts_hero_slider_enqueue(string $front_header_type, string $site_header_type, bool $is_front_page, bool $should_enqueue_slider, string $description) {
		// Load the sydney_scripts function
		$this->loadFunctionFromFile('sydney_scripts', 'add_action( \'wp_enqueue_scripts\', \'sydney_scripts\' );');

		// Set up basic WordPress mocks
		$this->setupBasicWordPressMocks();
		$this->mockFunction('sydney_google_fonts_url', null); // No Google Fonts for this test
		
		// Use BaseThemeTest helper for theme mods
		$this->mockThemeMods([
			'front_header_type' => $front_header_type,
			'site_header_type' => $site_header_type
		]);
		
		// Use BaseThemeTest helper for conditional functions
		$this->mockConditionalFunctions([
			'is_front_page' => $is_front_page
		]);

		// Track enqueued styles and scripts
		$enqueued_styles = [];
		$enqueued_scripts = [];
		$this->setupEnqueueTracking($enqueued_styles, $enqueued_scripts);

		// Execute the function
		sydney_scripts();

		// Assert based on expected behavior
		if ($should_enqueue_slider) {
			$this->assertArrayHasKey('sydney-scripts', $enqueued_scripts, 'Sydney scripts should be enqueued when hero slider is active');
			$this->assertEquals(self::TEST_THEME_URL . '/js/scripts.js', $enqueued_scripts['sydney-scripts']['src']);
			$this->assertEquals(['jquery'], $enqueued_scripts['sydney-scripts']['deps']);
			
			$this->assertArrayHasKey('sydney-hero-slider', $enqueued_scripts, 'Hero slider script should be enqueued when slider is active');
			$this->assertEquals(self::TEST_THEME_URL . '/js/hero-slider.js', $enqueued_scripts['sydney-hero-slider']['src']);
			$this->assertEquals(['jquery'], $enqueued_scripts['sydney-hero-slider']['deps']);
			
			$this->assertArrayHasKey('sydney-hero-slider', $enqueued_styles, 'Hero slider styles should be enqueued when slider is active');
			$this->assertEquals(self::TEST_THEME_URL . '/css/components/hero-slider.min.css', $enqueued_styles['sydney-hero-slider']['src']);
		} else {
			$this->assertArrayNotHasKey('sydney-hero-slider', $enqueued_scripts, $description . ' - Hero slider script should not be enqueued');
			$this->assertArrayNotHasKey('sydney-hero-slider', $enqueued_styles, $description . ' - Hero slider styles should not be enqueued');
		}
	}

	/**
	 * Test sydney_scripts function Elementor enqueue behavior.
	 *
	 * Tests that:
	 * - Elementor scripts and styles are enqueued when Elementor plugin is active
	 *
	 * @since 1.0.0
	 */
	public function test_sydney_scripts_elementor_enqueue() {
		// Load only the sydney_scripts function definition
		if (!function_exists('sydney_scripts')) {
			$functions_content = file_get_contents(__DIR__ . '/../../functions.php');
			// Extract only the sydney_scripts function
			$start = strpos($functions_content, 'function sydney_scripts() {');
			$end = strpos($functions_content, 'add_action( \'wp_enqueue_scripts\', \'sydney_scripts\' );');
			$function_code = substr($functions_content, $start, $end - $start);
			eval($function_code);
		}

		// Mock WordPress functions for Elementor scenario
		$this->mockFunction('sydney_is_amp', false);
		$this->mockFunction('sydney_google_fonts_url', null); // No Google Fonts for this test
		$this->mockFunction('wp_style_add_data', true);
		
		// Use BaseThemeTest helper for site info functions
		$this->mockSiteInfoFunctions([
			'template_directory' => 'https://example.com/wp-content/themes/sydney'
		]);
		
		// Mock get_template_directory_uri separately as it's not in the helper
		$this->mockFunction('get_template_directory_uri', 'https://example.com/wp-content/themes/sydney');
		
		// Use BaseThemeTest helper for theme mods - NO SLIDER
		$this->mockThemeMods([
			'front_header_type' => 'nothing',
			'site_header_type' => 'nothing'
		]);
		
		// Use BaseThemeTest helper for conditional functions
		$this->mockConditionalFunctions([
			'is_front_page' => false
		]);
		
		$this->mockFunction('is_singular', false);
		$this->mockFunction('comments_open', false);
		$this->mockFunction('get_comments_number', '0');
		$this->mockFunction('get_option', false);
		$this->mockFunction('get_stylesheet_uri', 'https://example.com/wp-content/themes/sydney/style.css');
		
		// Create mock Elementor class to simulate plugin being active
		if (!class_exists('Elementor\Plugin')) {
			eval('namespace Elementor { class Plugin {} }');
		}

		// Track enqueued styles and scripts
		$enqueued_styles = [];
		$enqueued_scripts = [];
		
		M::userFunction('wp_enqueue_style', [
			'return' => function($handle, $src = '', $deps = [], $ver = false, $media = 'all') use (&$enqueued_styles) {
				$enqueued_styles[$handle] = [
					'src' => $src,
					'deps' => $deps,
					'ver' => $ver,
					'media' => $media
				];
				return true;
			}
		]);
		
		M::userFunction('wp_enqueue_script', [
			'return' => function($handle, $src = '', $deps = [], $ver = false, $in_footer = false) use (&$enqueued_scripts) {
				$enqueued_scripts[$handle] = [
					'src' => $src,
					'deps' => $deps,
					'ver' => $ver,
					'in_footer' => $in_footer
				];
				return true;
			}
		]);

		// Execute the function
		sydney_scripts();

		// ASSERTION 5: Verify Elementor Scripts and Styles are enqueued when Elementor plugin is active
		$this->assertArrayHasKey('sydney-scripts', $enqueued_scripts, 'Sydney scripts should be enqueued when Elementor is active');
		$this->assertEquals('https://example.com/wp-content/themes/sydney/js/scripts.js', $enqueued_scripts['sydney-scripts']['src']);
		$this->assertEquals(['jquery'], $enqueued_scripts['sydney-scripts']['deps']);
		
		$this->assertArrayHasKey('sydney-elementor', $enqueued_styles, 'Elementor styles should be enqueued when Elementor is active');
		$this->assertEquals('https://example.com/wp-content/themes/sydney/css/components/elementor.min.css', $enqueued_styles['sydney-elementor']['src']);
		$this->assertEquals([], $enqueued_styles['sydney-elementor']['deps']);
	}

	/**
	 * Test sydney_scripts function SiteOrigin enqueue behavior.
	 *
	 * Tests that:
	 * - SiteOrigin scripts and styles are enqueued when SiteOrigin Panels plugin is active
	 *
	 * @since 1.0.0
	 */
	public function test_sydney_scripts_siteorigin_enqueue() {
		// Load only the sydney_scripts function definition
		if (!function_exists('sydney_scripts')) {
			$functions_content = file_get_contents(__DIR__ . '/../../functions.php');
			// Extract only the sydney_scripts function
			$start = strpos($functions_content, 'function sydney_scripts() {');
			$end = strpos($functions_content, 'add_action( \'wp_enqueue_scripts\', \'sydney_scripts\' );');
			$function_code = substr($functions_content, $start, $end - $start);
			eval($function_code);
		}

		// Mock WordPress functions for SiteOrigin scenario
		$this->mockFunction('sydney_is_amp', false);
		$this->mockFunction('sydney_google_fonts_url', null); // No Google Fonts for this test
		$this->mockFunction('wp_style_add_data', true);
		
		// Use BaseThemeTest helper for site info functions
		$this->mockSiteInfoFunctions([
			'template_directory' => 'https://example.com/wp-content/themes/sydney'
		]);
		
		// Mock get_template_directory_uri separately as it's not in the helper
		$this->mockFunction('get_template_directory_uri', 'https://example.com/wp-content/themes/sydney');
		
		// Use BaseThemeTest helper for theme mods - NO SLIDER
		$this->mockThemeMods([
			'front_header_type' => 'nothing',
			'site_header_type' => 'nothing'
		]);
		
		// Use BaseThemeTest helper for conditional functions
		$this->mockConditionalFunctions([
			'is_front_page' => false
		]);
		
		$this->mockFunction('is_singular', false);
		$this->mockFunction('comments_open', false);
		$this->mockFunction('get_comments_number', '0');
		$this->mockFunction('get_stylesheet_uri', 'https://example.com/wp-content/themes/sydney/style.css');
		
		// Mock get_option to return Font Awesome v5 option as false (use v4)
		$this->mockOptions([
			'sydney-fontawesome-v5' => false
		]);
		
		// Define SITEORIGIN_PANELS_VERSION constant to simulate plugin being active
		if (!defined('SITEORIGIN_PANELS_VERSION')) {
			define('SITEORIGIN_PANELS_VERSION', '2.5.0');
		}

		// Track enqueued styles and scripts
		$enqueued_styles = [];
		$enqueued_scripts = [];
		
		M::userFunction('wp_enqueue_style', [
			'return' => function($handle, $src = '', $deps = [], $ver = false, $media = 'all') use (&$enqueued_styles) {
				$enqueued_styles[$handle] = [
					'src' => $src,
					'deps' => $deps,
					'ver' => $ver,
					'media' => $media
				];
				return true;
			}
		]);
		
		M::userFunction('wp_enqueue_script', [
			'return' => function($handle, $src = '', $deps = [], $ver = false, $in_footer = false) use (&$enqueued_scripts) {
				$enqueued_scripts[$handle] = [
					'src' => $src,
					'deps' => $deps,
					'ver' => $ver,
					'in_footer' => $in_footer
				];
				return true;
			}
		]);

		// Execute the function
		sydney_scripts();

		// ASSERTION 6: Verify SiteOrigin Scripts and Styles are enqueued when SiteOrigin Panels plugin is active
		$this->assertArrayHasKey('sydney-siteorigin', $enqueued_styles, 'SiteOrigin styles should be enqueued when SiteOrigin Panels is active');
		$this->assertEquals('https://example.com/wp-content/themes/sydney/css/components/siteorigin.min.css', $enqueued_styles['sydney-siteorigin']['src']);
		$this->assertEquals([], $enqueued_styles['sydney-siteorigin']['deps']);
		
		$this->assertArrayHasKey('sydney-scripts', $enqueued_scripts, 'Sydney scripts should be enqueued when SiteOrigin is active');
		$this->assertEquals('https://example.com/wp-content/themes/sydney/js/scripts.js', $enqueued_scripts['sydney-scripts']['src']);
		$this->assertEquals(['jquery'], $enqueued_scripts['sydney-scripts']['deps']);
		
		$this->assertArrayHasKey('sydney-so-legacy-scripts', $enqueued_scripts, 'SiteOrigin legacy scripts should be enqueued');
		$this->assertEquals('https://example.com/wp-content/themes/sydney/js/so-legacy.js', $enqueued_scripts['sydney-so-legacy-scripts']['src']);
		$this->assertEquals(['jquery'], $enqueued_scripts['sydney-so-legacy-scripts']['deps']);
		
		$this->assertArrayHasKey('sydney-so-legacy-main', $enqueued_scripts, 'SiteOrigin legacy main script should be enqueued');
		$this->assertEquals('https://example.com/wp-content/themes/sydney/js/so-legacy-main.min.js', $enqueued_scripts['sydney-so-legacy-main']['src']);
		$this->assertEquals(['jquery'], $enqueued_scripts['sydney-so-legacy-main']['deps']);
		
		$this->assertArrayHasKey('sydney-font-awesome', $enqueued_styles, 'Font Awesome v4 should be enqueued when SiteOrigin is active and v5 option is false');
		$this->assertEquals('https://example.com/wp-content/themes/sydney/fonts/font-awesome.min.css', $enqueued_styles['sydney-font-awesome']['src']);
	}

	/**
	 * Test sydney_scripts function main theme styles enqueue behavior.
	 *
	 * Tests that:
	 * - Main theme styles (minified and main stylesheet) are always enqueued
	 *
	 * @since 1.0.0
	 */
	public function test_sydney_scripts_main_theme_styles_enqueue() {
		// Load only the sydney_scripts function definition
		if (!function_exists('sydney_scripts')) {
			$functions_content = file_get_contents(__DIR__ . '/../../functions.php');
			// Extract only the sydney_scripts function
			$start = strpos($functions_content, 'function sydney_scripts() {');
			$end = strpos($functions_content, 'add_action( \'wp_enqueue_scripts\', \'sydney_scripts\' );');
			$function_code = substr($functions_content, $start, $end - $start);
			eval($function_code);
		}

		// Mock WordPress functions for basic scenario (no special plugins/features)
		$this->mockFunction('sydney_is_amp', false);
		$this->mockFunction('sydney_google_fonts_url', null); // No Google Fonts for this test
		$this->mockFunction('wp_style_add_data', true);
		
		// Use BaseThemeTest helper for site info functions
		$this->mockSiteInfoFunctions([
			'template_directory' => 'https://example.com/wp-content/themes/sydney'
		]);
		
		// Mock get_template_directory_uri separately as it's not in the helper
		$this->mockFunction('get_template_directory_uri', 'https://example.com/wp-content/themes/sydney');
		
		// Use BaseThemeTest helper for theme mods - NO SLIDER
		$this->mockThemeMods([
			'front_header_type' => 'nothing',
			'site_header_type' => 'nothing'
		]);
		
		// Use BaseThemeTest helper for conditional functions
		$this->mockConditionalFunctions([
			'is_front_page' => false
		]);
		
		$this->mockFunction('is_singular', false);
		$this->mockFunction('comments_open', false);
		$this->mockFunction('get_comments_number', '0');
		$this->mockFunction('get_option', false);
		$this->mockFunction('get_stylesheet_uri', 'https://example.com/wp-content/themes/sydney/style.css');

		// Track enqueued styles and scripts
		$enqueued_styles = [];
		$enqueued_scripts = [];
		
		M::userFunction('wp_enqueue_style', [
			'return' => function($handle, $src = '', $deps = [], $ver = false, $media = 'all') use (&$enqueued_styles) {
				$enqueued_styles[$handle] = [
					'src' => $src,
					'deps' => $deps,
					'ver' => $ver,
					'media' => $media
				];
				return true;
			}
		]);
		
		M::userFunction('wp_enqueue_script', [
			'return' => function($handle, $src = '', $deps = [], $ver = false, $in_footer = false) use (&$enqueued_scripts) {
				$enqueued_scripts[$handle] = [
					'src' => $src,
					'deps' => $deps,
					'ver' => $ver,
					'in_footer' => $in_footer
				];
				return true;
			}
		]);

		// Execute the function
		sydney_scripts();

		// ASSERTION 10: Verify Main Theme Styles are always enqueued
		$this->assertArrayHasKey('sydney-style-min', $enqueued_styles, 'Minified theme styles should always be enqueued');
		$this->assertEquals('https://example.com/wp-content/themes/sydney/css/styles.min.css', $enqueued_styles['sydney-style-min']['src']);
		$this->assertEquals('', $enqueued_styles['sydney-style-min']['deps']);
		
		$this->assertArrayHasKey('sydney-style', $enqueued_styles, 'Main theme stylesheet should always be enqueued');
		$this->assertEquals('https://example.com/wp-content/themes/sydney/style.css', $enqueued_styles['sydney-style']['src']);
		$this->assertEquals('', $enqueued_styles['sydney-style']['deps']);
	}

	/**
	 * Test sydney_enqueue_bootstrap function behavior.
	 *
	 * Tests that:
	 * - Bootstrap styles are enqueued via separate function
	 *
	 * @since 1.0.0
	 */
	public function test_sydney_enqueue_bootstrap() {
		// Load the sydney_enqueue_bootstrap function
		$this->loadFunctionFromFile('sydney_enqueue_bootstrap', 'add_action( \'wp_enqueue_scripts\', \'sydney_enqueue_bootstrap\', 9 );');

		// Mock WordPress functions for Bootstrap enqueue
		$this->mockFunction('get_template_directory_uri', self::TEST_THEME_URL);

		// Track enqueued styles
		$enqueued_styles = [];
		$enqueued_scripts = []; // Not used but needed for setupEnqueueTracking
		$this->setupEnqueueTracking($enqueued_styles, $enqueued_scripts);

		// Execute the function
		sydney_enqueue_bootstrap();

		// ASSERTION 11: Verify Bootstrap Styles are enqueued
		$this->assertArrayHasKey('sydney-bootstrap', $enqueued_styles, 'Bootstrap styles should be enqueued');
		$this->assertEquals(self::TEST_THEME_URL . '/css/bootstrap/bootstrap.min.css', $enqueued_styles['sydney-bootstrap']['src']);
		$this->assertEquals([], $enqueued_styles['sydney-bootstrap']['deps']);
		$this->assertTrue($enqueued_styles['sydney-bootstrap']['ver'], 'Bootstrap styles should have version set to true');
	}

	/**
	 * Data provider for sydney_preloader test scenarios.
	 *
	 * @return array Test scenarios with [is_amp, enable_preloader, expected_output, description]
	 */
	public function preloaderScenariosProvider(): array {
		return [
			'enabled_not_amp' => [
				'is_amp' => false,
				'enable_preloader' => 1,
				'expected_output' => true,
				'description' => 'Preloader HTML should be output when enabled and not AMP'
			],
			'disabled_via_theme_mod' => [
				'is_amp' => false,
				'enable_preloader' => 0,
				'expected_output' => false,
				'description' => 'No HTML should be output when preloader is disabled'
			],
			'amp_active_overrides' => [
				'is_amp' => true,
				'enable_preloader' => 1,
				'expected_output' => false,
				'description' => 'No HTML should be output when AMP is active'
			],
			'default_behavior' => [
				'is_amp' => false,
				'enable_preloader' => null, // Use default value
				'expected_output' => true,
				'description' => 'Preloader HTML should be output by default when no theme mod is set'
			],
		];
	}

	/**
	 * Test sydney_preloader function behavior with various scenarios.
	 *
	 * @dataProvider preloaderScenariosProvider
	 * @param bool $is_amp Whether AMP is active
	 * @param int|null $enable_preloader Theme mod value for enable_preloader
	 * @param bool $expected_output Whether HTML output is expected
	 * @param string $description Test scenario description
	 *
	 * @since 1.0.0
	 */
	public function test_sydney_preloader(bool $is_amp, $enable_preloader, bool $expected_output, string $description) {
		// Load the sydney_preloader function
		$this->loadFunctionFromFile('sydney_preloader', 'add_action(\'wp_body_open\', \'sydney_preloader\');');

		// Set up mocks for this scenario
		$this->mockFunction('sydney_is_amp', $is_amp);
		
		if ($enable_preloader === null) {
			// Mock get_theme_mod to return default value when enable_preloader is not set
			$this->mockFunction('get_theme_mod', function($name, $default = null) {
				if ($name === 'enable_preloader') {
					return $default; // Return the default value (1)
				}
				return $default;
			});
		} else {
			$this->mockThemeMods([
				'enable_preloader' => $enable_preloader
			]);
		}

		// Capture output
		$output = $this->captureOutput(function() {
			sydney_preloader();
		});

		// Assert based on expected output
		if ($expected_output) {
			$this->assertHtmlContainsAll($output, [
				'<div class="preloader">',
				'<div class="spinner">',
				'<div class="pre-bounce1"></div>',
				'<div class="pre-bounce2"></div>',
			], $description);
		} else {
			$this->assertEquals('', $output, $description);
		}
	}

	/**
	 * Test that all required files in functions.php actually exist.
	 *
	 * Tests that:
	 * - All files referenced in require statements exist in the filesystem
	 *
	 * @since 1.0.0
	 */
	public function test_required_files_exist() {
		// Get the theme directory path
		$theme_dir = __DIR__ . '/../../';
		
		// List of all files that are required in functions.php
		$required_files = [
			// Widget files (conditional - only loaded when SiteOrigin Panels is active)
			'/widgets/fp-list.php',
			'/widgets/fp-services-type-a.php',
			'/widgets/fp-services-type-b.php',
			'/widgets/fp-facts.php',
			'/widgets/fp-clients.php',
			'/widgets/fp-testimonials.php',
			'/widgets/fp-skills.php',
			'/widgets/fp-call-to-action.php',
			'/widgets/video-widget.php',
			'/widgets/fp-social.php',
			'/widgets/fp-employees.php',
			'/widgets/fp-latest-news.php',
			'/widgets/fp-portfolio.php',
			'/inc/so-page-builder.php',
			'/widgets/contact-info.php',
			
			// Core theme files
			'/inc/custom-header.php',
			'/inc/template-tags.php',
			'/inc/extras.php',
			'/inc/classes/class-sydney-page-metabox.php',
			'/inc/classes/class-sydney-posts-archive.php',
			'/inc/display-conditions.php',
			'/inc/classes/class-sydney-header.php',
			'/inc/customizer/customizer.php',
			'/inc/jetpack.php',
			'/inc/slider.php',
			'/inc/styles.php',
			'/inc/woocommerce.php',
			
			// Integration files (conditional)
			'/inc/integrations/wpml/class-sydney-wpml.php',
			'/inc/integrations/lifter/class-sydney-lifterlms.php',
			'/inc/integrations/learndash/class-sydney-learndash.php',
			'/inc/integrations/learnpress/class-sydney-learnpress.php',
			'/inc/integrations/class-sydney-maxmegamenu.php',
			'/inc/integrations/class-sydney-amp.php',
			
			// Customizer files
			'/inc/customizer/upsell/class-customize.php',
			'/inc/customizer/style-book/control/class-customizer-style-book.php',
			
			// Additional core files
			'/inc/editor.php',
			'/inc/fonts.php',
			'/inc/classes/class-sydney-svg-icons.php',
			'/inc/notices/class-sydney-review.php',
			'/inc/notices/class-sydney-campaign.php',
			'/inc/schema.php',
			'/inc/theme-update.php',
			
			// Module files
			'/inc/modules/class-sydney-modules.php',
			'/inc/modules/block-templates/class-sydney-block-templates.php',
			'/inc/modules/hf-builder/class-header-footer-builder.php',
			
			// Dashboard files
			'/inc/dashboard/class-dashboard.php',
			'/inc/dashboard/class-dashboard-settings.php',
			
			// Performance files
			'/inc/performance/class-sydney-performance.php',
			
			// Elementor integration files
			'/inc/integrations/elementor/class-sydney-elementor-global-colors.php',
			'/inc/integrations/elementor/library/library-manager.php',
			'/inc/integrations/elementor/library/library-source.php',
			
			// Block styles
			'/inc/block-styles.php',
		];
		
		$missing_files = [];
		
		foreach ($required_files as $file) {
			$full_path = $theme_dir . $file;
			if (!file_exists($full_path)) {
				$missing_files[] = $file;
			}
		}
		
		$this->assertEmpty(
			$missing_files,
			'The following required files are missing: ' . implode(', ', $missing_files)
		);
		
		// Additional assertion to confirm we tested a reasonable number of files
		$this->assertGreaterThan(50, count($required_files), 'Should be testing a substantial number of required files');
	}
}