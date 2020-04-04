<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_GP_PP_HTML_Radio' ) ) {
	/**
	 * Handle the construction and rendering of the
	 * <input> radio element.
	 *
	 * @since 0.1.0
	 */
	class WP_GP_PP_HTML_Radio {
		/**
		 * Render the <input> radio.
		 *
		 * @since  0.1.0
		 *
		 * @param  array   $setting_data   The current field setting data.
		 * @return string                  The constructed <input> radio element.
		 */
		public function render( $setting_data ) {
			$html    = '';
			$options = $setting_data['options'];

			foreach ( $options as $option_id => $option_text ) {
				$html .= '<label>';
				$html .= '<input type="radio" id="' . esc_attr( $option_id ) . '" ';
				$html .= 'name="wp_gp_pp_settings[' . esc_attr( $setting_data['name'] ) . ']" ';
				$html .= 'value="' . esc_attr( $option_id ) . '"';
				$html .= ' ' . checked( $option_id, $setting_data['current'], false ) . '>';
				$html .= '<span>' . $option_text . '</span> </label> <br>';
			}

			return $html;
		}
	}
}
