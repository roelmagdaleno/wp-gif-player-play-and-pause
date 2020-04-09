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
		 * The plugin settings.
		 *
		 * @since  0.1.0
		 * @access private
		 *
		 * @var    array   $settings   The plugin settings.
		 */
		private $settings;

		/**
		 * Initialize the shortcode to render the GIF player.
		 *
		 * @since 0.1.0
		 */
		public function __construct() {
			$this->settings = wp_gp_pp_get_settings();
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
			if ( ! isset( $raw_atts['id'] ) || empty( $raw_atts['id'] ) ) {
				return '';
			}

			$attachment_id = (int) $raw_atts['id'];
			$attachment    = wp_gp_pp_get_attachment_data( $attachment_id, $raw_atts );

			if ( empty( $attachment ) ) {
				return '';
			}

			$render = 'wp_gp_pp_render_wrapper_for_' . $this->settings['gif_method'];
			$image  = $render( $attachment );
			$image .= '<div class="wp-gp-pp-overlay"> ';
			$image .= '<div class="wp-gp-pp-play-button">GIF</div> ';
			$image .= '</div> </div>';

			return $image;
		}
	}
}
