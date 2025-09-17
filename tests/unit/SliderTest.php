<?php
/**
 * Unit tests for the Slider functionality in Sydney theme
 *
 * @package Sydney
 * @subpackage Tests
 */

namespace Sydney\Tests;

use Sydney\Tests\BaseThemeTest;
use WP_Mock as M;

/**
 * Test class for Slider functionality.
 *
 * @since 1.0.0
 */
class SliderTest extends BaseThemeTest {

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
     * Set up common mocks for slider tests.
     *
     * @since 1.0.0
     * @return void
     */
    protected function setupSliderMocks(): void {
        // Load the slider functions
        require_once __DIR__ . '/../../inc/slider.php';
        
        // Load extras.php for sydney_is_amp and sydney_image_alt functions
        if (!function_exists('sydney_is_amp')) {
            require_once __DIR__ . '/../../inc/extras.php';
        }
        
        // Mock WordPress media functions for sydney_image_alt
        $this->mockFunction('attachment_url_to_postid', function($url) {
            // Return a mock attachment ID based on URL
            return 123;
        });
        $this->mockFunction('get_post_meta', function($id, $key, $single = false) {
            if ($key === '_wp_attachment_image_alt') {
                return 'Alt text for image ' . $id;
            }
            return '';
        });
        
        // Mock common WordPress functions
        $this->mockFunction('sydney_is_amp', false);
        
        // Mock sydney_image_alt function to avoid loading extras.php dependencies
        $this->mockFunction('sydney_image_alt', function($image) {
            return 'Alt text for image ' . basename($image);
        });
    }

    /**
     * Test slider template function with front page slider enabled.
     * 
     * Tests that:
     * - Slider renders on front page when front_header_type is 'slider'
     * - Proper HTML structure is generated
     * - Multiple slides are rendered correctly
     * - Slider speed and mobile configuration are applied
     *
     * @since 1.0.0
     */
    public function test_slider_template_front_page_enabled() {
        $this->setupSliderMocks();
        
        // Mock front page conditions
        $this->mockConditionalFunctions(['is_front_page' => true]);
        
        // Mock theme options and mods
        $this->mockOptions(['sydney-update-header' => false]);
        $this->mockThemeMods([
            'front_header_type' => 'slider',
            'textslider_slide' => 0,
            'mobile_slider' => 'responsive',
            'slider_speed' => '4000',
            'slider_title_1' => 'Welcome to Sydney',
            'slider_title_2' => 'Ready to begin your journey?',
            'slider_subtitle_1' => 'Feel free to look around',
            'slider_subtitle_2' => 'Feel free to look around',
            'slider_image_1' => 'https://example.com/slide1.jpg',
            'slider_image_2' => 'https://example.com/slide2.jpg',
            'slider_button_text' => 'Click to begin',
            'slider_button_url' => '#primary'
        ]);

        // Capture the slider output
        $output = $this->captureOutput(function() {
            sydney_slider_template();
        });

        // Assert slider container and configuration
        $this->assertHtmlContainsAll($output, [
            '<div id="slideshow" class="header-slider"',
            'data-speed="4000"',
            'data-mobileslider="responsive"',
            '<div class="slides-container">',
            'slide-item slide-item-1',
            'slide-item slide-item-2',
            'background-image:url(\'https://example.com/slide1.jpg\')',
            'background-image:url(\'https://example.com/slide2.jpg\')',
            '<h2 class="maintitle">Welcome to Sydney</h2>',
            '<h2 class="maintitle">Ready to begin your journey?</h2>',
            '<p class="subtitle">Feel free to look around</p>',
            '<a href="#primary" class="roll-button button-slider">Click to begin</a>'
        ]);
    }

    /**
     * Test slider template function with site header slider enabled.
     * 
     * Tests that:
     * - Slider renders on non-front pages when site_header_type is 'slider'
     * - Proper conditions are checked
     *
     * @since 1.0.0
     */
    public function test_slider_template_site_header_enabled() {
        $this->setupSliderMocks();
        
        // Mock non-front page conditions
        $this->mockConditionalFunctions(['is_front_page' => false]);
        
        // Mock theme options and mods
        $this->mockOptions(['sydney-update-header' => true]);
        $this->mockThemeMods([
            'site_header_type' => 'slider',
            'slider_image_1' => 'https://example.com/slide1.jpg',
            'slider_title_1' => 'Site Header Slider',
            'slider_subtitle_1' => 'Not on front page'
        ]);

        // Capture the slider output
        $output = $this->captureOutput(function() {
            sydney_slider_template();
        });

        // Assert slider is rendered
        $this->assertStringContainsString('<div id="slideshow" class="header-slider"', $output);
        $this->assertStringContainsString('Site Header Slider', $output);
    }

    /**
     * Test slider template with no first image returns early.
     * 
     * Tests that:
     * - Function returns early when slider_image_1 is empty
     * - No HTML output is generated
     *
     * @since 1.0.0
     */
    public function test_slider_template_no_images_early_return() {
        $this->setupSliderMocks();
        
        // Mock front page conditions
        $this->mockConditionalFunctions(['is_front_page' => true]);
        
        // Mock theme options and mods with no first image
        $this->mockOptions(['sydney-update-header' => false]);
        $this->mockThemeMods([
            'front_header_type' => 'slider',
            'slider_image_1' => '', // Empty first image should cause early return
        ]);

        // Capture the slider output
        $output = $this->captureOutput(function() {
            sydney_slider_template();
        });

        // Assert no output is generated
        $this->assertEmpty($output, 'Slider should not render when first image is empty');
    }

    /**
     * Test slider with single slide (no autoplay).
     * 
     * Tests that:
     * - Single slide sets speed to 0 (no autoplay)
     * - Only one slide is rendered
     * - Proper HTML structure is maintained
     *
     * @since 1.0.0
     */
    public function test_slider_single_slide_no_autoplay() {
        $this->setupSliderMocks();
        
        // Mock front page conditions
        $this->mockConditionalFunctions(['is_front_page' => true]);
        
        // Mock theme options and mods with single slide
        $this->mockOptions(['sydney-update-header' => false]);
        $this->mockThemeMods([
            'front_header_type' => 'slider',
            'slider_image_1' => 'https://example.com/slide1.jpg',
            'slider_image_2' => '', // Empty second image
            'slider_title_1' => 'Single Slide',
            'slider_subtitle_1' => 'No autoplay'
        ]);

        // Capture the slider output
        $output = $this->captureOutput(function() {
            sydney_slider_template();
        });

        // Assert single slide with speed 0
        $this->assertStringContainsString('data-speed="0"', $output);
        $this->assertStringContainsString('slide-item-1', $output);
        $this->assertStringNotContainsString('slide-item-2', $output);
        $this->assertStringContainsString('Single Slide', $output);
    }


    /**
     * Test slider button functionality.
     * 
     * Tests that:
     * - Button is rendered when slider_button_text is provided
     * - Proper URL and text are applied
     * - Button has correct CSS classes
     * - No button when text is empty
     *
     * @since 1.0.0
     */
    public function test_slider_button_functionality() {
        $this->setupSliderMocks();

        // Test with button text
        $this->mockThemeMods([
            'slider_button_text' => 'Get Started',
            'slider_button_url' => 'https://example.com/start'
        ]);

        $button_output = sydney_slider_button();
        
        $this->assertStringContainsString('<a href="https://example.com/start"', $button_output);
        $this->assertStringContainsString('class="roll-button button-slider"', $button_output);
        $this->assertStringContainsString('Get Started', $button_output);

        // Test with empty button text (should return nothing)
        M::tearDown();
        $this->setUp(); // Re-setup base mocks
        $this->setupSliderMocks();
        
        $this->mockThemeMods([
            'slider_button_text' => '',
            'slider_button_url' => 'https://example.com/start'
        ]);

        $empty_button_output = sydney_slider_button();
        $this->assertEmpty($empty_button_output, 'Button should not render when text is empty');
    }

    /**
     * Test sydney_stop_text functionality.
     * 
     * Tests that:
     * - Stop text renders with proper HTML structure
     * - Uses first slide's title and subtitle
     * - Includes slider button
     * - Has correct CSS classes
     *
     * @since 1.0.0
     */
    public function test_stop_text_functionality() {
        $this->setupSliderMocks();
        
        // Mock theme mods for stop text
        $this->mockThemeMods([
            'slider_title_1' => 'Welcome Text',
            'slider_subtitle_1' => 'Descriptive text',
            'slider_button_text' => 'Learn More',
            'slider_button_url' => '#content'
        ]);

        // Capture the stop text output
        $output = $this->captureOutput(function() {
            sydney_stop_text();
        });

        // Assert stop text structure
        $this->assertHtmlContainsAll($output, [
            '<div class="slide-inner text-slider-stopped">',
            '<div class="contain text-slider">',
            '<h2 class="maintitle">Welcome Text</h2>',
            '<p class="subtitle">Descriptive text</p>',
            '<a href="#content" class="roll-button button-slider">Learn More</a>'
        ]);
    }

    /**
     * Test slider with text slider enabled.
     * 
     * Tests that:
     * - Text slider is rendered when textslider_slide is enabled
     * - Stop text is included in output
     * - Proper conditional rendering
     *
     * @since 1.0.0
     */
    public function test_slider_with_text_slider_enabled() {
        $this->setupSliderMocks();
        
        // Mock front page conditions
        $this->mockConditionalFunctions(['is_front_page' => true]);
        
        // Mock theme options and mods with text slider enabled
        $this->mockOptions(['sydney-update-header' => false]);
        $this->mockThemeMods([
            'front_header_type' => 'slider',
            'textslider_slide' => 1, // Enable text slider
            'slider_image_1' => 'https://example.com/slide1.jpg',
            'slider_title_1' => 'Text Slider Title',
            'slider_subtitle_1' => 'Text slider subtitle',
            'slider_button_text' => 'Action Button',
            'slider_button_url' => '#action'
        ]);

        // Capture the slider output
        $output = $this->captureOutput(function() {
            sydney_slider_template();
        });

        // Assert text slider is included
        $this->assertStringContainsString('text-slider-stopped', $output);
        $this->assertStringContainsString('Text Slider Title', $output);
    }

    /**
     * Test slider mobile configuration.
     * 
     * Tests that:
     * - Mobile slider configuration is properly applied
     * - data-mobileslider attribute is set correctly
     * - Different mobile slider values work
     *
     * @since 1.0.0
     */
    public function test_slider_mobile_configuration() {
        $this->setupSliderMocks();
        
        // Mock front page conditions
        $this->mockConditionalFunctions(['is_front_page' => true]);
        
        // Test responsive mobile slider
        $this->mockOptions(['sydney-update-header' => false]);
        $this->mockThemeMods([
            'front_header_type' => 'slider',
            'mobile_slider' => 'responsive',
            'slider_image_1' => 'https://example.com/slide1.jpg'
        ]);

        $output = $this->captureOutput(function() {
            sydney_slider_template();
        });

        $this->assertStringContainsString('data-mobileslider="responsive"', $output);

        // Test hidden mobile slider
        M::tearDown();
        $this->setUp();
        $this->setupSliderMocks();
        
        $this->mockConditionalFunctions(['is_front_page' => true]);
        $this->mockOptions(['sydney-update-header' => false]);
        $this->mockThemeMods([
            'front_header_type' => 'slider',
            'mobile_slider' => 'hidden',
            'slider_image_1' => 'https://example.com/slide1.jpg'
        ]);

        $output_hidden = $this->captureOutput(function() {
            sydney_slider_template();
        });

        $this->assertStringContainsString('data-mobileslider="hidden"', $output_hidden);
    }

    /**
     * Test slider conditions when disabled.
     * 
     * Tests that:
     * - Slider doesn't render when conditions are not met
     * - No output when front_header_type is not 'slider'
     * - No output when site_header_type is not 'slider' on non-front pages
     *
     * @since 1.0.0
     */
    public function test_slider_disabled_conditions() {
        $this->setupSliderMocks();
        
        // Test front page with slider disabled
        $this->mockConditionalFunctions(['is_front_page' => true]);
        $this->mockOptions(['sydney-update-header' => false]);
        $this->mockThemeMods([
            'front_header_type' => 'nothing', // Disabled
            'slider_image_1' => 'https://example.com/slide1.jpg'
        ]);

        $output = $this->captureOutput(function() {
            sydney_slider_template();
        });

        $this->assertEmpty($output, 'Slider should not render when front_header_type is not slider');

        // Test non-front page with site header slider disabled
        M::tearDown();
        $this->setUp();
        $this->setupSliderMocks();
        
        $this->mockConditionalFunctions(['is_front_page' => false]);
        $this->mockOptions(['sydney-update-header' => true]);
        $this->mockThemeMods([
            'site_header_type' => 'nothing', // Disabled
            'slider_image_1' => 'https://example.com/slide1.jpg'
        ]);

        $output_site = $this->captureOutput(function() {
            sydney_slider_template();
        });

        $this->assertEmpty($output_site, 'Slider should not render when site_header_type is not slider');
    }
}
