<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://portfolio-khuzemah-saeed.vercel.app/
 * @author     Khuzemah Saeed
 * @since      1.0.0
 * @package    Credipost
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Clean up any plugin transients.
delete_transient( 'credipost_cache' );
