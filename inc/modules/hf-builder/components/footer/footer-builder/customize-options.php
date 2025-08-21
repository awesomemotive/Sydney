<?php
/**
 * Footer Builder
 * General Footer Settings
 * 
 * @package Sydney_Pro
 */

// @codingStandardsIgnoreStart WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

// List of options we'll need to move.
$opts_to_move = array(
    'general' => array(
        'footer_container'
    ),
    'style'   => array()
);

/**
 * Tabs (Layout / Design)
 * 
 */
$wp_customize->add_setting(
    'sydney_section_fb_wrapper__footer_builder_tabs',
    array(
        'default'           => '',
        'sanitize_callback' => 'esc_attr'
    )
);
$wp_customize->add_control(
    new Sydney_Tab_Control (
        $wp_customize,
        'sydney_section_fb_wrapper__footer_builder_tabs',
        array(
            'label' 				=> '',
            'section'       		=> 'sydney_section_fb_wrapper',
            'controls_general'		=> wp_json_encode(
				array_merge(
					array(
						'#customize-control-sydney_section_fb_wrapper__footer_builder_goto_sections',
						'#customize-control-header_transparent_fb_rows',
						'#customize-control-sydney_section_fb_wrapper__footer_builder_sticky_row',
						'#customize-control-sydney_section_fb_wrapper__footer_builder_available_footer_components',
						'#customize-control-sydney_section_fb_wrapper__footer_builder_upsell'
					),
					array_map( function( $name ){ return "#customize-control-$name"; }, $opts_to_move[ 'general' ] )
				)
            ),
            'controls_design'		=> wp_json_encode(
				array(
					'#customize-control-sydney_section_fb_wrapper__footer_builder_background_color',
					'#customize-control-sydney_section_fb_wrapper__footer_builder__divider2',
					'#customize-control-sydney_section_fb_wrapper__footer_builder_background_image',
					'#customize-control-sydney_section_fb_wrapper__footer_builder_background_size',
					'#customize-control-sydney_section_fb_wrapper__footer_builder_background_position',
					'#customize-control-sydney_section_fb_wrapper__footer_builder_background_repeat',
					'#customize-control-sydney_section_fb_wrapper__footer_builder_padding'
				)
            ),
            'priority' 				=> 10
        )
    )
);

/**
 * Layout (Tab Content)
 * 
 */

/**
 * Design (Tab Content)
 * 
 */

// Background Color
$wp_customize->add_setting(
	'sydney_section_fb_wrapper__footer_builder_background_color',
	array(
		'default'           => '#f5f5f5',
		'sanitize_callback' => 'sydney_sanitize_hex_rgba',
		'transport'         => 'postMessage'
	)
);
$wp_customize->add_control(
	new Sydney_Alpha_Color(
		$wp_customize,
		'sydney_section_fb_wrapper__footer_builder_background_color',
		array(
			'label'         	=> esc_html__( 'Background color', 'sydney' ),
			'section'       	=> 'sydney_section_fb_wrapper',
			'priority'			=> 35
		)
	)
);

// Background Image
$wp_customize->add_setting( 
	'sydney_section_fb_wrapper__footer_builder_background_image',
	array(
		'default'           => '',
		'sanitize_callback' => 'absint',
	) 
);
$wp_customize->add_control( 
	new WP_Customize_Media_Control( 
		$wp_customize, 
		'sydney_section_fb_wrapper__footer_builder_background_image',
		array(
			'label'           => __( 'Background Image', 'sydney' ),
			'section'         => 'sydney_section_fb_wrapper',
			'mime_type'       => 'image',
			'priority'	      => 35
		)
	)
);

// Background Size
$wp_customize->add_setting( 
	'sydney_section_fb_wrapper__footer_builder_background_size',
	array(
		'default'           => 'cover',
		'sanitize_callback' => 'sydney_sanitize_select',
		'transport'         => 'postMessage'
	) 
);
$wp_customize->add_control( 
	'sydney_section_fb_wrapper__footer_builder_background_size',
	array(
		'type' 		      => 'select',
		'label' 	      => esc_html__( 'Background Size', 'sydney' ),
		'choices'         => array(
			'cover'   => esc_html__( 'Cover', 'sydney' ),
			'contain' => esc_html__( 'Contain', 'sydney' )
		),
		'section' 	      => 'sydney_section_fb_wrapper',
		'active_callback' => function(){ return get_theme_mod( 'sydney_section_fb_wrapper__footer_builder_background_image' ) ? true : false; },
		'priority'        => 35
	)
);

// Background Position
$wp_customize->add_setting( 
	'sydney_section_fb_wrapper__footer_builder_background_position',
	array(
		'default'           => 'center',
		'sanitize_callback' => 'sydney_sanitize_select',
		'transport'         => 'postMessage'
	) 
);
$wp_customize->add_control( 
	'sydney_section_fb_wrapper__footer_builder_background_position',
	array(
		'type' 		      => 'select',
		'label' 	      => esc_html__( 'Background Position', 'sydney' ),
		'choices'         => array(
			'top'    => esc_html__( 'Top', 'sydney' ),
			'center' => esc_html__( 'Center', 'sydney' ),
			'bottom' => esc_html__( 'Bottom', 'sydney' )
		),
		'section' 	      => 'sydney_section_fb_wrapper',
		'active_callback' => function(){ return get_theme_mod( 'sydney_section_fb_wrapper__footer_builder_background_image' ) ? true : false; },
		'priority'        => 35
	)
);

// Background Repeat
$wp_customize->add_setting( 
	'sydney_section_fb_wrapper__footer_builder_background_repeat',
	array(
		'default'           => 'no-repeat',
		'sanitize_callback' => 'sydney_sanitize_select',
		'transport'         => 'postMessage'
	) 
);
$wp_customize->add_control( 
	'sydney_section_fb_wrapper__footer_builder_background_repeat',
	array(
		'type' 		      => 'select',
		'label' 	      => esc_html__( 'Background Repeat', 'sydney' ),
		'choices'         => array(
			'no-repeat' => esc_html__( 'No Repeat', 'sydney' ),
			'repeat'    => esc_html__( 'Repeat', 'sydney' )
		),
		'section' 	      => 'sydney_section_fb_wrapper',
		'active_callback' => function(){ return get_theme_mod( 'sydney_section_fb_wrapper__footer_builder_background_image' ) ? true : false; },
		'priority'        => 35
	)
);

// Padding
$wp_customize->add_setting( 
	'sydney_section_fb_wrapper__footer_builder_padding_desktop',
	array(
		'default'           => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
		'sanitize_callback' => 'sydney_sanitize_text',
		'transport'         => 'postMessage'
	) 
);
$wp_customize->add_setting( 
	'sydney_section_fb_wrapper__footer_builder_padding_tablet',
	array(
		'default'           => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
		'sanitize_callback' => 'sydney_sanitize_text',
		'transport'         => 'postMessage'
	) 
);
$wp_customize->add_setting( 
	'sydney_section_fb_wrapper__footer_builder_padding_mobile',
	array(
		'default'           => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
		'sanitize_callback' => 'sydney_sanitize_text',
		'transport'         => 'postMessage'
	) 
);
$wp_customize->add_control( 
	new Sydney_Dimensions_Control( 
		$wp_customize, 
		'sydney_section_fb_wrapper__footer_builder_padding',
		array(
			'label'           	=> __( 'Padding', 'sydney' ),
			'section'         	=> 'sydney_section_fb_wrapper',
			'sides'             => array(
				'top'    => true,
				'right'  => true,
				'bottom' => true,
				'left'   => true
			),
			'units'              => array( 'px', '%', 'rem', 'em', 'vw', 'vh' ),
			'link_values_toggle' => true,
			'is_responsive'   	 => true,
			'settings'        	 => array(
				'desktop' => 'sydney_section_fb_wrapper__footer_builder_padding_desktop',
				'tablet'  => 'sydney_section_fb_wrapper__footer_builder_padding_tablet',
				'mobile'  => 'sydney_section_fb_wrapper__footer_builder_padding_mobile'
			),
			'priority'	      	 => 35
		)
	)
);

/**
 * Layout / Design
 * Is not assigned to any tab, so it will display in both tabs
 * 
 */
// Available Footer Components Area
$wp_customize->add_setting( 'sydney_section_fb_wrapper__footer_builder_available_footer_components',
	array(
		'default' 			=> '',
		'sanitize_callback' => 'esc_attr'
	)
);
$wp_customize->add_control( new Sydney_Text_Control( $wp_customize, 'sydney_section_fb_wrapper__footer_builder_available_footer_components',
		array(
			'description' 	=> '<span class="customize-control-title" style="font-style: normal;">'. esc_html__( 'Available Components', 'sydney' ) .'</span><div class="shfb-available-components sydney-footer-builder-available-footer-components sydney-shfb-area"></div>',
			'section' 		=> 'sydney_section_fb_wrapper',
            'priority' 		=> 40
		)
	)
);

// Upsell
if( ! defined( 'SYDNEY_AWL_ACTIVE' ) && ! defined( 'SYDNEY_PRO_VERSION' ) ) {
	$wp_customize->add_setting( 
		'sydney_section_fb_wrapper__footer_builder_upsell',
		array(
			'default'           => '',
			'sanitize_callback' => 'sydney_sanitize_text'
		)
	);
	
	$wp_customize->add_control( 
		new Sydney_Upsell_Message( 
			$wp_customize, 
			'sydney_section_fb_wrapper__footer_builder_upsell',
			array(
				'section'     => 'sydney_section_fb_wrapper',
				'description' => __( 'Create one-of-a-kind footer designs with Sydney Pro!', 'sydney' ),
				'features'    => array(
					__( 'Elementor footer builder', 'sydney' ),
					__( 'Footer background image', 'sydney' ),
					__( 'Pre-footer area', 'sydney' ),
					__( 'SVG footer separators', 'sydney' ),
					__( 'Reveal animation effect', 'sydney' ),
					__( 'Extra components: HTML, Shortcode, Button, Footer Menu', 'sydney' ),
                    '<a target="_blank" href="' . esc_url( sydney_admin_upgrade_link( 'https://athemes.com/sydney-upgrade', array( 'utm_source' => 'theme_customizer_footer_credits', 'utm_medium' => 'sydney_customizer', 'utm_campaign' => 'Sydney' ), 'customizer-footer-credits-features-link' ) ) . '#features">' . __( '&hellip;and many more premium features', 'sydney' ) . '</a>',   
				), 
				'priority'    => 999
			)
		) 
	);

}

// Move existing options.
$priority = 25;
foreach( $opts_to_move as $control_tabs ) {
    foreach( $control_tabs as $option_name ) {

        if( $wp_customize->get_control( $option_name ) === NULL ) {
            continue;
        }
        
        $wp_customize->get_control( $option_name )->section  = 'sydney_section_fb_wrapper';
        $wp_customize->get_control( $option_name )->priority = $priority;
        
        $priority++;
    }
}

// @codingStandardsIgnoreEnd WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound