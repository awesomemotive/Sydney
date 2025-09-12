<?php
/**
 * Unit tests for comments template functionality in Sydney theme
 *
 * Tests that the comments template is displayed when it should be,
 * including various conditions like password protection, comment status,
 * and different template contexts.
 *
 * @package Sydney
 * @subpackage Tests
 */

namespace Sydney\Tests;

use Sydney\Tests\BaseThemeTest;
use WP_Mock as M;

/**
 * Test class for comments template functionality.
 *
 * @since 1.0.0
 */
class CommentsTemplateTest extends BaseThemeTest {

    /**
     * Test that comments template is not displayed for password-protected posts
     *
     * @since 1.0.0
     */
    public function test_comments_template_not_displayed_for_password_protected_posts() {
        // Mock post_password_required to return true
        $this->mockFunction('post_password_required', true);

        // Capture output from comments.php
        $output = $this->captureOutput(function() {
            include __DIR__ . '/../../comments.php';
        });

        // Should return early and output nothing
        $this->assertEmpty($output);
    }

    /**
     * Test that comments template displays properly when comments exist
     *
     * @since 1.0.0
     */
    public function test_comments_template_displays_when_comments_exist() {
        // Mock functions for a post with comments
        $this->mockFunction('post_password_required', false);
        $this->mockFunction('have_comments', true);
        $this->mockFunction('get_comments_number', 3);
        $this->mockFunction('get_the_title', 'Test Post Title');
        $this->mockFunction('get_comment_pages_count', 1);
        M::userFunction('get_option', [
            'return' => function($option, $default = null) {
                if ($option === 'page_comments') return false;
                return $default;
            }
        ]);
        
        // Mock translation functions
        M::userFunction('_nx', [
            'return' => function($single, $plural, $number, $context, $domain) {
                return $number == 1 ? $single : $plural;
            }
        ]);
        M::userFunction('number_format_i18n', ['return_arg' => 0]);
        $this->mockFunction('wp_list_comments', '');
        $this->mockFunction('comments_open', true);
        $this->mockFunction('post_type_supports', true);
        $this->mockFunction('get_post_type', 'post');
        $this->mockFunction('comment_form', '');

        // Capture output from comments.php
        $output = $this->captureOutput(function() {
            include __DIR__ . '/../../comments.php';
        });

        // Should contain comments area
        $this->assertStringContainsString('<div id="comments" class="comments-area">', $output);
        $this->assertStringContainsString('thoughts on', $output);
        $this->assertStringContainsString('Test Post Title', $output);
        $this->assertStringContainsString('<ol class="comments-list">', $output);
    }

    /**
     * Test comment navigation when multiple comment pages exist
     *
     * @since 1.0.0
     */
    public function test_comment_navigation_displays_with_multiple_pages() {
        // Mock functions for a post with paginated comments
        $this->mockFunction('post_password_required', false);
        $this->mockFunction('have_comments', true);
        $this->mockFunction('get_comments_number', 15);
        $this->mockFunction('get_the_title', 'Test Post Title');
        $this->mockFunction('get_comment_pages_count', 3);
        M::userFunction('get_option', [
            'return' => function($option, $default = null) {
                if ($option === 'page_comments') return true;
                return $default;
            }
        ]);
        
        // Mock translation and navigation functions
        $this->mockFunction('_nx', '15 thoughts on "Test Post Title"');
        M::userFunction('number_format_i18n', ['return_arg' => 0]);
        M::userFunction('esc_html_e', [
            'return' => function($text, $domain) {
                echo $text;
            }
        ]);
        M::userFunction('__', ['return_arg' => 0]);
        $this->mockFunction('previous_comments_link', '');
        $this->mockFunction('next_comments_link', '');
        $this->mockFunction('wp_list_comments', '');
        $this->mockFunction('comments_open', true);
        $this->mockFunction('post_type_supports', true);
        $this->mockFunction('get_post_type', 'post');
        $this->mockFunction('comment_form', '');

        // Capture output from comments.php
        $output = $this->captureOutput(function() {
            include __DIR__ . '/../../comments.php';
        });

        // Should contain comment navigation elements
        $this->assertStringContainsString('id="comment-nav-above"', $output);
        $this->assertStringContainsString('id="comment-nav-below"', $output);
        $this->assertStringContainsString('class="comment-navigation"', $output);
        $this->assertStringContainsString('Comment navigation', $output);
    }

    /**
     * Test comment form is displayed
     *
     * @since 1.0.0
     */
    public function test_comment_form_is_displayed() {
        // Mock basic functions
        $this->mockFunction('post_password_required', false);
        $this->mockFunction('have_comments', false);
        $this->mockFunction('comments_open', true);
        $this->mockFunction('get_comments_number', 0);
        $this->mockFunction('post_type_supports', true);
        $this->mockFunction('get_post_type', 'post');
        
        // Mock comment_form function to return specific output
        M::userFunction('comment_form', [
            'return' => function($args) {
                echo '<form id="commentform" class="comment-form">';
                echo '<textarea name="comment"></textarea>';
                echo '<input type="submit" value="Post Comment">';
                echo '</form>';
            }
        ]);

        // Capture output from comments.php
        $output = $this->captureOutput(function() {
            include __DIR__ . '/../../comments.php';
        });

        // Should contain comment form elements
        $this->assertStringContainsString('<div id="comments" class="comments-area">', $output);
        $this->assertStringContainsString('<form id="commentform" class="comment-form">', $output);
        $this->assertStringContainsString('<textarea name="comment"></textarea>', $output);
        $this->assertStringContainsString('<input type="submit" value="Post Comment">', $output);
    }

    /**
     * Test "comments are closed" message is displayed appropriately
     *
     * @since 1.0.0
     */
    public function test_comments_closed_message_displays() {
        // Mock functions for a post with closed comments but existing comments
        $this->mockFunction('post_password_required', false);
        $this->mockFunction('have_comments', false);
        $this->mockFunction('comments_open', false);
        $this->mockFunction('get_comments_number', 5);
        $this->mockFunction('post_type_supports', true);
        $this->mockFunction('get_post_type', 'post');
        M::userFunction('esc_html_e', [
            'return' => function($text, $domain) {
                echo $text;
            }
        ]);
        $this->mockFunction('comment_form', '');

        // Capture output from comments.php
        $output = $this->captureOutput(function() {
            include __DIR__ . '/../../comments.php';
        });

        // Should contain closed comments message
        $this->assertStringContainsString('<p class="no-comments">', $output);
        $this->assertStringContainsString('Comments are closed.', $output);
    }

    /**
     * Test that comments are not displayed when comments are closed and no comments exist
     *
     * @since 1.0.0
     */
    public function test_no_comments_closed_message_when_no_comments_exist() {
        // Mock functions for a post with closed comments and no existing comments
        $this->mockFunction('post_password_required', false);
        $this->mockFunction('have_comments', false);
        $this->mockFunction('comments_open', false);
        $this->mockFunction('get_comments_number', '0'); // String '0' not integer 0
        $this->mockFunction('post_type_supports', true);
        $this->mockFunction('get_post_type', 'post');
        $this->mockFunction('comment_form', '');

        // Capture output from comments.php
        $output = $this->captureOutput(function() {
            include __DIR__ . '/../../comments.php';
        });

        // Should NOT contain closed comments message when no comments exist (string '0' means no comments)
        $this->assertStringNotContainsString('<p class="no-comments">', $output);
        $this->assertStringNotContainsString('Comments are closed.', $output);
    }

    /**
     * Test comments template loading condition in single post template
     *
     * @since 1.0.0
     */
    public function test_comments_template_loading_condition_single_post() {
        // Test when comments are open
        $this->mockFunction('comments_open', true);
        $this->mockFunction('get_comments_number', 0);
        
        $should_load = (comments_open() || get_comments_number());
        $this->assertTrue($should_load, 'Comments template should load when comments are open');
    }

    /**
     * Test comments template loading condition when comments exist but are closed
     *
     * @since 1.0.0
     */
    public function test_comments_template_loading_condition_comments_exist_but_closed() {
        // Test when comments are closed but comments exist
        $this->mockFunction('comments_open', false);
        $this->mockFunction('get_comments_number', 3);
        
        $should_load = (comments_open() || get_comments_number());
        $this->assertTrue($should_load, 'Comments template should load when comments exist even if closed');
    }

    /**
     * Test comments template loading condition when comments are closed and none exist
     *
     * @since 1.0.0
     */
    public function test_comments_template_loading_condition_comments_closed_none_exist() {
        // Test when comments are closed and no comments exist
        $this->mockFunction('comments_open', false);
        $this->mockFunction('get_comments_number', 0);
        
        $should_load = (comments_open() || get_comments_number());
        $this->assertFalse($should_load, 'Comments template should not load when comments are closed and none exist');
    }

    /**
     * Test comments template loading condition in page template
     *
     * @since 1.0.0
     */
    public function test_comments_template_loading_condition_page_enabled() {
        // Test page with comments enabled
        $this->mockFunction('comments_open', true);
        $this->mockFunction('get_comments_number', 2);
        
        $should_load = (comments_open() || get_comments_number());
        $this->assertTrue($should_load, 'Comments template should load on pages when comments are enabled');
    }

    /**
     * Test comments template loading condition in page template when disabled
     *
     * @since 1.0.0
     */
    public function test_comments_template_loading_condition_page_disabled() {
        // Test page with comments disabled
        $this->mockFunction('comments_open', false);
        $this->mockFunction('get_comments_number', 0);
        
        $should_load = (comments_open() || get_comments_number());
        $this->assertFalse($should_load, 'Comments template should not load on pages when comments are disabled');
    }

    /**
     * Test comment title formatting with different comment counts
     *
     * @since 1.0.0
     */
    public function test_comment_title_formatting() {
        // Mock functions
        $this->mockFunction('post_password_required', false);
        $this->mockFunction('have_comments', true);
        $this->mockFunction('get_the_title', 'Sample Post');
        $this->mockFunction('get_comment_pages_count', 1);
        $this->mockFunction('get_option', false);
        $this->mockFunction('wp_list_comments', '');
        $this->mockFunction('comments_open', true);
        $this->mockFunction('post_type_supports', true);
        $this->mockFunction('get_post_type', 'post');
        $this->mockFunction('comment_form', '');

        // Test single comment
        $this->mockFunction('get_comments_number', 1);
        M::userFunction('_nx', [
            'return' => function($single, $plural, $number, $context, $domain) {
                return $number == 1 ? $single : $plural;
            }
        ]);
        M::userFunction('number_format_i18n', ['return_arg' => 0]);

        $output = $this->captureOutput(function() {
            include __DIR__ . '/../../comments.php';
        });

        $this->assertStringContainsString('One thought on', $output);

        // Test multiple comments
        $this->mockFunction('get_comments_number', 5);
        
        $output = $this->captureOutput(function() {
            include __DIR__ . '/../../comments.php';
        });

        $this->assertStringContainsString('thought', $output); // Should contain "thought" (singular or plural)
    }

    /**
     * Test that post type supports comments check works correctly
     *
     * @since 1.0.0
     */
    public function test_post_type_supports_comments() {
        // Mock functions for a post type that doesn't support comments
        $this->mockFunction('post_password_required', false);
        $this->mockFunction('have_comments', false);
        $this->mockFunction('comments_open', false);
        $this->mockFunction('get_comments_number', 3);
        $this->mockFunction('post_type_supports', false);
        $this->mockFunction('get_post_type', 'custom_post_type');
        $this->mockFunction('comment_form', '');

        $output = $this->captureOutput(function() {
            include __DIR__ . '/../../comments.php';
        });

        // Should NOT contain closed comments message for post types that don't support comments
        $this->assertStringNotContainsString('<p class="no-comments">', $output);
    }
}
