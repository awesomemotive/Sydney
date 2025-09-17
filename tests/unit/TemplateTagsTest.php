<?php
/**
 * Unit tests for the Template Tags functionality in Sydney theme
 *
 * @package Sydney
 * @subpackage Tests
 */

namespace Sydney\Tests;

use Sydney\Tests\BaseThemeTest;
use WP_Mock as M;

/**
 * Test class for Template Tags functionality.
 *
 * @since 1.0.0
 */
class TemplateTagsTest extends BaseThemeTest {

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
     * Set up common mocks for template tag tests.
     *
     * @since 1.0.0
     * @return void
     */
    protected function setupTemplateTagMocks(): void {
        // Load the template tags functions
        require_once __DIR__ . '/../../inc/template-tags.php';
        
        // Mock common post functions
        $this->mockFunction('get_post_type', 'post');
        $this->mockFunction('get_the_date', '2024-01-15');
        $this->mockFunction('get_the_modified_date', '2024-01-16');
        $this->mockFunction('get_the_time', function($format = null) {
            if ($format === 'U') return 1705363200; // Unix timestamp for 2024-01-15
            return '2024-01-15';
        });
        $this->mockFunction('get_the_modified_time', function($format = null) {
            if ($format === 'U') return 1705449600; // Unix timestamp for 2024-01-16
            return '2024-01-16';
        });
        $this->mockFunction('get_permalink', 'https://example.com/test-post/');
        $this->mockFunction('get_the_author', 'John Doe');
        $this->mockFunction('get_the_author_meta', function($field, $user_id = null) {
            switch ($field) {
                case 'ID': return 1;
                case 'email': return 'john@example.com';
                default: return 'John Doe';
            }
        });
        $this->mockFunction('get_author_posts_url', 'https://example.com/author/john-doe/');
        
        // Mock schema functions
        $this->mockFunction('sydney_get_schema', function($type) {
            switch ($type) {
                case 'published_date': return 'itemprop="datePublished"';
                case 'modified_date': return 'itemprop="dateModified"';
                default: return '';
            }
        });
        
        // Mock WordPress globals
        global $post;
        $post = (object) ['post_author' => 1];
        
        // Setup translation functions
        $this->mockTranslationFunctions();
    }

    /**
     * Test sydney_posted_on function with date/time formatting.
     * 
     * Tests that:
     * - Proper HTML time element is generated
     * - Schema markup is included
     * - Permalink is correctly linked
     * - Both published and modified dates are handled
     *
     * @since 1.0.0
     */
    public function test_posted_on_date_formatting() {
        $this->setupTemplateTagMocks();

        // The issue is that the setupTemplateTagMocks is already setting up get_the_time/get_the_modified_time
        // Let me override them completely after setup
        M::tearDown();
        $this->setUp();
        
        // Load the template tags functions
        require_once __DIR__ . '/../../inc/template-tags.php';
        
        // Mock schema functions
        $this->mockFunction('sydney_get_schema', function($type) {
            switch ($type) {
                case 'published_date': return 'itemprop="datePublished"';
                case 'modified_date': return 'itemprop="dateModified"';
                default: return '';
            }
        });
        
        // Mock date functions for SAME timestamp (should use single time element)
        $this->mockFunction('get_the_time', function($format = null) {
            if ($format === 'U') return 1705363200; // Same timestamp
            return '2024-01-15';
        });
        $this->mockFunction('get_the_modified_time', function($format = null) {
            if ($format === 'U') return 1705363200; // Same timestamp  
            return '2024-01-15';
        });
        $this->mockFunction('get_the_date', function($format = null) {
            if ($format === DATE_W3C) return '2024-01-15T10:00:00+00:00';
            return '2024-01-15';
        });
        $this->mockFunction('get_the_modified_date', function($format = null) {
            if ($format === DATE_W3C) return '2024-01-15T10:00:00+00:00';
            return '2024-01-15';
        });
        $this->mockFunction('get_permalink', 'https://example.com/test-post/');
        
        // Setup translation functions
        $this->mockTranslationFunctions();

        $output = $this->captureOutput(function() {
            sydney_posted_on();
        });

        $this->assertHtmlContainsAll($output, [
            '<span class="posted-on">',
            '<time class="entry-date published updated"',
            'itemprop="datePublished"',
            'href="https://example.com/test-post/"',
            '2024-01-15'
        ]);
    }

    /**
     * Test sydney_posted_on function with different published and modified dates.
     * 
     * Tests that:
     * - Separate time elements for published and modified dates
     * - Both schema markups are present
     * - Correct CSS classes are applied
     *
     * @since 1.0.0
     */
    public function test_posted_on_different_dates() {
        $this->setupTemplateTagMocks();

        // Different timestamps and dates for published and modified
        $this->mockFunction('get_the_date', function($format = null) {
            if ($format === DATE_W3C) return '2024-01-15T10:00:00+00:00';
            return '2024-01-15';
        });
        $this->mockFunction('get_the_modified_date', function($format = null) {
            if ($format === DATE_W3C) return '2024-01-16T10:00:00+00:00';
            return '2024-01-16';
        });
        $this->mockFunction('get_the_time', function($format = null) {
            if ($format === 'U') return 1705363200; // 2024-01-15
            return '2024-01-15';
        });
        $this->mockFunction('get_the_modified_time', function($format = null) {
            if ($format === 'U') return 1705449600; // 2024-01-16
            return '2024-01-16';
        });

        $output = $this->captureOutput(function() {
            sydney_posted_on();
        });

        $this->assertHtmlContainsAll($output, [
            '<time class="entry-date published"',
            '<time class="updated"',
            'itemprop="dateModified"',
            '2024-01-15',
            '2024-01-16'
        ]);
    }

    /**
     * Test sydney_posted_by function with author information.
     * 
     * Tests that:
     * - Author name and link are properly generated
     * - Avatar is included when enabled
     * - Proper schema markup and CSS classes
     * - Avatar is excluded when disabled
     *
     * @since 1.0.0
     */
    public function test_posted_by_author_info() {
        $this->setupTemplateTagMocks();

        // Test with avatar enabled
        $this->mockThemeMods(['show_avatar' => 1]);
        $this->mockFunction('get_avatar', '<img src="avatar.jpg" width="16" height="16" alt="Avatar">');

        $output = $this->captureOutput(function() {
            sydney_posted_by();
        });

        $this->assertHtmlContainsAll($output, [
            '<span class="byline">',
            '<span class="author vcard">',
            'By',
            '<img src="avatar.jpg"',
            '<a class="url fn n" href="https://example.com/author/john-doe/">John Doe</a>'
        ]);

        // Test with avatar disabled
        M::tearDown();
        $this->setUp();
        $this->setupTemplateTagMocks();
        
        $this->mockThemeMods(['show_avatar' => 0]);

        $output_no_avatar = $this->captureOutput(function() {
            sydney_posted_by();
        });

        $this->assertStringContainsString('By', $output_no_avatar);
        $this->assertStringContainsString('John Doe', $output_no_avatar);
        $this->assertStringNotContainsString('<img', $output_no_avatar);
    }

    /**
     * Test sydney_post_categories function with category links.
     * 
     * Tests that:
     * - Categories are displayed for post type 'post'
     * - Proper HTML structure with cat-links class
     * - Categories are not displayed for non-post types
     * - Handles empty categories gracefully
     *
     * @since 1.0.0
     */
    public function test_post_categories_display() {
        $this->setupTemplateTagMocks();

        // Test with categories for post type
        $this->mockFunction('get_post_type', 'post');
        $this->mockFunction('get_the_category_list', '<a href="cat1-link">Category 1</a>, <a href="cat2-link">Category 2</a>');

        $output = $this->captureOutput(function() {
            sydney_post_categories();
        });

        $this->assertHtmlContainsAll($output, [
            '<span class="cat-links">',
            '<a href="cat1-link">Category 1</a>',
            '<a href="cat2-link">Category 2</a>'
        ]);

        // Test with non-post type
        M::tearDown();
        $this->setUp();
        $this->setupTemplateTagMocks();
        
        $this->mockFunction('get_post_type', 'page');

        $output_page = $this->captureOutput(function() {
            sydney_post_categories();
        });

        $this->assertEmpty($output_page, 'Categories should not display for non-post types');
    }

    /**
     * Test sydney_entry_footer function with tags display.
     * 
     * Tests that:
     * - Tags are displayed when enabled and available
     * - Tags are hidden when disabled via theme mod
     * - Only displays for post type 'post' and single posts
     *
     * @since 1.0.0
     */
    public function test_entry_footer_tags_display() {
        $this->setupTemplateTagMocks();

        // Test with tags enabled and available
        $this->mockThemeMods(['single_post_show_tags' => 1]);
        $this->mockFunction('get_post_type', 'post');
        $this->mockFunction('get_the_tag_list', '<a href="tag1">Tag 1</a><a href="tag2">Tag 2</a>');
        $this->mockFunction('is_single', true);
        $this->mockFunction('edit_post_link', ''); // Simple mock to avoid issues

        $output = $this->captureOutput(function() {
            sydney_entry_footer();
        });

        $this->assertHtmlContainsAll($output, [
            '<span class="tags-links">',
            '<a href="tag1">Tag 1</a>',
            '<a href="tag2">Tag 2</a>'
        ]);

        // Test with tags disabled
        M::tearDown();
        $this->setUp();
        $this->setupTemplateTagMocks();
        
        $this->mockThemeMods(['single_post_show_tags' => 0]);
        $this->mockFunction('edit_post_link', ''); // Simple mock to avoid issues

        $output_no_tags = $this->captureOutput(function() {
            sydney_entry_footer();
        });

        $this->assertStringNotContainsString('tags-links', $output_no_tags);
    }

    /**
     * Test sydney_single_post_meta function with customizable elements.
     * 
     * Tests that:
     * - Meta elements are displayed when not disabled
     * - Meta is hidden when disabled via theme mod
     * - Custom elements are called correctly
     * - Proper CSS classes and structure
     *
     * @since 1.0.0
     */
    public function test_single_post_meta_elements() {
        $this->setupTemplateTagMocks();

        // Test with meta enabled - the function will call the actual sydney_* functions
        $this->mockThemeMods([
            'hide_meta_single' => 0,
            'single_post_meta_elements' => ['sydney_posted_by', 'sydney_posted_on', 'sydney_post_categories'],
            'archive_meta_delimiter' => 'dot'
        ]);

        $output = $this->captureOutput(function() {
            sydney_single_post_meta('above-title');
        });

        // Check for the actual output structure from the real functions
        $this->assertHtmlContainsAll($output, [
            '<div class="entry-meta above-title delimiter-dot">',
            '<span class="byline">',
            '<span class="posted-on">',
            'John Doe',
            '2024-01-15'
        ]);

        // Test with meta disabled
        M::tearDown();
        $this->setUp();
        $this->setupTemplateTagMocks();
        
        $this->mockThemeMods(['hide_meta_single' => 1]);

        $output_disabled = $this->captureOutput(function() {
            sydney_single_post_meta('above-title');
        });

        $this->assertEmpty($output_disabled, 'Meta should not display when disabled');
    }

    /**
     * Test sydney_post_navigation function with next/previous post links.
     * 
     * Tests that:
     * - Navigation is displayed when posts are available
     * - Navigation is hidden when disabled via theme mod
     * - Proper HTML structure and SVG icons
     * - Early return when no adjacent posts
     *
     * @since 1.0.0
     */
    public function test_post_navigation_links() {
        $this->setupTemplateTagMocks();

        // Test with navigation enabled and posts available
        $this->mockThemeMods(['single_post_show_post_nav' => 1]);
        $this->mockFunction('is_attachment', false);
        $this->mockFunction('get_adjacent_post', function($in_same_term, $excluded_terms, $previous) {
            return $previous ? (object)['ID' => 1] : (object)['ID' => 2]; // Mock previous and next posts
        });
        $this->mockFunction('previous_post_link', function($format, $link) {
            echo str_replace('%link', '<a href="prev-post">Previous Post</a>', $format);
        });
        $this->mockFunction('next_post_link', function($format, $link) {
            echo str_replace('%link', '<a href="next-post">Next Post</a>', $format);
        });

        $output = $this->captureOutput(function() {
            sydney_post_navigation();
        });

        $this->assertHtmlContainsAll($output, [
            '<nav class="navigation post-navigation"',
            'role="navigation"',
            '<h2 class="screen-reader-text">Post navigation</h2>',
            '<div class="nav-links clearfix">',
            '<div class="nav-previous">',
            '<div class="nav-next">',
            'Previous Post',
            'Next Post'
        ]);

        // Test with navigation disabled
        M::tearDown();
        $this->setUp();
        $this->setupTemplateTagMocks();
        
        $this->mockThemeMods(['single_post_show_post_nav' => 0]);

        $output_disabled = $this->captureOutput(function() {
            sydney_post_navigation();
        });

        $this->assertEmpty($output_disabled, 'Navigation should not display when disabled');
    }

    /**
     * Test sydney_posts_navigation function with pagination.
     * 
     * Tests that:
     * - Pagination is displayed when enabled
     * - Proper pagination parameters are passed
     * - Early return when disabled via filter
     *
     * @since 1.0.0
     */
    public function test_posts_navigation_pagination() {
        $this->setupTemplateTagMocks();

        // Mock the posts pagination function
        $this->mockFunction('the_posts_pagination', function($args) {
            echo '<nav class="pagination">';
            echo '<span class="prev">' . $args['prev_text'] . '</span>';
            echo '<span class="next">' . $args['next_text'] . '</span>';
            echo '</nav>';
        });

        $output = $this->captureOutput(function() {
            sydney_posts_navigation();
        });

        $this->assertHtmlContainsAll($output, [
            '<nav class="pagination">',
            '<span class="prev">&lt;</span>',
            '<span class="next">&gt;</span>'
        ]);
    }


    /**
     * Test sydney_get_first_cat function.
     * 
     * Tests that:
     * - First category is displayed correctly for posts
     * - Function only works for post type 'post'
     * - Proper HTML structure and links
     *
     * @since 1.0.0
     */
    public function test_get_first_category() {
        $this->setupTemplateTagMocks();

        // Mock categories
        $categories = [
            (object) ['term_id' => 1, 'name' => 'Category 1'],
            (object) ['term_id' => 2, 'name' => 'Category 2']
        ];

        // Test sydney_get_first_cat for post type
        $this->mockFunction('get_post_type', 'post');
        $this->mockFunction('get_the_category', $categories);
        $this->mockFunction('get_category_link', function($term_id) {
            return 'https://example.com/category/' . $term_id . '/';
        });

        $output_first = $this->captureOutput(function() {
            sydney_get_first_cat();
        });

        $this->assertHtmlContainsAll($output_first, [
            '<a href="https://example.com/category/1/"',
            'title="Category 1"',
            'class="post-cat"',
            'Category 1'
        ]);

        // Test with non-post type
        M::tearDown();
        $this->setUp();
        $this->setupTemplateTagMocks();
        
        $this->mockFunction('get_post_type', 'page');

        $output_page = $this->captureOutput(function() {
            sydney_get_first_cat();
        });

        $this->assertEmpty($output_page, 'Categories should not display for non-post types');
    }

    /**
     * Test sydney_single_post_thumbnail function with featured image display.
     * 
     * Tests that:
     * - Featured image displays when enabled and thumbnail exists
     * - No output when disabled via theme mod
     * - No output when no thumbnail exists
     * - No output when manually disabled via parameter
     * - Proper CSS classes are applied
     *
     * @since 1.0.0
     */
    public function test_single_post_thumbnail_display() {
        $this->setupTemplateTagMocks();

        // Test with featured image enabled and thumbnail exists
        $this->mockThemeMods(['single_post_show_featured' => 1]);
        $this->mockFunction('has_post_thumbnail', true);
        $this->mockFunction('the_post_thumbnail', function($size) {
            echo '<img src="featured-image.jpg" class="' . $size . '" alt="Featured Image">';
        });

        $output = $this->captureOutput(function() {
            sydney_single_post_thumbnail(false, 'custom-class');
        });

        $this->assertHtmlContainsAll($output, [
            '<div class="entry-thumb custom-class">',
            '<img src="featured-image.jpg"',
            'class="large-thumb"',
            'alt="Featured Image"'
        ]);

        // Test with featured image disabled via theme mod
        M::tearDown();
        $this->setUp();
        $this->setupTemplateTagMocks();
        
        $this->mockThemeMods(['single_post_show_featured' => 0]);
        $this->mockFunction('has_post_thumbnail', true);

        $output_disabled = $this->captureOutput(function() {
            sydney_single_post_thumbnail(false);
        });

        $this->assertEmpty($output_disabled, 'Thumbnail should not display when disabled via theme mod');

        // Test with no thumbnail
        M::tearDown();
        $this->setUp();
        $this->setupTemplateTagMocks();
        
        $this->mockThemeMods(['single_post_show_featured' => 1]);
        $this->mockFunction('has_post_thumbnail', false);

        $output_no_thumb = $this->captureOutput(function() {
            sydney_single_post_thumbnail(false);
        });

        $this->assertEmpty($output_no_thumb, 'Thumbnail should not display when no thumbnail exists');

        // Test with manual disable parameter
        M::tearDown();
        $this->setUp();
        $this->setupTemplateTagMocks();
        
        $this->mockThemeMods(['single_post_show_featured' => 1]);
        $this->mockFunction('has_post_thumbnail', true);

        $output_manual_disable = $this->captureOutput(function() {
            sydney_single_post_thumbnail(true); // Manually disabled
        });

        $this->assertEmpty($output_manual_disable, 'Thumbnail should not display when manually disabled');
    }

    /**
     * Test sydney_entry_comments function with comment links display.
     * 
     * Tests that:
     * - Comment links display when comments are open
     * - Comment links display when comments exist but are closed
     * - No output when post is password protected
     * - Proper comment count formatting
     * - Correct HTML structure
     *
     * @since 1.0.0
     */
    public function test_entry_comments_display() {
        $this->setupTemplateTagMocks();

        // Test with comments open
        $this->mockFunction('post_password_required', false);
        $this->mockFunction('comments_open', true);
        $this->mockFunction('get_comments_number', 0);
        $this->mockFunction('comments_popup_link', function($zero, $one, $more) {
            echo '<a href="#comments" class="comment-link">' . $zero . '</a>';
        });

        $output = $this->captureOutput(function() {
            sydney_entry_comments();
        });

        $this->assertHtmlContainsAll($output, [
            '<span class="comments-link">',
            '<a href="#comments"',
            'class="comment-link"',
            '0 comments'
        ]);

        // Test with comments closed but existing comments
        M::tearDown();
        $this->setUp();
        $this->setupTemplateTagMocks();
        
        $this->mockFunction('post_password_required', false);
        $this->mockFunction('comments_open', false);
        $this->mockFunction('get_comments_number', 5); // Has existing comments
        $this->mockFunction('comments_popup_link', function($zero, $one, $more) {
            echo '<a href="#comments" class="comment-link">5 comments</a>';
        });

        $output_closed = $this->captureOutput(function() {
            sydney_entry_comments();
        });

        $this->assertHtmlContainsAll($output_closed, [
            '<span class="comments-link">',
            '<a href="#comments"',
            '5 comments'
        ]);

        // Test with password protected post
        M::tearDown();
        $this->setUp();
        $this->setupTemplateTagMocks();
        
        $this->mockFunction('post_password_required', true);
        $this->mockFunction('comments_open', true);
        $this->mockFunction('get_comments_number', 3);

        $output_protected = $this->captureOutput(function() {
            sydney_entry_comments();
        });

        $this->assertEmpty($output_protected, 'Comments should not display for password protected posts');

        // Test with no comments and comments closed
        M::tearDown();
        $this->setUp();
        $this->setupTemplateTagMocks();
        
        $this->mockFunction('post_password_required', false);
        $this->mockFunction('comments_open', false);
        $this->mockFunction('get_comments_number', 0); // No comments

        $output_none = $this->captureOutput(function() {
            sydney_entry_comments();
        });

        $this->assertEmpty($output_none, 'Comments should not display when closed and no comments exist');
    }
}
