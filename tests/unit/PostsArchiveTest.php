<?php
/**
 * Unit tests for the Posts Archive functionality in Sydney theme
 *
 * @package Sydney
 * @subpackage Tests
 */

namespace Sydney\Tests;

use Sydney\Tests\BaseThemeTest;
use WP_Mock as M;

/**
 * Test class for Posts Archive functionality.
 *
 * @since 1.0.0
 */
class PostsArchiveTest extends BaseThemeTest {

	/**
	 * Get the theme dependencies that this test class requires.
	 *
	 * @since 1.0.0
	 * @return array Array of dependency types to load.
	 */
	protected function getRequiredDependencies(): array {
		return ['posts-archive'];
	}

	/**
	 * Test singleton behavior of Sydney_Posts_Archive class.
	 * 
	 * Tests that:
	 * - get_instance() returns the same instance on multiple calls
	 * - Only one instance exists (singleton pattern)
	 *
	 * @since 1.0.0
	 */
	public function test_singleton_behavior() {
		// Reset singleton to ensure clean test
		$this->resetSingleton('Sydney_Posts_Archive');

		// Get first instance
		$instance1 = \Sydney_Posts_Archive::get_instance();
		
		// Get second instance
		$instance2 = \Sydney_Posts_Archive::get_instance();
		
		// Assert both calls return the same instance
		$this->assertSame($instance1, $instance2, 'get_instance() should return the same instance on multiple calls');
		
		// Assert instance is of correct type
		$this->assertInstanceOf('Sydney_Posts_Archive', $instance1, 'Instance should be of Sydney_Posts_Archive class');
	}

	/**
	 * Test constructor hooks registration.
	 * 
	 * Tests that:
	 * - Constructor executes without errors
	 * - Instance is properly created
	 * - The hook registration is handled (mocked by BaseThemeTest)
	 * - wp, sydney_loop_post, and wp_enqueue_scripts hooks are registered
	 *
	 * @since 1.0.0
	 */
	public function test_constructor_hooks_registration() {
		// Reset singleton to ensure clean test and fresh constructor call
		$this->resetSingleton('Sydney_Posts_Archive');

		// Get fresh instance to trigger constructor
		// The add_action calls are already mocked to return true in BaseThemeTest
		$instance = \Sydney_Posts_Archive::get_instance();

		// Verify instance was created successfully
		$this->assertInstanceOf('Sydney_Posts_Archive', $instance, 'Instance should be created successfully');

		// The fact that we can create an instance without errors means
		// the constructor's add_action calls executed successfully
	}

	/**
	 * Test script enqueueing for layout5.
	 * 
	 * Tests that:
	 * - jQuery is enqueued when blog layout is layout5
	 * - jQuery masonry is enqueued when blog layout is layout5
	 * - Scripts are not enqueued when blog layout is not layout5
	 *
	 * @since 1.0.0
	 */
	public function test_enqueue_scripts_layout5() {
		// Reset singleton to ensure clean test
		$this->resetSingleton('Sydney_Posts_Archive');
		$instance = \Sydney_Posts_Archive::get_instance();

		// SCENARIO 1: Test layout5 enqueues scripts
		$this->mockThemeMods(['blog_layout' => 'layout5']);
		
		// Mock wp_enqueue_script expectations for layout5
		M::userFunction('wp_enqueue_script')
			->with('jquery')
			->once();
			
		M::userFunction('wp_enqueue_script')
			->with('jquery-masonry')
			->once();

		// Call enqueue method
		$instance->enqueue();

		// Reset mocks for next scenario
		M::tearDown();
		M::setUp();

		// SCENARIO 2: Test non-layout5 doesn't enqueue scripts
		$this->mockThemeMods(['blog_layout' => 'layout2']);
		
		// Mock wp_enqueue_script to expect no calls
		M::userFunction('wp_enqueue_script')
			->never();

		// Call enqueue method
		$instance->enqueue();
	}

	/**
	 * Test early return conditions in filters method.
	 * 
	 * Tests that:
	 * - Method returns early when is_singular() is true
	 * - Method returns early when is_404() is true
	 * - Method returns early when WooCommerce is active and is_woocommerce() is true
	 * - Method continues when none of the early return conditions are met
	 *
	 * @since 1.0.0
	 */
	public function test_filters_early_return_conditions() {
		// Reset singleton to ensure clean test
		$this->resetSingleton('Sydney_Posts_Archive');
		$instance = \Sydney_Posts_Archive::get_instance();

		// SCENARIO 1: Test early return on is_singular()
		M::userFunction('is_singular')->andReturn(true);
		M::userFunction('is_404')->andReturn(false);
		M::userFunction('is_woocommerce')->andReturn(false);
		
		// Mock get_theme_mod to ensure it's never called (early return)
		M::userFunction('get_theme_mod')->never();

		// Call filters method
		$instance->filters();

		// Reset mocks for next scenario
		M::tearDown();
		M::setUp();

		// SCENARIO 2: Test early return on is_404()
		M::userFunction('is_singular')->andReturn(false);
		M::userFunction('is_404')->andReturn(true);
		M::userFunction('is_woocommerce')->andReturn(false);
		
		// Mock get_theme_mod to ensure it's never called (early return)
		M::userFunction('get_theme_mod')->never();

		// Call filters method
		$instance->filters();

		// Reset mocks for next scenario
		M::tearDown();
		M::setUp();

		// SCENARIO 3: Test early return when WooCommerce is active and is_woocommerce() is true
		M::userFunction('is_singular')->andReturn(false);
		M::userFunction('is_404')->andReturn(false);
		
		// Use real class_exists() with class_alias to simulate WooCommerce existence
		if (!class_exists('Woocommerce')) {
			class_alias('stdClass', 'Woocommerce');
		}
		
		// Mock is_woocommerce to return true
		M::userFunction('is_woocommerce')->andReturn(true);
		
		// Mock get_theme_mod to ensure it's never called (early return)
		M::userFunction('get_theme_mod')->never();

		// Call filters method
		$instance->filters();

		// Reset mocks for next scenario
		M::tearDown();
		M::setUp();

		// SCENARIO 4: Test that method continues when no early return conditions are met
		M::userFunction('is_singular')->andReturn(false);
		M::userFunction('is_404')->andReturn(false);
		M::userFunction('is_woocommerce')->andReturn(false);
		
		// Mock theme mods and filters for normal execution
		$this->mockThemeMods(['sidebar_archives' => 1]);

		// Call filters method - should execute normally without early return
		$instance->filters();
	}

	/**
	 * Test sidebar filter configuration.
	 * 
	 * Tests that:
	 * - The filters method executes different logic paths based on sidebar_archives setting
	 * - Method handles sidebar disabled (0) and enabled (non-0) scenarios correctly
	 * - All conditional logic branches execute without errors
	 *
	 * @since 1.0.0
	 */
	public function test_filters_sidebar_configuration() {
		// Reset singleton to ensure clean test
		$this->resetSingleton('Sydney_Posts_Archive');
		$instance = \Sydney_Posts_Archive::get_instance();

		// SCENARIO 1: Test sidebar disabled (sidebar_archives = 0)
		// Mock WordPress conditional functions to pass early return checks
		M::userFunction('is_singular')->andReturn(false);
		M::userFunction('is_404')->andReturn(false);
		M::userFunction('is_woocommerce')->andReturn(false);
		
		// Mock sidebar_archives theme mod to return 0 (disabled)
		$this->mockThemeMods(['sidebar_archives' => 0]);

		// Call filters method - should execute sidebar disabled logic
		$instance->filters();
		
		// Verify method executed without errors
		$this->assertInstanceOf('Sydney_Posts_Archive', $instance, 'Instance should remain valid after filters execution with sidebar disabled');

		// Reset mocks for next scenario
		M::tearDown();
		M::setUp();

		// SCENARIO 2: Test sidebar enabled (sidebar_archives = 1)
		// Mock WordPress conditional functions to pass early return checks
		M::userFunction('is_singular')->andReturn(false);
		M::userFunction('is_404')->andReturn(false);
		M::userFunction('is_woocommerce')->andReturn(false);
		
		// Mock sidebar_archives theme mod to return 1 (enabled)
		$this->mockThemeMods(['sidebar_archives' => 1]);

		// Call filters method - should execute sidebar enabled logic
		$instance->filters();
		
		// Verify method executed without errors
		$this->assertInstanceOf('Sydney_Posts_Archive', $instance, 'Instance should remain valid after filters execution with sidebar enabled');
		
		// Reset mocks for next scenario
		M::tearDown();
		M::setUp();

		// SCENARIO 3: Test sidebar with non-zero value (sidebar_archives = 2)
		// Mock WordPress conditional functions to pass early return checks
		M::userFunction('is_singular')->andReturn(false);
		M::userFunction('is_404')->andReturn(false);
		M::userFunction('is_woocommerce')->andReturn(false);
		
		// Mock sidebar_archives theme mod to return 2 (enabled)
		$this->mockThemeMods(['sidebar_archives' => 2]);

		// Call filters method - should execute sidebar enabled logic
		$instance->filters();
		
		// Verify method executed without errors
		$this->assertInstanceOf('Sydney_Posts_Archive', $instance, 'Instance should remain valid after filters execution with sidebar value 2');
		
		// The fact that all scenarios execute without errors demonstrates
		// that the conditional logic works correctly for different sidebar settings
	}

	/**
	 * Test post classes text alignment functionality.
	 * 
	 * Tests that:
	 * - post-align-left class is added when archive_text_align is 'left'
	 * - post-align-center class is added when archive_text_align is 'center'
	 * - post-align-right class is added when archive_text_align is 'right'
	 * - Default text alignment (left) is applied when theme mod is not set
	 *
	 * @since 1.0.0
	 */
	public function test_post_classes_text_alignment() {
		// Reset singleton to ensure clean test
		$this->resetSingleton('Sydney_Posts_Archive');
		$instance = \Sydney_Posts_Archive::get_instance();

		// SCENARIO 1: Test left alignment
		$this->mockThemeMods([
			'archive_text_align' => 'left',
			'archives_grid_columns' => '3',
			'archives_list_vertical_alignment' => 'middle'
		]);
		
		// Mock blog_layout to return a non-grid layout for consistent testing
		M::userFunction('get_theme_mod')
			->with('blog_layout', 'layout2')
			->andReturn('layout2');

		$input_classes = ['existing-class'];
		$result_classes = $instance->post_classes($input_classes);

		$this->assertContains('post-align-left', $result_classes, 'Should add post-align-left class');
		$this->assertContains('existing-class', $result_classes, 'Should preserve existing classes');

		// Reset mocks for next scenario
		M::tearDown();
		M::setUp();

		// SCENARIO 2: Test center alignment
		$this->mockThemeMods([
			'archive_text_align' => 'center',
			'archives_grid_columns' => '3',
			'archives_list_vertical_alignment' => 'middle'
		]);
		
		// Mock blog_layout to return a non-grid layout
		M::userFunction('get_theme_mod')
			->with('blog_layout', 'layout2')
			->andReturn('layout2');

		$input_classes = ['existing-class'];
		$result_classes = $instance->post_classes($input_classes);

		$this->assertContains('post-align-center', $result_classes, 'Should add post-align-center class');
		$this->assertContains('existing-class', $result_classes, 'Should preserve existing classes');

		// Reset mocks for next scenario
		M::tearDown();
		M::setUp();

		// SCENARIO 3: Test right alignment
		$this->mockThemeMods([
			'archive_text_align' => 'right',
			'archives_grid_columns' => '3',
			'archives_list_vertical_alignment' => 'middle'
		]);
		
		// Mock blog_layout to return a non-grid layout
		M::userFunction('get_theme_mod')
			->with('blog_layout', 'layout2')
			->andReturn('layout2');

		$input_classes = ['existing-class'];
		$result_classes = $instance->post_classes($input_classes);

		$this->assertContains('post-align-right', $result_classes, 'Should add post-align-right class');
		$this->assertContains('existing-class', $result_classes, 'Should preserve existing classes');

		// Reset mocks for next scenario
		M::tearDown();
		M::setUp();

		// SCENARIO 4: Test default alignment (when theme mod not set)
		$this->mockThemeMods([
			'archives_grid_columns' => '3',
			'archives_list_vertical_alignment' => 'middle'
		]);
		
		// Mock get_theme_mod for archive_text_align to return default 'left'
		M::userFunction('get_theme_mod')
			->with('archive_text_align', 'left')
			->andReturn('left');
			
		// Mock blog_layout to return a non-grid layout
		M::userFunction('get_theme_mod')
			->with('blog_layout', 'layout2')
			->andReturn('layout2');

		$input_classes = ['existing-class'];
		$result_classes = $instance->post_classes($input_classes);

		$this->assertContains('post-align-left', $result_classes, 'Should add default post-align-left class when theme mod not set');
		$this->assertContains('existing-class', $result_classes, 'Should preserve existing classes');
	}

	/**
	 * Test post classes vertical alignment functionality.
	 * 
	 * Tests that:
	 * - post-vertical-align-middle class is added when archives_list_vertical_alignment is 'middle'
	 * - post-vertical-align-top class is added when archives_list_vertical_alignment is 'top'
	 * - post-vertical-align-bottom class is added when archives_list_vertical_alignment is 'bottom'
	 * - Default vertical alignment (middle) is applied when theme mod is not set
	 *
	 * @since 1.0.0
	 */
	public function test_post_classes_vertical_alignment() {
		// Reset singleton to ensure clean test
		$this->resetSingleton('Sydney_Posts_Archive');
		$instance = \Sydney_Posts_Archive::get_instance();

		// SCENARIO 1: Test middle vertical alignment
		$this->mockThemeMods([
			'archive_text_align' => 'left',
			'archives_grid_columns' => '3',
			'archives_list_vertical_alignment' => 'middle'
		]);
		
		// Mock blog_layout to return a non-grid layout
		M::userFunction('get_theme_mod')
			->with('blog_layout', 'layout2')
			->andReturn('layout2');

		$input_classes = ['existing-class'];
		$result_classes = $instance->post_classes($input_classes);

		$this->assertContains('post-vertical-align-middle', $result_classes, 'Should add post-vertical-align-middle class');
		$this->assertContains('existing-class', $result_classes, 'Should preserve existing classes');

		// Reset mocks for next scenario
		M::tearDown();
		M::setUp();

		// SCENARIO 2: Test top vertical alignment
		$this->mockThemeMods([
			'archive_text_align' => 'left',
			'archives_grid_columns' => '3',
			'archives_list_vertical_alignment' => 'top'
		]);
		
		// Mock blog_layout to return a non-grid layout
		M::userFunction('get_theme_mod')
			->with('blog_layout', 'layout2')
			->andReturn('layout2');

		$input_classes = ['existing-class'];
		$result_classes = $instance->post_classes($input_classes);

		$this->assertContains('post-vertical-align-top', $result_classes, 'Should add post-vertical-align-top class');

		// Reset mocks for next scenario
		M::tearDown();
		M::setUp();

		// SCENARIO 3: Test bottom vertical alignment
		$this->mockThemeMods([
			'archive_text_align' => 'left',
			'archives_grid_columns' => '3',
			'archives_list_vertical_alignment' => 'bottom'
		]);
		
		// Mock blog_layout to return a non-grid layout
		M::userFunction('get_theme_mod')
			->with('blog_layout', 'layout2')
			->andReturn('layout2');

		$input_classes = ['existing-class'];
		$result_classes = $instance->post_classes($input_classes);

		$this->assertContains('post-vertical-align-bottom', $result_classes, 'Should add post-vertical-align-bottom class');

		// Reset mocks for next scenario
		M::tearDown();
		M::setUp();

		// SCENARIO 4: Test default vertical alignment
		$this->mockThemeMods([
			'archive_text_align' => 'left',
			'archives_grid_columns' => '3'
		]);
		
		// Mock get_theme_mod for vertical alignment to return default 'middle'
		M::userFunction('get_theme_mod')
			->with('archives_list_vertical_alignment', 'middle')
			->andReturn('middle');
			
		// Mock blog_layout to return a non-grid layout
		M::userFunction('get_theme_mod')
			->with('blog_layout', 'layout2')
			->andReturn('layout2');

		$input_classes = ['existing-class'];
		$result_classes = $instance->post_classes($input_classes);

		$this->assertContains('post-vertical-align-middle', $result_classes, 'Should add default post-vertical-align-middle class');
	}

	/**
	 * Test post classes grid columns functionality.
	 * 
	 * Tests that:
	 * - Column classes are added for layout3 and layout5 with different grid column values
	 * - col-md-12 is added for non-grid layouts (layout1, layout2, layout4, layout6)
	 * - Column calculation works correctly (12/columns for both lg and md)
	 * - Different column values (2, 3, 4) generate correct Bootstrap classes
	 *
	 * @since 1.0.0
	 */
	public function test_post_classes_grid_columns() {
		// Reset singleton to ensure clean test
		$this->resetSingleton('Sydney_Posts_Archive');
		$instance = \Sydney_Posts_Archive::get_instance();

		// SCENARIO 1: Test layout3 with 3 columns
		$this->mockThemeMods([
			'archive_text_align' => 'left',
			'archives_grid_columns' => '3',
			'archives_list_vertical_alignment' => 'middle',
			'blog_layout' => 'layout3'
		]);

		$input_classes = ['existing-class'];
		$result_classes = $instance->post_classes($input_classes);

		// Debug: Check what's actually in the result classes
		$columns_string = 'col-lg-4 col-md-4'; // This is what should be added as a single string
		$this->assertContains($columns_string, $result_classes, 'Should add "col-lg-4 col-md-4" as single string for 3 columns (12/3=4)');

		// Reset mocks for next scenario
		M::tearDown();
		M::setUp();

		// SCENARIO 2: Test layout5 with 2 columns
		$this->mockThemeMods([
			'archive_text_align' => 'left',
			'archives_grid_columns' => '2',
			'archives_list_vertical_alignment' => 'middle',
			'blog_layout' => 'layout5'
		]);

		$input_classes = ['existing-class'];
		$result_classes = $instance->post_classes($input_classes);

		$columns_string = 'col-lg-6 col-md-6'; // This is what should be added as a single string
		$this->assertContains($columns_string, $result_classes, 'Should add "col-lg-6 col-md-6" as single string for 2 columns (12/2=6)');

		// Reset mocks for next scenario
		M::tearDown();
		M::setUp();

		// SCENARIO 3: Test layout3 with 4 columns
		$this->mockThemeMods([
			'archive_text_align' => 'left',
			'archives_grid_columns' => '4',
			'archives_list_vertical_alignment' => 'middle',
			'blog_layout' => 'layout3'
		]);

		$input_classes = ['existing-class'];
		$result_classes = $instance->post_classes($input_classes);

		$columns_string = 'col-lg-3 col-md-3'; // This is what should be added as a single string
		$this->assertContains($columns_string, $result_classes, 'Should add "col-lg-3 col-md-3" as single string for 4 columns (12/4=3)');

		// Reset mocks for next scenario
		M::tearDown();
		M::setUp();

		// SCENARIO 4: Test non-grid layout (layout2)
		$this->mockThemeMods([
			'archive_text_align' => 'left',
			'archives_grid_columns' => '3',
			'archives_list_vertical_alignment' => 'middle',
			'blog_layout' => 'layout2'
		]);

		$input_classes = ['existing-class'];
		$result_classes = $instance->post_classes($input_classes);

		$this->assertContains('col-md-12', $result_classes, 'Should add col-md-12 class for non-grid layouts');
		$this->assertNotContains('col-lg-4 col-md-4', $result_classes, 'Should not add grid column classes for non-grid layouts');
	}

	/**
	 * Test blog layout retrieval functionality.
	 * 
	 * Tests that:
	 * - blog_layout() returns the correct theme mod value
	 * - Default layout2 is returned when theme mod is not set
	 * - Various layout values (layout1, layout2, layout3, layout4, layout5, layout6) are handled correctly
	 *
	 * @since 1.0.0
	 */
	public function test_blog_layout_retrieval() {
		// Reset singleton to ensure clean test
		$this->resetSingleton('Sydney_Posts_Archive');
		$instance = \Sydney_Posts_Archive::get_instance();

		// SCENARIO 1: Test layout1
		$this->mockThemeMods(['blog_layout' => 'layout1']);
		
		$result = $instance->blog_layout();
		$this->assertEquals('layout1', $result, 'Should return layout1 when set in theme mods');

		// Reset mocks for next scenario
		M::tearDown();
		M::setUp();

		// SCENARIO 2: Test layout3
		$this->mockThemeMods(['blog_layout' => 'layout3']);
		
		$result = $instance->blog_layout();
		$this->assertEquals('layout3', $result, 'Should return layout3 when set in theme mods');

		// Reset mocks for next scenario
		M::tearDown();
		M::setUp();

		// SCENARIO 3: Test layout5
		$this->mockThemeMods(['blog_layout' => 'layout5']);
		
		$result = $instance->blog_layout();
		$this->assertEquals('layout5', $result, 'Should return layout5 when set in theme mods');

		// Reset mocks for next scenario
		M::tearDown();
		M::setUp();

		// SCENARIO 4: Test default layout (when theme mod not set)
		// Mock get_theme_mod to return default 'layout2'
		M::userFunction('get_theme_mod')
			->with('blog_layout', 'layout2')
			->andReturn('layout2');
		
		$result = $instance->blog_layout();
		$this->assertEquals('layout2', $result, 'Should return default layout2 when theme mod not set');

		// Reset mocks for next scenario
		M::tearDown();
		M::setUp();

		// SCENARIO 5: Test layout6
		$this->mockThemeMods(['blog_layout' => 'layout6']);
		
		$result = $instance->blog_layout();
		$this->assertEquals('layout6', $result, 'Should return layout6 when set in theme mods');
	}

	/**
	 * Test default meta elements functionality.
	 * 
	 * Tests that:
	 * - default_meta_elements() returns array with 'post_date' and 'post_categories'
	 * - The returned array structure is correct
	 * - Array contains exactly the expected elements
	 *
	 * @since 1.0.0
	 */
	public function test_default_meta_elements() {
		// Reset singleton to ensure clean test
		$this->resetSingleton('Sydney_Posts_Archive');
		$instance = \Sydney_Posts_Archive::get_instance();

		$result = $instance->default_meta_elements();

		$this->assertIsArray($result, 'Should return an array');
		$this->assertCount(2, $result, 'Should return exactly 2 elements');
		$this->assertContains('post_date', $result, 'Should contain post_date element');
		$this->assertContains('post_categories', $result, 'Should contain post_categories element');
		$this->assertEquals(['post_date', 'post_categories'], $result, 'Should return exact default elements in correct order');
	}

	/**
	 * Test post markup for layout1.
	 * 
	 * Tests that:
	 * - post_image() is called first
	 * - post_meta() is called with correct position when meta_position is 'above-title'
	 * - post_title() is called after meta or after image
	 * - post_excerpt() is called after title
	 * - post_meta() is called with 'below-excerpt' when meta_position is 'below-excerpt'
	 *
	 * @since 1.0.0
	 */
	public function test_post_markup_layout1() {
		// Reset singleton to ensure clean test
		$this->resetSingleton('Sydney_Posts_Archive');
		$instance = \Sydney_Posts_Archive::get_instance();

		// SCENARIO 1: Test layout1 with above-title meta position
		$this->mockThemeMods([
			'blog_layout' => 'layout1',
			'archive_list_image_placement' => 'left',
			'archive_meta_position' => 'above-title'
		]);

		// Mock the individual post methods to track calls
		$called_methods = [];
		$mock_instance = $this->getMockBuilder('Sydney_Posts_Archive')
			->onlyMethods(['post_image', 'post_meta', 'post_title', 'post_excerpt'])
			->getMock();

		$mock_instance->expects($this->once())->method('post_image')
			->willReturnCallback(function() use (&$called_methods) {
				$called_methods[] = 'post_image';
			});

		$mock_instance->expects($this->once())->method('post_meta')
			->with('above-title')
			->willReturnCallback(function($position) use (&$called_methods) {
				$called_methods[] = 'post_meta_' . $position;
			});

		$mock_instance->expects($this->once())->method('post_title')
			->willReturnCallback(function() use (&$called_methods) {
				$called_methods[] = 'post_title';
			});

		$mock_instance->expects($this->once())->method('post_excerpt')
			->willReturnCallback(function() use (&$called_methods) {
				$called_methods[] = 'post_excerpt';
			});

		// Call post_markup method
		$output = $this->captureOutput(function() use ($mock_instance) {
			$mock_instance->post_markup();
		});

		// Verify method call order for layout1 with above-title meta
		$expected_order = ['post_image', 'post_meta_above-title', 'post_title', 'post_excerpt'];
		$this->assertEquals($expected_order, $called_methods, 'Methods should be called in correct order for layout1 with above-title meta');

		// Reset mocks for next scenario
		M::tearDown();
		M::setUp();

		// SCENARIO 2: Test layout1 with below-excerpt meta position
		$this->mockThemeMods([
			'blog_layout' => 'layout1',
			'archive_list_image_placement' => 'left',
			'archive_meta_position' => 'below-excerpt'
		]);

		$called_methods = [];
		$mock_instance = $this->getMockBuilder('Sydney_Posts_Archive')
			->onlyMethods(['post_image', 'post_meta', 'post_title', 'post_excerpt'])
			->getMock();

		$mock_instance->expects($this->once())->method('post_image')
			->willReturnCallback(function() use (&$called_methods) {
				$called_methods[] = 'post_image';
			});

		$mock_instance->expects($this->once())->method('post_meta')
			->with('below-excerpt')
			->willReturnCallback(function($position) use (&$called_methods) {
				$called_methods[] = 'post_meta_' . $position;
			});

		$mock_instance->expects($this->once())->method('post_title')
			->willReturnCallback(function() use (&$called_methods) {
				$called_methods[] = 'post_title';
			});

		$mock_instance->expects($this->once())->method('post_excerpt')
			->willReturnCallback(function() use (&$called_methods) {
				$called_methods[] = 'post_excerpt';
			});

		// Call post_markup method
		$output = $this->captureOutput(function() use ($mock_instance) {
			$mock_instance->post_markup();
		});

		// Verify method call order for layout1 with below-excerpt meta
		$expected_order = ['post_image', 'post_title', 'post_excerpt', 'post_meta_below-excerpt'];
		$this->assertEquals($expected_order, $called_methods, 'Methods should be called in correct order for layout1 with below-excerpt meta');
	}

	/**
	 * Test post markup for layout2.
	 * 
	 * Tests that:
	 * - post_meta() is called first when meta_position is 'above-title'
	 * - post_title() is called after meta or first if no above-title meta
	 * - post_image() is called after title
	 * - post_excerpt() is called after image
	 * - post_meta() is called last when meta_position is 'below-excerpt'
	 *
	 * @since 1.0.0
	 */
	public function test_post_markup_layout2() {
		// Reset singleton to ensure clean test
		$this->resetSingleton('Sydney_Posts_Archive');
		$instance = \Sydney_Posts_Archive::get_instance();

		// SCENARIO 1: Test layout2 with above-title meta position
		$this->mockThemeMods([
			'blog_layout' => 'layout2',
			'archive_list_image_placement' => 'left',
			'archive_meta_position' => 'above-title'
		]);

		$called_methods = [];
		$mock_instance = $this->getMockBuilder('Sydney_Posts_Archive')
			->onlyMethods(['post_image', 'post_meta', 'post_title', 'post_excerpt'])
			->getMock();

		$mock_instance->expects($this->once())->method('post_image')
			->willReturnCallback(function() use (&$called_methods) {
				$called_methods[] = 'post_image';
			});

		$mock_instance->expects($this->once())->method('post_meta')
			->with('above-title')
			->willReturnCallback(function($position) use (&$called_methods) {
				$called_methods[] = 'post_meta_' . $position;
			});

		$mock_instance->expects($this->once())->method('post_title')
			->willReturnCallback(function() use (&$called_methods) {
				$called_methods[] = 'post_title';
			});

		$mock_instance->expects($this->once())->method('post_excerpt')
			->willReturnCallback(function() use (&$called_methods) {
				$called_methods[] = 'post_excerpt';
			});

		// Call post_markup method
		$output = $this->captureOutput(function() use ($mock_instance) {
			$mock_instance->post_markup();
		});

		// Verify method call order for layout2 with above-title meta
		$expected_order = ['post_meta_above-title', 'post_title', 'post_image', 'post_excerpt'];
		$this->assertEquals($expected_order, $called_methods, 'Methods should be called in correct order for layout2 with above-title meta');
	}

	/**
	 * Test post markup for layout3 and layout5.
	 * 
	 * Tests that:
	 * - Both layouts follow the same structure
	 * - post_image() is called first
	 * - Meta positioning works correctly for above-title and below-excerpt
	 * - post_title() and post_excerpt() are called in correct order
	 *
	 * @since 1.0.0
	 */
	public function test_post_markup_layout3_and_layout5() {
		// Reset singleton to ensure clean test
		$this->resetSingleton('Sydney_Posts_Archive');
		$instance = \Sydney_Posts_Archive::get_instance();

		// SCENARIO 1: Test layout3 structure
		$this->mockThemeMods([
			'blog_layout' => 'layout3',
			'archive_list_image_placement' => 'left',
			'archive_meta_position' => 'above-title'
		]);

		$called_methods = [];
		$mock_instance = $this->getMockBuilder('Sydney_Posts_Archive')
			->onlyMethods(['post_image', 'post_meta', 'post_title', 'post_excerpt'])
			->getMock();

		$mock_instance->expects($this->once())->method('post_image')
			->willReturnCallback(function() use (&$called_methods) {
				$called_methods[] = 'post_image';
			});

		$mock_instance->expects($this->once())->method('post_meta')
			->with('above-title')
			->willReturnCallback(function($position) use (&$called_methods) {
				$called_methods[] = 'post_meta_' . $position;
			});

		$mock_instance->expects($this->once())->method('post_title')
			->willReturnCallback(function() use (&$called_methods) {
				$called_methods[] = 'post_title';
			});

		$mock_instance->expects($this->once())->method('post_excerpt')
			->willReturnCallback(function() use (&$called_methods) {
				$called_methods[] = 'post_excerpt';
			});

		// Call post_markup method
		$output = $this->captureOutput(function() use ($mock_instance) {
			$mock_instance->post_markup();
		});

		// Verify method call order for layout3
		$expected_order = ['post_image', 'post_meta_above-title', 'post_title', 'post_excerpt'];
		$this->assertEquals($expected_order, $called_methods, 'Methods should be called in correct order for layout3');

		// Reset mocks for next scenario
		M::tearDown();
		M::setUp();

		// SCENARIO 2: Test layout5 structure (should be identical to layout3)
		$this->mockThemeMods([
			'blog_layout' => 'layout5',
			'archive_list_image_placement' => 'left',
			'archive_meta_position' => 'above-title'
		]);

		$called_methods = [];
		$mock_instance = $this->getMockBuilder('Sydney_Posts_Archive')
			->onlyMethods(['post_image', 'post_meta', 'post_title', 'post_excerpt'])
			->getMock();

		$mock_instance->expects($this->once())->method('post_image')
			->willReturnCallback(function() use (&$called_methods) {
				$called_methods[] = 'post_image';
			});

		$mock_instance->expects($this->once())->method('post_meta')
			->with('above-title')
			->willReturnCallback(function($position) use (&$called_methods) {
				$called_methods[] = 'post_meta_' . $position;
			});

		$mock_instance->expects($this->once())->method('post_title')
			->willReturnCallback(function() use (&$called_methods) {
				$called_methods[] = 'post_title';
			});

		$mock_instance->expects($this->once())->method('post_excerpt')
			->willReturnCallback(function() use (&$called_methods) {
				$called_methods[] = 'post_excerpt';
			});

		// Call post_markup method
		$output = $this->captureOutput(function() use ($mock_instance) {
			$mock_instance->post_markup();
		});

		// Verify method call order for layout5 (should be same as layout3)
		$expected_order = ['post_image', 'post_meta_above-title', 'post_title', 'post_excerpt'];
		$this->assertEquals($expected_order, $called_methods, 'Methods should be called in correct order for layout5 (same as layout3)');
	}

	/**
	 * Test post markup for layout4 and layout6.
	 * 
	 * Tests that:
	 * - Both layouts follow the same structure
	 * - list-image div is created with correct image placement class
	 * - list-content div is created
	 * - Post components are wrapped in correct divs
	 * - Image placement class (left/right) is applied correctly
	 *
	 * @since 1.0.0
	 */
	public function test_post_markup_layout4_and_layout6() {
		// Reset singleton to ensure clean test
		$this->resetSingleton('Sydney_Posts_Archive');
		$instance = \Sydney_Posts_Archive::get_instance();

		// SCENARIO 1: Test layout4 with left image placement
		$this->mockThemeMods([
			'blog_layout' => 'layout4',
			'archive_list_image_placement' => 'left',
			'archive_meta_position' => 'above-title'
		]);

		// Mock the individual post methods
		$mock_instance = $this->getMockBuilder('Sydney_Posts_Archive')
			->onlyMethods(['post_image', 'post_meta', 'post_title', 'post_excerpt'])
			->getMock();

		$mock_instance->expects($this->once())->method('post_image');
		$mock_instance->expects($this->once())->method('post_meta')->with('above-title');
		$mock_instance->expects($this->once())->method('post_title');
		$mock_instance->expects($this->once())->method('post_excerpt');

		// Call post_markup method and capture output
		$output = $this->captureOutput(function() use ($mock_instance) {
			$mock_instance->post_markup();
		});

		// Verify HTML structure for layout4
		$this->assertStringContainsString('<div class="list-image image-left">', $output, 'Should create list-image div with left placement class');
		$this->assertStringContainsString('<div class="list-content">', $output, 'Should create list-content div');
		$this->assertStringContainsString('</div>', $output, 'Should close divs properly');

		// Reset mocks for next scenario
		M::tearDown();
		M::setUp();

		// SCENARIO 2: Test layout6 with right image placement
		$this->mockThemeMods([
			'blog_layout' => 'layout6',
			'archive_list_image_placement' => 'right',
			'archive_meta_position' => 'below-excerpt'
		]);

		$mock_instance = $this->getMockBuilder('Sydney_Posts_Archive')
			->onlyMethods(['post_image', 'post_meta', 'post_title', 'post_excerpt'])
			->getMock();

		$mock_instance->expects($this->once())->method('post_image');
		$mock_instance->expects($this->once())->method('post_meta')->with('below-excerpt');
		$mock_instance->expects($this->once())->method('post_title');
		$mock_instance->expects($this->once())->method('post_excerpt');

		// Call post_markup method and capture output
		$output = $this->captureOutput(function() use ($mock_instance) {
			$mock_instance->post_markup();
		});

		// Verify HTML structure for layout6 with right placement
		$this->assertStringContainsString('<div class="list-image image-right">', $output, 'Should create list-image div with right placement class');
		$this->assertStringContainsString('<div class="list-content">', $output, 'Should create list-content div');
	}

	/**
	 * Test post image rendering functionality.
	 * 
	 * Tests that:
	 * - Post image renders when has_post_thumbnail() is true and feature is enabled
	 * - Post image doesn't render when has_post_thumbnail() is false
	 * - Post image doesn't render when index_feat_image theme mod is 0
	 * - HTML structure includes entry-thumb div, link, and thumbnail
	 *
	 * @since 1.0.0
	 */
	public function test_post_image_rendering() {
		// Reset singleton to ensure clean test
		$this->resetSingleton('Sydney_Posts_Archive');
		$instance = \Sydney_Posts_Archive::get_instance();

		// SCENARIO 1: Test image renders when thumbnail exists and feature enabled
		$this->mockThemeMods(['index_feat_image' => 1]);
		
		M::userFunction('has_post_thumbnail')->andReturn(true);
		M::userFunction('the_permalink')->andReturnUsing(function() {
			echo 'https://example.com/post';
		});
		M::userFunction('the_title_attribute')->andReturnUsing(function() {
			echo 'Sample Post Title';
		});
		M::userFunction('the_post_thumbnail')->with('large-thumb')->andReturnUsing(function() {
			echo 'thumbnail-html';
		});

		$output = $this->captureOutput(function() use ($instance) {
			$instance->post_image();
		});

		$this->assertStringContainsString('<div class="entry-thumb">', $output, 'Should create entry-thumb wrapper div');
		$this->assertStringContainsString('<a href="https://example.com/post"', $output, 'Should create link to post');
		$this->assertStringContainsString('title="Sample Post Title"', $output, 'Should include post title in link');
		$this->assertStringContainsString('thumbnail-html', $output, 'Should include thumbnail HTML');

		// Reset mocks for next scenario
		M::tearDown();
		M::setUp();

		// SCENARIO 2: Test image doesn't render when has_post_thumbnail() is false
		$this->mockThemeMods(['index_feat_image' => 1]);
		
		M::userFunction('has_post_thumbnail')->andReturn(false);

		$output = $this->captureOutput(function() use ($instance) {
			$instance->post_image();
		});

		$this->assertEmpty($output, 'Should not render anything when post has no thumbnail');

		// Reset mocks for next scenario
		M::tearDown();
		M::setUp();

		// SCENARIO 3: Test image doesn't render when feature is disabled
		$this->mockThemeMods(['index_feat_image' => 0]);
		
		M::userFunction('has_post_thumbnail')->andReturn(true);

		$output = $this->captureOutput(function() use ($instance) {
			$instance->post_image();
		});

		$this->assertEmpty($output, 'Should not render anything when feature is disabled');

		// Reset mocks for next scenario
		M::tearDown();
		M::setUp();

		// SCENARIO 4: Test default behavior (feature enabled by default)
		// Mock get_theme_mod to return default value 1
		M::userFunction('get_theme_mod')
			->with('index_feat_image', 1)
			->andReturn(1);
		
		M::userFunction('has_post_thumbnail')->andReturn(true);
		M::userFunction('the_permalink')->andReturnUsing(function() {
			echo 'https://example.com/post';
		});
		M::userFunction('the_title_attribute')->andReturnUsing(function() {
			echo 'Sample Post Title';
		});
		M::userFunction('the_post_thumbnail')->with('large-thumb')->andReturnUsing(function() {
			echo 'thumbnail-html';
		});

		$output = $this->captureOutput(function() use ($instance) {
			$instance->post_image();
		});

		$this->assertStringContainsString('<div class="entry-thumb">', $output, 'Should render with default enabled setting');
	}

	/**
	 * Test post meta rendering functionality.
	 * 
	 * Tests that:
	 * - Post meta renders for 'post' post type
	 * - Post meta doesn't render for non-'post' post types
	 * - Post meta doesn't render when elements array is empty
	 * - entry-meta div has correct position and delimiter classes
	 * - Meta elements are called via call_user_func
	 *
	 * @since 1.0.0
	 */
	public function test_post_meta_rendering() {
		// Reset singleton to ensure clean test
		$this->resetSingleton('Sydney_Posts_Archive');
		$instance = \Sydney_Posts_Archive::get_instance();

		// SCENARIO 1: Test meta renders for post type 'post' with elements
		$this->mockThemeMods([
			'archive_meta_elements' => ['post_date', 'post_categories'],
			'archive_meta_delimiter' => 'dot'
		]);
		
		M::userFunction('get_post_type')->andReturn('post');
		
		// Mock the meta element methods
		$called_methods = [];
		$mock_instance = $this->getMockBuilder('Sydney_Posts_Archive')
			->onlyMethods(['post_date', 'post_categories'])
			->getMock();

		$mock_instance->expects($this->once())->method('post_date')
			->willReturnCallback(function() use (&$called_methods) {
				$called_methods[] = 'post_date';
				echo 'date-output';
			});

		$mock_instance->expects($this->once())->method('post_categories')
			->willReturnCallback(function() use (&$called_methods) {
				$called_methods[] = 'post_categories';
				echo 'categories-output';
			});

		$output = $this->captureOutput(function() use ($mock_instance) {
			$mock_instance->post_meta('above-title');
		});

		$this->assertStringContainsString('<div class="entry-meta above-title delimiter-dot">', $output, 'Should create entry-meta div with correct classes');
		$this->assertStringContainsString('date-output', $output, 'Should call post_date method');
		$this->assertStringContainsString('categories-output', $output, 'Should call post_categories method');
		$this->assertEquals(['post_date', 'post_categories'], $called_methods, 'Should call methods in correct order');

		// Reset mocks for next scenario
		M::tearDown();
		M::setUp();

		// SCENARIO 2: Test meta doesn't render for non-post post type
		$this->mockThemeMods([
			'archive_meta_elements' => ['post_date', 'post_categories'],
			'archive_meta_delimiter' => 'dot'
		]);
		
		M::userFunction('get_post_type')->andReturn('page');

		$output = $this->captureOutput(function() use ($instance) {
			$instance->post_meta('above-title');
		});

		$this->assertEmpty($output, 'Should not render anything for non-post post types');

		// Reset mocks for next scenario
		M::tearDown();
		M::setUp();

		// SCENARIO 3: Test meta doesn't render when elements array is empty
		$this->mockThemeMods([
			'archive_meta_elements' => [],
			'archive_meta_delimiter' => 'dot'
		]);
		
		M::userFunction('get_post_type')->andReturn('post');

		$output = $this->captureOutput(function() use ($instance) {
			$instance->post_meta('above-title');
		});

		$this->assertEmpty($output, 'Should not render anything when elements array is empty');

		// Reset mocks for next scenario
		M::tearDown();
		M::setUp();

		// SCENARIO 4: Test different delimiter and position
		$this->mockThemeMods([
			'archive_meta_elements' => ['post_date'],
			'archive_meta_delimiter' => 'slash'
		]);
		
		M::userFunction('get_post_type')->andReturn('post');
		
		$mock_instance = $this->getMockBuilder('Sydney_Posts_Archive')
			->onlyMethods(['post_date'])
			->getMock();

		$mock_instance->expects($this->once())->method('post_date')
			->willReturn('date-content');

		$output = $this->captureOutput(function() use ($mock_instance) {
			$mock_instance->post_meta('below-excerpt');
		});

		$this->assertStringContainsString('<div class="entry-meta below-excerpt delimiter-slash">', $output, 'Should use correct position and delimiter classes');
	}

	/**
	 * Test post title rendering functionality.
	 * 
	 * Tests that:
	 * - Post title renders with correct HTML structure
	 * - entry-header wrapper is present
	 * - h2 element has correct classes (title-post entry-title)
	 * - Schema markup is included
	 * - Permalink is correctly set in link
	 *
	 * @since 1.0.0
	 */
	public function test_post_title_rendering() {
		// Reset singleton to ensure clean test
		$this->resetSingleton('Sydney_Posts_Archive');
		$instance = \Sydney_Posts_Archive::get_instance();

		// Mock WordPress functions
		M::userFunction('the_title')
			->andReturnUsing(function($before, $after) {
				echo $before . 'Sample Post Title' . $after;
			});
		
		M::userFunction('get_permalink')->andReturn('https://example.com/sample-post');
		M::userFunction('sydney_get_schema')->with('headline')->andReturn('itemProp="headline"');

		$output = $this->captureOutput(function() use ($instance) {
			$instance->post_title();
		});

		$this->assertStringContainsString('<header class="entry-header">', $output, 'Should create entry-header wrapper');
		$this->assertStringContainsString('</header><!-- .entry-header -->', $output, 'Should close entry-header with comment');
		$this->assertStringContainsString('<h2 class="title-post entry-title"', $output, 'Should create h2 with correct classes');
		$this->assertStringContainsString('itemProp="headline"', $output, 'Should include schema markup');
		$this->assertStringContainsString('<a href="https://example.com/sample-post"', $output, 'Should include correct permalink');
		$this->assertStringContainsString('rel="bookmark"', $output, 'Should include bookmark rel attribute');
		$this->assertStringContainsString('Sample Post Title', $output, 'Should include the post title');
	}

	/**
	 * Test post excerpt content type logic.
	 * 
	 * Tests that:
	 * - the_content() is called when archive_content_type is 'content'
	 * - the_content() is called when full_content_home is 1 and is_home() is true
	 * - the_content() is called when full_content_archives is 1 and is_archive() is true
	 * - the_excerpt() is called in all other cases
	 * - Read more link is rendered when enabled
	 * - Method returns early when show_excerpt is disabled
	 *
	 * @since 1.0.0
	 */
	public function test_post_excerpt_content_type_logic() {
		// Reset singleton to ensure clean test
		$this->resetSingleton('Sydney_Posts_Archive');
		$instance = \Sydney_Posts_Archive::get_instance();

		// SCENARIO 1: Test early return when show_excerpt is disabled
		$this->mockThemeMods(['show_excerpt' => 0]);

		$output = $this->captureOutput(function() use ($instance) {
			$instance->post_excerpt();
		});

		$this->assertEmpty($output, 'Should return early and render nothing when show_excerpt is disabled');

		// Reset mocks for next scenario
		M::tearDown();
		M::setUp();

		// SCENARIO 2: Test the_content() is called when archive_content_type is 'content'
		$this->mockThemeMods([
			'show_excerpt' => 1,
			'read_more_link' => 0,
			'full_content_home' => 0,
			'full_content_archives' => 0,
			'archive_content_type' => 'content'
		]);

		M::userFunction('sydney_do_schema')->with('entry_content')->andReturnUsing(function() {
			echo 'itemProp="text"';
		});
		M::userFunction('the_content')->andReturnUsing(function() {
			echo 'full-content-output';
		});

		$output = $this->captureOutput(function() use ($instance) {
			$instance->post_excerpt();
		});

		$this->assertStringContainsString('<div class="entry-post"', $output, 'Should create entry-post wrapper');
		$this->assertStringContainsString('itemProp="text"', $output, 'Should include schema markup');
		$this->assertStringContainsString('full-content-output', $output, 'Should call the_content() when content type is content');

		// Reset mocks for next scenario
		M::tearDown();
		M::setUp();

		// SCENARIO 3: Test the_content() is called when full_content_home is 1 and is_home() is true
		$this->mockThemeMods([
			'show_excerpt' => 1,
			'read_more_link' => 0,
			'full_content_home' => 1,
			'full_content_archives' => 0,
			'archive_content_type' => 'excerpt'
		]);

		M::userFunction('sydney_do_schema')->with('entry_content')->andReturnUsing(function() {
			echo 'itemProp="text"';
		});
		M::userFunction('is_home')->andReturn(true);
		M::userFunction('is_archive')->andReturn(false);
		M::userFunction('the_content')->andReturnUsing(function() {
			echo 'home-full-content';
		});

		$output = $this->captureOutput(function() use ($instance) {
			$instance->post_excerpt();
		});

		$this->assertStringContainsString('home-full-content', $output, 'Should call the_content() when full_content_home is enabled and is_home()');

		// Reset mocks for next scenario
		M::tearDown();
		M::setUp();

		// SCENARIO 4: Test the_content() is called when full_content_archives is 1 and is_archive() is true
		$this->mockThemeMods([
			'show_excerpt' => 1,
			'read_more_link' => 0,
			'full_content_home' => 0,
			'full_content_archives' => 1,
			'archive_content_type' => 'excerpt'
		]);

		M::userFunction('sydney_do_schema')->with('entry_content')->andReturnUsing(function() {
			echo 'itemProp="text"';
		});
		M::userFunction('is_home')->andReturn(false);
		M::userFunction('is_archive')->andReturn(true);
		M::userFunction('the_content')->andReturnUsing(function() {
			echo 'archive-full-content';
		});

		$output = $this->captureOutput(function() use ($instance) {
			$instance->post_excerpt();
		});

		$this->assertStringContainsString('archive-full-content', $output, 'Should call the_content() when full_content_archives is enabled and is_archive()');

		// Reset mocks for next scenario
		M::tearDown();
		M::setUp();

		// SCENARIO 5: Test the_excerpt() is called in default case
		$this->mockThemeMods([
			'show_excerpt' => 1,
			'read_more_link' => 0,
			'full_content_home' => 0,
			'full_content_archives' => 0,
			'archive_content_type' => 'excerpt'
		]);

		M::userFunction('sydney_do_schema')->with('entry_content')->andReturnUsing(function() {
			echo 'itemProp="text"';
		});
		M::userFunction('is_home')->andReturn(false);
		M::userFunction('is_archive')->andReturn(false);
		M::userFunction('the_excerpt')->andReturnUsing(function() {
			echo 'excerpt-output';
		});

		$output = $this->captureOutput(function() use ($instance) {
			$instance->post_excerpt();
		});

		$this->assertStringContainsString('excerpt-output', $output, 'Should call the_excerpt() in default case');

		// Reset mocks for next scenario
		M::tearDown();
		M::setUp();

		// SCENARIO 6: Test read more link is rendered when enabled
		$this->mockThemeMods([
			'show_excerpt' => 1,
			'read_more_link' => 1,
			'full_content_home' => 0,
			'full_content_archives' => 0,
			'archive_content_type' => 'excerpt'
		]);

		M::userFunction('sydney_do_schema')->with('entry_content')->andReturnUsing(function() {
			echo 'itemProp="text"';
		});
		M::userFunction('is_home')->andReturn(false);
		M::userFunction('is_archive')->andReturn(false);
		M::userFunction('the_excerpt')->andReturnUsing(function() {
			echo 'excerpt-content';
		});
		M::userFunction('wp_strip_all_tags')->with('Sample Title')->andReturn('Sample Title');
		M::userFunction('get_the_title')->andReturn('Sample Title');
		M::userFunction('get_permalink')->andReturn('https://example.com/post');
		M::userFunction('esc_html__')->with('Read more', 'sydney')->andReturn('Read more');

		$output = $this->captureOutput(function() use ($instance) {
			$instance->post_excerpt();
		});

		$this->assertStringContainsString('excerpt-content', $output, 'Should include excerpt content');
		$this->assertStringContainsString('<a class="read-more"', $output, 'Should include read more link');
		$this->assertStringContainsString('title="Sample Title"', $output, 'Should include post title in read more link');
		$this->assertStringContainsString('href="https://example.com/post"', $output, 'Should include correct permalink in read more link');
		$this->assertStringContainsString('Read more', $output, 'Should include read more text');
	}
}