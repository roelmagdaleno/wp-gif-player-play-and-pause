<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_GP_PP_HTML_Hidden' ) ) {
	/**
	 * Handle the construction and rendering of the
	 * <input> hidden element.
	 *
	 * @since 0.1.0
	 */
	class WP_GP_PP_HTML_Hidden {
		/**
		 * Render the <input> hidden.
		 *
		 * @since  0.1.0
		 *
		 * @param  array   $setting_data   The current field setting data.
		 * @return string                  The constructed <input> hidden element.
		 */
		public function render( $setting_data ) {
			$html  = '<input type="hidden" ';
			$html .= 'id="' . esc_attr( $setting_data['id'] ) . '" ';
			$html .= 'name="wp_gp_pp_settings[' . esc_attr( $setting_data['name'] ) . ']" ';
			$html .= 'value="' . esc_attr( $setting_data['current'] ) . '" />';

			return $html;
		}
	}
}
