<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @package    Credipost
 * @subpackage Credipost/public
 */

if ( ! defined( 'ABSPATH' ) && ! defined( 'WPINC' ) ) {
	exit;
}

class Credipost_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @var string
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the public stylesheets and JavaScript ONLY when needed.
	 */
	public function enqueue_assets() {
		wp_enqueue_style(
			$this->plugin_name . '-public',
			plugin_dir_url( __FILE__ ) . 'css/credipost-public.css',
			array( 'dashicons' ),
			$this->version,
			'all'
		);

		// Uses core jQuery - never replaces it!
		wp_enqueue_script(
			$this->plugin_name . '-public',
			plugin_dir_url( __FILE__ ) . 'js/credipost-public.js',
			array( 'jquery' ),
			$this->version,
			true
		);

		wp_localize_script(
			$this->plugin_name . '-public',
			'credipostPublic',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'credipost_admin_nonce' ),
				'i18n'    => array(
					'fetching'      => __( 'Fetching content...', 'credipost' ),
					'saving'        => __( 'Saving post...', 'credipost' ),
					'postSaved'     => __( 'Post successfully saved!', 'credipost' ),
					'emptyUrl'      => __( 'Please enter a valid URL.', 'credipost' ),
					'genericError'  => __( 'An error occurred. Please try again.', 'credipost' ),
					'removeBlock'   => __( 'Remove block', 'credipost' ),
					'undo'          => __( 'Undo', 'credipost' ),
					'noContent'     => __( 'No content found.', 'credipost' ),
					'embedNotFound' => __( 'Could not embed video URL.', 'credipost' ),
				),
			)
		);
	}

	/**
	 * Render the [CREDIPOST] shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string Rendered HTML.
	 */
	public function render_shortcode( $atts ) {
		// Only users who can edit posts should be able to curate/publish posts.
		if ( ! current_user_can( 'edit_posts' ) ) {
			return '<p class="credipost-auth-notice">' . esc_html__( 'You must be logged in with author permissions to use Credipost.', 'credipost' ) . '</p>';
		}

		// Enqueue assets dynamically when shortcode is rendered.
		$this->enqueue_assets();

		$categories = get_categories( array(
			'orderby'    => 'name',
			'order'      => 'ASC',
			'hide_empty' => false,
		) );

		$sites = array();
		if ( is_multisite() ) {
			$sites = get_sites( array( 'number' => 100 ) );
		}

		ob_start();
		include plugin_dir_path( __FILE__ ) . 'partials/credipost-public-display.php';
		return ob_get_clean();
	}
}
