<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_GP_PP' ) ) {
	/**
	 * Main class to run the plugin functionality.
	 *
	 * @since 0.1.0
	 */
	class WP_GP_PP {
		/**
		 * The plugin settings.
		 *
		 * @since  0.1.0
		 * @var    array   $settings   The plugin settings.
		 */
		public $settings;

		/**
		 * The players methods in a post.
		 *
		 * @since  0.1.0
		 * @var    array   $players_in_post   The players methods in a post.
		 */
		public $players_in_post = array();

		/**
		 * Get the existent instance so we won't
		 * instantiate it over and over again.
		 *
		 * @since  0.1.0
		 * @access private
		 *
		 * @var    WP_GP_PP   $instance   The main class instance.
		 */
		private static $instance;

		/**
		 * Get the existent parser instance so we won't instantiate it over and over again.
		 * This is a singleton pattern.
		 *
		 * @ince   0.1.0
		 *
		 * @return WP_GP_PP   The current class instance.
		 */
		public static function get_instance() {
			if ( ! self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Initialize the classes and action hooks to run the plugin.
		 *
		 * @since 0.1.0
		 */
		public function __construct() {
			self::$instance = $this;
			$this->settings = wp_gp_pp_get_settings();

			new WP_GP_PP_Shortcode();
			new WP_GP_PP_Gutenberg_Block();

			add_action( 'wp', array( $this, 'get_players_in_post' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

			if ( ! is_admin() ) {
				return;
			}

			new WP_GP_PP_Media_Uploader();
			new WP_GP_PP_Options();
		}

		/**
		 * Enqueue the assets in frontend but only if shortcode
		 * or Gutenberg block exists in the current post.
		 *
		 * For canvas method it will enqueue the "libgif.js"
		 *
		 * @since 0.1.0
		 */
		public function enqueue_assets() {
			if ( ! $this->should_enqueue_assets() ) {
				return;
			}

			$settings  = wp_gp_pp_get_settings();
			$in_footer = true;

			wp_enqueue_style(
				'wp-gp-pp.css',
				plugins_url( 'assets/css/wp-gp-pp.css', __DIR__ ),
				null,
				WP_GP_PP_VERSION
			);

			if ( 'canvas' === $settings['gif_method'] ) {
				wp_enqueue_script(
					'wp-gp-pp-libgif.js',
					plugins_url( 'assets/js/libgif.js', __DIR__ ),
					null,
					WP_GP_PP_VERSION,
					$in_footer
				);
			}

			$file_handle = 'wp-gp-pp-' . $settings['gif_method'] . '.js';

			wp_enqueue_script(
				$file_handle,
				plugins_url( 'assets/js/' . $file_handle, __DIR__ ),
				null,
				WP_GP_PP_VERSION,
				$in_footer
			);
		}

		/**
		 * Whether to enqueue the assets on frontend or not.
		 *
		 * First we look into the post if the shortcode "gif-player" exists
		 * after that we can do another check if the post has the block
		 * "roelmagdaleno/gif-player".
		 *
		 * @since  0.1.0
		 *
		 * @return bool   Whether to enqueue the assets on frontend or not.
		 */
		private function should_enqueue_assets() {
			global $post;

			if ( has_shortcode( $post->post_content, 'gif-player' ) ) {
				return true;
			}

			return has_block( 'roelmagdaleno/gif-player', $post->post_content );
		}

		/**
		 * Get the current GIF method players inside of the current post.
		 * To make this work the method should execute in the "wp" action hook.
		 *
		 * Maybe we can transform this method to a helper function where accepts
		 * the post id or object as a parameter. But for now we don't need it.
		 *
		 * @since  0.1.0
		 *
		 * @return array   The current GIF players methods.
		 */
		public function get_players_in_post() {
			global $post;

			if ( ! $post ) {
				return array();
			}

			$post_blocks = parse_blocks( $post->post_content );
			$gif_players = array_filter( $post_blocks, array( $this, 'get_our_gif_player_blocks' ) );

			if ( empty( $gif_players ) ) {
				return array();
			}

			$players   = wp_list_pluck( array_column( $gif_players, 'attrs' ), 'gifMethod' );
			$players[] = $this->settings['gif_method']; // Add default method.

			$this->players_in_post = array_unique( $players );
			return $this->players_in_post;
		}

		/**
		 * Get our GIF player blocks only if passes the conditions:
		 * The block name belongs to ours and if "gifMethod" exists in the attributes.
		 *
		 * @since  0.1.0
		 * @access private
		 *
		 * @param  array   $block   The current block in the post.
		 * @return bool             The "gif-player" block data.
		 */
		private function get_our_gif_player_blocks( $block ) {
			return WP_GP_PP_GUTENBERG_NAMESPACE === $block['blockName'] && isset( $block['attrs']['gifMethod'] );
		}
	}
}
