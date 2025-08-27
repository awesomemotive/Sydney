<?php

/**
 * Gutenberg support
 */

function sydney_editor_styles() {
	wp_enqueue_style( 'sydney-block-editor-styles', get_theme_file_uri( '/sydney-gutenberg-editor-styles.css' ), '', '20220208', 'all' );

	wp_enqueue_style( 'sydney-fonts', esc_url( sydney_google_fonts_url() ), array(), null );


	//Dynamic styles
	$custom = '';

	//Global colors
	$global_color_defaults = sydney_get_global_color_defaults();            
	$global_colors = array();
	
	$custom .= ":root {" . "\n";
	for ($i = 1; $i <= 9; $i++) {
		$color = get_theme_mod("global_color_" . $i, $global_color_defaults["global_color_" . $i]);
		$custom .= "  --sydney-global-color-" . $i . ":" . esc_attr( $color ) . ";" . "\n";
	}
	$custom .= "}" . "\n";

	//Fonts
	$typography_defaults = json_encode(
		array(
			'font'          => 'System default',
			'regularweight' => '400',
			'category'      => 'sans-serif',
		)
	);

	$body_font      = get_theme_mod( 'sydney_body_font', $typography_defaults );
	$headings_font  = get_theme_mod( 'sydney_headings_font', $typography_defaults );

	$body_font      = json_decode( $body_font, true );
	$headings_font  = json_decode( $headings_font, true );

	$custom .= ".editor-styles-wrapper, .editor-styles-wrapper .editor-block-list__block { font-family:" . esc_attr( $body_font['font'] ) . ',' . esc_attr( $body_font['category'] ) . '; font-weight: ' . esc_attr( $body_font['regularweight'] ) . ';}' . "\n";
	$custom .= ".editor-post-title__block .editor-post-title__input, .editor-styles-wrapper .editor-post-title__input, .editor-styles-wrapper h1, .editor-styles-wrapper h2, .editor-styles-wrapper h3, .editor-styles-wrapper h4, .editor-styles-wrapper h5, .editor-styles-wrapper h6 { font-family:" . esc_attr( $headings_font['font'] ) . ',' . esc_attr( $headings_font['category'] ) . '; font-weight: ' . esc_attr( $headings_font['regularweight'] ) . ';}' . "\n";
	
	$custom .= Sydney_Custom_CSS::get_font_sizes_css( 'h1_font_size', $defaults = array( 'desktop' => 48, 'tablet' => 42, 'mobile' => 32 ), '.editor-styles-wrapper h1' );
	$custom .= Sydney_Custom_CSS::get_font_sizes_css( 'h2_font_size', $defaults = array( 'desktop' => 38, 'tablet' => 32, 'mobile' => 24 ), '.editor-styles-wrapper h2' );
	$custom .= Sydney_Custom_CSS::get_font_sizes_css( 'h3_font_size', $defaults = array( 'desktop' => 32, 'tablet' => 24, 'mobile' => 20 ), '.editor-styles-wrapper h3' );
	$custom .= Sydney_Custom_CSS::get_font_sizes_css( 'h4_font_size', $defaults = array( 'desktop' => 24, 'tablet' => 18, 'mobile' => 16 ), '.editor-styles-wrapper h4' );
	$custom .= Sydney_Custom_CSS::get_font_sizes_css( 'h5_font_size', $defaults = array( 'desktop' => 20, 'tablet' => 16, 'mobile' => 16 ), '.editor-styles-wrapper h5' );
	$custom .= Sydney_Custom_CSS::get_font_sizes_css( 'h6_font_size', $defaults = array( 'desktop' => 16, 'tablet' => 16, 'mobile' => 16 ), '.editor-styles-wrapper h6' );


	$custom .= Sydney_Custom_CSS::get_color_css( 'color_heading_1', 'var(--sydney-global-color-4)', '.editor-styles-wrapper h1' );
	$custom .= Sydney_Custom_CSS::get_color_css( 'color_heading_2', 'var(--sydney-global-color-4)', '.editor-styles-wrapper h2' );
	$custom .= Sydney_Custom_CSS::get_color_css( 'color_heading_3', 'var(--sydney-global-color-4)', '.editor-styles-wrapper h3' );
	$custom .= Sydney_Custom_CSS::get_color_css( 'color_heading_4', 'var(--sydney-global-color-4)', '.editor-styles-wrapper h4' );
	$custom .= Sydney_Custom_CSS::get_color_css( 'color_heading_5', 'var(--sydney-global-color-4)', '.editor-styles-wrapper h5' );
	$custom .= Sydney_Custom_CSS::get_color_css( 'color_heading_6', 'var(--sydney-global-color-4)', '.editor-styles-wrapper h6' );
	$custom .= Sydney_Custom_CSS::get_color_css( 'single_post_title_color', 'var(--sydney-global-color-4)', '.editor-post-title__block .editor-post-title__input, .editor-styles-wrapper .editor-post-title__input' );

	$custom .= Sydney_Custom_CSS::get_font_sizes_css( 'body_font_size', $defaults = array( 'desktop' => 16, 'tablet' => 16, 'mobile' => 16 ), '.editor-styles-wrapper, .editor-styles-wrapper p' );            
	
	$body_font_style        = get_theme_mod( 'body_font_style' );
	$body_line_height       = get_theme_mod( 'body_line_height', 1.68 );
	$body_letter_spacing    = get_theme_mod( 'body_letter_spacing' );
	$body_text_transform    = get_theme_mod( 'body_text_transform' );
	$body_text_decoration   = get_theme_mod( 'body_text_decoration' );

	$custom .= ".editor-styles-wrapper > *:not(.wp-block-heading) { text-transform:" . esc_attr( $body_text_transform ) . ";font-style:" . esc_attr( $body_font_style ) . ";line-height:" . esc_attr( $body_line_height ) . ";letter-spacing:" . esc_attr( $body_letter_spacing ) . "px;}" . "\n";  

	//Single post title
	$custom .= Sydney_Custom_CSS::get_font_sizes_css( 'single_post_title_size', $defaults = array( 'desktop' => 48, 'tablet' => 32, 'mobile' => 32 ), '.editor-post-title__block .editor-post-title__input, .editor-styles-wrapper .editor-post-title__input' );

	//Headings
	$headings_font_style        = get_theme_mod( 'headings_font_style', 'normal' );
	$headings_line_height       = get_theme_mod( 'headings_line_height', 1.2 );
	$headings_letter_spacing    = get_theme_mod( 'headings_letter_spacing' );
	$headings_text_transform    = get_theme_mod( 'headings_text_transform' );
	$headings_text_decoration   = get_theme_mod( 'headings_text_decoration' );

	$custom .= ".editor-styles-wrapper .wp-block-heading { font-style:" . esc_attr( $headings_font_style ) . ";line-height:" . esc_attr( $headings_line_height ) . ";letter-spacing:" . esc_attr( $headings_letter_spacing ) . "px;text-transform:" . esc_attr( $headings_text_transform ) . ";text-decoration:" . esc_attr( $headings_text_decoration ) . ";}" . "\n";
	
	//__COLORS

	//Body
	$body_text = get_theme_mod( 'body_text_color', '#47425d' );
	$custom .= ".editor-styles-wrapper, .editor-styles-wrapper .editor-block-list__block { color:" . esc_attr($body_text) . "}"."\n";
	$body_background = get_theme_mod( 'background_color' );
	if (strpos($body_background, '#') === false) {
		$body_background = '#'.$body_background;
	}
	$custom .= ".editor-styles-wrapper { background-color:" . esc_attr($body_background) . "}"."\n";
	
	//Buttons
	$custom .= Sydney_Custom_CSS::get_top_bottom_padding_css( 'button_top_bottom_padding', $defaults = array( 'desktop' => 12, 'tablet' => 12, 'mobile' => 12 ), '.editor-styles-wrapper .wp-block-button__link,button,a.button,.wp-block-button__link,input[type="button"],input[type="reset"],input[type="submit"]' );
	$custom .= Sydney_Custom_CSS::get_left_right_padding_css( 'button_left_right_padding', $defaults = array( 'desktop' => 35, 'tablet' => 35, 'mobile' => 35 ), '.editor-styles-wrapper .wp-block-button__link,button,a.button,.wp-block-button__link,input[type="button"],input[type="reset"],input[type="submit"]' );

	$buttons_radius = get_theme_mod( 'buttons_radius', 3 );
	$custom .= ".editor-styles-wrapper .wp-block-button__link { border-radius:" . intval( $buttons_radius ) . "px;}" . "\n";

	$custom .= Sydney_Custom_CSS::get_font_sizes_css( 'button_font_size', $defaults = array( 'desktop' => 13, 'tablet' => 13, 'mobile' => 13 ), '.editor-styles-wrapper .wp-block-button__link,button,a.button,.wp-block-button__link,input[type="button"],input[type="reset"],input[type="submit"]' );
	$button_text_transform = get_theme_mod( 'button_text_transform', 'uppercase' );
	$custom .= ".editor-styles-wrapper .wp-block-button__link { text-transform:" . esc_attr( $button_text_transform ) . ";}" . "\n";

	$custom .= Sydney_Custom_CSS::get_background_color_css( 'button_background_color', '', '.editor-styles-wrapper .wp-block-button:not(.is-style-outline) .wp-block-button__link' );           
	
	$custom .= Sydney_Custom_CSS::get_background_color_css( 'button_background_color_hover', '', '.editor-styles-wrapper .wp-block-button:not(.is-style-outline) .wp-block-button__link:hover' );           

	$custom .= Sydney_Custom_CSS::get_color_css( 'button_color', '#ffffff', '.editor-styles-wrapper .wp-block-button__link' );          
	$custom .= Sydney_Custom_CSS::get_color_css( 'button_color_hover', '#ffffff', '.editor-styles-wrapper .wp-block-button__link:hover' );          

	$button_border_color = get_theme_mod( 'button_border_color', '' );
	$button_border_color_hover = get_theme_mod( 'button_border_color_hover', '' );
	$custom .= ".editor-styles-wrapper .is-style-outline .wp-block-button__link,.editor-styles-wrapper .wp-block-button__link.is-style-outline,.editor-styles-wrapper .wp-block-button__link { border-color:" . esc_attr( $button_border_color ) . ";}" . "\n";
	$custom .= ".editor-styles-wrapper .wp-block-button__link:hover { border-color:" . esc_attr( $button_border_color_hover ) . ";}" . "\n";

	
	//Output all the styles
	wp_add_inline_style( 'sydney-block-editor-styles', $custom );   
}
add_action( 'enqueue_block_editor_assets', 'sydney_editor_styles' );