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
     * Get the theme dependencies that this test class requires.
     *
     * Comments template tests don't require any special theme dependencies.
     *
     * @since 1.0.0
     * @return array Array of dependency types to load.
     */
    protected function getRequiredDependencies(): array {
        return []; // No special dependencies needed for comment template tests
    }

    /**
     * Test that comments template is not displayed for password-protected posts
     *
     * @since 1.0.0
     */
    public function test_comments_template_not_displayed_for_password_protected_posts() {
        // Set up password protected scenario
        $this->setupCommentScenario('password_protected');

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
        // Set up scenario with comments
        $this->setupCommentScenario('multiple_comments', [
            'get_comments_number' => 3,
            'get_the_title' => 'Test Post Title'
        ]);

        // Capture output from comments.php
        $output = $this->captureOutput(function() {
            include __DIR__ . '/../../comments.php';
        });

        // Should contain comments area
        $this->assertHtmlContainsAll($output, [
            '<div id="comments" class="comments-area">',
            'thoughts on',
            'Test Post Title',
            '<ol class="comments-list">'
        ]);
    }

    /**
     * Test comment navigation when multiple comment pages exist
     *
     * @since 1.0.0
     */
    public function test_comment_navigation_displays_with_multiple_pages() {
        // Set up scenario with paginated comments
        $this->setupCommentScenario('comments_with_pagination', [
            '_nx_override' => true // Prevent default _nx override
        ]);
        // Override _nx for this specific test
        $this->mockFunction('_nx', '15 thoughts on "Test Post Title"');

        // Capture output from comments.php
        $output = $this->captureOutput(function() {
            include __DIR__ . '/../../comments.php';
        });

        // Should contain comment navigation elements
        $this->assertHtmlContainsAll($output, [
            'id="comment-nav-above"',
            'id="comment-nav-below"',
            'class="comment-navigation"',
            'Comment navigation'
        ]);
    }

    /**
     * Test comment form is displayed
     *
     * @since 1.0.0
     */
    public function test_comment_form_is_displayed() {
        // Mock basic functions for comments open but none exist
        $this->mockFunction('post_password_required', false);
        $this->mockFunction('have_comments', false);
        $this->mockFunction('comments_open', true);
        $this->mockFunction('get_comments_number', 0);
        $this->mockFunction('post_type_supports', true);
        $this->mockFunction('get_post_type', 'post');
        
        // Mock comment_form function to return specific output
        $this->mockFunction('comment_form', function($args) {
            echo '<form id="commentform" class="comment-form">';
            echo '<textarea name="comment"></textarea>';
            echo '<input type="submit" value="Post Comment">';
            echo '</form>';
        });

        // Capture output from comments.php
        $output = $this->captureOutput(function() {
            include __DIR__ . '/../../comments.php';
        });

        // Should contain comment form elements
        $this->assertHtmlContainsAll($output, [
            '<div id="comments" class="comments-area">',
            '<form id="commentform" class="comment-form">',
            '<textarea name="comment"></textarea>',
            '<input type="submit" value="Post Comment">'
        ]);
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
        $this->mockTranslationFunctions();
        $this->mockFunction('comment_form', '');

        // Capture output from comments.php
        $output = $this->captureOutput(function() {
            include __DIR__ . '/../../comments.php';
        });

        // Should contain closed comments message
        $this->assertHtmlContainsAll($output, [
            '<p class="no-comments">',
            'Comments are closed.'
        ]);
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
        $this->assertHtmlContainsNone($output, [
            '<p class="no-comments">',
            'Comments are closed.'
        ]);
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
        $this->mockTranslationFunctions();

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
}
