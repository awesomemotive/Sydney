<?php
/**
 * Footer Builder
 * Button 1 Component
 * 
 * @package Sydney_Pro
 */

$wp_customize->add_section(
    new Sydney_Section_Hidden(
        $wp_customize,
        'sydney_section_fb_component__button',
        array(
            'title'      => esc_html__( 'Button 1', 'sydney' ),
            'panel'      => 'sydney_panel_footer',
        )
    )
);

$wp_customize->add_setting(
    'sydney_section_fb_component__button_tabs',
    array(
        'default'           => '',
        'sanitize_callback' => 'esc_attr',
    )
);
$wp_customize->add_control(
    new Sydney_Tab_Control (
        $wp_customize,
        'sydney_section_fb_component__button_tabs',
        array(
            'label'                 => '',
            'section'               => 'sydney_section_fb_component__button',
            'controls_general'      => wp_json_encode(
                array(
                    '#customize-control-shfb_footer_button_text',
                    '#customize-control-shfb_footer_button_link',
                    '#customize-control-shfb_footer_button_class',
                    '#customize-control-shfb_footer_button_newtab',
                    '#customize-control-shfb_footer_button_visibility',
                )
            ),
            'controls_design'       => wp_json_encode(
                array(
                    '#customize-control-shfb_footer_button_colors_title',
                    '#customize-control-shfb_footer_button_background',
                    '#customize-control-shfb_footer_button',
                    '#customize-control-shfb_footer_button_border',
					'#customize-control-shfb_footer_button_padding',
					'#customize-control-shfb_footer_button_margin',
                )
            ),
            'priority'              => 20,
        )
    )
);

// Button Text
$wp_customize->add_setting(
	'shfb_footer_button_text',
	array(
		'sanitize_callback' => 'sydney_sanitize_text',
		'default'           => esc_html__( 'Click me', 'sydney' ),
	)       
);
$wp_customize->add_control( 'shfb_footer_button_text', array(
	'label'       => esc_html__( 'Button text', 'sydney' ),
	'type'        => 'text',
	'section'     => 'sydney_section_fb_component__button',
	'priority'          => 25,
) );

// Button Link
$wp_customize->add_setting(
	'shfb_footer_button_link',
	array(
		'sanitize_callback' => 'esc_url_raw',
		'default'           => '#',
	)       
);
$wp_customize->add_control( 'shfb_footer_button_link', array(
	'label'       => esc_html__( 'Button link', 'sydney' ),
	'type'        => 'text',
	'section'     => 'sydney_section_fb_component__button',
	'priority'          => 30,
) );

// Button Class
$wp_customize->add_setting(
	'shfb_footer_button_class',
	array(
		'sanitize_callback' => 'esc_attr',
		'default'           => '',
	)       
);
$wp_customize->add_control( 'shfb_footer_button_class', array(
	'label'       => esc_html__( 'Button Class', 'sydney' ),
	'type'        => 'text',
	'section'     => 'sydney_section_fb_component__button',
	'priority'          => 35,
) );

// Button Target
$wp_customize->add_setting(
	'shfb_footer_button_newtab',
	array(
		'default'           => 0,
		'sanitize_callback' => 'sydney_sanitize_checkbox',
	)
);
$wp_customize->add_control(
	new Sydney_Toggle_Control(
		$wp_customize,
		'shfb_footer_button_newtab',
		array(
			'label'             => esc_html__( 'Open in a new tab?', 'sydney' ),
			'section'           => 'sydney_section_fb_component__button',
			'priority'          => 40,
		)
	)
);

// Visibility
$wp_customize->add_setting( 
    'shfb_footer_button_visibility_desktop',
    array(
        'default'           => 'visible',
        'sanitize_callback' => 'sydney_sanitize_text',
        'transport'         => 'postMessage',
    )
);
$wp_customize->add_setting( 
    'shfb_footer_button_visibility_tablet',
    array(
        'default'           => 'visible',
        'sanitize_callback' => 'sydney_sanitize_text',
        'transport'         => 'postMessage',
    )
);
$wp_customize->add_setting( 
    'shfb_footer_button_visibility_mobile',
    array(
        'default'           => 'visible',
        'sanitize_callback' => 'sydney_sanitize_text',
        'transport'         => 'postMessage',
    )
);
$wp_customize->add_control( 
    new Sydney_Radio_Buttons( 
        $wp_customize, 
        'shfb_footer_button_visibility',
        array(
            'label'         => esc_html__( 'Visibility', 'sydney' ),
            'section'       => 'sydney_section_fb_component__button',
            'is_responsive' => true,
            'settings' => array(
                'desktop'       => 'shfb_footer_button_visibility_desktop',
                'tablet'        => 'shfb_footer_button_visibility_tablet',
                'mobile'        => 'shfb_footer_button_visibility_mobile',
            ),
            'choices'       => array(
                'visible' => esc_html__( 'Visible', 'sydney' ),
                'hidden'  => esc_html__( 'Hidden', 'sydney' ),
            ),
            'priority'      => 42,
        )
    ) 
);

// Colors Title.
$wp_customize->add_setting( 'shfb_footer_button_colors_title',
	array(
		'default'           => '',
		'sanitize_callback' => 'esc_attr',
	)
);
$wp_customize->add_control( new Sydney_Text_Control( $wp_customize, 'shfb_footer_button_colors_title',
		array(
			'label'         => esc_html__( 'Colors', 'sydney' ),
			'section'       => 'sydney_section_fb_component__button',
            'priority'      => 45,
		)
	)
);

// Background Color.
$wp_customize->add_setting(
	'shfb_footer_button_background_color',
	array(
		'default'           => '#212121',
		'sanitize_callback' => 'sydney_sanitize_hex_rgba',
		'transport'         => 'postMessage',
	)
);
$wp_customize->add_setting(
	'shfb_footer_button_background_color_hover',
	array(
		'default'           => '#757575',
		'sanitize_callback' => 'sydney_sanitize_hex_rgba',
		'transport'         => 'postMessage',
	)
);
$wp_customize->add_control(
    new Sydney_Color_Group(
        $wp_customize,
        'shfb_footer_button_background',
        array(
            'label'    => esc_html__( 'Background Color', 'sydney' ),
            'section'  => 'sydney_section_fb_component__button',
            'settings' => array(
                'normal' => 'shfb_footer_button_background_color',
                'hover'  => 'shfb_footer_button_background_color_hover',
            ),
            'priority' => 50,
        )
    )
);

// Text Color.
$wp_customize->add_setting(
	'shfb_footer_button_color',
	array(
		'default'           => '#FFF',
		'sanitize_callback' => 'sydney_sanitize_hex_rgba',
		'transport'         => 'postMessage',
	)
);
$wp_customize->add_setting(
	'shfb_footer_button_color_hover',
	array(
		'default'           => '#FFF',
		'sanitize_callback' => 'sydney_sanitize_hex_rgba',
		'transport'         => 'postMessage',
	)
);
$wp_customize->add_control(
    new Sydney_Color_Group(
        $wp_customize,
        'shfb_footer_button',
        array(
            'label'    => esc_html__( 'Text Color', 'sydney' ),
            'section'  => 'sydney_section_fb_component__button',
            'settings' => array(
                'normal' => 'shfb_footer_button_color',
                'hover'  => 'shfb_footer_button_color_hover',
            ),
            'priority' => 55,
        )
    )
);

// Border Color.
$wp_customize->add_setting(
	'shfb_footer_button_border_color',
	array(
		'default'           => '#212121',
		'sanitize_callback' => 'sydney_sanitize_hex_rgba',
		'transport'         => 'postMessage',
	)
);
$wp_customize->add_setting(
	'shfb_footer_button_border_color_hover',
	array(
		'default'           => '#757575',
		'sanitize_callback' => 'sydney_sanitize_hex_rgba',
		'transport'         => 'postMessage',
	)
);
$wp_customize->add_control(
    new Sydney_Color_Group(
        $wp_customize,
        'shfb_footer_button_border',
        array(
            'label'    => esc_html__( 'Border Color', 'sydney' ),
            'section'  => 'sydney_section_fb_component__button',
            'settings' => array(
                'normal' => 'shfb_footer_button_border_color',
                'hover'  => 'shfb_footer_button_border_color_hover',
            ),
            'priority' => 60,
        )
    )
);

// Layout Title
$wp_customize->add_setting(
    'shfb_footer_button_layout_title',
    array(
        'default'           => '',
        'sanitize_callback' => 'esc_attr',
    )
);

$wp_customize->add_control(
    new Sydney_Text_Control(
        $wp_customize,
        'shfb_footer_button_layout_title',
        array(
            'label'    => esc_html__( 'Layout', 'sydney' ),
            'section'  => 'sydney_section_fb_component__button',
            'priority' => 70,
            'separator' => 'before',
        )
    )
);

// Padding
$wp_customize->add_setting( 
    'shfb_footer_button_padding_desktop',
    array(
        'default'           => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
        'sanitize_callback' => 'sydney_sanitize_text',
        'transport'         => 'postMessage',
    ) 
);
$wp_customize->add_setting( 
    'shfb_footer_button_padding_tablet',
    array(
        'default'           => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
        'sanitize_callback' => 'sydney_sanitize_text',
        'transport'         => 'postMessage',
    ) 
);
$wp_customize->add_setting( 
    'shfb_footer_button_padding_mobile',
    array(
        'default'           => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
        'sanitize_callback' => 'sydney_sanitize_text',
        'transport'         => 'postMessage',
    ) 
);
$wp_customize->add_control( 
    new Sydney_Dimensions_Control( 
        $wp_customize, 
        'shfb_footer_button_padding',
        array(
            'label'             => __( 'Wrapper Padding', 'sydney' ),
            'section'           => 'sydney_section_fb_component__button',
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
                'desktop' => 'shfb_footer_button_padding_desktop',
                'tablet'  => 'shfb_footer_button_padding_tablet',
                'mobile'  => 'shfb_footer_button_padding_mobile',
            ),
            'priority'           => 72,
        )
    )
);

// Margin
$wp_customize->add_setting( 
    'shfb_footer_button_margin_desktop',
    array(
        'default'           => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
        'sanitize_callback' => 'sydney_sanitize_text',
        'transport'         => 'postMessage',
    ) 
);
$wp_customize->add_setting( 
    'shfb_footer_button_margin_tablet',
    array(
        'default'           => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
        'sanitize_callback' => 'sydney_sanitize_text',
        'transport'         => 'postMessage',
    ) 
);
$wp_customize->add_setting( 
    'shfb_footer_button_margin_mobile',
    array(
        'default'           => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
        'sanitize_callback' => 'sydney_sanitize_text',
        'transport'         => 'postMessage',
    ) 
);
$wp_customize->add_control( 
    new Sydney_Dimensions_Control( 
        $wp_customize, 
        'shfb_footer_button_margin',
        array(
            'label'             => __( 'Wrapper Margin', 'sydney' ),
            'section'           => 'sydney_section_fb_component__button',
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
                'desktop' => 'shfb_footer_button_margin_desktop',
                'tablet'  => 'shfb_footer_button_margin_tablet',
                'mobile'  => 'shfb_footer_button_margin_mobile',
            ),
            'priority'           => 72,
        )
    )
);