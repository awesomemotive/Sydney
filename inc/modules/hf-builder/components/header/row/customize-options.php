<?php
/**
 * Header/Footer Builder
 * Rows
 * 
 * @package Sydney_Pro
 */

// @codingStandardsIgnoreStart WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

/**
 * Rows
 */

foreach( $this->header_rows as $row ) {
    $wp_customize->add_setting(
        'sydney_header_row__' . $row['id'],
        array(
            'default'           => $row['default'],
            'sanitize_callback' => 'sydney_sanitize_text',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        'sydney_header_row__' . $row['id'],
        array(
            'type'     => 'text',
            'label'    => esc_html( $row['label'] ),
            'section'  => $row['section'],
            'settings' => 'sydney_header_row__' . $row['id'],
            'priority' => 10
        )
    );

    // Selective Refresh
    if ( isset( $wp_customize->selective_refresh ) ) {
        $wp_customize->selective_refresh->add_partial(
            'sydney_header_row__' . $row['id'],
            array(
                'selector'        => '.shfb-desktop .shfb-rows .shfb-' . $row['id'],
                'render_callback' => function() use( $row ) {
                    $this->rows_callback( 'header', $row['id'], 'desktop' ); // phpcs:ignore PHPCompatibility.FunctionDeclarations.NewClosure.ThisFoundOutsideClass
                },
            )
        );
    }

    $wp_customize->add_setting(
        'sydney_header_row__' . $row['id'] . '_tabs',
        array(
            'default'           => '',
            'sanitize_callback' => 'esc_attr'
        )
    );
    $wp_customize->add_control(
        new Sydney_Tab_Control (
            $wp_customize,
            'sydney_header_row__' . $row['id'] . '_tabs',
            array(
                'label' 				=> '',
                'section'       		=> $row['section'],
                'controls_general'		=> wp_json_encode( array( 
                    '#customize-control-sydney_header_row__mobile_above_header_row',
                    '#customize-control-sydney_header_row__mobile_main_header_row',
                    '#customize-control-sydney_header_row__mobile_below_header_row',
                    '#customize-control-sydney_header_row__' . $row['id'] ,
                    '#customize-control-sydney_header_row__' . $row['id'] . '_height',
                    '#customize-control-sydney_header_row__' . $row['id'] . '_columns',
                    '#customize-control-sydney_header_row__' . $row['id'] . '_columns_layout',
                    '#customize-control-sydney_header_row__' . $row['id'] . '_available_columns'
                ) ),
                'controls_design'		=> wp_json_encode( array( 
                    '#customize-control-sydney_header_row__' . $row['id'] . '_background_color',
                    '#customize-control-sydney_header_row__' . $row['id'] . '_transparent_background_color',
                    '#customize-control-sydney_header_row__' . $row['id'] . '_divider2',
                    '#customize-control-sydney_header_row__' . $row['id'] . '_background_image',
                    '#customize-control-sydney_header_row__' . $row['id'] . '_background_size',
                    '#customize-control-sydney_header_row__' . $row['id'] . '_background_position',
                    '#customize-control-sydney_header_row__' . $row['id'] . '_background_repeat',
                    '#customize-control-sydney_header_row__' . $row['id'] . '_border_bottom',
                    '#customize-control-sydney_header_row__' . $row['id'] . '_border_bottom_color',
                    '#customize-control-sydney_header_row__' . $row['id'] . '_padding',
                    '#customize-control-sydney_header_row__' . $row['id'] . '_margin',

                    // Stiky Active State
                    '#customize-control-sydney_header_row__' . $row['id'] . '_sticky_divider1',
                    '#customize-control-sydney_header_row__' . $row['id'] . '_sticky_title',
                    '#customize-control-sydney_header_row__' . $row['id'] . '_sticky_background_color',
                    '#customize-control-sydney_header_row__' . $row['id'] . '_sticky_border_bottom_color'
                ) ),
                'priority' 				=> 20
            )
        )
    );

    /**
     * General
     */

    // Height.
    $wp_customize->add_setting( 'sydney_header_row__' . $row['id'] . '_height_desktop', array(
        'default'   		=> 100,
        'transport'			=> 'postMessage',
        'sanitize_callback' => 'absint'
    ) );			
    $wp_customize->add_setting( 'sydney_header_row__' . $row['id'] . '_height_tablet', array(
        'default'   		=> 100,
        'transport'			=> 'postMessage',
        'sanitize_callback' => 'absint'
    ) );
    $wp_customize->add_setting( 'sydney_header_row__' . $row['id'] . '_height_mobile', array(
        'default'   		=> 100,
        'transport'			=> 'postMessage',
        'sanitize_callback' => 'absint'
    ) );			
    
    $wp_customize->add_control( new Sydney_Responsive_Slider( $wp_customize, 'sydney_header_row__' . $row['id'] . '_height',
        array(
            'label' 		=> esc_html__( 'Height', 'sydney' ),
            'section' 		=> $row['section'],
            'is_responsive'	=> 1,
            'settings' 		=> array (
                'size_desktop' 		=> 'sydney_header_row__' . $row['id'] . '_height_desktop',
                'size_tablet' 		=> 'sydney_header_row__' . $row['id'] . '_height_tablet',
                'size_mobile' 		=> 'sydney_header_row__' . $row['id'] . '_height_mobile',
            ),
            'input_attrs' => array (
                'min'	=> 0,
                'max'	=> 500
            ),
            'priority'              => 30
        )
    ) );

    // Columns.
    $wp_customize->add_setting( 'sydney_header_row__' . $row['id'] . '_columns_desktop',
        array(
            'default' 			=> '3',
            'sanitize_callback' => 'sydney_sanitize_text',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_setting( 'sydney_header_row__' . $row['id'] . '_columns_tablet',
        array(
            'default' 			=> '3',
            'sanitize_callback' => 'sydney_sanitize_text',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control( new Sydney_Radio_Buttons( $wp_customize, 'sydney_header_row__' . $row['id'] . '_columns',
        array(
            'label'         => esc_html__( 'Columns', 'sydney' ),
            'section'       => $row['section'],
            'is_responsive' => true,
            'settings' 		=> array (
                'desktop' 		=> 'sydney_header_row__' . $row['id'] . '_columns_desktop',
                'tablet' 		=> 'sydney_header_row__' . $row['id'] . '_columns_tablet'
            ),
            'choices'       => array(
                '1' => '1',
                '2' => '2',
                '3' => '3',
                '4' => '4',
                '5' => '5',
                '6' => '6'
            ),
            'priority'      => 35
        )
    ) );

    // Columns Layout.
    $wp_customize->add_setting(
        'sydney_header_row__' . $row['id'] . '_columns_layout_desktop',
        array(
            'default'           => 'equal',
            'sanitize_callback' => 'sanitize_key',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_setting(
        'sydney_header_row__' . $row['id'] . '_columns_layout_tablet',
        array(
            'default'           => 'equal',
            'sanitize_callback' => 'sanitize_key',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new Sydney_Radio_Images(
            $wp_customize,
            'sydney_header_row__' . $row['id'] . '_columns_layout',
            array(
                'label'    => esc_html__( 'Columns Layout', 'sydney' ),
                'section'  => $row['section'],
                'cols' 		=> 4,
                'class'    => 'sydney-radio-images-small',
                'is_responsive' => true,
                'settings' 		=> array (
                    'desktop' 		=> 'sydney_header_row__' . $row['id'] . '_columns_layout_desktop',
                    'tablet' 		=> 'sydney_header_row__' . $row['id'] . '_columns_layout_tablet'
                ),
                'choices'  => array(			
                    '1col-equal' => array(
                        'label' => esc_html__( 'Equal Width', 'sydney' ),
                        'url'   => '%s/images/fl1.svg'
                    ),
                    '2col-equal' => array(
                        'label' => esc_html__( 'Equal Width', 'sydney' ),
                        'url'   => '%s/images/fl2.svg'
                    ),		
                    '2col-bigleft' => array(
                        'label' => esc_html__( 'Big Left', 'sydney' ),
                        'url'   => '%s/images/fl3.svg'
                    ),				
                    '2col-bigright' => array(
                        'label' => esc_html__( 'Big Right', 'sydney' ),
                        'url'   => '%s/images/fl4.svg'
                    ),
                    '3col-equal' => array(
                        'label' => esc_html__( 'Equal Width', 'sydney' ),
                        'url'   => '%s/images/fl5.svg'
                    ),	
                    '3col-fluid' => array(
                        'label' => esc_html__( 'Fluid Width', 'sydney' ),
                        'url'   => '%s/images/fl13.svg'
                    ),	
                    '3col-bigleft' => array(
                        'label' => esc_html__( 'Big Left', 'sydney' ),
                        'url'   => '%s/images/fl6.svg'
                    ),
                    '3col-bigright' => array(
                        'label' => esc_html__( 'Big Right', 'sydney' ),
                        'url'   => '%s/images/fl7.svg'
                    ),	
                    '4col-equal' => array(
                        'label' => esc_html__( 'Equal', 'sydney' ),
                        'url'   => '%s/images/fl8.svg'
                    ),	
                    '4col-bigleft' => array(
                        'label' => esc_html__( 'Big Left', 'sydney' ),
                        'url'   => '%s/images/fl9.svg'
                    ),
                    '4col-bigright' => array(
                        'label' => esc_html__( 'Big Right', 'sydney' ),
                        'url'   => '%s/images/fl10.svg'
                    ),
                    '5col-equal' => array(
                        'label' => esc_html__( 'Equal Width', 'sydney' ),
                        'url'   => '%s/images/fl11.svg'
                    ),
                    '6col-equal' => array(
                        'label' => esc_html__( 'Equal Width', 'sydney' ),
                        'url'   => '%s/images/fl12.svg'
                    ),
                ),
                'priority' => 35
            )
        )
    );

    // Available Columns.
    $devices = array( 'desktop', 'tablet' );
    $desc    = '';
    foreach( $devices as $device ) {
        $desc .= '<div class="shfb-available-columns shfb-available-columns-'. esc_attr( $device ) .'">';
            $desc .= '<span class="customize-control-title shfb-columns-control-title" style="font-style: normal;">'. esc_html__( 'Available Columns', 'sydney' ) .'</span>';
            $desc .= '<div class="shfb-available-columns-items-wrapper">';
            for( $i=1;$i<=6;$i++ ) {
                $col_section_id = 'sydney_header_row__' . $row['id'] . '_column' . $i;

                $desc .= '<a class="shfb-available-columns-item" href="#" data-column="'. absint( $i ) .'" onClick="wp.customize.section(\''. esc_js( $col_section_id ) .'\').focus()">'. /* translators: 1: column number. */ sprintf( esc_html__( 'Column %s', 'sydney' ), absint( $i ) ) .'<span class="dashicons dashicons-arrow-right-alt2"></span></a>';
            }
            $desc .= '</div>';
        $desc .= '</div>';
    }

    $wp_customize->add_setting( 
        'sydney_header_row__' . $row['id'] . '_available_columns',
        array(
            'default' 			=> '',
            'sanitize_callback' => 'esc_attr'
        )
    );
    $wp_customize->add_control( 
        new Sydney_Text_Control( 
            $wp_customize, 
            'sydney_header_row__' . $row['id'] . '_available_columns',
            array(
                'description' 	=> $desc,
                'section' 		=> $row['section'],
                'priority' 		=> 37
            )
        )
    );

    /**
     * Styling
     */

    // Background.
    $wp_customize->add_setting(
        'global_sydney_header_row__' . $row['id'] . '_background_color',
        array(
            'default'           => '',
            'sanitize_callback' => 'wp_kses_post',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_setting(
        'sydney_header_row__' . $row['id'] . '_background_color',
        array(
            'default'           => '',
            'sanitize_callback' => 'sydney_sanitize_hex_rgba',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new Sydney_Alpha_Color(
            $wp_customize,
            'sydney_header_row__' . $row['id'] . '_background_color',
            array(
                'label'         	=> esc_html__( 'Background Color', 'sydney' ),
                'section'       	=> $row['section'],
                'settings'       => array(
                    'global'  => 'global_sydney_header_row__' . $row['id'] . '_background_color',
                    'setting' => 'sydney_header_row__' . $row['id'] . '_background_color',
                ),
                'priority'			=> 32
            )
        )
    );

    // Transparent Header Background Color
    $wp_customize->add_setting(
        'global_sydney_header_row__' . $row['id'] . '_transparent_background_color',
        array(
            'default'           => '',
            'sanitize_callback' => 'wp_kses_post',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_setting(
        'sydney_header_row__' . $row['id'] . '_transparent_background_color',
        array(
            'default'           => '',
            'sanitize_callback' => 'sydney_sanitize_hex_rgba',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new Sydney_Alpha_Color(
            $wp_customize,
            'sydney_header_row__' . $row['id'] . '_transparent_background_color',
            array(
                'label'         	=> esc_html__( 'Background Color (transparent mode)', 'sydney' ),
                'section'       	=> $row['section'],
                'settings'       => array(
                    'global'  => 'global_sydney_header_row__' . $row['id'] . '_transparent_background_color',
                    'setting' => 'sydney_header_row__' . $row['id'] . '_transparent_background_color',
                ),
                'active_callback' => function() {
                    $transparent_header = get_theme_mod( 'transparent_header', '' );
                    return ! empty( $transparent_header );
                },
                'priority'			=> 32
            )
        )
    );

    // Background Image
    $wp_customize->add_setting( 
        'sydney_header_row__' . $row['id'] . '_background_image',
        array(
            'default'           => '',
            'sanitize_callback' => 'absint',
        ) 
    );
    $wp_customize->add_control( 
        new WP_Customize_Media_Control( 
            $wp_customize, 
            'sydney_header_row__' . $row['id'] . '_background_image',
            array(
                'label'           => __( 'Background Image', 'sydney' ),
                'section'         => $row['section'],
                'mime_type'       => 'image',
                'priority'	      => 32
            )
        )
    );

    // Background Size
    $wp_customize->add_setting( 
        'sydney_header_row__' . $row['id'] . '_background_size',
        array(
            'default'           => 'cover',
            'sanitize_callback' => 'sydney_sanitize_select',
            'transport'         => 'postMessage'
        ) 
    );
    $wp_customize->add_control( 
        'sydney_header_row__' . $row['id'] . '_background_size',
        array(
            'type' 		      => 'select',
            'label' 	      => esc_html__( 'Background Size', 'sydney' ),
            'choices'         => array(
                'cover'   => esc_html__( 'Cover', 'sydney' ),
                'contain' => esc_html__( 'Contain', 'sydney' )
            ),
            'section' 	      => $row['section'],
            'active_callback' => function() use ( $row ){ return get_theme_mod( 'sydney_header_row__' . $row['id'] . '_background_image' ) ? true : false; },
            'priority'        => 32
        )
    );

    // Background Position
    $wp_customize->add_setting( 
        'sydney_header_row__' . $row['id'] . '_background_position',
        array(
            'default'           => 'center',
            'sanitize_callback' => 'sydney_sanitize_select',
            'transport'         => 'postMessage'
        ) 
    );
    $wp_customize->add_control( 
        'sydney_header_row__' . $row['id'] . '_background_position',
        array(
            'type' 		      => 'select',
            'label' 	      => esc_html__( 'Background Position', 'sydney' ),
            'choices'         => array(
                'top'    => esc_html__( 'Top', 'sydney' ),
                'center' => esc_html__( 'Center', 'sydney' ),
                'bottom' => esc_html__( 'Bottom', 'sydney' )
            ),
            'section' 	      => $row['section'],
            'active_callback' => function() use ( $row ){ return get_theme_mod( 'sydney_header_row__' . $row['id'] . '_background_image' ) ? true : false; },
            'priority'        => 32
        )
    );

    // Background Repeat
    $wp_customize->add_setting( 
        'sydney_header_row__' . $row['id'] . '_background_repeat',
        array(
            'default'           => 'no-repeat',
            'sanitize_callback' => 'sydney_sanitize_select',
            'transport'         => 'postMessage'
        ) 
    );
    $wp_customize->add_control( 
        'sydney_header_row__' . $row['id'] . '_background_repeat',
        array(
            'type' 		      => 'select',
            'label' 	      => esc_html__( 'Background Repeat', 'sydney' ),
            'choices'         => array(
                'no-repeat' => esc_html__( 'No Repeat', 'sydney' ),
                'repeat'    => esc_html__( 'Repeat', 'sydney' )
            ),
            'section' 	      => $row['section'],
            'active_callback' => function() use ( $row ){ return get_theme_mod( 'sydney_header_row__' . $row['id'] . '_background_image' ) ? true : false; },
            'priority'        => 32
        )
    );

    // Border Bottom.
    $wp_customize->add_setting( 'sydney_header_row__' . $row['id'] . '_border_bottom_desktop', array(
        'default'   		=> 1,
        'transport'			=> 'postMessage',
        'sanitize_callback' => 'absint'
    ) );						
    $wp_customize->add_control( new Sydney_Responsive_Slider( $wp_customize, 'sydney_header_row__' . $row['id'] . '_border_bottom',
        array(
            'label' 		=> esc_html__( 'Border Bottom Size', 'sydney' ),
            'section' 		=> $row['section'],
            'is_responsive'	=> 0,
            'settings' 		=> array (
                'size_desktop' 		=> 'sydney_header_row__' . $row['id'] . '_border_bottom_desktop'
            ),
            'input_attrs' => array (
                'min'	=> 0,
                'max'	=> 10
            ),
            'priority'              => 34
        )
    ) );

    // Border Bottom Color.
    $wp_customize->add_setting(
        'sydney_header_row__' . $row['id'] . '_border_bottom_color',
        array(
            'default'           => '#EAEAEA',
            'sanitize_callback' => 'sydney_sanitize_hex_rgba',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new Sydney_Alpha_Color_Border_Bottom(
            $wp_customize,
            'sydney_header_row__' . $row['id'] . '_border_bottom_color',
            array(
                'label'         	=> esc_html__( 'Border Bottom Color', 'sydney' ),
                'section'       	=> $row['section'],
                'priority'			=> 36
            )
        )
    );

    // Padding
    $wp_customize->add_setting( 
        'sydney_header_row__' . $row['id'] . '_padding_desktop',
        array(
            'default'           => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
            'sanitize_callback' => 'sydney_sanitize_text',
            'transport'         => 'postMessage'
        ) 
    );
    $wp_customize->add_setting( 
        'sydney_header_row__' . $row['id'] . '_padding_tablet',
        array(
            'default'           => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
            'sanitize_callback' => 'sydney_sanitize_text',
            'transport'         => 'postMessage'
        ) 
    );
    $wp_customize->add_setting( 
        'sydney_header_row__' . $row['id'] . '_padding_mobile',
        array(
            'default'           => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
            'sanitize_callback' => 'sydney_sanitize_text',
            'transport'         => 'postMessage'
        ) 
    );
    $wp_customize->add_control( 
        new Sydney_Dimensions_Control( 
            $wp_customize, 
            'sydney_header_row__' . $row['id'] . '_padding',
            array(
                'label'           	=> __( 'Padding', 'sydney' ),
                'section'         	=> $row['section'],
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
                    'desktop' => 'sydney_header_row__' . $row['id'] . '_padding_desktop',
                    'tablet'  => 'sydney_header_row__' . $row['id'] . '_padding_tablet',
                    'mobile'  => 'sydney_header_row__' . $row['id'] . '_padding_mobile'
                ),
                'priority'	      	 => 36
            )
        )
    );

    // Margin
    $wp_customize->add_setting( 
        'sydney_header_row__' . $row['id'] . '_margin_desktop',
        array(
            'default'           => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
            'sanitize_callback' => 'sydney_sanitize_text',
            'transport'         => 'postMessage'
        ) 
    );
    $wp_customize->add_setting( 
        'sydney_header_row__' . $row['id'] . '_margin_tablet',
        array(
            'default'           => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
            'sanitize_callback' => 'sydney_sanitize_text',
            'transport'         => 'postMessage'
        ) 
    );
    $wp_customize->add_setting( 
        'sydney_header_row__' . $row['id'] . '_margin_mobile',
        array(
            'default'           => '{ "unit": "px", "linked": false, "top": "", "right": "", "bottom": "", "left": "" }',
            'sanitize_callback' => 'sydney_sanitize_text',
            'transport'         => 'postMessage'
        ) 
    );
    $wp_customize->add_control( 
        new Sydney_Dimensions_Control( 
            $wp_customize, 
            'sydney_header_row__' . $row['id'] . '_margin',
            array(
                'label'           	=> __( 'Margin', 'sydney' ),
                'section'         	=> $row['section'],
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
                    'desktop' => 'sydney_header_row__' . $row['id'] . '_margin_desktop',
                    'tablet'  => 'sydney_header_row__' . $row['id'] . '_margin_tablet',
                    'mobile'  => 'sydney_header_row__' . $row['id'] . '_margin_mobile'
                ),
                'priority'	      	 => 36
            )
        )
    );

    /**
     * Sticky Header State
     * 
     */

    // Sticky Header - Title
    $wp_customize->add_setting( 
        'sydney_header_row__' . $row['id'] . '_sticky_title',
        array(
            'default' 			=> '',
            'sanitize_callback' => 'esc_attr'
        )
        );
        $wp_customize->add_control( 
        new Sydney_Text_Control( 
            $wp_customize, 
            'sydney_header_row__' . $row['id'] . '_sticky_title',
            array(
                'label'			  => esc_html__( 'Sticky Mode', 'sydney' ),
                'section' 		  => $row['section'],
                'active_callback' => 'sydney_callback_sticky_header',
                'priority'	 	  => 38
            )
        )
    );

    // Sticky Header - Background.
    $wp_customize->add_setting(
        'sydney_header_row__' . $row['id'] . '_sticky_background_color',
        array(
            'default'           => '',
            'sanitize_callback' => 'sydney_sanitize_hex_rgba',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new Sydney_Alpha_Color(
            $wp_customize,
            'sydney_header_row__' . $row['id'] . '_sticky_background_color',
            array(
                'label'         	=> esc_html__( 'Background Color', 'sydney' ),
                'section'       	=> $row['section'],
                'active_callback'   => 'sydney_callback_sticky_header',
                'priority'			=> 39
            )
        )
    );

    // Sticky Header - Border Bottom Color.
    $wp_customize->add_setting(
        'sydney_header_row__' . $row['id'] . '_sticky_border_bottom_color',
        array(
            'default'           => '#EAEAEA',
            'sanitize_callback' => 'sydney_sanitize_hex_rgba',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new Sydney_Alpha_Color(
            $wp_customize,
            'sydney_header_row__' . $row['id'] . '_sticky_border_bottom_color',
            array(
                'label'         	=> esc_html__( 'Border Bottom Color', 'sydney' ),
                'section'       	=> $row['section'],
                'active_callback'   => 'sydney_callback_sticky_header',
                'priority'			=> 40
            )
        )
    );
}

/**
 * Mobile Controls
 * Currently we only add mobile partials here to trigger the 'change'
 * javascript event and consequently do the selective refresh in the desired area.
 * 
 * For now no more controls than that are needed to be added here.
 */
foreach( $this->header_rows as $row ) {
    $wp_customize->add_setting(
        'sydney_header_row__mobile_' . $row['id'],
        array(
            'default'           => '',
            'sanitize_callback' => 'sydney_sanitize_text',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        'sydney_header_row__mobile_' . $row['id'],
        array(
            'type'     => 'text',
            /* translators: 1: Mobile row identifier. */
            'label'    => sprintf( esc_html__( 'Mobile - %s', 'sydney' ), $row['label'] ),
            'section'  => $row['section'],
            'settings' => 'sydney_header_row__mobile_' . $row['id'],
            'priority' => 10
        )
    );

    // Selective Refresh
    if ( isset( $wp_customize->selective_refresh ) ) {
        $wp_customize->selective_refresh->add_partial(
            'sydney_header_row__mobile_' . $row['id'],
            array(
                'selector'        => '.shfb-mobile .shfb-' . $row['id'],
                'render_callback' => function() use( $row ) {
                    $this->rows_callback( 'header', $row['id'], 'mobile' ); // phpcs:ignore PHPCompatibility.FunctionDeclarations.NewClosure.ThisFoundOutsideClass
                },
            )
        );
    }
}

// @codingStandardsIgnoreEnd WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound