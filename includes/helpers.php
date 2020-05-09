<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Verify if "ffmpeg" library is installed in the server.
 *
 * For now we have three validations:
 *
 * 1. Check if "shell_exec" function exists.
 * 2. Check if current command belongs to "ffmpeg".
 * 3. Check if "ffmpeg" library is installed.
 *
 * For the last step we are extracting the strings to compare
 * the version string and if the version number is a real number.
 *
 * @since  0.1.0
 *
 * @return bool|WP_Error   Verify if "ffmpeg" library is installed in the server.
 */
function wp_gp_pp_is_ffmpeg_installed() {
	$settings = WP_GP_PP::get_instance()->settings;

	if ( isset( $settings['ffmpeg_installed'] ) && (bool) $settings['ffmpeg_installed'] ) {
		return true;
	}

	$errors = array(
		'no_shell_exec' => array(
			'title'       => 'The "shell_exec" function is not enabled in your server.',
			'description' => 'To use the "<strong>ffmpeg</strong>" command to <strong>convert GIF to Video</strong> you need to enable the "<strong>shell_exec</strong>" command in your PHP configuration.',
		),
		'not_installed' => array(
			'title'       => 'Library "FFmpeg" and "VP9" codec not installed.',
			'description' => 'To use the <strong>video</strong> method for the GIF player you need to install the "FFmpeg" library with "VP9" codec in your server.',
		),
		'no_example'    => array(
			'title'       => 'The example.gif file to test the FFmpeg video conversion does not exists.',
			'description' => 'The "admin/images/example.gif" file is missing. Download the plugin again and be sure the file exists.',
		),
		'not_working'   => array(
			'title'       => 'The FFmpeg library is not working as expected.',
			'description' => 'Check and fix your FFmpeg configuration in your server and run the library test again.',
		),
	);

	if ( ! function_exists( 'shell_exec' ) ) {
		return new WP_Error( 'no_shell_exec', $errors['no_shell_exec']['title'], $errors['no_shell_exec'] );
	}

	$ffmpeg_test    = shell_exec( 'ffmpeg 2>&1' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
	$ffmpeg_strings = explode( ' ', $ffmpeg_test );
	$command        = $ffmpeg_strings[0];
	$version_string = $ffmpeg_strings[1];
	$version_number = (int) str_replace( '.', '', $ffmpeg_strings[2] );

	if ( 'ffmpeg' !== $command ) {
		return new WP_Error( 'not_installed', $errors['not_installed']['title'], $errors['not_installed'] );
	}

	if ( 'version' !== $version_string || ! is_numeric( $version_number ) ) {
		return new WP_Error( 'not_installed', $errors['not_installed']['title'], $errors['not_installed'] );
	}

	$test = wp_gp_pp_test_example_video();

	if ( ! $test['success'] ) {
		return new WP_Error( $test['code'], $errors[ $test['code'] ]['title'], $errors[ $test['code'] ] );
	}

	$settings['ffmpeg_installed']      = true;
	WP_GP_PP::get_instance()->settings = $settings;

	update_option( 'wp_gp_pp_settings', $settings, 'no' );

	return true;
}

/**
 * Test the FFmpeg library by converting the example.gif file
 * in webm and mp4 formats.
 *
 * Our example.gif file path is "admin/images/example.gif".
 *
 * The generated videos will be deleted from our plugin to not grow
 * the plugin size.
 *
 * @since  0.1.0
 *
 * @return array   Whether the ffmpeg test was success or not. Includes the error code if fails.
 */
function wp_gp_pp_test_example_video() {
	$gif_path = WP_GP_PP_PLUGIN_PATH . 'admin/images/example.gif';
	$response = array(
		'success' => false,
		'code'    => 'not_working',
	);

	if ( ! file_exists( $gif_path ) ) {
		$response['code'] = 'no_example';
		return $response;
	}

	$video_paths = array();
	$video_types = array(
		'.webm' => '-c vp9 -b:v 0 -crf 41',
		'.mp4'  => '-b:v 0 -crf 25 -f mp4 -vcodec libx264 -pix_fmt yuv420p -vf "pad=ceil(iw/2)*2:ceil(ih/2)*2"',
	);

	foreach ( $video_types as $video_type => $video_command ) {
		$video_path = str_replace( '.gif', $video_type, $gif_path );

		shell_exec( 'ffmpeg -i ' . $gif_path . ' ' . $video_command . ' ' . $video_path ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions

		if ( ! file_exists( $video_path ) ) {
			continue;
		}

		if ( 0 === filesize( $video_path ) ) {
			wp_delete_file( $video_path );
			continue;
		}

		$video_paths[] = $video_path;
		wp_delete_file( $video_path );
	}

	return empty( $video_paths ) ? $response : array( 'success' => true );
}

/**
 * Whether WordPress is in DEBUG MODE.
 * This setting is configured in "wp-config.php" file.
 *
 * @return bool   Whether WordPress is in DEBUG MODE.
 */
function wp_gp_pp_is_debug_mode() {
	return defined( 'WP_DEBUG' ) && WP_DEBUG;
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

	$attachment_data = array(
		'thumbnail'     => wp_gp_pp_get_thumbnail( $gif_source[0] ),
		'width'         => esc_attr( $default['width'] ?? $gif_source[1] ),
		'height'        => esc_attr( $default['height'] ?? $gif_source[2] ),
		'attachment_id' => $attachment_id,
		'image_id'      => 'wp-gp-pp--id-' . $attachment_id,
	);

	if ( isset( $default['align'] ) ) {
		$attachment_data['align'] = esc_attr( $default['align'] );
	}

	return $attachment_data;
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
 * @since  0.1.1    Add filter to update the default CSS classes for the GIF asset.
 *
 * @param  array    $attachment   The GIF attachment data.
 * @return string                 The GIF player wrapper for canvas method.
 */
function wp_gp_pp_render_wrapper_for_gif( $attachment ) {
	$thumbnail = $attachment['thumbnail'];
	$width     = $attachment['width'];
	$height    = $attachment['height'];
	$image_id  = $attachment['image_id'];

	$default     = array( 'wp-gp-pp-gif' );
	$css_classes = apply_filters( 'wp_gp_pp_gif_css_classes', $default );
	$css_classes = wp_gp_pp_validate_css_filter( $css_classes, $default );

	$image  = '<div class="wp-gp-pp-container" style="width: ' . $width . 'px; height: ' . $height . 'px" ';
	$image .= 'data-width="' . $width . '" data-height="' . $height . '" data-media-id="' . $attachment['attachment_id'] . '">';
	$image .= '<img src="' . esc_attr( $thumbnail ) . '" id="' . esc_attr( $image_id ) . '--thumbnail" ';
	$image .= 'class="wp-gp-pp-gif-thumbnail" width="' . $width . '" height="' . $height . '" alt="">';

	$image .= '<img src="" id="' . esc_attr( $image_id . wp_rand() ) . '" ';
	$image .= 'class="' . implode( ' ', $css_classes ) . '" width="' . $width . '" height="' . $height . '" alt="">';

	return $image;
}

/**
 * Generate the HTML to use the GIF player as a canvas method.
 *
 * This <canvas> method will use the "libgif.js" library to setup
 * the GIF play and pause actions.
 *
 * @since  0.1.0
 * @since  0.1.1    Add filter to update the default CSS classes for the GIF asset.
 *
 * @param  array    $attachment   The GIF attachment data.
 * @return string                 The GIF player wrapper for canvas method.
 */
function wp_gp_pp_render_wrapper_for_canvas( $attachment ) {
	$thumbnail = $attachment['thumbnail'];
	$width     = $attachment['width'];
	$height    = $attachment['height'];
	$source    = str_replace( '_gif_thumbnail.jpeg', '.gif', $thumbnail );

	$default     = array( 'wp-gp-pp-gif-canvas-player' );
	$css_classes = apply_filters( 'wp_gp_pp_canvas_css_classes', $default );
	$css_classes = wp_gp_pp_validate_css_filter( $css_classes, $default );

	$image  = '<div class="wp-gp-pp-container" style="width: ' . $width . 'px; height: ' . $height . 'px" ';
	$image .= 'data-width="' . $width . '" data-height="' . $height . '" data-media-id="' . $attachment['attachment_id'] . '">';
	$image .= '<img src="' . esc_attr( $thumbnail ) . '" id="' . esc_attr( $attachment['image_id'] . wp_rand() ) . '" ';
	$image .= 'rel:animated_src="' . esc_attr( $source ) . '" rel:auto_play="0" class="' . implode( ' ', $css_classes ) . '" ';
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
 * @since  0.1.1    Add filter to update the default CSS classes for the GIF asset.
 * @since  0.1.2    Render original GIF as fallback when no video sources found.
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

	if ( empty( $children ) ) {
		return '<p>For some reason the selected GIF was not converted to video format. Please, use the REPLACE option to select a new GIF.</p>';
	}

	$sources = '';

	foreach ( $children as $video_source ) {
		$video_path = wp_gp_pp_url_to_path( $video_source['guid'] );

		if ( ! file_exists( $video_path ) ) {
			continue;
		}

		$guid = esc_attr( $video_source['guid'] );
		$type = esc_attr( $video_source['post_mime_type'] );

		$sources .= '<source src="' . $guid . '" type="' . $type . '">';
	}

	if ( empty( $sources ) ) {
		return wp_gp_pp_render_wrapper_for_gif( $attachment );
	}

	$default     = array( 'wp-gp-pp-video-player' );
	$css_classes = apply_filters( 'wp_gp_pp_video_css_classes', $default );
	$css_classes = wp_gp_pp_validate_css_filter( $css_classes, $default );

	$video  = '<div class="wp-gp-pp-container" style="width: ' . $width . 'px; height: ' . $height . 'px" ';
	$video .= 'data-width="' . $width . '" data-height="' . $height . '" data-media-id="' . $attachment['attachment_id'] . '">';
	$video .= '<video loop muted playsinline class="' . implode( ' ', $css_classes ) . '" ';
	$video .= 'poster="' . esc_attr( $thumbnail ) . '" id="' . esc_attr( $attachment['image_id'] . wp_rand() ) . '" ';
	$video .= 'width="' . $width . '" height="' . $height . '">';
	$video .= $sources;
	$video .= '</video>';

	return $video;
}

/**
 * Check if the current GIF needs a fallback render method.
 * This function applies only for videos.
 *
 * @since  0.1.2
 *
 * @param  WP_Post   $attachment   The current attachment ID.
 * @return bool                    Whether the GIF video needs render fallback.
 */
function wp_gp_pp_video_needs_fallback( $attachment ) {
	if ( 'image/gif' !== $attachment->post_mime_type ) {
		return false;
	}

	$args = array(
		'post_parent'    => $attachment->ID,
		'orderby'        => 'ID',
		'order'          => 'ASC',
		'post_mime_type' => wp_gp_pp_get_video_mime_types(),
	);

	$children = get_children( $args, ARRAY_A );

	if ( empty( $children ) ) {
		return true;
	}

	$sources = array();

	foreach ( $children as $video_source ) {
		$video_path = wp_gp_pp_url_to_path( $video_source['guid'] );

		if ( ! file_exists( $video_path ) ) {
			continue;
		}

		$sources[] = $video_path;
	}

	return empty( $sources );
}

/**
 * Validate if the CSS filter for the GIF render methods are valid.
 *
 * If the user filtered value is not array or is an empty value
 * it will return the plugin default value.
 *
 * @since  0.1.1
 *
 * @param  array   $filter_value   The user filtered value.
 * @param  array   $default        The plugin default value.
 * @return array                   The user filtered value if validation passes or default it fails.
 */
function wp_gp_pp_validate_css_filter( $filter_value, $default ) {
	return ( ! is_array( $filter_value ) || empty( $filter_value ) ) ? $default : $filter_value;
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
	return str_replace( ABSPATH, home_url( '/' ), $path );
}

/**
 * Convert the media element to a valid file path.
 *
 * @since  0.1.2
 *
 * @param  string   $url   The current GIF/Image/Video URL.
 * @return string          The converted GIF/Image/Video path.
 */
function wp_gp_pp_url_to_path( $url ) {
	return str_replace( home_url( '/' ), ABSPATH, $url );
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
 * @param string   $file             The GIF asset path.
 */
function wp_gp_pp_insert_new_attachment( $new_attachment, $file ) {
	$new_attachment_id = wp_insert_attachment(
		$new_attachment,
		$file,
		$new_attachment['post_parent']
	);

	if ( 0 === $new_attachment_id || is_wp_error( $new_attachment_id ) ) {
		return;
	}

	if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
	}

	if ( ! function_exists( 'wp_read_video_metadata' ) ) {
		require_once ABSPATH . 'wp-admin/includes/media.php';
	}

	$attachment_data = wp_generate_attachment_metadata( $new_attachment_id, $file );
	wp_update_attachment_metadata( $new_attachment_id, $attachment_data );
}
