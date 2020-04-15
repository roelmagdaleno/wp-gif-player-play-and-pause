<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_GP_PP_Options' ) ) {
	/**
	 * Render all form fields into our settings page.
	 *
	 * @since 0.1.0
	 */
	class WP_GP_PP_Options {
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
		 * Whether ffmpeg library is installed.
		 *
		 * @since  0.1.0
		 * @access private
		 *
		 * @var    bool|WP_Error   $ffmpeg_installed   Whether ffmpeg library is installed.
		 */
		private $ffmpeg_installed;

		/**
		 * The plugin admin page slug.
		 *
		 * @since  0.1.0
		 * @access private
		 */
		private const ADMIN_PAGE = 'wp-gif-player';

		/**
		 * The plugin option name to store our settings.
		 *
		 * @since  0.1.0
		 * @access private
		 */
		private const OPTION_NAME = 'wp_gp_pp_settings';

		/**
		 * Initialize the plugin settings and register the action
		 * hooks that enables our submenu page.
		 *
		 * @since 0.1.0
		 */
		public function __construct() {
			$this->settings = WP_GP_PP::get_instance()->settings;

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
			add_action( 'wp_ajax_wp_gp_pp_test_ffmpeg', array( $this, 'test_ffmpeg' ) );
			add_action( 'admin_menu', array( $this, 'register_submenu' ) );
			add_action( 'admin_init', array( $this, 'add_fields' ) );
		}

		/**
		 * Test if "ffmpeg" command exists on the server using AJAX
		 * request. We will send a JSON error if any ffmpeg is not installed
		 * otherwise send a success message.
		 *
		 * @since 0.1.0
		 */
		public function test_ffmpeg() {
			check_admin_referer( 'wp-gp-pp-gif-player' );

			if ( (bool) $this->settings['ffmpeg_installed'] ) {
				return;
			}

			$is_installed = wp_gp_pp_is_ffmpeg_installed();

			is_wp_error( $is_installed )
				? wp_send_json_error( $is_installed->get_error_data() )
				: wp_send_json_success( array(
					'title'       => 'Library "FFmpeg" is installed in your server.',
					'description' => 'You can now convert GIF to Videos and use it in your posts and pages.',
				) );
		}

		/**
		 * Enqueue the plugin admin script that allow to test
		 * the FFmpeg library in the server.
		 *
		 * @since 0.1.0
		 *
		 * @param string   $hook_sufix   The current admin page.
		 */
		public function enqueue_admin_scripts( $hook_sufix ) {
			if ( 'settings_page_wp-gif-player' !== $hook_sufix ) {
				return;
			}

			$this->ffmpeg_installed = wp_gp_pp_is_ffmpeg_installed();

			if ( ! is_wp_error( $this->ffmpeg_installed ) ) {
				return;
			}

			wp_enqueue_script(
				'wp-gp-pp.admin.js',
				plugins_url( 'assets/js/wp-gp-pp.admin.js', __DIR__ ),
				null,
				WP_GP_PP_VERSION,
				true
			);

			wp_localize_script( 'wp-gp-pp.admin.js', 'WP_GP_PP_ADMIN', array(
				'admin_url'  => admin_url( 'admin-ajax.php' ),
				'ajax_nonce' => wp_create_nonce( 'wp-gp-pp-gif-player' ),
			) );
		}

		/**
		 * Register the submenu inside of Settings menu or also
		 * called "options-general.php".
		 *
		 * @since 0.1.0
		 */
		public function register_submenu() {
			add_submenu_page(
				'options-general.php',
				'WP GIF Player - Play & Pause',
				'WP GIF Player',
				'manage_options',
				self::ADMIN_PAGE,
				array( $this, 'show_submenu_page' )
			);
		}

		/**
		 * Add the form fields into the plugin section.
		 * Every field will call its own class depending on its input type.
		 *
		 * The settings will be grabbed from "$this->settings()" method.
		 *
		 * @since 0.1.0
		 */
		public function add_fields() {
			register_setting( self::ADMIN_PAGE, self::OPTION_NAME );

			$section_id = 'wp_gp_pp_section';

			add_settings_section(
				$section_id,
				'Settings',
				'__return_false',
				self::ADMIN_PAGE
			);

			foreach ( $this->settings() as $setting_id => $setting_data ) {
				$label = isset( $setting_data['title'] )
					? '<label for="' . $setting_id . '">' . $setting_data['title'] . '</label>'
					: '';

				add_settings_field(
					'wp_gp_pp_setting_field_' . $setting_id,
					$label,
					array( $this, 'generate_field' ),
					self::ADMIN_PAGE,
					$section_id,
					$setting_data
				);
			}
		}

		/**
		 * Render the field.
		 *
		 * The render process doesn't happen here but in its own field class.
		 * We will use the field input type to render it.
		 *
		 * @since 0.1.0
		 *
		 * @param array   $setting_data   The current field setting data.
		 */
		public function generate_field( $setting_data ) {
			$setting_data['current'] = $this->settings[ $setting_data['id'] ] ?? '';

			$class = 'WP_GP_PP_HTML_' . ucfirst( $setting_data['type'] );
			echo ( new $class() )->render( $setting_data );
		}

		/**
		 * Show the submenu page content inside of its own page.
		 *
		 * It contains the form tag and start to render the previous
		 * registered settings.
		 *
		 * @since 0.1.0
		 */
		public function show_submenu_page() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			echo '<h1>WP GIF Player - Play & Pause</h1>';

			echo '<div class="wrap">';

			if ( is_wp_error( $this->ffmpeg_installed ) ) {
				$title       = $this->ffmpeg_installed->get_error_message();
				$description = $this->ffmpeg_installed->get_error_data()['description'];

				$warning  = '<section class="notice notice-warning" id="wp-gp-pp-admin-notice">';
				$warning .= '<p class="wp-gp-pp-title"><strong>' . esc_html( $title ) . '</strong></p>';
				$warning .= '<p class="wp-gp-pp-description">' . $description . '</p>';
				$warning .= '<section class="wp-gp-pp-button-section"><p><button class="button button-primary" style="margin-right: 10px;" onclick="WP_GP_PP_testFFmpeg(this)">Test FFmpeg</button>';
				$warning .= '<a href="https://ffmpeg.org/download.html" target="_blank" class="button button-secondary" style="display: inline-flex; align-items: center;">';
				$warning .= 'Download FFmpeg <span class="dashicons dashicons-external" style="margin-left: 5px;"></span></a></p>';
				$warning .= '<p class="description">If you don\'t know how to download and install FFmpeg ask your hosting support to do it.</p> </section>';
				$warning .= '</section>';

				echo $warning;
			}

			echo '<form action="options.php" method="POST">';

			settings_fields( self::ADMIN_PAGE );
			do_settings_sections( self::ADMIN_PAGE );
			submit_button();

			echo '</form> </div>';
		}

		/**
		 * The plugin settings to render the form.
		 *
		 * @since  0.1.0
		 * @access private
		 *
		 * @return array   The plugin settings to render the form.
		 */
		private function settings() {
			return array(
				'gif_method'       => array(
					'id'       => 'gif_method',
					'name'     => 'gif_method',
					'title'    => 'GIF Method',
					'type'     => 'radio',
					'options'  => array(
						'gif'    => 'GIF',
						'canvas' => 'Canvas',
						'video'  => 'Video (Recommended)',
					),
					'disabled' => $this->settings['ffmpeg_installed'] ? array() : array( 'video' ),
				),
				'ffmpeg_installed' => array(
					'id'    => 'ffmpeg_installed',
					'name'  => 'ffmpeg_installed',
					'title' => '',
					'type'  => 'hidden',
				),
			);
		}
	}
}
