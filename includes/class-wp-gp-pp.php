<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_GP_PP' ) ) {
	/**
	 * Main class to run the plugin functionality.
	 *
	 * @since 0.1.0
	 */
	class WP_GP_PP {
		/**
		 * Initialize the classes and action hooks to run the plugin.
		 *
		 * @since 0.1.0
		 */
		public function __construct() {
			new WP_GP_PP_Shortcode();
			new WP_GP_PP_Gutenberg_Block();

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

			if ( ! is_admin() ) {
				return;
			}

			new WP_GP_PP_Media_Uploader();
			new WP_GP_PP_Options();
		}

		/**
		 * Enqueue the assets in frontend but only if shortcode
		 * or Gutenberg block exists in the current post.
		 *
		 * For canvas method it will enqueue the "libgif.js"
		 *
		 * @since 0.1.0
		 */
		public function enqueue_assets() {
			if ( ! $this->should_enqueue_assets() ) {
				return;
			}

			$settings  = wp_gp_pp_get_settings();
			$in_footer = true;

			wp_enqueue_style(
				'wp-gp-pp.css',
				plugins_url( 'assets/css/wp-gp-pp.css', __DIR__ ),
				null,
				WP_GP_PP_VERSION
			);

			if ( 'canvas' === $settings['gif_method'] ) {
				wp_enqueue_script(
					'wp-gp-pp-libgif.js',
					plugins_url( 'assets/js/libgif.js', __DIR__ ),
					null,
					WP_GP_PP_VERSION,
					$in_footer
				);
			}

			$file_handle = 'wp-gp-pp-' . $settings['gif_method'] . '.js';

			wp_enqueue_script(
				$file_handle,
				plugins_url( 'assets/js/' . $file_handle, __DIR__ ),
				null,
				WP_GP_PP_VERSION,
				$in_footer
			);
		}

		/**
		 * Whether to enqueue the assets on frontend or not.
		 *
		 * First we look into the post if the shortcode "gif-player" exists
		 * after that we can do another check if the post has the block
		 * "roelmagdaleno/gif-player".
		 *
		 * @since  0.1.0
		 *
		 * @return bool   Whether to enqueue the assets on frontend or not.
		 */
		private function should_enqueue_assets() {
			global $post;

			if ( has_shortcode( $post->post_content, 'gif-player' ) ) {
				return true;
			}

			return has_block( 'roelmagdaleno/gif-player', $post->post_content );
		}
	}
}
