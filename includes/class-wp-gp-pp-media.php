<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_GP_PP_Media' ) ) {
	/**
	 * Handle all GIF media uploader and thumbnail creations.
	 *
	 * @since 0.1.0
	 */
	class WP_GP_PP_Media {
		// Includes the thumbnail and video functionality.
		use WP_GP_PP_Thumbnail_Creator, WP_GP_PP_Video_Creator;

		/**
		 * The plugin settings.
		 *
		 * @since  0.1.0
		 * @access private
		 *
		 * @var    array   $settings   The plugin settings.
		 */
		private $settings;

		/**
		 * Initialize all action hooks to upload the GIF images
		 * and set our own player.
		 *
		 * There are two action hooks that creates a thumbnail for
		 * the GIF preview.
		 *
		 * These actions happens when the user uploads a GIF image using the
		 * Media Library and another one then save the post.
		 *
		 * @since 0.1.0
		 */
		public function __construct() {
			$this->settings = WP_GP_PP::get_instance()->settings;

			add_action( 'delete_post', array( $this, 'delete_generated_assets' ) );
			add_action( 'add_attachment', array( $this, 'pre_create_thumbnail_from_gif' ) );
			add_action( 'save_post', array( $this, 'maybe_create_thumbnail_from_post' ), 10, 2 );
			add_action( 'media_buttons', array( $this, 'add_uploader_gif_button' ) );
			add_action( 'wp_enqueue_media', array( $this, 'add_gif_button_scripts' ) );
			add_action( 'admin_post_wp_gp_pp_generate_gif_player', array( $this, 'create_gif_from_media_row' ) );
			add_filter( 'media_row_actions', array( $this, 'add_row_actions' ), 10, 2 );
		}

		/**
		 * Delete the generated assets like thumbnail and videos from
		 * the uploads folder path and posts and postmeta database tables.
		 *
		 * @since 0.1.0
		 *
		 * @param int   $attachment_id   The current attachment id to delete.
		 */
		public function delete_generated_assets( $attachment_id ) {
			global $wpdb;

			if ( ! wp_gp_pp_is_gif( $attachment_id ) ) {
				return;
			}

			$attachments = get_children( $attachment_id );

			if ( empty( $attachments ) ) {
				return;
			}

			$posts_ids = array();

			foreach ( $attachments as $attachment ) {
				$file = get_attached_file( $attachment->ID );

				if ( ! file_exists( $file ) ) {
					continue;
				}

				$posts_ids[] = $attachment->ID;
				wp_delete_file( $file );
			}

			if ( empty( $posts_ids ) ) {
				return;
			}

			$placeholder = implode( ',', array_fill( 0, count( $posts_ids ), '%d' ) );

			// phpcs:disable
			$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE post_id IN($placeholder)", $posts_ids ) );
			$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->posts WHERE ID IN($placeholder)", $posts_ids ) );
		}

		/**
		 * Start to create the GIF from the clicked "Generate GIF Player" button.
		 *
		 * We have to verify nonces and be sure the attachment (post) id exists
		 * otherwise won't do anything.
		 *
		 * @since 0.1.0
		 *
		 * @SuppressWarnings(PHPMD.ExitExpression)
		 */
		public function create_gif_from_media_row() {
			if ( ! wp_verify_nonce( $_GET['gif_player'], 'wp_gp_pp_generate_gif_player' ) || ! isset( $_GET['post_id'] ) ) {
				$message = array(
					'type'    => 'error',
					'message' => 'You cannot generate the GIF player for security reasons.',
				);

				set_transient( 'wp_gp_pp_admin_notice', $message );

				wp_safe_redirect( wp_get_referer() );
				exit;
			}

			$this->pre_create_thumbnail_from_gif( $_GET['post_id'] );

			$message = array(
				'type'    => 'success',
				'message' => 'The GIF player assets for the selected GIF was successfully created.',
			);

			set_transient( 'wp_gp_pp_admin_notice', $message );

			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		/**
		 * Add the "Generate GIF Player" to the current media
		 * action links in the Media Library page.
		 *
		 * @since  0.1.0
		 *
		 * @param  array     $actions   An array of action links for each attachment.
		 * @param  WP_Post   $post      WP_Post object for the current attachment.
		 * @return array                An array of action links for each attachment.
		 */
		public function add_row_actions( $actions, $post ) {
			if ( 'image/gif' !== $post->post_mime_type ) {
				return $actions;
			}

			$gif_method = $this->settings['gif_method'];
			$mime_types = array( 'image/jpeg' );

			if ( 'video' === $gif_method ) {
				array_merge( $mime_types, wp_gp_pp_get_video_mime_types() );
			}

			$saved_mime_types = wp_list_pluck( get_children( $post->ID ), 'post_mime_type' );

			if ( empty( array_diff( $mime_types, $saved_mime_types ) ) ) {
				return $actions;
			}

			$action_hook = 'wp_gp_pp_generate_gif_player';
			$query_args  = array(
				'action'  => $action_hook,
				'post_id' => $post->ID,
			);

			$admin_url = add_query_arg( $query_args, 'admin-post.php' );
			$url       = wp_nonce_url( admin_url( $admin_url ), $action_hook, 'gif_player' );

			$actions[ $action_hook ] = '<a href="' . esc_url( $url ) . '">Generate GIF Player</a>';

			return $actions;
		}

		/**
		 * Add the "Add GIF Player" button into the post actions buttons.
		 * This only works for classic editor. For Gutenberg we will use a custom block.
		 *
		 * @since 0.1.0
		 */
		public function add_uploader_gif_button() {
			echo '<button type="button" id="wp-gp-pp-media-uploader" class="button">Add GIF Player</button>';
		}

		/**
		 * Enqueue the script that allows to open the Media Library
		 * when the user clicks on our button.
		 *
		 * @since 0.1.0
		 */
		public function add_gif_button_scripts() {
			$current_screen = get_current_screen();

			if ( 'post' !== $current_screen->base ) {
				return;
			}

			$in_footer = true;

			wp_enqueue_script(
				'wp-gp-pp-media-button.js',
				plugins_url( 'admin/js/media-button.js', __DIR__ ),
				null,
				WP_GP_PP_VERSION,
				$in_footer
			);
		}
	}
}
