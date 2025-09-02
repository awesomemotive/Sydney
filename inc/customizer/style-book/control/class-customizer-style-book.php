<?php
/**
 * Singleton class for handling the theme's customizer integration.
 *
 * @since  1.0.0
 * @access public
 */
final class Sydney_Customizer_Style_Book {

	/**
	 * Returns the instance.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return object
	 */
	public static function get_instance() {

		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new self();
			$instance->setup_actions();
		}

		return $instance;
	}

	/**
	 * Constructor method.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return void
	 */
	private function __construct() {}

	/**
	 * Sets up initial actions.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return void
	 */
	private function setup_actions() {

		// Register panels, sections, settings, controls, and partials.
		add_action( 'customize_register', array( $this, 'sections' ) );

		// Register scripts and styles for the controls.
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_control_scripts' ), 0 );
	}

	/**
	 * Sets up the customizer sections.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  object  $manager
	 * @return void
	 */
	public function sections( $manager ) {

		// Load custom sections.
		require_once( trailingslashit( get_template_directory() ) . 'inc/customizer/style-book/control/section-style-book.php' );

		// Register custom section types.
		$manager->register_section_type( 'Sydney_Customize_StyleBook_Toggle' );

		// Register sections.
		$manager->add_section(
			new Sydney_Customize_StyleBook_Toggle(
				$manager,
				'sydney_stylebook_toggle',
				array(
					'title'    => esc_html__( 'Open Style Book', 'sydney' ),
					'priority' => -998,
				)
			)
		);
	}

	/**
	 * Loads theme customizer JS.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function enqueue_control_scripts() {
		wp_enqueue_script( 'sydney-stylebook-customize-controls', trailingslashit( get_template_directory_uri() ) . 'inc/customizer/style-book/control/customize-controls.js', array( 'customize-controls' ), '20250902', true );
	}
}

Sydney_Customizer_Style_Book::get_instance();
