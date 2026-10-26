<?php
/**
 * The plugin bootstrap file
 *
 * @link              https://crediblock.com/
 * @since             1.0.0
 * @package           Credipost
 *
 * @wordpress-plugin
 * Plugin Name:       Credipost
 * Plugin URI:        https://crediblock.com/
 * Description:       Curate, scrape, prune, and syndicate web articles and video embeds safely to WordPress posts and multisite networks.
 * Version:           2.0.0
 * Author:            Crediblock
 * Author URI:        https://crediblock.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       credipost
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently active plugin version.
 */
define( 'CREDIPOST_VERSION', '2.0.0' );
define( 'CREDIPOST_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CREDIPOST_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CREDIPOST_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_credipost() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-credipost-activator.php';
	Credipost_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_credipost() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-credipost-deactivator.php';
	Credipost_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_credipost' );
register_deactivation_hook( __FILE__, 'deactivate_credipost' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-credipost.php';

/**
 * Begins execution of the plugin.
 */
function run_credipost() {
	$plugin = new Credipost();
	$plugin->run();
}
run_credipost();