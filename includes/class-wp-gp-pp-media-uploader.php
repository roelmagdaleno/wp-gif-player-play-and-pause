<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_GP_PP_Media_Uploader' ) ) {
	/**
	 * Handle all GIF media uploader and thumbnail creations.
	 *
	 * @since 0.1.0
	 */
	class WP_GP_PP_Media_Uploader {
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
			$this->settings = wp_gp_pp_get_settings();

			add_action( 'add_attachment', array( $this, 'pre_create_thumbnail_from_gif' ) );
			add_action( 'save_post', array( $this, 'maybe_create_thumbnail_from_gif' ), 10, 2 );
			add_action( 'media_buttons', array( $this, 'add_uploader_gif_button' ) );
			add_action( 'wp_enqueue_media', array( $this, 'add_gif_button_scripts' ) );
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
				plugins_url( 'assets/js/media-button.js', __DIR__ ),
				null,
				WP_GP_PP_VERSION,
				$in_footer
			);
		}
	}
}
