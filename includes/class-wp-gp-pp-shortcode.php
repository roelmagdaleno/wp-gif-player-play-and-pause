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
			$this->settings = get_option( 'wp_gp_pp_settings' );
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

			$in_footer = true;

			wp_enqueue_style(
				'wp-gp-pp.css',
				plugins_url( 'assets/css/wp-gp-pp.css', __DIR__ ),
				null,
				WP_GP_PP_VERSION
			);

			if ( 'canvas' === $this->settings['gif_method'] ) {
				wp_enqueue_script(
					'wp-gp-pp-libgif.js',
					plugins_url( 'assets/js/libgif.js', __DIR__ ),
					null,
					WP_GP_PP_VERSION,
					$in_footer
				);
			}

			wp_enqueue_script(
				'wp-gp-pp-' . $this->settings['gif_method'] . '.js',
				plugins_url( 'assets/js/wp-gp-pp-' . $this->settings['gif_method'] . '.js', __DIR__ ),
				null,
				WP_GP_PP_VERSION,
				$in_footer
			);

			$attachment_id = (int) $raw_atts['id'];

			if ( ! wp_gp_pp_is_gif( $attachment_id ) ) {
				return '';
			}

			$size       = apply_filters( 'wp_gp_pp_attachment_size', 'full' );
			$gif_source = wp_get_attachment_image_src( $attachment_id, $size );

			if ( ! $gif_source ) {
				return '';
			}

			$raw_atts['id'] = 'wp-gp-pp--id-' . $attachment_id;

			$atts = shortcode_atts( array(
				'id'     => $raw_atts['id'],
				'width'  => $gif_source['1'],
				'height' => $gif_source['2'],
			), $raw_atts );

			$thumbnail  = $this->get_thumbnail( $gif_source[0] );
			$width      = esc_attr( $atts['width'] );
			$height     = esc_attr( $atts['height'] );
			$render     = 'render_wrapper_for_' . $this->settings['gif_method'];
			$attachment = array(
				'thumbnail'     => $thumbnail,
				'width'         => $width,
				'height'        => $height,
				'attachment_id' => $attachment_id,
			);

			$image  = $this->$render( $attachment, $atts );
			$image .= '<div class="wp-gp-pp-overlay"> ';
			$image .= '<div class="wp-gp-pp-play-button">GIF</div> ';
			$image .= '</div> </div>';

			return $image;
		}

		/**
		 * Get the thumbnail link image that belongs to the current GIF.
		 *
		 * @since  0.1.0
		 * @access private
		 *
		 * @param  string   $gif_link   The original GIF link (uploaded by the user).
		 * @return string               The thumbnail link.
		 */
		private function get_thumbnail( $gif_link ) {
			return str_replace( '.gif', '_gif_thumbnail.jpeg', $gif_link );
		}

		/**
		 * Generate the HTML to use the GIF player as a normal method.
		 *
		 * This method will set two images, one is the preview and the
		 * second one will be used to set the real GIF source file.
		 *
		 * This method is the simpler one because uses the <img> tag to
		 * load the GIF(s).
		 *
		 * @since  0.1.0
		 * @access private
		 *
		 * @param  array    $attachment   The GIF attachment data.
		 * @param  array    $atts         The GIF current attributes.
		 * @return string                 The GIF player wrapper for canvas method.
		 */
		private function render_wrapper_for_gif( $attachment, $atts ) {
			$thumbnail = $attachment['thumbnail'];
			$width     = $attachment['width'];
			$height    = $attachment['height'];

			$image  = '<div class="wp-gp-pp-container" style="width: ' . $width . 'px; height: ' . $height . 'px">';
			$image .= '<img src="' . esc_attr( $thumbnail ) . '" id="' . esc_attr( $atts['id'] ) . '--thumbnail" ';
			$image .= 'class="wp-gp-pp-gif-thumbnail" width="' . $width . '" height="' . $height . '" alt="">';

			$image .= '<img src="" id="' . esc_attr( $atts['id'] ) . '" ';
			$image .= 'class="wp-gp-pp-gif" width="' . $width . '" height="' . $height . '" alt="">';

			return $image;
		}

		/**
		 * Generate the HTML to use the GIF player as a canvas method.
		 *
		 * This <canvas> method will use the "libgif.js" library to setup
		 * the GIF play and pause actions.
		 *
		 * @since  0.1.0
		 * @access private
		 *
		 * @param  array    $attachment   The GIF attachment data.
		 * @param  array    $atts         The GIF current attributes.
		 * @return string                 The GIF player wrapper for canvas method.
		 */
		private function render_wrapper_for_canvas( $attachment, $atts ) {
			$thumbnail = $attachment['thumbnail'];
			$width     = $attachment['width'];
			$height    = $attachment['height'];

			$source = str_replace( '_gif_thumbnail.jpeg', '.gif', $thumbnail );

			$image  = '<div class="wp-gp-pp-container" style="width: ' . $width . 'px; height: ' . $height . 'px">';
			$image .= '<img src="' . esc_attr( $thumbnail ) . '" id="' . esc_attr( $atts['id'] ) . '" ';
			$image .= 'rel:animated_src="' . esc_attr( $source ) . '" rel:auto_play="0" class="wp-gp-pp-gif-canvas-player" ';
			$image .= 'width="' . $width . '" height="' . $height . '" alt="">';

			return $image;
		}

		/**
		 * Generate the HTML to use the GIF player as a video method.
		 *
		 * To get the <video> sources the plugin should've stored the
		 * video files in the database and their respective folder path.
		 *
		 * The first video we show is the "webm" after that the "mp4".
		 *
		 * Also, the thumbnail will be used as the video poster.
		 *
		 * @since  0.1.0
		 * @access private
		 *
		 * @param  array    $attachment   The GIF attachment data.
		 * @param  array    $atts         The GIF current attributes.
		 * @return string                 The GIF player wrapper for canvas method.
		 */
		private function render_wrapper_for_video( $attachment, $atts ) {
			$thumbnail = $attachment['thumbnail'];
			$width     = $attachment['width'];
			$height    = $attachment['height'];
			$args      = array(
				'post_parent'    => $attachment['attachment_id'],
				'orderby'        => 'ID',
				'order'          => 'ASC',
				'post_mime_type' => wp_gp_pp_get_video_mime_types(),
			);

			$children = get_children( $args, ARRAY_A );

			$video  = '<div class="wp-gp-pp-container" style="width: ' . $width . 'px; height: ' . $height . 'px">';
			$video .= '<video loop muted playsinline class="wp-gp-pp-video-player" ';
			$video .= 'poster="' . esc_attr( $thumbnail ) . '" id="' . esc_attr( $atts['id'] ) . '" ';
			$video .= 'width="' . $width . '" height="' . $height . '">';

			foreach ( $children as $video_source ) {
				$guid = esc_attr( $video_source['guid'] );
				$type = esc_attr( $video_source['post_mime_type'] );

				$video .= '<source src="' . $guid . '" type="' . $type . '">';
			}

			$video .= '</video>';

			return $video;
		}
	}
}
