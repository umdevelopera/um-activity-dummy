<?php
/**
 * Add dashboard widget.
 *
 * @package uma\includes
 */

namespace uma\includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Widget {

	public function __construct() {
		$this->plugin_dir = trailingslashit( dirname( plugin_dir_path( __FILE__ ) ) );
		$this->plugin_url = trailingslashit( dirname( plugin_dir_url( __FILE__ ) ) );

		add_action( 'wp_dashboard_setup', array( &$this, 'dashboard_widgets' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue' ) );
	}

	public function enqueue() {
		global $current_screen;
		if ( isset( $current_screen ) && 'dashboard' === $current_screen->id ) {
			wp_enqueue_style(
				'um-activity-dummy-widget',
				$this->plugin_url . 'assets/css/um-activity-dummy-widget.css',
				array()
			);
		}
	}

	public function dashboard_widgets() {
		global $current_screen;
		if ( isset( $current_screen ) && 'dashboard' === $current_screen->id ) {
			wp_add_dashboard_widget( 'um-activity-dummy', __( 'Activity dummy posts', 'ultimate-member' ), array( &$this, 'dashboard_widget_content' ) );
		}
	}

	public function dashboard_widget_content() {
		$template   = wp_normalize_path( $this->plugin_dir . 'templates/widget.php' );
		if ( file_exists( $template ) ) {
			require_once $template;
		}
	}
}
