<?php
/**
 * Header/Footer Builder
 * Header Builder CSS Output
 * 
 * @package Sydney_Pro
 */

// @codingStandardsIgnoreStart WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

// Mobile breakpoint.
$mobile_breakpoint = absint( get_theme_mod( 'mobile_breakpoint', 1024 ) );
$min_width         = $mobile_breakpoint + 1;

// Some of the CSS below is already present in the .CSS files. However we have to duplicate it here 
// because the breakpoint in those static CSS files are not dynamic.
$css .= "
    @media (max-width: {$mobile_breakpoint}px) {
        .shfb-header.shfb-mobile,
        .sydney-offcanvas-menu {
            display: block;
        }
        .shfb-header.shfb-desktop {
            display: none;
        }
        .sydney-offcanvas-menu .sydney-dropdown .sydney-dropdown-ul .sydney-dropdown-ul {
            -webkit-transform: none;
            transform: none;
            opacity: 1;
        }

        .sydney-mega-menu-column {
            margin-left: -10px;
        }
        .sydney-mega-menu-column > .sydney-dropdown-link,
        .sydney-mega-menu-column > span {
            display: none !important;
        }
        .sydney-mega-menu-column > .sub-menu.sydney-dropdown-ul{
            display: block !important;
        }
        .is-mega-menu:not(.is-mega-menu-vertical) .sydney-mega-menu-column .is-mega-menu-heading {
            display: none !important;
        }
        .sydney-offcanvas-menu.toggled .menu {
           display: block !important;
        }
    }

    @media (min-width: {$min_width}px) {
        .shfb-header.shfb-mobile {
            display: none;
        }
        .shfb-header.shfb-desktop {
            display: block;
        }
        .shfb-header .sydney-dropdown > .sydney-dropdown-ul,
        .shfb-header .sydney-dropdown > div > .sydney-dropdown-ul {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
        }
    }
";

// Padding
$css .= Sydney_Custom_CSS::get_responsive_dimensions_css( 
    'sydney_section_hb_wrapper__header_builder_padding', 
    array(
        'desktop' => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
        'tablet'  => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
        'mobile'  => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
    ), 
    '.shfb-header.shfb-desktop, .shfb-header.shfb-mobile', 
    'padding'
);

// Background Image
$hb_background_image = get_theme_mod( 'sydney_section_hb_wrapper__header_builder_background_image', '' );
if( $hb_background_image ) {
    $image_url           = wp_get_attachment_image_url( $hb_background_image, 'full' );

    $css .= '.shfb-header.shfb-desktop, .shfb-header.shfb-mobile { background-image: url(' . esc_url( $image_url ) . '); }';
    $css .= Sydney_Custom_CSS::get_css( 
        'sydney_section_hb_wrapper__header_builder_background_size', 
        'cover', 
        '.shfb-header.shfb-desktop, .shfb-header.shfb-mobile', 
        'background-size', 
        '' 
    );
    $css .= Sydney_Custom_CSS::get_css( 
        'sydney_section_hb_wrapper__header_builder_background_position', 
        'center', 
        '.shfb-header.shfb-desktop, .shfb-header.shfb-mobile', 
        'background-position', 
        '' 
    );
    $css .= Sydney_Custom_CSS::get_css( 
        'sydney_section_hb_wrapper__header_builder_background_repeat', 
        'no-repeat', 
        '.shfb-header.shfb-desktop, .shfb-header.shfb-mobile', 
        'background-repeat', 
        '' 
    );
}

// Background Color
$css .= Sydney_Custom_CSS::get_background_color_css( 'sydney_section_hb_wrapper__header_builder_background_color', '', '.shfb-header.shfb-desktop, .shfb-header.shfb-mobile' );

// @codingStandardsIgnoreEnd WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound