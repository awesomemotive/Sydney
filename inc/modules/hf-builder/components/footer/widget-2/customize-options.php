<?php
/**
 * Footer Builder
 * Widget 2 Component
 * 
 * @package Sydney_Pro
 */

$wp_customize->add_section(
    new Sydney_Section_Hidden(
        $wp_customize,
        'sydney_section_fb_component__widget2',
        array(
            'title'      => esc_html__( 'Widget Area 2', 'sydney' ),
            'panel'      => 'sydney_panel_footer',
        )
    )
);

$wp_customize->add_setting(
    'sydney_section_fb_component__widget2_tabs',
    array(
        'default'           => '',
        'sanitize_callback' => 'esc_attr',
    )
);
$wp_customize->add_control(
    new Sydney_Tab_Control (
        $wp_customize,
        'sydney_section_fb_component__widget2_tabs',
        array(
            'section'               => 'sydney_section_fb_component__widget2',
            'controls_general'      => wp_json_encode(
                array(
                    '#customize-control-sydney_section_fb_component__widget2_goto_edit',
                    '#customize-control-sydney_section_fb_component__widget2_visibility',
                )
            ),
            'controls_design'       => wp_json_encode(
                array(
                    '#customize-control-sydney_section_fb_component__widget2_title_color',
                    '#customize-control-sydney_section_fb_component__widget2_text_color',
                    '#customize-control-sydney_section_fb_component__widget2_links',
					'#customize-control-sydney_section_fb_component__widget2_padding',
					'#customize-control-sydney_section_fb_component__widget2_margin',
                )
            ),
            'priority'              => 20,
        )
    )
);

// Go to button (edit widget)
$wp_customize->add_setting( 'sydney_section_fb_component__widget2_goto_edit',
	array(
		'default'           => '',
		'sanitize_callback' => 'esc_attr',
	)
);
$wp_customize->add_control( new Sydney_Text_Control( $wp_customize, 'sydney_section_fb_component__widget2_goto_edit',
		array(
			'description'   => '<a class="sydney-to-widget-area-link" href="javascript:wp.customize.section( \'sidebar-widgets-footer-2\' ).active(true); wp.customize.section( \'sidebar-widgets-footer-2\' ).focus();">' . esc_html__( 'Footer Widget Area 2', 'sydney' ) . '<span class="dashicons dashicons-arrow-right-alt2"></span></a>',
			'section'       => 'sydney_section_fb_component__widget2',
            'priority'      => 30,
		)
	)
);

// Visibility
$wp_customize->add_setting( 
    'sydney_section_fb_component__widget2_visibility_desktop',
    array(
        'default'           => 'visible',
        'sanitize_callback' => 'sydney_sanitize_text',
        'transport'         => 'postMessage',
    )
);
$wp_customize->add_setting( 
    'sydney_section_fb_component__widget2_visibility_tablet',
    array(
        'default'           => 'visible',
        'sanitize_callback' => 'sydney_sanitize_text',
        'transport'         => 'postMessage',
    )
);
$wp_customize->add_setting( 
    'sydney_section_fb_component__widget2_visibility_mobile',
    array(
        'default'           => 'visible',
        'sanitize_callback' => 'sydney_sanitize_text',
        'transport'         => 'postMessage',
    )
);
$wp_customize->add_control( 
    new Sydney_Radio_Buttons( 
        $wp_customize, 
        'sydney_section_fb_component__widget2_visibility',
        array(
            'label'         => esc_html__( 'Visibility', 'sydney' ),
            'section'       => 'sydney_section_fb_component__widget2',
            'is_responsive' => true,
            'settings' => array(
                'desktop'       => 'sydney_section_fb_component__widget2_visibility_desktop',
                'tablet'        => 'sydney_section_fb_component__widget2_visibility_tablet',
                'mobile'        => 'sydney_section_fb_component__widget2_visibility_mobile',
            ),
            'choices'       => array(
                'visible' => esc_html__( 'Visible', 'sydney' ),
                'hidden'  => esc_html__( 'Hidden', 'sydney' ),
            ),
            'priority'      => 42,
        )
    ) 
);

// Widget Title Color
$wp_customize->add_setting(
	'sydney_section_fb_component__widget2_title_color',
	array(
		'default'           => '#212121',
		'sanitize_callback' => 'sydney_sanitize_hex_rgba',
		'transport'         => 'postMessage',
	)
);
$wp_customize->add_control(
	new Sydney_Alpha_Color(
		$wp_customize,
		'sydney_section_fb_component__widget2_title_color',
		array(
			'label'             => esc_html__( 'Widget Title Color', 'sydney' ),
			'section'           => 'sydney_section_fb_component__widget2',
			'priority'          => 50,
		)
	)
);

// Text Color
$wp_customize->add_setting(
	'sydney_section_fb_component__widget2_text_color',
	array(
		'default'           => '#212121',
		'sanitize_callback' => 'sydney_sanitize_hex_rgba',
		'transport'         => 'postMessage',
	)
);
$wp_customize->add_control(
	new Sydney_Alpha_Color(
		$wp_customize,
		'sydney_section_fb_component__widget2_text_color',
		array(
			'label'             => esc_html__( 'Text Color', 'sydney' ),
			'section'           => 'sydney_section_fb_component__widget2',
			'priority'          => 50,
		)
	)
);

// Links Color
$wp_customize->add_setting(
	'sydney_section_fb_component__widget2_links_color',
	array(
		'default'           => '#212121',
		'sanitize_callback' => 'sydney_sanitize_hex_rgba',
		'transport'         => 'postMessage',
	)
);
$wp_customize->add_setting(
	'sydney_section_fb_component__widget2_links_color_hover',
	array(
		'default'           => '#212121',
		'sanitize_callback' => 'sydney_sanitize_hex_rgba',
		'transport'         => 'postMessage',
	)
);
$wp_customize->add_control(
    new Sydney_Color_Group(
        $wp_customize,
        'sydney_section_fb_component__widget2_links',
        array(
            'label'    => esc_html__( 'Links Color', 'sydney' ),
            'section'  => 'sydney_section_fb_component__widget2',
            'settings' => array(
                'normal' => 'sydney_section_fb_component__widget2_links_color',
                'hover'  => 'sydney_section_fb_component__widget2_links_color_hover',
            ),
            'priority' => 50,
        )
    )
);

// Layout Title
$wp_customize->add_setting(
    'shfb_footer_widget_2_layout_title',
    array(
        'default'           => '',
        'sanitize_callback' => 'esc_attr',
    )
);

$wp_customize->add_control(
    new Sydney_Text_Control(
        $wp_customize,
        'shfb_footer_widget_2_layout_title',
        array(
            'label'    => esc_html__( 'Layout', 'sydney' ),
            'section'  => 'sydney_section_fb_component__widget2',
            'priority' => 70,
            'separator' => 'before',
        )
    )
);

// Padding
$wp_customize->add_setting( 
    'sydney_section_fb_component__widget2_padding_desktop',
    array(
        'default'           => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
        'sanitize_callback' => 'sydney_sanitize_text',
        'transport'         => 'postMessage',
    ) 
);
$wp_customize->add_setting( 
    'sydney_section_fb_component__widget2_padding_tablet',
    array(
        'default'           => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
        'sanitize_callback' => 'sydney_sanitize_text',
        'transport'         => 'postMessage',
    ) 
);
$wp_customize->add_setting( 
    'sydney_section_fb_component__widget2_padding_mobile',
    array(
        'default'           => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
        'sanitize_callback' => 'sydney_sanitize_text',
        'transport'         => 'postMessage',
    ) 
);
$wp_customize->add_control( 
    new Sydney_Dimensions_Control( 
        $wp_customize, 
        'sydney_section_fb_component__widget2_padding',
        array(
            'label'             => __( 'Wrapper Padding', 'sydney' ),
            'section'           => 'sydney_section_fb_component__widget2',
            'sides'             => array(
                'top'    => true,
                'right'  => true,
                'bottom' => true,
                'left'   => true,
            ),
            'units'              => array( 'px', '%', 'rem', 'em', 'vw', 'vh' ),
            'link_values_toggle' => true,
            'is_responsive'      => true,
            'settings'           => array(
                'desktop' => 'sydney_section_fb_component__widget2_padding_desktop',
                'tablet'  => 'sydney_section_fb_component__widget2_padding_tablet',
                'mobile'  => 'sydney_section_fb_component__widget2_padding_mobile',
            ),
            'priority'           => 72,
        )
    )
);

// Margin
$wp_customize->add_setting( 
    'sydney_section_fb_component__widget2_margin_desktop',
    array(
        'default'           => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
        'sanitize_callback' => 'sydney_sanitize_text',
        'transport'         => 'postMessage',
    ) 
);
$wp_customize->add_setting( 
    'sydney_section_fb_component__widget2_margin_tablet',
    array(
        'default'           => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
        'sanitize_callback' => 'sydney_sanitize_text',
        'transport'         => 'postMessage',
    ) 
);
$wp_customize->add_setting( 
    'sydney_section_fb_component__widget2_margin_mobile',
    array(
        'default'           => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
        'sanitize_callback' => 'sydney_sanitize_text',
        'transport'         => 'postMessage',
    ) 
);
$wp_customize->add_control( 
    new Sydney_Dimensions_Control( 
        $wp_customize, 
        'sydney_section_fb_component__widget2_margin',
        array(
            'label'             => __( 'Wrapper Margin', 'sydney' ),
            'section'           => 'sydney_section_fb_component__widget2',
            'sides'             => array(
                'top'    => true,
                'right'  => true,
                'bottom' => true,
                'left'   => true,
            ),
            'units'              => array( 'px', '%', 'rem', 'em', 'vw', 'vh' ),
            'link_values_toggle' => true,
            'is_responsive'      => true,
            'settings'           => array(
                'desktop' => 'sydney_section_fb_component__widget2_margin_desktop',
                'tablet'  => 'sydney_section_fb_component__widget2_margin_tablet',
                'mobile'  => 'sydney_section_fb_component__widget2_margin_mobile',
            ),
            'priority'           => 72,
        )
    )
);