<?php
/**
 * Handle actions.
 *
 * @package uma\includes
 */

namespace uma\includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Actions {

	public function __construct() {
		$this->plugin_dir = trailingslashit( dirname( plugin_dir_path( __FILE__ ) ) );

		add_action( 'admin_action_um-activity-dummy', array( &$this, 'add_posts' ) );
	}

	public function get_random_content() {
		$dir   = wp_normalize_path( $this->plugin_dir . 'data/text/' );
		$files = glob( $dir . '*.txt' );
		if ( is_array( $files ) ) {
			shuffle( $files );
			$file = current( $files );
		}
		return isset( $file ) && is_file( $file ) ? file_get_contents( $file ) : 'Test content ' . time();
	}

	public function get_random_photo() {
		$dir   = wp_normalize_path( $this->plugin_dir . 'data/photo/' );
		$files = glob( $dir . '*.jpg' );
		if ( is_array( $files ) ) {
			shuffle( $files );
			$file = current( $files );
		}
		return isset( $file ) && is_file( $file ) ? $file : '';
	}

	public function add_post_photo( $post_id, $user_id ) {
		$source = $this->get_random_photo();
		if ( $source ) {
			$user_dir = UM()->uploader()->get_upload_user_base_dir( $user_id );
			$hashed   = hash('ripemd160', time() . mt_rand( 10, 1000 ) );
			$filename = "stream_photo_{$hashed}.jpg";
			$dest     = trailingslashit( $user_dir ) . wp_basename( $filename );

			$res = copy( $source, $dest );

			update_post_meta( $post_id, '_photo', $filename );

			$wp_filetype = wp_check_filetype_and_ext( $dest, $filename );

			$photo_metadata                  = array();
			$photo_metadata['ext']           = empty( $wp_filetype['ext'] ) ? '' : $wp_filetype['ext'];
			$photo_metadata['type']          = empty( $wp_filetype['type'] ) ? '' : $wp_filetype['type'];
			$photo_metadata['size']          = filesize( $dest );
			$photo_metadata['name']          = $dest;
			$photo_metadata['basename']      = wp_basename( $filename );
			$photo_metadata['original_name'] = wp_basename( $source );

			update_post_meta( $post_id, '_photo_metadata', $photo_metadata );
		}
	}

	public function add_post( $array = array() ) {

		$user_id = empty( $array['author'] ) ? get_current_user_id() : absint( $array['author'] );

		$postarr = array(
			'post_author'  => $user_id,
			'post_status'  => 'publish',
			'post_title'   => get_current_user() . ' ' . current_time( 'mysql' ),
			'post_type'    => 'um_activity',
			'meta_input'	 => array(
				'_action'   => 'status',
				'_comments' => 0,
				'_likes'    => 0,
				'_user_id'  => $user_id,
				'_wall_id'  => $user_id,
			),
		);

		if ( isset( $array['template'] ) ) {
			$postarr['meta_input']['_action'] = absint( $array['template'] );
		}
		if ( isset( $array['related_id'] ) ) {
			$postarr['meta_input']['_related_id'] = absint( $array['related_id'] );
		}

		// Content.
		if ( ! empty( $_POST['uma-content-text'] ) ) {
			$original_content = wp_kses_post( $this->get_random_content() );
			$post_content     = $original_content;
			$urls             = wp_extract_urls( $post_content );
			if ( is_array( $urls ) ) {
				foreach ( $urls as $url ) {
					$oEmbed = wp_oembed_get( $url );
					if ( $oEmbed ) {
						$post_content = str_replace( $url, '', $post_content );
						break;
					}
				}
			}
			$postarr['post_content']                    = $post_content;
			$postarr['meta_input']['_original_content'] = $original_content;
		}

		// oEmbed.
		if ( ! empty( $oEmbed ) ) {
			$postarr['meta_input']['_oembed'] = (string) $oEmbed;
		}

		// Add new post.
		$post_id = wp_insert_post( $postarr );

		// Photo.
		if ( ! empty( $_POST['uma-content-photo'] ) ) {
			$this->add_post_photo( $post_id, $user_id );
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
}
