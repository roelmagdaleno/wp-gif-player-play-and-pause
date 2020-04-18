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

			register_activation_hook( WP_GP_PP_PLUGIN_FILE, array( $this, 'install_default_options' ) );

			new WP_GP_PP_Shortcode();
			new WP_GP_PP_Gutenberg_Block();
			new WP_GP_PP_Media();

			add_action( 'wp', array( $this, 'get_players_in_post' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

			if ( ! is_admin() ) {
				return;
			}

			add_action( 'admin_notices', array( $this, 'show_admin_notices' ) );
			add_filter( 'plugin_action_links', array( $this, 'add_settings_action_link' ), 10, 2 );
			add_filter( 'network_admin_plugin_action_links', array( $this, 'add_settings_action_link' ), 10, 2 );

			new WP_GP_PP_Options();
		}

		/**
		 * Add the "Settings" action link to our plugin inside
		 * of the plugins table.
		 *
		 * @since  0.1.0
		 *
		 * @param  array    $actions        An array of plugin action links.
		 * @param  string   $plugin_file    Path to the plugin file relative to the plugins directory.
		 * @return array                    The plugin action links with Settings for our plugin.
		 */
		public function add_settings_action_link( $actions, $plugin_file ) {
			if ( false !== strpos( $plugin_file, 'wp-gif-player-play-and-pause.php' ) ) {
				$url                 = admin_url( 'options-general.php?page=wp-gif-player' );
				$actions['settings'] = '<a href="' . esc_attr( $url ) . '">Settings</a>';
			}

			return $actions;
		}

		/**
		 * Add or update the plugin settings.
		 *
		 * If the setting doesn't exists then will store the default options
		 * otherwise will look for any setting that doesn't exists yet
		 * in the stored settings.
		 *
		 * @since 0.1.0
		 */
		public function install_default_options() {
			$option_name   = 'wp_gp_pp_settings';
			$stored_option = get_option( $option_name, array() );
			$option_values = array(
				'gif_method'       => 'gif',
				'ffmpeg_installed' => false,
			);

			if ( empty( $stored_option ) ) {
				update_option( $option_name, $option_values, 'no' );
				return;
			}

			if ( empty( array_diff_key( $option_values, $stored_option ) ) ) {
				return;
			}

			foreach ( $option_values as $value => $data ) {
				if ( isset( $stored_option[ $value ] ) ) {
					continue;
				}

				$stored_option[ $value ] = $data;
			}

			update_option( $option_name, $stored_option, 'no' );
		}

		/**
		 * Show the stored admin notices in the transient data.
		 *
		 * The transient data needs a "type" and "message" values to
		 * shot the admin notice properly.
		 *
		 * @since 0.1.0
		 */
		public function show_admin_notices() {
			$transient = get_transient( 'wp_gp_pp_admin_notice' );

			if ( ! $transient ) {
				return;
			}

			if ( ! isset( $transient['type'], $transient['message'] ) ) {
				return;
			}

			$message  = '<div class="notice notice-' . esc_attr( $transient['type'] ) . ' is-dismissible">';
			$message .= '<p> <strong>WP GIF Player - Play & Pause</strong> </p>';
			$message .= '<p>' . esc_html( $transient['message'] ) . '</p>';
			$message .= '</div>';

			delete_transient( 'wp_gp_pp_admin_notice' );

			echo $message; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
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

			$in_footer = true;

			wp_enqueue_style(
				'wp-gp-pp.css',
				plugins_url( 'assets/css/wp-gp-pp.css', __DIR__ ),
				null,
				WP_GP_PP_VERSION
			);

			if ( 'canvas' === $this->settings['gif_method'] || in_array( 'canvas', $this->players_in_post, true ) ) {
				wp_enqueue_script(
					'wp-gp-pp-libgif.js',
					plugins_url( 'assets/js/libgif.js', __DIR__ ),
					null,
					WP_GP_PP_VERSION,
					$in_footer
				);
			}

			$script_handle = wp_gp_pp_is_debug_mode() ? 'wp-gp-pp.js' : 'wp-gp-pp.min.js';

			wp_enqueue_script(
				$script_handle,
				plugins_url( 'assets/js/' . $script_handle, __DIR__ ),
				null,
				WP_GP_PP_VERSION,
				$in_footer
			);

			$js_values = array(
				'defaultGifPlayer' => $this->settings['gif_method'],
				'gifPlayersInPost' => $this->players_in_post,
			);

			wp_localize_script( $script_handle, 'WP_GIF_PLAYER', $js_values );
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

			if ( ! $post ) {
				return false;
			}

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

			// Always include the default method.
			$this->players_in_post = array( $this->settings['gif_method'] );

			if ( ! $post ) {
				return $this->players_in_post;
			}

			$post_blocks = parse_blocks( $post->post_content );
			$gif_players = array_filter( $post_blocks, array( $this, 'get_our_gif_player_blocks' ) );

			if ( empty( $gif_players ) ) {
				return $this->players_in_post;
			}

			$gif_players           = wp_list_pluck( array_column( $gif_players, 'attrs' ), 'gifMethod' );
			$this->players_in_post = array_unique( array_merge( $this->players_in_post, $gif_players ) );

			return $this->players_in_post;
		}

		/**
		 * Get our GIF player blocks only if passes the conditions:
		 * The block name belongs to ours and if "gifMethod" exists in the attributes.
		 *
		 * @since  0.1.0
		 *
		 * @param  array   $block   The current block in the post.
		 * @return bool             The "gif-player" block data.
		 */
		public function get_our_gif_player_blocks( $block ) {
			return WP_GP_PP_GUTENBERG_NAMESPACE === $block['blockName'] && isset( $block['attrs']['gifMethod'] );
		}
	}
}
