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
		 * Initialize the classes and action hooks to run the plugin.
		 *
		 * @since 0.1.0
		 */
		public function __construct() {
			new WP_GP_PP_Options();
		}
	}
}
