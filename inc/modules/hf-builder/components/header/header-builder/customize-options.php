<?php
/**
 * Header/Footer Builder
 * Mobile Offcanvas Options
 * 
 * @package Sydney_Pro
 */

// @codingStandardsIgnoreStart WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

// List of options we'll need to move.
$opts_to_move = array(
    'general' => array(
        'enable_sticky_header',
        'sticky_header_type',
        'transparent_header',
        'header_container',
    ),
    'style'   => array()
);

/**
 * Tabs (Layout / Design)
 * 
 */
$wp_customize->add_setting(
    'sydney_section_hb_wrapper__header_builder_tabs',
    array(
        'default'           => '',
        'sanitize_callback' => 'esc_attr'
    )
);
$wp_customize->add_control(
    new Sydney_Tab_Control (
        $wp_customize,
        'sydney_section_hb_wrapper__header_builder_tabs',
        array(
            'label' 				=> '',
            'section'       		=> 'sydney_section_hb_wrapper',
            'controls_general'		=> wp_json_encode(
				array_merge(
					array(
						'#customize-control-sydney_section_hb_wrapper__header_builder_goto_sections',
						'#customize-control-sydney_section_hb_wrapper__header_builder_sticky_title',
						'#customize-control-sydney_section_hb_wrapper__header_builder_sticky_row',
						'#customize-control-sydney_section_hb_wrapper__header_builder_available_components',
						'#customize-control-sydney_section_hb_wrapper__header_builder_available_mobile_components',
						'#customize-control-sydney_section_hb_wrapper__header_builder_transparent_title',
						'#customize-control-sydney_section_hb_wrapper__header_builder_container_title',
						'#customize-control-sydney_section_hb_wrapper__header_builder_upsell',
						'#customize-control-sydney_section_hb_wrapper__header_builder_sticky_group',
						'#customize-control-mobile_breakpoint'
					),
					array_map( function( $name ){ return "#customize-control-$name"; }, $opts_to_move[ 'general' ] )
				)
            ),
            'controls_design'		=> wp_json_encode(
				array(
					'#customize-control-sydney_section_hb_wrapper__header_builder_background_color',
					'#customize-control-sydney_section_hb_wrapper__header_builder_divider2',
					'#customize-control-sydney_section_hb_wrapper__header_builder_background_image',
					'#customize-control-sydney_section_hb_wrapper__header_builder_background_size',
					'#customize-control-sydney_section_hb_wrapper__header_builder_background_position',
					'#customize-control-sydney_section_hb_wrapper__header_builder_background_repeat',
					'#customize-control-sydney_section_hb_wrapper__header_builder_padding'
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

// Header Section Shortcuts
$wp_customize->add_setting( 'sydney_section_hb_wrapper__header_builder_goto_sections',
	array(
		'default'             => '',
		'sanitize_callback' => 'esc_attr'
	)
);
$wp_customize->add_control( new Sydney_Text_Control( $wp_customize, 'sydney_section_hb_wrapper__header_builder_goto_sections',
		array(
			'description' 	=> '
				<span class="customize-control-title" style="font-style: normal;">'. esc_html__( 'Header', 'sydney' ) .'</span>
				<div class="customize-section-shortcuts">
					<a style="font-weight:400;" class="sydney-to-widget-area-link" href="javascript:wp.customize.section( \'sydney_section_hb_presets\' ).focus();" data-goto-section="sydney_section_hb_presets">' . esc_html__( 'Header Presets', 'sydney' ) . '<span class="dashicons dashicons-arrow-right-alt2"></span></a>
					<a style="font-weight:400;" class="sydney-to-widget-area-link" href="javascript:wp.customize.section( \'sydney_section_hb_mobile_offcanvas\' ).focus();" data-goto-section="sydney_section_hb_mobile_offcanvas">' . esc_html__( 'Mobile Header', 'sydney' ) . '<span class="dashicons dashicons-arrow-right-alt2"></span></a>
					<a style="font-weight:400;" class="sydney-to-widget-area-link" href="javascript:wp.customize.panel( \'sydney_panel_hero\' ).focus();" data-goto-section="sydney_panel_hero">' . esc_html__( 'Hero Section', 'sydney' ) . '<span class="dashicons dashicons-arrow-right-alt2"></span></a>
				</div>
			',
			'section' 		=> 'sydney_section_hb_wrapper',
            'priority' 		=> 20
		)
	)
);

// Sticky Header Row
$wp_customize->add_setting( 
	'sydney_section_hb_wrapper__header_builder_sticky_title', 
	array(
		'default'           => '',
		'sanitize_callback' => 'esc_attr'
	) 
);
$wp_customize->add_control( 
	new Sydney_Text_Control( 
		$wp_customize, 
		'sydney_section_hb_wrapper__header_builder_sticky_title', 
		array(
			'description' 	=> '<span class="customize-control-title" style="font-style: normal;">'. esc_html__( 'Sticky Options', 'sydney' ) .'</span>',
			'section' 		=> 'sydney_section_hb_wrapper',
            'priority' 		=> 24,
			'separator'		=> 'before'
		)
	)
);

$wp_customize->add_setting( 
	'sydney_section_hb_wrapper__header_builder_sticky_row', 
	array(
		'sanitize_callback' => 'sydney_sanitize_select',
		'default' 			=> 'main-header-row'
	) 
);
$wp_customize->add_control( 
	'sydney_section_hb_wrapper__header_builder_sticky_row', 
	array(
		'type' 		      => 'select',
		'label' 	      => esc_html__( 'Header Row To Sticky', 'sydney' ),
		'choices'         => array(
            'all' 	            => esc_html__( 'All Rows', 'sydney' ),
			'main-header-row' 	=> esc_html__( 'Main Row', 'sydney' ),
            'below-header-row' 	=> esc_html__( 'Bottom Row', 'sydney' )
		),
        'section' 	      => 'sydney_section_hb_wrapper',
        'active_callback' => 'sydney_callback_sticky_header',
        'priority'        => 26
	) 
);


// Header Transparent - Apply transparent header to
$wp_customize->add_setting( 
	'sydney_section_hb_wrapper__header_builder_transparent_title', 
	array(
		'default'           => '',
		'sanitize_callback' => 'esc_attr'
	) 
);
$wp_customize->add_control( 
	new Sydney_Text_Control( 
		$wp_customize, 
		'sydney_section_hb_wrapper__header_builder_transparent_title', 
		array(
			'description' 	=> '<span class="customize-control-title" style="font-style: normal;">'. esc_html__( 'Transparent Options', 'sydney' ) .'</span>',
			'section' 		=> 'sydney_section_hb_wrapper',
            'priority' 		=> 26,
			'separator'		=> 'before'
		)
	)
);

// Mobile breakpoint.
$wp_customize->add_setting( 'mobile_breakpoint', array(
	'default'           => 1024,
	'sanitize_callback' => 'absint',
) );            
$wp_customize->add_control( new Sydney_Responsive_Slider( $wp_customize, 'mobile_breakpoint',
	array(
		'label'           => esc_html__( 'Mobile Breakpoint', 'sydney' ),
		'section'         => 'sydney_section_hb_wrapper',
		'is_responsive'   => 0,
		'settings'        => array(
			'size_desktop'      => 'mobile_breakpoint',
		),
		'input_attrs'     => array(
			'min'   => 0,
			'max'   => 3600,
		),
		'priority'        => 30,
	)
) );

/**
 * Design (Tab Content)
 * 
 */

// Background Color
$wp_customize->add_setting(
	'sydney_section_hb_wrapper__header_builder_background_color',
	array(
		'default'           => '#fff',
		'sanitize_callback' => 'sydney_sanitize_hex_rgba',
		'transport'         => 'postMessage'
	)
);
$wp_customize->add_control(
	new Sydney_Alpha_Color(
		$wp_customize,
		'sydney_section_hb_wrapper__header_builder_background_color',
		array(
			'label'         	=> esc_html__( 'Background color', 'sydney' ),
			'section'       	=> 'sydney_section_hb_wrapper',
			'priority'			=> 35
		)
	)
);

// Background Image
$wp_customize->add_setting( 
	'sydney_section_hb_wrapper__header_builder_background_image',
	array(
		'default'           => '',
		'sanitize_callback' => 'absint',
	) 
);
$wp_customize->add_control( 
	new WP_Customize_Media_Control( 
		$wp_customize, 
		'sydney_section_hb_wrapper__header_builder_background_image',
		array(
			'label'           => __( 'Background Image', 'sydney' ),
			'section'         => 'sydney_section_hb_wrapper',
			'mime_type'       => 'image',
			'priority'	      => 35
		)
	)
);

// Background Size
$wp_customize->add_setting( 
	'sydney_section_hb_wrapper__header_builder_background_size',
	array(
		'default'           => 'cover',
		'sanitize_callback' => 'sydney_sanitize_select',
		'transport'         => 'postMessage'
	) 
);
$wp_customize->add_control( 
	'sydney_section_hb_wrapper__header_builder_background_size',
	array(
		'type' 		      => 'select',
		'label' 	      => esc_html__( 'Background Size', 'sydney' ),
		'choices'         => array(
			'cover'   => esc_html__( 'Cover', 'sydney' ),
			'contain' => esc_html__( 'Contain', 'sydney' )
		),
		'section' 	      => 'sydney_section_hb_wrapper',
		'active_callback' => function(){ return get_theme_mod( 'sydney_section_hb_wrapper__header_builder_background_image' ) ? true : false; },
		'priority'        => 35
	)
);

// Background Position
$wp_customize->add_setting( 
	'sydney_section_hb_wrapper__header_builder_background_position',
	array(
		'default'           => 'center',
		'sanitize_callback' => 'sydney_sanitize_select',
		'transport'         => 'postMessage'
	) 
);
$wp_customize->add_control( 
	'sydney_section_hb_wrapper__header_builder_background_position',
	array(
		'type' 		      => 'select',
		'label' 	      => esc_html__( 'Background Position', 'sydney' ),
		'choices'         => array(
			'top'    => esc_html__( 'Top', 'sydney' ),
			'center' => esc_html__( 'Center', 'sydney' ),
			'bottom' => esc_html__( 'Bottom', 'sydney' )
		),
		'section' 	      => 'sydney_section_hb_wrapper',
		'active_callback' => function(){ return get_theme_mod( 'sydney_section_hb_wrapper__header_builder_background_image' ) ? true : false; },
		'priority'        => 35
	)
);

// Background Repeat
$wp_customize->add_setting( 
	'sydney_section_hb_wrapper__header_builder_background_repeat',
	array(
		'default'           => 'no-repeat',
		'sanitize_callback' => 'sydney_sanitize_select',
		'transport'         => 'postMessage'
	) 
);
$wp_customize->add_control( 
	'sydney_section_hb_wrapper__header_builder_background_repeat',
	array(
		'type' 		      => 'select',
		'label' 	      => esc_html__( 'Background Repeat', 'sydney' ),
		'choices'         => array(
			'no-repeat' => esc_html__( 'No Repeat', 'sydney' ),
			'repeat'    => esc_html__( 'Repeat', 'sydney' )
		),
		'section' 	      => 'sydney_section_hb_wrapper',
		'active_callback' => function(){ return get_theme_mod( 'sydney_section_hb_wrapper__header_builder_background_image' ) ? true : false; },
		'priority'        => 35
	)
);

// Padding
$wp_customize->add_setting( 
	'sydney_section_hb_wrapper__header_builder_padding_desktop',
	array(
		'default'           => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
		'sanitize_callback' => 'sydney_sanitize_text',
		'transport'         => 'postMessage'
	) 
);
$wp_customize->add_setting( 
	'sydney_section_hb_wrapper__header_builder_padding_tablet',
	array(
		'default'           => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
		'sanitize_callback' => 'sydney_sanitize_text',
		'transport'         => 'postMessage'
	) 
);
$wp_customize->add_setting( 
	'sydney_section_hb_wrapper__header_builder_padding_mobile',
	array(
		'default'           => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
		'sanitize_callback' => 'sydney_sanitize_text',
		'transport'         => 'postMessage'
	) 
);
$wp_customize->add_control( 
	new Sydney_Dimensions_Control( 
		$wp_customize, 
		'sydney_section_hb_wrapper__header_builder_padding',
		array(
			'label'           	=> __( 'Padding', 'sydney' ),
			'section'         	=> 'sydney_section_hb_wrapper',
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
				'desktop' => 'sydney_section_hb_wrapper__header_builder_padding_desktop',
				'tablet'  => 'sydney_section_hb_wrapper__header_builder_padding_tablet',
				'mobile'  => 'sydney_section_hb_wrapper__header_builder_padding_mobile'
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

// Available Header Components Area
$wp_customize->add_setting( 'sydney_section_hb_wrapper__header_builder_available_components',
	array(
		'default' 			=> '',
		'sanitize_callback' => 'esc_attr'
	)
);
$wp_customize->add_control( new Sydney_Text_Control( $wp_customize, 'sydney_section_hb_wrapper__header_builder_available_components',
		array(
			'description' 	=> '<span class="customize-control-title" style="font-style: normal;">'. esc_html__( 'Available Components', 'sydney' ) .'</span><p class="customizer-section-intro customize-control-description">'. esc_html__( 'Drag and drop components to the header builder area.', 'sydney' ) .'</p><div class="shfb-available-components sydney-header-builder-available-components sydney-shfb-area"></div>',
			'section' 		=> 'sydney_section_hb_wrapper',
            'priority' 		=> 40,
			'separator'		=> 'before'
		)
	)
);

// Available Header Mobile Components Area
$wp_customize->add_setting( 'sydney_section_hb_wrapper__header_builder_available_mobile_components',
	array(
		'default' 			=> '',
		'sanitize_callback' => 'esc_attr'
	)
);
$wp_customize->add_control( new Sydney_Text_Control( $wp_customize, 'sydney_section_hb_wrapper__header_builder_available_mobile_components',
		array(
			'description' 	=> '<span class="customize-control-title" style="font-style: normal;">'. esc_html__( 'Available Components', 'sydney' ) .'</span><p class="customizer-section-intro customize-control-description">'. esc_html__( 'Drag and drop components to the header builder area.', 'sydney' ) .'</p><div class="shfb-available-components sydney-header-builder-available-mobile-components sydney-shfb-area"></div>',
			'section' 		=> 'sydney_section_hb_wrapper',
            'priority' 		=> 40,
			'separator'		=> 'before'
		)
	)
);


// Upsell
if( ! defined( 'SYDNEY_AWL_ACTIVE' ) && ! defined( 'SYDNEY_PRO_VERSION' ) ) {
	$wp_customize->add_setting( 
		'sydney_section_hb_wrapper__header_builder_upsell',
		array(
			'default'           => '',
			'sanitize_callback' => 'sydney_sanitize_text'
		)
	);
	
	$wp_customize->add_control( 
		new Sydney_Upsell_Message( 
			$wp_customize, 
			'sydney_section_hb_wrapper__header_builder_upsell',
			array(
				'section'     => 'sydney_section_hb_wrapper',
				'description' => __( 'Enhance your header with Sydney Pro!', 'sydney' ),
				'features'    => array(
					__( 'Building headers with Elementor', 'sydney' ),
					__( 'Extra header layouts', 'sydney' ),
					__( 'Language switcher', 'sydney' ),
					__( 'Page Headers module', 'sydney' ),
					__( 'Custom breakpoints', 'sydney' ),
					__( 'A mobile-only menu & logo', 'sydney' ),
					__( 'Elementor mega menu builder', 'sydney' ),
                    '<a target="_blank" href="' . esc_url( sydney_admin_upgrade_link( 'https://athemes.com/sydney-upgrade', array( 'utm_source' => 'theme_customizer_main_header', 'utm_medium' => 'sydney_customizer', 'utm_campaign' => 'Sydney' ), 'customizer-main-header-features-link' ) ) . '#features">' . __( '&hellip;and many more premium features', 'sydney' ) . '</a>',
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

		if( $option_name === 'transparent_header' ) {
			$wp_customize->get_control( $option_name )->description = esc_html__( 'The header will be placed over the content. You need to manually change the background color from each header builder row to be transparent.', 'sydney' );
		}

        // Add container title before header_container
        if( $option_name === 'header_container' ) {
            $wp_customize->add_setting( 
                'sydney_section_hb_wrapper__header_builder_container_title', 
                array(
                    'default'           => '',
                    'sanitize_callback' => 'esc_attr'
                ) 
            );
            $wp_customize->add_control( 
                new Sydney_Text_Control( 
                    $wp_customize, 
                    'sydney_section_hb_wrapper__header_builder_container_title', 
                    array(
                        'description' 	=> '<span class="customize-control-title" style="font-style: normal;">'. esc_html__( 'Layout', 'sydney' ) .'</span>',
                        'section' 		=> 'sydney_section_hb_wrapper',
                        'priority' 		=> $priority - 1,
                        'separator'		=> 'before'
                    )
                )
            );
        }

        $wp_customize->get_control( $option_name )->section  = 'sydney_section_hb_wrapper';
        $wp_customize->get_control( $option_name )->priority = $priority;
        
        $priority++;
    }
}

/**
 * Header Presets Section
 * 
 */
$wp_customize->add_section(
	new Sydney_Section_Hidden(
        $wp_customize,
		'sydney_section_hb_presets',
		array(
			'title'       => esc_html__( 'Header Layouts', 'sydney' ),
			'description' => esc_html__( 'Choose a header layout to start with.', 'sydney' ),
			'panel'       => 'sydney_panel_header'
		)
	)
);

$choices = sydney_header_layouts();
$wp_customize->add_setting(
	'sydney_section_hb_presets__header_preset_layout',
	array(
		'default'           => 'header_layout_1',
		'sanitize_callback' => 'sanitize_key',
        'transport'         => 'postMessage'
	)
);
$wp_customize->add_control(
	new Sydney_Radio_Images(
		$wp_customize,
		'sydney_section_hb_presets__header_preset_layout',
		array(
			'label'    	=> esc_html__( 'Layout', 'sydney' ),
			'section'  	=> 'sydney_section_hb_presets',
			'cols'		=> 2,
			'choices'  	=> $choices,
			'priority'	=> 20
		)
	)
);

// @codingStandardsIgnoreEnd WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound