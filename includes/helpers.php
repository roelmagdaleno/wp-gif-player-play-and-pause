<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the attachment data to pass to our render
 * methods so it can grab the data and finally show to the user.
 *
 * @since  0.1.0
 *
 * @param  int     $attachment_id   The current attachment id.
 * @param  array   $default         The default values to pass to the GIF player.
 * @return array                    The current attachment data.
 */
function wp_gp_pp_get_attachment_data( $attachment_id, $default = array() ) {
	if ( ! wp_gp_pp_is_gif( $attachment_id ) ) {
		return array();
	}

	$gif_source = wp_gp_pp_get_gif_source( $attachment_id );

	if ( ! $gif_source ) {
		return array();
	}

	return array(
		'thumbnail'     => wp_gp_pp_get_thumbnail( $gif_source[0] ),
		'width'         => esc_attr( $default['width'] ?? $gif_source[1] ),
		'height'        => esc_attr( $default['height'] ?? $gif_source[2] ),
		'attachment_id' => $attachment_id,
		'image_id'      => 'wp-gp-pp--id-' . $attachment_id,
	);
}

/**
 * Get the attachment image source by giving the
 * attachment id.
 *
 * By default we're getting the "full" size but if
 * you want to get another size you can change it using
 * the filter: "wp_gp_pp_attachment_size".
 *
 * The response will be an array where the first index is
 * the source url then width and height, for example:
 *
 * array( 'https://...', 300, 300 )
 *
 * @since  0.1.0
 *
 * @param  int   $attachment_id   The current attachment id.
 * @return array|false            The attachment source.
 */
function wp_gp_pp_get_gif_source( $attachment_id ) {
	$size = apply_filters( 'wp_gp_pp_attachment_size', 'full' );
	return wp_get_attachment_image_src( $attachment_id, $size );
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
function wp_gp_pp_get_thumbnail( $gif_link ) {
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
 * @return string                 The GIF player wrapper for canvas method.
 */
function wp_gp_pp_render_wrapper_for_gif( $attachment ) {
	$thumbnail = $attachment['thumbnail'];
	$width     = $attachment['width'];
	$height    = $attachment['height'];
	$image_id  = $attachment['image_id'];

	$image  = '<div class="wp-gp-pp-container" style="width: ' . $width . 'px; height: ' . $height . 'px" ';
	$image .= 'data-width="' . $width . '" data-height="' . $height . '" data-media-id="' . $attachment['attachment_id'] . '">';
	$image .= '<img src="' . esc_attr( $thumbnail ) . '" id="' . esc_attr( $image_id ) . '--thumbnail" ';
	$image .= 'class="wp-gp-pp-gif-thumbnail" width="' . $width . '" height="' . $height . '" alt="">';

	$image .= '<img src="" id="' . esc_attr( $image_id ) . '" ';
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
 * @return string                 The GIF player wrapper for canvas method.
 */
function wp_gp_pp_render_wrapper_for_canvas( $attachment ) {
	$thumbnail = $attachment['thumbnail'];
	$width     = $attachment['width'];
	$height    = $attachment['height'];
	$source    = str_replace( '_gif_thumbnail.jpeg', '.gif', $thumbnail );

	$image  = '<div class="wp-gp-pp-container" style="width: ' . $width . 'px; height: ' . $height . 'px" ';
	$image .= 'data-width="' . $width . '" data-height="' . $height . '" data-media-id="' . $attachment['attachment_id'] . '">';
	$image .= '<img src="' . esc_attr( $thumbnail ) . '" id="' . esc_attr( $attachment['image_id'] ) . '" ';
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
 * @return string                 The GIF player wrapper for canvas method.
 */
function wp_gp_pp_render_wrapper_for_video( $attachment ) {
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

	$video  = '<div class="wp-gp-pp-container" style="width: ' . $width . 'px; height: ' . $height . 'px" ';
	$video .= 'data-width="' . $width . '" data-height="' . $height . '" data-media-id="' . $attachment['attachment_id'] . '">';
	$video .= '<video loop muted playsinline class="wp-gp-pp-video-player" ';
	$video .= 'poster="' . esc_attr( $thumbnail ) . '" id="' . esc_attr( $attachment['image_id'] ) . '" ';
	$video .= 'width="' . $width . '" height="' . $height . '">';

	foreach ( $children as $video_source ) {
		$guid = esc_attr( $video_source['guid'] );
		$type = esc_attr( $video_source['post_mime_type'] );

		$video .= '<source src="' . $guid . '" type="' . $type . '">';
	}

	$video .= '</video>';

	return $video;
}

/**
 * Get the current plugin settings.
 *
 * @since  0.1.0
 *
 * @return array   The current plugin settings.
 */
function wp_gp_pp_get_settings() {
	return get_option( 'wp_gp_pp_settings', array() );
}

/**
 * Whether the current attachment id belongs to a GIF image.
 *
 * @since  0.1.0
 *
 * @param  int   $attachment_id   The current attachment id.
 * @return bool                   Whether the attachment is a GIF.
 */
function wp_gp_pp_is_gif( $attachment_id ) {
	$mime = get_post_mime_type( $attachment_id );

	if ( ! $mime || empty( $mime ) ) {
		return false;
	}

	return 'image/gif' === $mime;
}

/**
 * Convert the media element to a valid URL.
 * Mostly this URL will be stored in the new attachment data.
 *
 * @since  0.1.0
 *
 * @param  string   $path   The current GIF/Image/Video path.
 * @return string           The converted GIF/Image/Video url.
 */
function wp_gp_pp_path_to_url( $path ) {
	return str_replace( ABSPATH, home_url(), $path );
}

/**
 * Get the allowed video mime types.
 *
 * These mime types will be use to search the children that
 * belongs to the current GIF with its attachment id.
 *
 * The children posts are previously stored and attached to their parents.
 *
 * @since  0.1.0
 *
 * @return array   The allowed video mime types.
 */
function wp_gp_pp_get_video_mime_types() {
	$video_mime_types = array( 'video/webm', 'video/mp4' );
	return apply_filters( 'wp_gp_pp_video_mime_types', $video_mime_types );
}

/**
 * Insert the new attachment data into the database.
 *
 * The "parent_id" value is the original attachment id that
 * belongs to the current GIF.
 *
 * @since 0.1.0
 *
 * @param array    $new_attachment   The new attachment data.
 * @param string   $thumbnail_path   The GIF thumbnail path.
 */
function wp_gp_pp_insert_new_attachment( $new_attachment, $thumbnail_path ) {
	$new_attachment_id = wp_insert_attachment(
		$new_attachment,
		$thumbnail_path,
		$new_attachment['parent_id']
	);

	if ( 0 === $new_attachment_id || is_wp_error( $new_attachment_id ) ) {
		return;
	}

	if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
	}

	$attachment_data = wp_generate_attachment_metadata( $new_attachment_id, $thumbnail_path );
	wp_update_attachment_metadata( $new_attachment_id, $attachment_data );
}
