<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_GP_PP_Shortcode' ) ) {
	/**
	 * Handle all GIF player functionality when using the shortcode.
	 *
	 * @since 0.1.0
	 */
	class WP_GP_PP_Shortcode {
		/**
		 * Initialize the shortcode to render the GIF player.
		 *
		 * @since 0.1.0
		 */
		public function __construct() {
			add_shortcode( 'gif-player', array( $this, 'render_gif_player' ) );
		}

		/**
		 * Render the GIF player into the post or wherever the shortcode is called.
		 *
		 * Something to be aware of:
		 * If there's no source in the shortcode it won't render anything.
		 *
		 * @since  0.1.0
		 *
		 * @param  array   $raw_atts   The user shortcode attributes.
		 * @return string              The GIF player.
		 */
		public function render_gif_player( $raw_atts ) {
			if ( ! isset( $raw_atts['src'] ) || empty( $raw_atts['src'] ) ) {
				return '';
			}

			$in_footer = true;

			wp_enqueue_style(
				'wp-gp-pp.css',
				plugins_url( 'assets/css/wp-gp-pp.css', __DIR__ ),
				null,
				WP_GP_PP_VERSION
			);

			wp_enqueue_script(
				'wp-gp-pp-libgif.js',
				plugins_url( 'assets/js/libgif.js', __DIR__ ),
				null,
				WP_GP_PP_VERSION,
				$in_footer
			);

			wp_enqueue_script(
				'wp-gp-pp.js',
				plugins_url( 'assets/js/wp-gp-pp.js', __DIR__ ),
				null,
				WP_GP_PP_VERSION,
				$in_footer
			);

			$atts = shortcode_atts( array(
				'id'        => 'wp-gp-pp--id-' . wp_rand(),
				'auto_play' => '0',
				'src'       => $raw_atts['src'] ? trim( $raw_atts['src'] ) : '',
			), $raw_atts );

			if ( empty( $atts['src'] ) ) {
				return '';
			}

			$maybe_playing = ( '1' === $atts['auto_play'] ) ? 'is-playing' : '';

			$image  = '<div class="wp-gp-pp-container">';
			$image .= '<img src="' . esc_attr( $atts['src'] ) . '" id="' . esc_attr( $atts['id'] ) . '" ';
			$image .= 'rel:animated_src="' . esc_attr( $atts['src'] ) . '" ';
			$image .= 'rel:auto_play="' . esc_attr( $atts['auto_play'] ) . '" class="wp-gp-pp-gif-player">';
			$image .= '<div class="wp-gp-pp-overlay"> ';
			$image .= '<div class="wp-gp-pp-play-button ' . $maybe_playing . '">GIF</div> ';
			$image .= '</div> </div>';

			return $image;
		}
	}
}
