<?php
/**
 * Header/Footer Builder
 * Rows CSS Output
 * 
 * @package Sydney_Pro
 */

// @codingStandardsIgnoreStart WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$sticky_header_type = get_theme_mod( 'sticky_header_type', 'always' );
$sticky_row         = get_theme_mod( 'sydney_section_hb_wrapper__header_builder_sticky_row', 'main-header-row' );

$rows = array( 'above_header_row', 'main_header_row', 'below_header_row' );
foreach( $rows as $row ) {

    // Height
    $css .= Sydney_Custom_CSS::get_responsive_css( 
        "sydney_header_row__{$row}_height", 
        array( 'desktop' => 100, 'tablet' => 100, 'mobile' => 100 ), 
        ".shfb-$row",
        'min-height',
        'px' 
    );

    // Background Color
    $css .= Sydney_Custom_CSS::get_background_color_css( "sydney_header_row__{$row}_background_color", '', ".shfb-$row" );

    // Transparent Header Background Color
    $css .= Sydney_Custom_CSS::get_background_color_css( "sydney_header_row__{$row}_transparent_background_color", '', ".transparent-header .shfb-$row:not(.sticky-header-active .has-sticky-header .shfb-$row)" );

    // Background Image
    $background_image = get_theme_mod( "sydney_header_row__{$row}_background_image", '' );
    if( $background_image ) {
        $image_url           = wp_get_attachment_image_url( $background_image, 'full' );
        $background_size     = get_theme_mod( "sydney_header_row__{$row}_background_size", 'cover' );
        $background_position = get_theme_mod( "sydney_header_row__{$row}_background_position", 'center' );
        $background_repeat   = get_theme_mod( "sydney_header_row__{$row}_background_repeat", 'no-repeat' );

        $css .= ".shfb-$row { background-image: url(" . esc_url( $image_url ) . "); }";
        $css .= Sydney_Custom_CSS::get_css( 
            "sydney_header_row__{$row}_background_size", 
            'cover', 
            ".shfb-$row", 
            'background-size', 
            '' 
        );
        $css .= Sydney_Custom_CSS::get_css( 
            "sydney_header_row__{$row}_background_position", 
            'center', 
            ".shfb-$row", 
            'background-position', 
            '' 
        );
        $css .= Sydney_Custom_CSS::get_css( 
            "sydney_header_row__{$row}_background_repeat", 
            'no-repeat', 
            ".shfb-$row", 
            'background-repeat', 
            '' 
        );
    }

    // Border Bottom
    $css .= Sydney_Custom_CSS::get_css( 
        "sydney_header_row__{$row}_border_bottom_desktop",
        1, 
        ".shfb-$row",
        array(
            array(
                'prop' => 'border-bottom-width',
                'unit' => 'px'
            )
        )
    );
    $css .= ".shfb-$row { border-bottom-style: solid; }";
    $css .= Sydney_Custom_CSS::get_border_bottom_color_rgba_css( "sydney_header_row__{$row}_border_bottom_color", '#EAEAEA', ".shfb-$row", 0.1 );

    // Padding
    $css .= Sydney_Custom_CSS::get_responsive_dimensions_css( 
        "sydney_header_row__{$row}_padding",
        array(
            'desktop' => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
            'tablet'  => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
            'mobile'  => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
        ), 
        ".shfb-$row", 
        'padding'
    );

    // Margin
    $css .= Sydney_Custom_CSS::get_responsive_dimensions_css( 
        "sydney_header_row__{$row}_margin",
        array(
            'desktop' => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
            'tablet'  => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
            'mobile'  => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
        ), 
        ".shfb-$row", 
        'margin'
    );

    /**
     * Stick Header State
     * 
     */

    if( sydney_callback_sticky_header() ) {
        
        // Sticky Header - Background Color
        $css .= Sydney_Custom_CSS::get_background_color_css( "sydney_header_row__{$row}_sticky_background_color", '', ".sticky-header-active .has-sticky-header .shfb-$row" ); 

        // Sticky Header - Border Bottom Color
        $css .= Sydney_Custom_CSS::get_border_bottom_color_rgba_css( "sydney_header_row__{$row}_sticky_border_bottom_color", '#EAEAEA', ".sticky-header-active .has-sticky-header .shfb-$row" );

    }

}

// @codingStandardsIgnoreEnd WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound