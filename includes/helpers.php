<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
