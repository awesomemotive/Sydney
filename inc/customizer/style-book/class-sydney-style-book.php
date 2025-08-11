<?php
/**
 * Sydney Style Book
 * 
 * Used in the Customizer.
 *
 * @package Sydney
 */

if ( ! class_exists( 'Sydney_Style_Book' ) ) :
    class Sydney_Style_Book {

        /**
         * Config.
         */
        public $color_config        = array();
        public $typography_config   = array();
        
        /**
         * Constructor.
         */
        public function __construct() {
            $this->color_config      = $this->color_config();
            $this->typography_config = $this->typography_config();

            add_action( 'customize_controls_print_footer_scripts', array( $this, 'template' ) );
            add_action( 'customize_controls_print_footer_scripts', array( $this, 'enqueue' ) );
        }

        public function enqueue() {
            wp_enqueue_style( 'sydney-style-book', get_stylesheet_directory_uri() . '/inc/customizer/style-book/css/styles.min.css' );
            wp_enqueue_script( 'sydney-style-book', get_stylesheet_directory_uri() . '/inc/customizer/style-book/js/scripts.min.js', array( 'customize-controls' ), false, true );

            /**
             * Dynamic CSS
             */
            $custom = '';

            /* Images */
			$image_border_radius = get_theme_mod('image_border_radius', 0);
			$custom .= ".sydney-style-book-section-content img { border-radius:" . intval($image_border_radius) . "px;}" . "\n";
            $custom .= Sydney_Custom_CSS::get_font_sizes_css('image_caption_font_size', $defaults = array('desktop' => 16, 'tablet' => 16, 'mobile' => 16), '.sydney-style-book-section-content figcaption');
			$custom .= Sydney_Custom_CSS::get_color_css('image_caption_color', '', '.sydney-style-book-section-content figcaption');

            /* Buttons */
            $custom .= Sydney_Custom_CSS::get_top_bottom_padding_css( 'button_top_bottom_padding', $defaults = array( 'desktop' => 12, 'tablet' => 12, 'mobile' => 12 ), '.sydney-style-book-section-content .roll-button' );
            $custom .= Sydney_Custom_CSS::get_left_right_padding_css( 'button_left_right_padding', $defaults = array( 'desktop' => 35, 'tablet' => 35, 'mobile' => 35 ), '.sydney-style-book-section-content .roll-button' );

            $buttons_radius = get_theme_mod( 'buttons_radius', 3 );
            $custom .= ".sydney-style-book-section-content .roll-button { border-radius:" . intval( $buttons_radius ) . "px;}" . "\n";

            $custom .= Sydney_Custom_CSS::get_font_sizes_css( 'button_font_size_desktop', $defaults = array( 'desktop' => 13, 'tablet' => 13, 'mobile' => 13 ), '.sydney-style-book-section-content .roll-button' );
            $button_text_transform = get_theme_mod( 'button_text_transform', 'uppercase' );
            $custom .= ".sydney-style-book-section-content .roll-button { text-transform:" . esc_attr( $button_text_transform ) . ";}" . "\n";

            $custom .= Sydney_Custom_CSS::get_background_color_css( 'button_background_color', '', '.sydney-style-book-section-content .roll-button' );			
            
            $custom .= Sydney_Custom_CSS::get_background_color_css( 'button_background_color_hover', '', '.sydney-style-book-section-content .roll-button:hover' );			

            $custom .= Sydney_Custom_CSS::get_color_css( 'button_color', '#ffffff', '.sydney-style-book-section-content .roll-button' );			
            $custom .= Sydney_Custom_CSS::get_color_css( 'button_color_hover', '#ffffff', '.sydney-style-book-section-content .roll-button:hover' );			

            $button_border_color = get_theme_mod( 'button_border_color', '' );
            $button_border_color_hover = get_theme_mod( 'button_border_color_hover', '' );
            $custom .= ".sydney-style-book-section-content .roll-button { border-color:" . esc_attr( $button_border_color ) . ";}" . "\n";
            $custom .= ".sydney-style-book-section-content .roll-button:hover { border-color:" . esc_attr( $button_border_color_hover ) . ";}" . "\n";
            
            /* Typography */
            //Families
            $typography_defaults = json_encode(
                array(
                    'font' 			=> 'System default',
                    'regularweight' => '400',
                    'category' 		=> 'sans-serif'
                )
            );
            $body_font		= get_theme_mod( 'sydney_body_font', $typography_defaults );
            $headings_font 	= get_theme_mod( 'sydney_headings_font', $typography_defaults );
        
            $body_font 		= json_decode( $body_font, true );
            $headings_font 	= json_decode( $headings_font, true );

            $custom .= ".sydney-style-book-section-content .style-book-body { font-family:" . esc_attr( $body_font['font'] ) . ',' . esc_attr( $body_font['category'] ) . '; font-weight: ' . esc_attr( $body_font['regularweight'] ) . ';}' . "\n";
            $custom .= ".sydney-style-book-section-content h1.style-book-heading, .sydney-style-book-section-content h2.style-book-heading, .sydney-style-book-section-content h3.style-book-heading, .sydney-style-book-section-content h4.style-book-heading, .sydney-style-book-section-content h5.style-book-heading, .sydney-style-book-section-content h6.style-book-heading { font-family:" . esc_attr( $headings_font['font'] ) . ',' . esc_attr( $headings_font['category'] ) . '; font-weight: ' . esc_attr( $headings_font['regularweight'] ) . ';}' . "\n";

            //Headings styling
            $custom .= Sydney_Custom_CSS::get_font_sizes_css( 'h1_font_size', $defaults = array( 'desktop' => 48, 'tablet' => 42, 'mobile' => 32 ), '.sydney-style-book-section-content h1.style-book-heading' );
            $custom .= Sydney_Custom_CSS::get_font_sizes_css( 'h2_font_size', $defaults = array( 'desktop' => 38, 'tablet' => 32, 'mobile' => 24 ), '.sydney-style-book-section-content h2.style-book-heading' );
            $custom .= Sydney_Custom_CSS::get_font_sizes_css( 'h3_font_size', $defaults = array( 'desktop' => 32, 'tablet' => 24, 'mobile' => 20 ), '.sydney-style-book-section-content h3.style-book-heading' );
            $custom .= Sydney_Custom_CSS::get_font_sizes_css( 'h4_font_size', $defaults = array( 'desktop' => 24, 'tablet' => 18, 'mobile' => 16 ), '.sydney-style-book-section-content h4.style-book-heading' );
            $custom .= Sydney_Custom_CSS::get_font_sizes_css( 'h5_font_size', $defaults = array( 'desktop' => 20, 'tablet' => 16, 'mobile' => 16 ), '.sydney-style-book-section-content h5.style-book-heading' );
            $custom .= Sydney_Custom_CSS::get_font_sizes_css( 'h6_font_size', $defaults = array( 'desktop' => 16, 'tablet' => 16, 'mobile' => 16 ), '.sydney-style-book-section-content h6.style-book-heading' );
        
            $headings_line_height = get_theme_mod( 'headings_line_height', 1.2 );
            $custom .= ".sydney-style-book-section-content h1.style-book-heading, .sydney-style-book-section-content h2.style-book-heading, .sydney-style-book-section-content h3.style-book-heading, .sydney-style-book-section-content h4.style-book-heading, .sydney-style-book-section-content h5.style-book-heading, .sydney-style-book-section-content h6.style-book-heading { line-height:" . esc_attr( $headings_line_height ) . ";}" . "\n";
            
            //Body styling
            $custom .= Sydney_Custom_CSS::get_font_sizes_css( 'body_font_size', $defaults = array( 'desktop' => 16, 'tablet' => 16, 'mobile' => 16 ), '.sydney-style-book-section-content .style-book-body' );            

            $body_font_style 		= get_theme_mod( 'body_font_style' );
            $body_line_height 		= get_theme_mod( 'body_line_height', 1.68 );
            $body_letter_spacing 	= get_theme_mod( 'body_letter_spacing' );
            $body_text_transform 	= get_theme_mod( 'body_text_transform' );
            $body_text_decoration 	= get_theme_mod( 'body_text_decoration' );
        
            $custom .= ".sydney-style-book-section-content .style-book-body { text-transform:" . esc_attr( $body_text_transform ) . ";font-style:" . esc_attr( $body_font_style ) . ";line-height:" . esc_attr( $body_line_height ) . ";letter-spacing:" . esc_attr( $body_letter_spacing ) . "px;}" . "\n";	
        

            wp_add_inline_style( 'sydney-style-book', $custom );
        }

        /**
         * Template.
         */
        public function template() {
            ?>
            <script type="text/template" id="tmpl-sydney-style-book">
                <div class="sydney-style-book wp-full-overlay expanded">
                    <div class="sydney-style-book-header">
                        <h1><?php esc_html_e( 'Style Book', 'sydney' ); ?></h1>
                        <div class="style-book-light-text"><?php esc_html_e( 'An interactive visual guide of the theme\'s base styles.', 'sydney' ); ?></div>
                        <span class="sydney-style-book-close">
                            <span class="dashicons dashicons-no-alt"></span>
                        </span>
                    </div>
                    <div class="sydney-style-book-body">
                        <div class="sydney-style-book-navigation">
                            <a href="#section-colors" class="sydney-style-book-nav-link" data-section="colors"><?php esc_html_e( 'Global Colors', 'sydney' ); ?></a>
                            <a href="#section-buttons" class="sydney-style-book-nav-link" data-section="buttons"><?php esc_html_e( 'Buttons', 'sydney' ); ?></a>
                            <a href="#section-typography" class="sydney-style-book-nav-link" data-section="typography"><?php esc_html_e( 'Typography', 'sydney' ); ?></a>
                            <a href="#section-media" class="sydney-style-book-nav-link" data-section="media"><?php esc_html_e( 'Media', 'sydney' ); ?></a>
                            <a href="#section-forms" class="sydney-style-book-nav-link" data-section="forms"><?php esc_html_e( 'Forms', 'sydney' ); ?></a>
                            <a href="#section-lists" class="sydney-style-book-nav-link" data-section="lists"><?php esc_html_e( 'Lists', 'sydney' ); ?></a>
                            <a href="#section-tables" class="sydney-style-book-nav-link" data-section="tables"><?php esc_html_e( 'Tables', 'sydney' ); ?></a>
                            <a href="#section-quotes" class="sydney-style-book-nav-link" data-section="quotes"><?php esc_html_e( 'Quotes', 'sydney' ); ?></a>
                        </div>
                        <div class="sydney-style-book-content">
                            <?php $this->get_section( 'colors' ); ?>
                            <?php $this->get_section( 'buttons' ); ?>
                            <?php $this->get_section( 'typography' ); ?>
                            <?php $this->get_section( 'media' ); ?>
                            <?php $this->get_section( 'forms' ); ?>
                            <?php $this->get_section( 'lists' ); ?>
                            <?php $this->get_section( 'tables' ); ?>
                            <?php $this->get_section( 'quotes' ); ?>
                        </div>
                    </div>
                </div>
            </script>
        <?php
        }

        /**
         * Config.
         */
        public function color_config() {
            $config = array();

            //Colors
            $global_colors = sydney_get_global_colors();
            $global_colors_data = array(
                'global_color_1' => array(
                    'label' => esc_html__( 'Color 1', 'sydney' ),
                    'info'  => esc_html__( 'Primary color, used for accents, buttons, etc.', 'sydney' ),
                ),
                'global_color_2' => array(
                    'label' => esc_html__( 'Color 2', 'sydney' ),
                    'info'  => esc_html__( 'Hover color', 'sydney' ),
                ),
                'global_color_3' => array(
                    'label' => esc_html__( 'Color 3', 'sydney' ),
                    'info'  => esc_html__( 'Body text color', 'sydney' ),
                ),
                'global_color_4' => array(
                    'label' => esc_html__( 'Color 4', 'sydney' ),
                    'info'  => esc_html__( 'General headings color', 'sydney' ),
                ),
                'global_color_5' => array(
                    'label' => esc_html__( 'Color 5', 'sydney' ),
                    'info'  => esc_html__( 'Used for smaller text, like post meta', 'sydney' ),
                ),
                'global_color_6' => array(
                    'label' => esc_html__( 'Color 6', 'sydney' ),
                    'info'  => esc_html__( 'Dark backgrounds, like the header & footer', 'sydney' ),
                ),
                'global_color_7' => array(
                    'label' => esc_html__( 'Color 7', 'sydney' ),
                    'info'  => esc_html__( 'Light backgrounds', 'sydney' ),
                ),
                'global_color_8' => array(
                    'label' => esc_html__( 'Color 8', 'sydney' ),
                    'info'  => esc_html__( 'Border color', 'sydney' ),
                ),
                'global_color_9' => array(
                    'label' => esc_html__( 'Color 9', 'sydney' ),
                    'info'  => esc_html__( 'Site background color', 'sydney' ),
                ),
            );

            $config['colors'] = array();

            foreach ( $global_colors as $slug => $color_value ) {
                $config['colors'][ $slug ] = array(
                    'label' => $global_colors_data[ $slug ]['label'],
                    'info'  => $global_colors_data[ $slug ]['info'],
                    'value' => $color_value,
                );
            }

            return $config;
        }

        /**
         * Typography config.
         */
        public function typography_config() {
            $config = array(
                'headings' => array(),
                'body' => array(),
            );

            //Headings
            //we currently only support one font for all headings
            $headings_font = get_theme_mod( 'sydney_headings_font' );
            if ( false !== $headings_font ) {
                $headings = json_decode( $headings_font, true );
            } else {
                $headings = array(
                    'font' => 'System default',
                    'regularweight' => 400,
                );
            }
            
            if ( 'regular' === $headings['regularweight'] ) {
                $headings['regularweight'] = 400;
            }

            $config['headings'] = array(
                'h1' => array(
                    'family' => $headings['font'],
                    'weight' => $headings['regularweight'],
                    'size'   => get_theme_mod( 'h1_font_size_desktop' ),
                ),
                'h2' => array(
                    'family' => $headings['font'],
                    'weight' => $headings['regularweight'],
                    'size'   => get_theme_mod( 'h2_font_size_desktop' ),
                ),
                'h3' => array(
                    'family' => $headings['font'],
                    'weight' => $headings['regularweight'],
                    'size'   => get_theme_mod( 'h3_font_size_desktop' ),
                ),
                'h4' => array(
                    'family' => $headings['font'],
                    'weight' => $headings['regularweight'],
                    'size'   => get_theme_mod( 'h4_font_size_desktop' ),
                ),
                'h5' => array(
                    'family' => $headings['font'],
                    'weight' => $headings['regularweight'],
                    'size'   => get_theme_mod( 'h5_font_size_desktop' ),
                ),
                'h6' => array(
                    'family' => $headings['font'],
                    'weight' => $headings['regularweight'],
                    'size'   => get_theme_mod( 'h6_font_size_desktop' ),
                ),
            );

            //Body
            $body_font = get_theme_mod( 'sydney_body_font' );
            if ( false !== $body_font ) {
                $body = json_decode( get_theme_mod( 'sydney_body_font' ), true );
            } else {
                $body = array(
                    'font' => 'System default',
                    'regularweight' => 400,
                );
            }
            
            if ( 'regular' === $body['regularweight'] ) {
                $body['regularweight'] = 400;
            }

            $config['body'] = array(
                'family' => $body['font'],
                'weight' => $body['regularweight'],
                'size'   => get_theme_mod( 'body_font_size_desktop' ),
            );

            return $config;
        }

        /**
         * Get section.
         */
        public function get_section( $section ) {
            if ( method_exists( $this, "{$section}_section" ) ) {
                call_user_func( array( $this, "{$section}_section" ) );
            }
        }

        /**
         * Colors section.
         */
        public function colors_section() {
            $config = $this->color_config;
            ?>
            <div id="section-colors" class="sydney-style-book-section" data-section="colors">
                <div class="sydney-style-book-section-header">
                    <h2 class="sydney-style-book-section-title"><?php esc_html_e( 'Global Colors', 'sydney' ); ?></h2>
                </div>
                <div class="sydney-style-book-section-content">
                    <?php foreach ( $config['colors'] as $slug => $color ) : ?>
                        <a class="sydney-style-book-color sydney-style-book-customizer-link" href="javascript:wp.customize.control( 'custom_palette' ).focus();">
                            <div class="sydney-style-book-color-value" data-color-setting="<?php echo $slug; ?>" style="background-color: <?php echo esc_attr( $color['value'] ); ?>"></div>
                            <div class="sydney-style-book-color-label"><?php echo esc_html( $color['label'] ); ?></div>
                            <div class="sydney-style-book-color-tooltip"><?php echo esc_html( $color['info'] ); ?></div>
                            <?php $this->render_edit_overlay(); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php
        }

        /**
         * Buttons section.
         */
        public function buttons_section() {
            ?>
            <div id="section-buttons" class="sydney-style-book-section" data-section="buttons">
                <div class="sydney-style-book-section-header">
                    <h2 class="sydney-style-book-section-title"><?php esc_html_e( 'Buttons', 'sydney' ); ?></h2>
                </div>
                <div class="sydney-style-book-section-content">
                    <a href="javascript:wp.customize.section( 'sydney_section_buttons' ).focus();" class="roll-button sydney-style-book-customizer-link">
                        <?php echo esc_html__( 'Button', 'sydney' ); ?>
                        <?php $this->render_edit_overlay(); ?>
                    </a>
                </div>
            </div>
            <?php
        }

        /**
         * Typography section.
         */
        public function typography_section() {
            ?>
            <div id="section-typography" class="sydney-style-book-section" data-section="typography">
                <div class="sydney-style-book-section-header">
                    <h2 class="sydney-style-book-section-title"><?php esc_html_e( 'Typography', 'sydney' ); ?></h2>
                </div>
                <div class="sydney-style-book-section-content">
                    <div class="sydney-style-book-typography-headings">
                        <h3 class="sydney-style-book-subsection-title"><?php echo esc_html__( 'Headings', 'sydney' ); ?></h3>
                        <a href="javascript:wp.customize.section( 'sydney_section_typography_headings' ).focus();" class="sydney-style-book-customizer-link">
                            <?php $this->get_typography_data( 'headings', 'h1' ); ?>
                            <h1 class="style-book-heading"><?php esc_html_e( 'Heading 1', 'sydney' ); ?></h1>
                            <?php $this->render_edit_overlay(); ?>
                        </a>
                        <a href="javascript:wp.customize.section( 'sydney_section_typography_headings' ).focus();" class="sydney-style-book-customizer-link">
                            <?php $this->get_typography_data( 'headings', 'h2' ); ?>
                            <h2 class="style-book-heading"><?php esc_html_e( 'Heading 2', 'sydney' ); ?></h2>
                            <?php $this->render_edit_overlay(); ?>
                        </a>
                        <a href="javascript:wp.customize.section( 'sydney_section_typography_headings' ).focus();" class="sydney-style-book-customizer-link">
                            <?php $this->get_typography_data( 'headings', 'h3' ); ?>
                            <h3 class="style-book-heading"><?php esc_html_e( 'Heading 3', 'sydney' ); ?></h3>
                            <?php $this->render_edit_overlay(); ?>
                        </a>
                        <a href="javascript:wp.customize.section( 'sydney_section_typography_headings' ).focus();" class="sydney-style-book-customizer-link">
                            <?php $this->get_typography_data( 'headings', 'h4' ); ?>
                            <h4 class="style-book-heading"><?php esc_html_e( 'Heading 4', 'sydney' ); ?></h4>
                            <?php $this->render_edit_overlay(); ?>
                        </a>
                        <a href="javascript:wp.customize.section( 'sydney_section_typography_headings' ).focus();" class="sydney-style-book-customizer-link">
                            <?php $this->get_typography_data( 'headings', 'h5' ); ?>
                            <h5 class="style-book-heading"><?php esc_html_e( 'Heading 5', 'sydney' ); ?></h5>
                            <?php $this->render_edit_overlay(); ?>
                        </a>
                        <a href="javascript:wp.customize.section( 'sydney_section_typography_headings' ).focus();" class="sydney-style-book-customizer-link">
                            <?php $this->get_typography_data( 'headings', 'h6' ); ?>
                            <h6 class="style-book-heading"><?php esc_html_e( 'Heading 6', 'sydney' ); ?></h6>
                            <?php $this->render_edit_overlay(); ?>
                        </a>
                    </div>
                    <div class="sydney-style-book-typography-body">
                        <h3 class="sydney-style-book-subsection-title"><?php echo esc_html__( 'Body Text', 'sydney' ); ?></h3>
                        <a href="javascript:wp.customize.section( 'sydney_section_typography_body' ).focus();" class="sydney-style-book-customizer-link">
                            <?php $this->get_typography_data( 'body' ); ?>
                            <p class="style-book-body">
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam porttitor dui et gravida hendrerit. Praesent et odio a dolor ultrices bibendum. Sed lacinia ante vitae odio mollis, sit amet venenatis arcu porttitor. Aenean id ligula sit amet nibh rutrum ullamcorper ac et urna.
                            </p>
                            <?php $this->render_edit_overlay(); ?>
                        </a>
                    </div>
                </div>
            </div>
            <?php
        }

        /**
         * Media section.
         */
        public function media_section() {
            ?>
            <div id="section-media" class="sydney-style-book-section" data-section="media">
                <div class="sydney-style-book-section-header">
                    <h2 class="sydney-style-book-section-title"><?php esc_html_e( 'Media', 'sydney' ); ?></h2>
                </div>
                <div class="sydney-style-book-section-content">
                    <a href="javascript:wp.customize.section( 'sydney_section_images' ).focus();" class="sydney-style-book-customizer-link">
                        <div class="sydney-style-book-media">
                            <figure>
                                <img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/images/stylebook.jpg' ); ?>" alt="<?php esc_attr_e( 'Media Image', 'sydney' ); ?>">
                                <figcaption><?php esc_html_e( 'Image Caption', 'sydney' ); ?></figcaption>   
                            </figure>
                        </div>
                        <?php $this->render_edit_overlay(); ?>
                    </a>
                </div>
            </div>
            <?php
        }

        /**
         * Forms section.
         */
        public function forms_section() {
            ?>
            <div id="section-forms" class="sydney-style-book-section" data-section="forms">
                <div class="sydney-style-book-section-header">
                    <h2 class="sydney-style-book-section-title"><?php esc_html_e( 'Forms', 'sydney' ); ?></h2>
                    <?php if ( !defined( 'SYDNEY_PRO_VERSION' ) ) : ?>
                    <a href="<?php echo esc_url( sydney_admin_upgrade_link( 'https://athemes.com/sydney-upgrade', array( 'utm_source' => 'style_book', 'utm_medium' => 'sydney_customizer', 'utm_campaign' => 'Sydney' ), 'style-book-requires-pro-link' ) ); ?>" target="_blank"><?php esc_html_e( 'Requires Sydney Pro.', 'sydney' ); ?></a>
                    <?php endif; ?>
                </div>
                <div class="sydney-style-book-section-content <?php defined( 'SYDNEY_PRO_VERSION' ) ? '' : 'sydney-style-book-disabled'; ?>">
                    <a href="javascript:wp.customize.section( 'sydney_section_html_designer' ).focus();" class="sydney-style-book-customizer-link">
                        <div class="sydney-style-book-form">
                            <form>
                                <input type="text" placeholder="<?php esc_attr_e( 'Your Name', 'sydney' ); ?>">
                                <input type="email" placeholder="<?php esc_attr_e( 'Your Email', 'sydney' ); ?>">
                                <textarea placeholder="<?php esc_attr_e( 'Your Message', 'sydney' ); ?>"></textarea>
                                <button class="roll-button"><?php esc_html_e( 'Submit', 'sydney' ); ?></button>
                            </form>
                        </div>
                        <?php $this->render_edit_overlay( $is_pro_section = true); ?>
                    </a>
                </div>
            </div>
            <?php
        }

        /**
         * Lists section.
         */
        public function lists_section() {
            ?>
            <div id="section-lists" class="sydney-style-book-section" data-section="lists">
                <div class="sydney-style-book-section-header">
                    <h2 class="sydney-style-book-section-title"><?php esc_html_e( 'Lists', 'sydney' ); ?></h2>
                    <?php if ( !defined( 'SYDNEY_PRO_VERSION' ) ) : ?>
                    <a href="<?php echo esc_url( sydney_admin_upgrade_link( 'https://athemes.com/sydney-upgrade', array( 'utm_source' => 'style_book', 'utm_medium' => 'sydney_customizer', 'utm_campaign' => 'Sydney' ), 'style-book-requires-pro-link' ) ); ?>" target="_blank"><?php esc_html_e( 'Requires Sydney Pro.', 'sydney' ); ?></a>
                    <?php endif; ?>
                </div>
                <div class="sydney-style-book-section-content <?php defined( 'SYDNEY_PRO_VERSION' ) ? '' : 'sydney-style-book-disabled'; ?>">
                    <a href="javascript:wp.customize.section( 'sydney_section_html_designer' ).focus();" class="sydney-style-book-customizer-link">
                        <div class="sydney-style-book-lists">
                            <ul>
                                <li><?php esc_html_e( 'List Item 1', 'sydney' ); ?></li>
                                <li><?php esc_html_e( 'List Item 2', 'sydney' ); ?></li>
                                <li><?php esc_html_e( 'List Item 3', 'sydney' ); ?></li>
                            </ul>
                            <ol>
                                <li><?php esc_html_e( 'List Item 1', 'sydney' ); ?></li>
                                <li><?php esc_html_e( 'List Item 2', 'sydney' ); ?></li>
                                <li><?php esc_html_e( 'List Item 3', 'sydney' ); ?></li>
                            </ol>
                        </div>
                        <?php $this->render_edit_overlay( $is_pro_section = true); ?>
                    </a>
                </div>
            </div>
            <?php
        }

        /**
         * Tables section.
         */
        public function tables_section() {
            ?>
            <div id="section-tables" class="sydney-style-book-section" data-section="tables">
                <div class="sydney-style-book-section-header">
                    <h2 class="sydney-style-book-section-title"><?php esc_html_e( 'Tables', 'sydney' ); ?></h2>
                    <?php if ( !defined( 'SYDNEY_PRO_VERSION' ) ) : ?>
                    <a href="<?php echo esc_url( sydney_admin_upgrade_link( 'https://athemes.com/sydney-upgrade', array( 'utm_source' => 'style_book', 'utm_medium' => 'sydney_customizer', 'utm_campaign' => 'Sydney' ), 'style-book-requires-pro-link' ) ); ?>" target="_blank"><?php esc_html_e( 'Requires Sydney Pro.', 'sydney' ); ?></a>
                    <?php endif; ?>
                </div>
                <div class="sydney-style-book-section-content <?php defined( 'SYDNEY_PRO_VERSION' ) ? '' : 'sydney-style-book-disabled'; ?>">
                    <a href="javascript:wp.customize.section( 'sydney_section_html_designer' ).focus();" class="sydney-style-book-customizer-link">
                        <div class="sydney-style-book-tables">
                            <table>
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e( 'Table Heading 1', 'sydney' ); ?></th>
                                        <th><?php esc_html_e( 'Table Heading 2', 'sydney' ); ?></th>
                                        <th><?php esc_html_e( 'Table Heading 3', 'sydney' ); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><?php esc_html_e( 'Table Data 1', 'sydney' ); ?></td>
                                        <td><?php esc_html_e( 'Table Data 2', 'sydney' ); ?></td>
                                        <td><?php esc_html_e( 'Table Data 3', 'sydney' ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><?php esc_html_e( 'Table Data 1', 'sydney' ); ?></td>
                                        <td><?php esc_html_e( 'Table Data 2', 'sydney' ); ?></td>
                                        <td><?php esc_html_e( 'Table Data 3', 'sydney' ); ?></td>
                                    </tr>
                                    <tr>
                                        <td><?php esc_html_e( 'Table Data 1', 'sydney' ); ?></td>
                                        <td><?php esc_html_e( 'Table Data 2', 'sydney' ); ?></td>
                                        <td><?php esc_html_e( 'Table Data 3', 'sydney' ); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <?php $this->render_edit_overlay( $is_pro_section = true); ?>
                    </a>
                </div>
            </div>
            <?php
        }

        /**
         * Quotes section.
         */
        public function quotes_section() {
            ?>
            <div id="section-quotes" class="sydney-style-book-section" data-section="quotes">
                <div class="sydney-style-book-section-header">
                    <h2 class="sydney-style-book-section-title"><?php esc_html_e( 'Quotes', 'sydney' ); ?></h2>
                    <?php if ( !defined( 'SYDNEY_PRO_VERSION' ) ) : ?>
                    <a href="<?php echo esc_url( sydney_admin_upgrade_link( 'https://athemes.com/sydney-upgrade', array( 'utm_source' => 'style_book', 'utm_medium' => 'sydney_customizer', 'utm_campaign' => 'Sydney' ), 'style-book-requires-pro-link' ) ); ?>" target="_blank"><?php esc_html_e( 'Requires Sydney Pro.', 'sydney' ); ?></a>
                    <?php endif; ?>
                </div>
                <div class="sydney-style-book-section-content <?php defined( 'SYDNEY_PRO_VERSION' ) ? '' : 'sydney-style-book-disabled'; ?>">
                    <a href="javascript:wp.customize.section( 'sydney_section_html_designer' ).focus();" class="sydney-style-book-customizer-link">
                        <div class="sydney-style-book-quotes">
                            <blockquote>
                                <p><?php esc_html_e( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam porttitor dui et gravida hendrerit.', 'sydney' ); ?></p>
                                <cite><?php esc_html_e( 'Author Name', 'sydney' ); ?></cite>
                            </blockquote>
                        </div>
                        <?php $this->render_edit_overlay( $is_pro_section = true); ?>
                    </a>
                </div>
            </div>
            <?php
        }

        /**
         * Get typography data.
         */
        public function get_typography_data( $type, $tag = false ) {
            $config = $this->typography_config;
            if ( 'headings' === $type ) : ?>
                <div class="style-book-light-text style-book-headings-typography-data" data-heading="<?php echo esc_attr( $tag ); ?>"><?php echo wp_kses_post( '<span class="style-book-typography-family">' . $config['headings'][$tag]['family'] . '</span> / <span class="style-book-typography-weight">' . $config['headings'][$tag]['weight'] . '</span> / <span class="style-book-typography-size">' . $config['headings'][$tag]['size'] . 'px' . '</span>' ); ?></div>
            <?php else : ?>
                <div class="style-book-light-text style-book-body-typography-data"><?php echo wp_kses_post( '<span class="style-book-typography-family">' . $config['body']['family'] . '</span> / <span class="style-book-typography-weight">' . $config['body']['weight'] . '</span> / <span class="style-book-typography-size">' . $config['body']['size'] . 'px' . '</span>' ); ?></div>
            <?php endif;
        }

        /**
         * Render edit overlay.
         */
        public function render_edit_overlay( $is_pro_section = false ) {
            if ( $is_pro_section && !defined( 'SYDNEY_PRO_VERSION' ) ) {
                ?>
                <div class="sydney-style-book-edit-overlay">
                    <i class="dashicons dashicons-lock"></i>
                </div>
                <?php
            } else {
                ?>
                <div class="sydney-style-book-edit-overlay">
                    <i class="dashicons dashicons-edit"></i>
                </div>
                <?php
            }
        }
    }
    new Sydney_Style_Book;
endif;