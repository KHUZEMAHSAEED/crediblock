<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Credipost
 * @subpackage Credipost/admin
 */

if ( ! defined( 'ABSPATH' ) && ! defined( 'WPINC' ) ) {
	exit;
}

class Credipost_Admin {

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
	 * The admin page hook suffix.
	 *
	 * @var string
	 */
	private $page_hook = '';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the admin menu page.
	 */
	public function add_admin_menu() {
		$this->page_hook = add_menu_page(
			__( 'Credipost Curation', 'credipost' ),
			__( 'Credipost', 'credipost' ),
			'edit_posts',
			'credipost_page',
			array( $this, 'render_admin_page' ),
			'dashicons-media-document',
			100
		);
	}

	/**
	 * Register and enqueue stylesheets and JavaScript for the admin area.
	 * Strictly scoped to the Credipost admin page only.
	 *
	 * @param string $hook The current admin page hook.
	 */
	public function enqueue_assets( $hook ) {
		// Only enqueue assets on the Credipost admin screen to avoid conflicts with WP admin.
		if ( $hook !== $this->page_hook ) {
			return;
		}

		// CSS
		wp_enqueue_style(
			$this->plugin_name . '-admin',
			plugin_dir_url( __FILE__ ) . 'css/credipost-admin.css',
			array( 'dashicons' ),
			$this->version,
			'all'
		);

		// JS (Uses WordPress core jQuery - NEVER override core jQuery!)
		wp_enqueue_script(
			$this->plugin_name . '-admin',
			plugin_dir_url( __FILE__ ) . 'js/credipost-admin.js',
			array( 'jquery' ),
			$this->version,
			true
		);

		// Localize script with nonce, ajaxurl, and i18n messages.
		wp_localize_script(
			$this->plugin_name . '-admin',
			'credipostAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'credipost_admin_nonce' ),
				'i18n'    => array(
					'fetching'        => __( 'Fetching content from URL...', 'credipost' ),
					'saving'          => __( 'Saving post...', 'credipost' ),
					'postSaved'       => __( 'Post successfully created!', 'credipost' ),
					'viewEditPost'    => __( 'Edit Post', 'credipost' ),
					'removeBlock'     => __( 'Remove this block', 'credipost' ),
					'undo'            => __( 'Undo', 'credipost' ),
					'emptyUrl'        => __( 'Please enter a valid URL.', 'credipost' ),
					'invalidUrl'      => __( 'The URL entered appears invalid.', 'credipost' ),
					'genericError'    => __( 'An error occurred while processing your request.', 'credipost' ),
					'noContent'       => __( 'No readable article content was found at this URL.', 'credipost' ),
					'embedNotFound'   => __( 'Could not generate video embed for this URL.', 'credipost' ),
					'blockRemoved'    => __( 'Block removed.', 'credipost' ),
					'confirmClear'    => __( 'Are you sure you want to clear the editor?', 'credipost' ),
				),
			)
		);
	}

	/**
	 * Render the admin page.
	 */
	public function render_admin_page() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'credipost' ) );
		}

		$categories = get_categories( array(
			'orderby'    => 'name',
			'order'      => 'ASC',
			'hide_empty' => false,
		) );

		$sites = array();
		if ( is_multisite() ) {
			$sites = get_sites( array( 'number' => 100 ) );
		}

		include_once plugin_dir_path( __FILE__ ) . 'partials/credipost-admin-display.php';
	}

	/**
	 * AJAX Handler: Scrape URL content.
	 */
	public function ajax_scrape_url() {
		check_ajax_referer( 'credipost_admin_nonce', 'security' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'credipost' ) ), 403 );
		}

		$url = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';
		if ( empty( $url ) || ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			wp_send_json_error( array( 'message' => __( 'Please provide a valid URL.', 'credipost' ) ) );
		}

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-credipost-scraper.php';

		$scraper = new Credipost_Scraper( $url );
		$result  = $scraper->scrape();

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		if ( empty( $result['content'] ) && empty( $result['title'] ) ) {
			wp_send_json_error( array( 'message' => __( 'No readable content could be extracted from this URL.', 'credipost' ) ) );
		}

		wp_send_json_success( $result );
	}

	/**
	 * AJAX Handler: Fetch video embed via native WordPress oEmbed.
	 */
	public function ajax_get_embed() {
		check_ajax_referer( 'credipost_admin_nonce', 'security' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'credipost' ) ), 403 );
		}

		$url = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';
		if ( empty( $url ) || ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			wp_send_json_error( array( 'message' => __( 'Please provide a valid video URL.', 'credipost' ) ) );
		}

		// Use WordPress Core oEmbed API.
		$embed_html = wp_oembed_get( $url, array( 'width' => 720 ) );

		if ( empty( $embed_html ) ) {
			wp_send_json_error( array( 'message' => __( 'WordPress could not embed this URL. Ensure it is a supported video provider.', 'credipost' ) ) );
		}

		wp_send_json_success( array(
			'embed_html' => $embed_html,
			'source_url' => $url,
		) );
	}

	/**
	 * AJAX Handler: Save content as a new post.
	 */
	public function ajax_save_post() {
		check_ajax_referer( 'credipost_admin_nonce', 'security' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'credipost' ) ), 403 );
		}

		$title       = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
		$source_url  = isset( $_POST['source_url'] ) ? esc_url_raw( wp_unslash( $_POST['source_url'] ) ) : '';
		$raw_content = isset( $_POST['content'] ) ? wp_unslash( $_POST['content'] ) : '';
		$content     = wp_kses_post( $raw_content );
		$category_id = isset( $_POST['category_id'] ) ? absint( $_POST['category_id'] ) : 0;
		$site_id     = isset( $_POST['site_id'] ) ? absint( $_POST['site_id'] ) : 0;
		$status      = isset( $_POST['status'] ) ? sanitize_key( $_POST['status'] ) : 'draft';

		if ( ! in_array( $status, array( 'draft', 'publish', 'pending' ), true ) ) {
			$status = 'draft';
		}

		if ( empty( $title ) ) {
			$title = ! empty( $source_url )
				? sprintf( __( 'Curated from %s', 'credipost' ), wp_parse_url( $source_url, PHP_URL_HOST ) )
				: __( 'Curated Post', 'credipost' );
		}

		$switched = false;
		if ( is_multisite() && ! empty( $site_id ) && $site_id !== get_current_blog_id() ) {
			if ( ! current_user_can_for_blog( $site_id, 'edit_posts' ) ) {
				wp_send_json_error( array( 'message' => __( 'You do not have permission to post to the selected subsite.', 'credipost' ) ), 403 );
			}
			switch_to_blog( $site_id );
			$switched = true;
		}

		$post_arr = array(
			'post_title'    => $title,
			'post_content'  => $content,
			'post_status'   => $status,
			'post_category' => $category_id > 0 ? array( $category_id ) : array(),
		);

		$post_id = wp_insert_post( $post_arr, true );

		if ( is_wp_error( $post_id ) ) {
			if ( $switched ) {
				restore_current_blog();
			}
			wp_send_json_error( array( 'message' => $post_id->get_error_message() ) );
		}

		// Store source URL in post meta.
		if ( ! empty( $source_url ) ) {
			update_post_meta( $post_id, '_credipost_source_url', $source_url );
		}

		$edit_link = get_edit_post_link( $post_id, 'raw' );

		if ( $switched ) {
			restore_current_blog();
		}

		wp_send_json_success( array(
			'message'   => __( 'Post saved successfully!', 'credipost' ),
			'post_id'   => $post_id,
			'edit_link' => $edit_link,
		) );
	}
}
