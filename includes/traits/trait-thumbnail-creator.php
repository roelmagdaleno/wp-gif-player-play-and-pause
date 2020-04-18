<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait WP_GP_PP_Thumbnail_Creator {
	/**
	 * Search and try to create the thumbnails for the current
	 * inserted GIF players inside the updated post.
	 *
	 * The process will execute if our shortcode "gif-player" is
	 * inside of the post.
	 *
	 * Also, we're not using any image sizes for our thumbnail creations.
	 * We avoid unused thumbnails files.
	 *
	 * @since 0.1.0
	 *
	 * @param int       $post_id   The current post id.
	 * @param WP_Post   $post      The current post data.
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function maybe_create_thumbnail_from_post( $post_id, $post ) {
		if ( ! has_shortcode( $post->post_content, 'gif-player' ) ) {
			return;
		}

		$pattern = get_shortcode_regex( array( 'gif-player' ) );

		preg_match_all(
			'/' . $pattern . '/',
			$post->post_content,
			$matches,
			PREG_SET_ORDER
		);

		if ( empty( $matches ) ) {
			return;
		}

		add_filter( 'intermediate_image_sizes_advanced', '__return_empty_array' );

		foreach ( $matches as $shortcode ) {
			if ( ! isset( $shortcode[3] ) ) {
				continue;
			}

			$params = trim( $shortcode[3] );

			if ( empty( $params ) ) {
				continue;
			}

			$attachment_id = $this->get_attachment_id_from_string( $params );

			if ( ! $attachment_id ) {
				continue;
			}

			if ( ! wp_gp_pp_is_gif( $attachment_id ) ) {
				continue;
			}

			$this->create_thumbnail_from_gif( $attachment_id );
		}

		remove_filter( 'intermediate_image_sizes_advanced', '__return_empty_array' );
	}

	/**
	 * Start the thumbnail GIF creation.
	 * This method will execute if the current attachment is a GIF.
	 *
	 * Also, we're not using any image sizes for our thumbnail creations.
	 * We avoid unused thumbnails files.
	 *
	 * @since 0.1.0
	 *
	 * @param int   $attachment_id   The current attachment id.
	 */
	public function pre_create_thumbnail_from_gif( $attachment_id ) {
		if ( ! wp_gp_pp_is_gif( $attachment_id ) ) {
			return;
		}

		add_filter( 'intermediate_image_sizes_advanced', '__return_empty_array' );

		$this->create_thumbnail_from_gif( $attachment_id );

		remove_filter( 'intermediate_image_sizes_advanced', '__return_empty_array' );
	}

	/**
	 * Create the thumbnail GIF in "jpeg" format.
	 *
	 * We have to set the new thumbnail path and url so we can store
	 * the first GIF frame in the new file.
	 *
	 * If the current file path already exists then won't do nothing.
	 *
	 * By default the thumbnail size is "full" but you can still change
	 * it using a filter.
	 *
	 * After the thumbnail is created we have to store it as a new attachment
	 * in WordPress database.
	 *
	 * @since 0.1.0
	 *
	 * @param int   $attachment_id   The current attachment id.
	 */
	public function create_thumbnail_from_gif( $attachment_id ) {
		$size       = apply_filters( 'wp_gp_pp_attachment_size', 'full' );
		$gif_source = wp_get_attachment_image_src( $attachment_id, $size );

		if ( ! $gif_source ) {
			return;
		}

		if ( 'video' === $this->settings['gif_method'] ) {
			$this->create_video_from_gif( $attachment_id, $gif_source[0] );
		}

		$thumbnail_path = $this->get_thumbnail_path( $gif_source[0] );

		if ( file_exists( $thumbnail_path ) ) {
			return;
		}

		$this->create_thumbnail_file( $gif_source[0], $thumbnail_path );

		$thumbnail_url  = wp_gp_pp_path_to_url( $thumbnail_path );
		$file_type      = wp_check_filetype( $thumbnail_path );
		$new_attachment = array(
			'guid'           => $thumbnail_url,
			'post_mime_type' => $file_type['type'],
			'post_title'     => str_replace( '.jpeg', '', wp_basename( $thumbnail_url ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
			'post_parent'    => $attachment_id,
		);

		wp_gp_pp_insert_new_attachment( $new_attachment, $thumbnail_path );
	}

	/**
	 * Get the new thumbnail file path.
	 *
	 * Convert the url filename into a "jpeg" format with this name
	 * and change the home url link into an absolute path.
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @param  string   $gif_link   The original GIF link (uploaded by the user).
	 * @return string               The new thumbnail file path.
	 */
	private function get_thumbnail_path( $gif_link ) {
		$jpg_link = str_replace( '.gif', '_gif_thumbnail.jpeg', $gif_link );
		return str_replace( home_url( '/' ), ABSPATH, $jpg_link );
	}

	/**
	 * Get the first frame of the GIF and its content will be set
	 * into our JPEG file path.
	 *
	 * For this process we're using two GD and Image Functions:
	 *
	 * - imagejpeg
	 * - imagecreatefromgif
	 *
	 * Find more details in the links below.
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @link   https://www.php.net/manual/en/function.imagejpeg.php
	 * @link   https://www.php.net/manual/en/function.imagecreatefromgif
	 *
	 * @param  string   $gif_link   The original GIF link (uploaded by the user).
	 * @param  string   $jpg_path   The new thumbnail file path.
	 */
	private function create_thumbnail_file( $gif_link, $jpg_path ) {
		imagejpeg( imagecreatefromgif( $gif_link ), $jpg_path );
	}

	/**
	 * Get the attachment id from the passed parameters by the user.
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @param  string   $params   The current [gif-player] shortcode params.
	 * @return bool|int           The attachment id or false if doesn't exists.
	 */
	private function get_attachment_id_from_string( $params ) {
		$atts = shortcode_parse_atts( $params );
		return ( ! isset( $atts['id'] ) || empty( $atts['id'] ) ) ? false : (int) $atts['id'];
	}
}
