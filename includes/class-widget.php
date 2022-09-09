<?php
/**
 * Add metaboxes.
 *
 * @package uma\includes
 */

namespace uma\includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Widget {

	/**
	 * Metabox constructor.
	 */
	public function __construct() {
		add_action( 'wp_dashboard_setup', array( &$this, 'dashboard_widgets' ) );
		add_action( 'admin_action_um-activity-dummy', array( &$this, 'add_posts' ) );
	}

	public function get_content() {

		$plugin_dir = dirname( plugin_dir_path( __FILE__ ) );
		$dir        = wp_normalize_path( trailingslashit( $plugin_dir ) . 'data/text/' );

		$files = glob( $dir . '*.txt' );
		if ( is_array( $files ) ) {
			shuffle( $files );
			$file = current( $files );
		}

		return isset( $file ) && is_file(  $file ) ? file_get_contents( $file ) : 'Test content ' . time();
	}

	public function add_post( $array = array() ) {

		$array['author'] = get_current_user_id();

		$args = array(
			'post_author'  => $array['author'],
			'post_content' => $this->get_content(),
			'post_status'  => 'publish',
			'post_title'   => '',
			'post_type'    => 'um_activity',
		);

		// Add a new post.
		$post_id = wp_insert_post( $args );
		wp_update_post(
			array(
				'ID'         => $post_id,
				'post_title' => $post_id,
				'post_name'  => $post_id,
			)
		);

		update_post_meta( $post_id, '_action', 'status' );
		update_post_meta( $post_id, '_comments', 0 );
		update_post_meta( $post_id, '_likes', 0 );

		if ( isset( $array['author'] ) ) {
			update_post_meta( $post_id, '_user_id', absint( $array['author'] ) );
		}
		if ( isset( $array['related_id'] ) ) {
			update_post_meta( $post_id, '_related_id', absint( $array['related_id'] ) );
		}
		if ( isset( $array['template'] ) ) {
			update_post_meta( $post_id, '_action', absint( $array['template'] ) );
		}
		if ( isset( $array['wall_id'] ) ) {
			update_post_meta( $post_id, '_wall_id', $array['wall_id'] );
		}

		return $post_id;
	}

	public function add_posts(){
		check_admin_referer( 'um-activity-dummy' );

		if ( empty( $_POST[ 'uma-number' ] ) ) {
			die( 'The "Number of posts" fields is empty' );
		}
		$number = absint( $_POST[ 'uma-number' ] );

		$i = 0;
		while ( $i < $number ) {
			$this->add_post();
			$i++;
		}

		return;
	}

	public function dashboard_widgets() {
		wp_add_dashboard_widget( 'um-activity-dummy', __( 'Activity dummy posts', 'ultimate-member' ), array( &$this, 'dashboard_widget_content' ) );
	}

	public function dashboard_widget_content() {

		$plugin_dir = dirname( plugin_dir_path( __FILE__ ) );
		$template   = wp_normalize_path( trailingslashit( $plugin_dir ) . 'templates/widget.php' );

		if ( file_exists( $template ) ) {
			require_once $template;
		}
	}
}
