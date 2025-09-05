<?php
/**
 * Header/Footer Builder
 * Rows CSS Output
 * 
 * @package Sydney_Pro
 */

// @codingStandardsIgnoreStart WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

// Site TItle Color
$css .= Sydney_Custom_CSS::get_color_css( 'site_title_color', '', '.shfb .site-branding .site-title a,.shfb .site-branding .site-title a:visited' );

// Site Description Color
$css .= Sydney_Custom_CSS::get_color_css( 'site_desc_color', '', '.shfb .site-branding .site-description' );

// Site Logo Size
$css .= Sydney_Custom_CSS::get_responsive_css( 
    'site_logo_size', 
    array( 'desktop' => 120, 'tablet' => 100, 'mobile' => 100 ), 
    '.custom-logo-link img',
    'width',
    'px' 
);

// Text Alignment
$css .= Sydney_Custom_CSS::get_responsive_css( 
    'sydney_section_hb_component__logo_text_alignment', 
    array( 'desktop' => 'left', 'tablet' => 'left', 'mobile' => 'left' ), 
    '.shfb.shfb-header .shfb-component-logo',
    'text-align',
    '' 
);

// Visibility
$css .= Sydney_Custom_CSS::get_responsive_css( 
    'sydney_section_hb_component__logo_visibility', 
    'visible', 
    '.shfb.shfb-header .shfb-builder-item.shfb-component-logo, .shfb-mobile_offcanvas .shfb-builder-item.shfb-component-logo', 
    'display',
    ''
);

if( sydney_callback_sticky_header() ) {
    // Sticky Header - Site TItle Color
    $css .= Sydney_Custom_CSS::get_color_css( 'site_title_sticky_color', '', '.sticky-header-active .shfb .site-branding .site-title a' );
    
    // Sticky Header - Site Description Color
    $css .= Sydney_Custom_CSS::get_color_css( 'site_description_sticky_color', '', '.sticky-header-active .shfb .site-description' );
}

// Padding
$css .= Sydney_Custom_CSS::get_responsive_dimensions_css( 
    'sydney_section_hb_component__logo_padding',
    array(
        'desktop' => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
        'tablet'  => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
        'mobile'  => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
    ), 
    '.shfb-header .shfb-component-logo', 
    'padding'
);

// Margin
$css .= Sydney_Custom_CSS::get_responsive_dimensions_css( 
    'sydney_section_hb_component__logo_margin',
    array(
        'desktop' => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
        'tablet'  => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
        'mobile'  => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
    ), 
    '.shfb-header .shfb-component-logo', 
    'margin',
    true
);

// @codingStandardsIgnoreEnd WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound