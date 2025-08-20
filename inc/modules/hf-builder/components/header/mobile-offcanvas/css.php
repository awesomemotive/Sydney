<?php
/**
 * Header/Footer Builder
 * Mobile Offcanvas CSS Output
 * 
 * @package Sydney_Pro
 */

// @codingStandardsIgnoreStart WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

// Elements Spacing.
$el_spacing = get_theme_mod( 'mobile_menu_elements_spacing', 20 );
$css .= '.shfb-mobile_offcanvas .shfb-builder-item + .shfb-builder-item { margin-top: '. esc_attr( $el_spacing ) .'px; }';

// Close Icon Offset
$offset = get_theme_mod( 'shfb_mobile_offcanvas_close_offset', 20 );
if ( is_rtl() ) {
    $css .= '.shfb-mobile_offcanvas .mobile-menu-close { top: '. esc_attr( $offset ) .'px; left: '. esc_attr( $offset ) .'px; right: auto; }';
} else {
    $css .= '.shfb-mobile_offcanvas .mobile-menu-close { top: '. esc_attr( $offset ) .'px; right: '. esc_attr( $offset ) .'px; }';
}

// Background Color
$css .= Sydney_Custom_CSS::get_background_color_css( 'offcanvas_menu_background', '#FFF', '.shfb-mobile_offcanvas' );

// Close Icon Background
$css .= Sydney_Custom_CSS::get_background_color_css( 'shfb_mobile_offcanvas_close_background_color', 'rgba(255,255,255,0)', '.shfb-mobile_offcanvas .mobile-menu-close' );

// Close Icon Text Color
$css .= Sydney_Custom_CSS::get_fill_css( 'shfb_mobile_offcanvas_close_text_color', '#ffffff', '.shfb-mobile_offcanvas .mobile-menu-close svg' );

// Close Icon Text Color Hover
$css .= Sydney_Custom_CSS::get_fill_css( 'shfb_mobile_offcanvas_close_text_color_hover', '#ffffff', '.shfb-mobile_offcanvas .mobile-menu-close:hover svg' );

// Padding
$css .= Sydney_Custom_CSS::get_responsive_dimensions_css( 
    'shfb_mobile_offcanvas_padding',
    array(
        'desktop' => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
        'tablet'  => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
        'mobile'  => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
    ), 
    '.shfb-mobile_offcanvas', 
    'padding'
);

// Margin
$css .= Sydney_Custom_CSS::get_responsive_dimensions_css( 
    'shfb_mobile_offcanvas_margin',
    array(
        'desktop' => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
        'tablet'  => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
        'mobile'  => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
    ), 
    '.shfb-mobile_offcanvas', 
    'margin',
    true
);

// @codingStandardsIgnoreEnd WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound