<?php
/**
 * The core plugin class orchestrating hooks and dependencies.
 *
 * @package    Credipost
 * @subpackage Credipost/includes
 */

if ( ! defined( 'ABSPATH' ) && ! defined( 'WPINC' ) ) {
	exit;
}

class Credipost {

	/**
	 * Maintains and registers all hooks for the plugin.
	 *
	 * @var Credipost_Loader $loader
	 */
	protected $loader;

	/**
	 * Unique identifier of this plugin.
	 *
	 * @var string $plugin_name
	 */
	protected $plugin_name;

	/**
	 * Current version of the plugin.
	 *
	 * @var string $version
	 */
	protected $version;

	/**
	 * Initialize the core plugin.
	 */
	public function __construct() {
		if ( defined( 'CREDIPOST_VERSION' ) ) {
			$this->version = CREDIPOST_VERSION;
		} else {
			$this->version = '2.0.0';
		}
		$this->plugin_name = 'credipost';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load required dependencies.
	 */
	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-credipost-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-credipost-i18n.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-credipost-scraper.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-credipost-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-credipost-public.php';

		$this->loader = new Credipost_Loader();
	}

	/**
	 * Define the locale for internationalization.
	 */
	private function set_locale() {
		$plugin_i18n = new Credipost_i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all hooks related to the admin area.
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Credipost_Admin( $this->get_plugin_name(), $this->get_version() );

		// Menu & Assets
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_admin_menu' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_assets' );

		// AJAX Endpoints
		$this->loader->add_action( 'wp_ajax_credipost_scrape_url', $plugin_admin, 'ajax_scrape_url' );
		$this->loader->add_action( 'wp_ajax_credipost_get_embed', $plugin_admin, 'ajax_get_embed' );
		$this->loader->add_action( 'wp_ajax_credipost_save_post', $plugin_admin, 'ajax_save_post' );

		// Backward-compatible AJAX hooks
		$this->loader->add_action( 'wp_ajax_scrape_data_php', $plugin_admin, 'ajax_scrape_url' );
		$this->loader->add_action( 'wp_ajax_embed_code', $plugin_admin, 'ajax_get_embed' );
		$this->loader->add_action( 'wp_ajax_save_as_post', $plugin_admin, 'ajax_save_post' );
	}

	/**
	 * Register all hooks related to the public-facing side.
	 */
	private function define_public_hooks() {
		$plugin_public = new Credipost_Public( $this->get_plugin_name(), $this->get_version() );

		// Shortcodes
		$this->loader->add_shortcode( 'CREDIPOST', $plugin_public, 'render_shortcode' );
		$this->loader->add_shortcode( 'credipost', $plugin_public, 'render_shortcode' );
	}

	/**
	 * Run the loader to execute all hooks.
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * Get the plugin name.
	 *
	 * @return string
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Get the loader.
	 *
	 * @return Credipost_Loader
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Get the version number.
	 *
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}
}
