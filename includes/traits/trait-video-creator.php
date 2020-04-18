<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait WP_GP_PP_Video_Creator {
	/**
	 * Create a video from the current GIF.
	 *
	 * We're using the "ffmpeg" library to convert the GIF to our default
	 * video formats (webm and mp4).
	 *
	 * WARNING:
	 * You need "ffmpeg" library in your server so the the <video> can work.
	 *
	 * @since 0.1.0
	 *
	 * @param int      $attachment_id   The current attachment id.
	 * @param string   $gif_link        The original GIF link (uploaded by the user).
	 */
	public function create_video_from_gif( $attachment_id, $gif_link ) {
		$ffmpeg_installed = wp_gp_pp_is_ffmpeg_installed();

		if ( is_wp_error( $ffmpeg_installed ) ) {
			return;
		}

		$gif_path    = str_replace( home_url( '/' ), ABSPATH, $gif_link );
		$video_types = $this->get_video_extensions_and_commands();

		foreach ( $video_types as $video_type => $video_command ) {
			$video_path = str_replace( '.gif', $video_type, $gif_path );

			if ( file_exists( $video_path ) ) {
				continue;
			}

			shell_exec( 'ffmpeg -i ' . $gif_path . ' ' . $video_command . ' ' . $video_path ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions

			if ( 0 === filesize( $video_path ) ) {
				wp_delete_file( $video_path );
				continue;
			}

			$video_url      = wp_gp_pp_path_to_url( $video_path );
			$file_type      = wp_check_filetype( $video_path );
			$new_attachment = array(
				'guid'           => $video_url,
				'post_mime_type' => $file_type['type'],
				'post_title'     => str_replace( $video_type, '', wp_basename( $video_url ) ),
				'post_content'   => '',
				'post_status'    => 'inherit',
				'post_parent'    => $attachment_id,
			);

			wp_gp_pp_insert_new_attachment( $new_attachment, $video_path );
		}
	}

	/**
	 * Get the video file extensions that will be used to convert
	 * the current GIF.
	 *
	 * Every file extension has its own command. That command belongs to
	 * our main dependency "ffmpeg".
	 *
	 * These commands were extracted from:
	 * https://developers.google.com/web/fundamentals/performance/optimizing-content-efficiency/replace-animated-gifs-with-video
	 *
	 * If you add a new command be sure is a valid one otherwise the videos won't generate.
	 *
	 * WARNING:
	 * You need "ffmpeg" library in your server so the the <video> can work.
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @return array   The video file extensions and its commands to use with ffmpeg.
	 */
	private function get_video_extensions_and_commands() {
		$video_types = array(
			'.webm' => '-c vp9 -b:v 0 -crf 41',
			'.mp4'  => '-b:v 0 -crf 25 -f mp4 -vcodec libx264 -pix_fmt yuv420p',
		);

		return apply_filters( 'wp_gp_pp_video_types_and_commands', $video_types );
	}
}
