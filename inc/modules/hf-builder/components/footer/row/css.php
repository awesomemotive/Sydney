<?php
/**
 * Footer Builder
 * Rows CSS Output
 * 
 * @package Sydney_Pro
 */

// @codingStandardsIgnoreStart WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$rows = array( 'above_footer_row', 'main_footer_row', 'below_footer_row' );
foreach( $rows as $row ) {

    // Height
    $default = Sydney_Header_Footer_Builder::get_row_height_default_customizer_value( $row );
    $css .= Sydney_Custom_CSS::get_responsive_css( 
        "sydney_footer_row__{$row}_height", 
        array( 'desktop' => $default, 'tablet' => $default, 'mobile' => $default ), 
        ".shfb-$row",
        'min-height',
        'px' 
    );

    // Background Color
    $css .= Sydney_Custom_CSS::get_background_color_css( "sydney_footer_row__{$row}_background_color", '', ".shfb-$row" ); 

    // Background Image
    $background_image = get_theme_mod( "sydney_footer_row__{$row}_background_image", '' );
    if( $background_image ) {
        $image_url           = wp_get_attachment_image_url( $background_image, 'full' );
        $background_size     = get_theme_mod( "sydney_footer_row__{$row}_background_size", 'cover' );
        $background_position = get_theme_mod( "sydney_footer_row__{$row}_background_position", 'center' );
        $background_repeat   = get_theme_mod( "sydney_footer_row__{$row}_background_repeat", 'no-repeat' );

        $css .= ".shfb-$row { background-image: url(" . esc_url( $image_url ) . "); }";
        $css .= Sydney_Custom_CSS::get_css( 
            "sydney_footer_row__{$row}_background_size", 
            'cover', 
            ".shfb-$row", 
            'background-size', 
            '' 
        );
        $css .= Sydney_Custom_CSS::get_css( 
            "sydney_footer_row__{$row}_background_position", 
            'center', 
            ".shfb-$row", 
            'background-position', 
            '' 
        );
        $css .= Sydney_Custom_CSS::get_css( 
            "sydney_footer_row__{$row}_background_repeat", 
            'no-repeat', 
            ".shfb-$row", 
            'background-repeat', 
            '' 
        );
    }

    // Border Top
    $css .= Sydney_Custom_CSS::get_css( 
        "sydney_footer_row__{$row}_border_top_desktop",
        Sydney_Header_Footer_Builder::get_row_border_default_customizer_value( $row ), 
        ".shfb-$row",
        array(
            array(
                'prop' => 'border-top-width',
                'unit' => 'px'
            )
        )
    );
    $css .= ".shfb-$row { border-top-style: solid; }";
   // Get opacity - default 0.1 for existing users, 1 for new users
   $opacity = get_theme_mod( 'sydney_hf_border_opacity', '0.1' );
   $css .= Sydney_Custom_CSS::get_border_top_color_rgba_css( "sydney_footer_row__{$row}_border_top_color", '#EAEAEA', ".shfb-$row", $opacity );

    // Elements Spacing.
    $elements_spacing = get_theme_mod( "sydney_footer_row__{$row}_elements_spacing", '25' );
    $css .= ":root { --sydney_footer_row__{$row}_elements_spacing: {$elements_spacing}px; }";

    // Padding
    $css .= Sydney_Custom_CSS::get_responsive_dimensions_css( 
        "sydney_footer_row__{$row}_padding",
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
        "sydney_footer_row__{$row}_margin",
        array(
            'desktop' => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
            'tablet'  => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
            'mobile'  => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
        ), 
        ".shfb-$row", 
        'margin'
    );

}

// @codingStandardsIgnoreEnd WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound