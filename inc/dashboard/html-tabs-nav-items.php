<?php

/**
 * Tabs Nav Items
 * 
 * @package Dashboard
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

echo '<nav class="sydney-dashboard-tabs-nav" data-tab-wrapper-id="main">';
    echo '<ul>';

        $sydney_tab_num = 0;

        $sydney_current_tab = ( isset( $_GET['tab'] ) ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        foreach ($this->settings['tabs'] as $sydney_tab_id => $sydney_tab_title) {

            if ($this->settings['has_pro'] && $sydney_tab_id === 'free-vs-pro') {
                continue;
            }

            $sydney_tab_link   = add_query_arg(array( 'page' => $this->settings['menu_slug'], 'tab' => $sydney_tab_id ), admin_url('themes.php'));
            $sydney_tab_active = (($sydney_current_tab && $sydney_current_tab === $sydney_tab_id) || (!$sydney_current_tab && $sydney_tab_num === 0)) ? 'active' : '';

            printf('<li class="sydney-dashboard-tabs-nav-item %s"><a href="#" class="sydney-dashboard-tabs-nav-link" data-tab-to="%s">%s</a></li>', esc_attr($sydney_tab_active), esc_attr($sydney_tab_id), esc_html($sydney_tab_title));

            $sydney_tab_num++;
        }

    echo '</ul>';
echo '</nav>';