<?php
/**
 * Plugin Name: Ultimate Member - Activity dummy posts
 * Description: Creates dummy posts to test the activity module
 * Version:     1.0.0
 * Author:      Ultimate Member support
 * Author URI:  https://ultimatemember.com/support/
 * Text Domain: um-activity-dummy
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Require the Ultimate Member plugin.
if ( ! function_exists( 'UM' ) ) {
	return;
}

$dir = trailingslashit( plugin_dir_path( __FILE__ ) );

if ( isset( $_POST['action'] ) ) {
	$include = wp_normalize_path( $dir . 'includes/class-actions.php' );
	if ( file_exists( $include ) ) {
		require_once $include;
		if ( class_exists( '\uma\includes\Actions' ) ) {
			$uma = new uma\includes\Actions();
		}
	}
}

$include = wp_normalize_path( $dir . 'includes/class-widget.php' );
if ( file_exists( $include ) ) {
	require_once $include;
	if ( class_exists( '\uma\includes\Widget' ) ) {
		$uma = new uma\includes\Widget();
	}
}