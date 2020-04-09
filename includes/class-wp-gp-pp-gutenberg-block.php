<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_GP_PP_Gutenberg_Block' ) ) {
	/**
	 * Handle all Gutenberg block functionality.
	 *
	 * @since 0.1.0
	 */
	class WP_GP_PP_Gutenberg_Block {
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
		 * The script handle to register in queue.
		 *
		 * @since  0.1.0
		 * @access private
		 */
		private const SCRIPT_HANDLE = 'wp-gp-pp.block.js';

		/**
		 * Initializes the settings and actions to render the
		 * Gutenberg block.
		 *
		 * The main assets for the block will enqueue only in
		 * the block editor.
		 *
		 * @since 0.1.0
		 */
		public function __construct() {
			$this->settings = wp_gp_pp_get_settings();

			add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_block_assets' ) );
			add_action( 'init', array( $this, 'register_block' ) );
		}

		/**
		 * Enqueue the main JS and CSS files to handle the
		 * Gutenberg block.
		 *
		 * The main assets for the block will enqueue only in
		 * the block editor.
		 *
		 * @since 0.1.0
		 */
		public function enqueue_editor_block_assets() {
			wp_enqueue_style(
				'wp-gp-pp.css',
				plugins_url( 'assets/css/wp-gp-pp.css', __DIR__ ),
				null,
				WP_GP_PP_VERSION
			);

			wp_enqueue_script(
				self::SCRIPT_HANDLE,
				plugins_url( 'assets/js/wp-gp-pp.block.js', __DIR__ ),
				array( 'wp-blocks', 'wp-editor', 'wp-element' ),
				WP_GP_PP_VERSION
			);

			wp_localize_script( self::SCRIPT_HANDLE, 'WP_GIF_PLAYER', array(
				'gifMethod' => $this->settings['gif_method'],
			) );
		}

		/**
		 * Register the Gutenberg block in WordPress.
		 *
		 * For the block we have to set a render callback so it can
		 * return the required view to the user.
		 *
		 * We're using Server Side Render (SSR) for this functionality.
		 *
		 * @since 0.1.0
		 */
		public function register_block() {
			register_block_type( WP_GP_PP_GUTENBERG_NAMESPACE, array(
				'editor_script'   => self::SCRIPT_HANDLE,
				'render_callback' => array( $this, 'render_gif_player' ),
				'attributes'      => array(
					'mediaURL'    => array(
						'type' => 'string',
					),
					'mediaID'     => array(
						'type' => 'number',
					),
					'gifMethod'   => array(
						'type'    => 'string',
						'default' => $this->settings['gif_method'],
					),
					'width'       => array(
						'type' => 'number',
					),
					'height'      => array(
						'type' => 'number',
					),
					'imageWidth'  => array(
						'type' => 'number',
					),
					'imageHeight' => array(
						'type' => 'number',
					),
				),
			) );
		}

		/**
		 * Render the GIF player requested by the user.
		 *
		 * The main values we need is the mediaID and gifMethod,
		 * without them it won't render anything.
		 *
		 * @since  0.1.0
		 *
		 * @param  array   $args   The current user arguments.
		 * @return string          The GIF player.
		 */
		public function render_gif_player( $args ) {
			if ( ! isset( $args['mediaID'] ) ) {
				return '';
			}

			$gif_method        = $args['gifMethod'] ?? $this->settings['gif_method'];
			$valid_gif_methods = array(
				'gif',
				'canvas',
				'video',
			);

			if ( ! in_array( $gif_method, $valid_gif_methods, true ) ) {
				return '';
			}

			$attachment_id = (int) $args['mediaID'];
			$attachment    = wp_gp_pp_get_attachment_data( $attachment_id, $args );

			if ( empty( $attachment ) ) {
				return '';
			}

			$render = 'wp_gp_pp_render_wrapper_for_' . $gif_method;
			$image  = $render( $attachment );
			$image .= '<div class="wp-gp-pp-overlay"> ';
			$image .= '<div class="wp-gp-pp-play-button">GIF</div> ';
			$image .= '</div> </div>';

			return $image;
		}
	}
}
