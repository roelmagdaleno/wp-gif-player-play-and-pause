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
