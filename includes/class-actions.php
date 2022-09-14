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

	public function get_random_emoji() {
		if ( empty( $this->emojis ) ) {
			$file         = wp_normalize_path( $this->plugin_dir . 'data/emojis.json' );
			$this->emojis = json_decode( trim( file_get_contents( $file ) ), true );
		}
		if ( is_array( $this->emojis ) ) {
			shuffle( $this->emojis );
			$emoji = current( $this->emojis );
		}
		return isset( $emoji ) && is_array( $emoji ) ? $emoji : null;
	}

	public function get_random_feeling() {
		if ( empty( $this->feelings ) ) {
			$file           = wp_normalize_path( $this->plugin_dir . 'data/feelings.json' );
			$this->feelings = json_decode( trim( file_get_contents( $file ) ), true );
		}
		if ( is_array( $this->feelings ) ) {
			shuffle( $this->feelings );
			$feeling = current( $this->feelings );
		}
		return isset( $feeling ) && is_array( $feeling ) ? $feeling : null;
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

	public function get_random_privacy() {
		$privacies = array(
			array(
				'_privacy' => 0,
			),
			array(
				'_privacy' => 1,
			),
			array(
				'_privacy' => 2,
			),
			array(
				'_privacy'					 => 9,
				'_privacy_friends'	 => 1,
				'_privacy_followers' => 0,
				'_privacy_following' => 0,
			),
			array(
				'_privacy'					 => 9,
				'_privacy_friends'	 => 0,
				'_privacy_followers' => 1,
				'_privacy_following' => 0,
			),
			array(
				'_privacy'					 => 9,
				'_privacy_friends'	 => 0,
				'_privacy_followers' => 0,
				'_privacy_following' => 1,
			),
			array(
				'_privacy'					 => 9,
				'_privacy_friends'	 => 1,
				'_privacy_followers' => 1,
				'_privacy_following' => 0,
			),
			array(
				'_privacy'					 => 9,
				'_privacy_friends'	 => 1,
				'_privacy_followers' => 0,
				'_privacy_following' => 1,
			),
			array(
				'_privacy'					 => 9,
				'_privacy_friends'	 => 0,
				'_privacy_followers' => 1,
				'_privacy_following' => 1,
			),
			array(
				'_privacy'					 => 9,
				'_privacy_friends'	 => 1,
				'_privacy_followers' => 1,
				'_privacy_following' => 1,
			),
		);
		if ( is_array( $privacies ) ) {
			shuffle( $privacies );
			$privacy = current( $privacies );
		}
		return isset( $privacy ) && is_array( $privacy ) ? $privacy : null;
	}

	public function get_random_user() {
		if ( empty( $this->users ) ) {
			$args = array(
				'fields'     => 'ID',
				'number'     => 10,
				'meta_key'   => 'account_status',
				'meta_value' => 'approved',
			);
			$this->users = get_users( $args );
		}
		if ( is_array( $this->users ) ) {
			shuffle( $this->users );
			$user_id = current( $this->users );
		}
		return isset( $user_id ) && is_numeric( $user_id ) ? $user_id : get_current_user_id();
	}

	public function get_random_youtube() {
		if ( empty( $this->videos ) ) {
			$file         = wp_normalize_path( $this->plugin_dir . 'data/youtube.php' );
			$this->videos = include $file;
		}
		if ( is_array( $this->videos ) ) {
			shuffle( $this->videos );
			$video = current( $this->videos );
		}
		return isset( $video ) && is_string( $video ) ? $video : null;
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

	public function add_post( $array = array() ) {

		$user_id = empty( $array['author'] ) ? $this->get_random_user() : absint( $array['author'] );

		$postarr = array(
			'post_author'  => $user_id,
			'post_content' => '',
			'post_status'  => 'publish',
			'post_title'   => get_current_user() . ' ' . current_time( 'mysql' ),
			'post_type'    => 'um_activity',
			'meta_input'	 => array(
				'_action'   => 'status',
				'_comments' => 0,
				'_likes'    => 0,
				'_privacy'  => 0,
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
			$post_content = wp_kses_post( $this->get_random_content() );
			$postarr['post_content']                    = $post_content;
			$postarr['meta_input']['_original_content'] = $post_content;
		}

		// YouTube video.
		if ( ! empty( $_POST['uma-content-youtube'] ) ) {
			$link = $this->get_random_youtube();
			if ( is_string( $link ) ) {
				$postarr['post_content'] .= "\n" . $link;
			}
		}

		// oEmbed.
		$urls = wp_extract_urls( $postarr['post_content'] );
		if ( is_array( $urls ) ) {
			$post_content = $postarr['post_content'];
			foreach ( $urls as $url ) {
				$oEmbed = (string) wp_oembed_get( $url );
				if ( $oEmbed ) {
					$post_content = str_replace( $url, '', $post_content );

					$postarr['post_content']          = $post_content;
					$postarr['meta_input']['_oembed'] = $oEmbed;
					break;
				}
			}
		}

		// Emoji.
		if ( ! empty( $_POST['uma-content-emoji'] ) ) {
			$emoji = $this->get_random_emoji();
			if( is_array( $emoji ) ){
				$postarr['post_content'] .= "\n" . $emoji['c'];
			}
		}

		// Feeling.
		if ( ! empty( $_POST['uma-content-feeling'] ) ) {
			$feeling = $this->get_random_feeling();
			if( is_array( $feeling ) ){
				$postarr['meta_input']['_feeling_emoji'] = $feeling['c'];
				$postarr['meta_input']['_feeling_title'] = $feeling['t'];
			}
		}

		// Privacy.
		if ( ! empty( $_POST['uma-content-privacy'] ) ) {
			$privacy = $this->get_random_privacy();
			if( is_array( $privacy ) ){
				$postarr['meta_input'] = array_merge( $postarr['meta_input'], $privacy );
			}
		}

		// Flagged.
		if ( ! empty( $_POST['uma-content-flagged'] ) ) {
			$postarr['meta_input']['_reported'] = 1;
		}

		// Add new post.
		$post_id = wp_insert_post( $postarr );

		// Photo.
		if ( ! empty( $_POST['uma-content-photo'] ) ) {
			$this->add_post_photo( $post_id, $user_id );
		}
		return $post_id;
	}

	public function add_post_photo( $post_id, $user_id = 0 ) {
		if ( empty( $user_id ) ) {
			$user_id = get_post_meta( $post_id, '_user_id', true );
		}

		$source = $this->get_random_photo();
		if ( $source ) {
			$user_dir = UM()->uploader()->get_upload_user_base_dir( $user_id );
			$hashed   = hash('ripemd160', time() . mt_rand( 10, 1000 ) );
			$filename = "stream_photo_{$hashed}.jpg";
			$dest     = trailingslashit( $user_dir ) . wp_basename( $filename );

			// maybe create directory.
			if ( ! is_dir( $user_dir ) ) {
				wp_mkdir_p( $user_dir );
			}

			if ( copy( $source, $dest ) ) {
				$wp_filetype = wp_check_filetype_and_ext( $dest, $filename );

				$photo_metadata                  = array();
				$photo_metadata['ext']           = empty( $wp_filetype['ext'] ) ? '' : $wp_filetype['ext'];
				$photo_metadata['type']          = empty( $wp_filetype['type'] ) ? '' : $wp_filetype['type'];
				$photo_metadata['size']          = filesize( $dest );
				$photo_metadata['name']          = $dest;
				$photo_metadata['basename']      = wp_basename( $filename );
				$photo_metadata['original_name'] = wp_basename( $source );

				update_post_meta( $post_id, '_photo', $filename );
				update_post_meta( $post_id, '_photo_metadata', $photo_metadata );
			}
		}
	}
}
