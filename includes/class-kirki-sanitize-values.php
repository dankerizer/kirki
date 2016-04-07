<?php
/**
 * Additional sanitization methods for controls.
 * These are used in the field's 'sanitize_callback' argument.
 *
 * @package     Kirki
 * @category    Core
 * @author      Aristeides Stathopoulos
 * @copyright   Copyright (c) 2016, Aristeides Stathopoulos
 * @license     http://opensource.org/licenses/https://opensource.org/licenses/MIT
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Kirki_Sanitize_Values' ) ) {

	class Kirki_Sanitize_Values {

		/**
		 * Fallback for non-existing methods.
		 */
		public static function __callStatic( $name, $arguments ) {
			error_log( "Kirki_Sanitize_Values::$name does not exist" );
		}

		/**
		 * Checkbox sanitization callback.
		 *
		 * Sanitization callback for 'checkbox' type controls. This callback sanitizes `$checked`
		 * as a boolean value, either TRUE or FALSE.
		 *
		 * @param bool|string $checked Whether the checkbox is checked.
		 * @return bool Whether the checkbox is checked.
		 */
		public static function checkbox( $checked ) {
			return ( ( isset( $checked ) && ( true == $checked || 'on' == $checked ) ) ? true : false );
		}

		/**
		 * Sanitize number options
		 *
		 * @since 0.5
		 */
		public static function number( $value ) {
			return ( is_numeric( $value ) ) ? $value : intval( $value );
		}

		/**
		 * Drop-down Pages sanitization callback.
		 *
		 * - Sanitization: dropdown-pages
		 * - Control: dropdown-pages
		 *
		 * Sanitization callback for 'dropdown-pages' type controls. This callback sanitizes `$page_id`
		 * as an absolute integer, and then validates that $input is the ID of a published page.
		 *
		 * @see absint() https://developer.wordpress.org/reference/functions/absint/
		 * @see get_post_status() https://developer.wordpress.org/reference/functions/get_post_status/
		 *
		 * @param int                  $page_id    Page ID.
		 * @param WP_Customize_Setting $setting Setting instance.
		 * @return int|string Page ID if the page is published; otherwise, the setting default.
		 */
		public static function dropdown_pages( $page_id, $setting ) {
			// Ensure $input is an absolute integer.
			$page_id = absint( $page_id );

			// If $page_id is an ID of a published page, return it; otherwise, return the default.
			return ( 'publish' == get_post_status( $page_id ) ? $page_id : $setting->default );
		}

		/**
		 * Sanitizes css dimensions
		 *
		 * @since 2.2.0
		 * @return string
		 */
		public static function css_dimension( $value ) {
			// trim it
			$value = trim( $value );
			// if round, return 50%
			if ( 'round' == $value ) {
				$value = '50%';
			}
			// if empty, return empty
			if ( '' == $value ) {
				return '';
			}
			// If auto, return auto
			if ( 'auto' == $value ) {
				return 'auto';
			}
			// Return empty if there are no numbers in the value.
			if ( ! preg_match( '#[0-9]#' , $value ) ) {
				return '';
			}
			// If we're using calc() then return the value
			if ( false !== strpos( $value, 'calc(' ) ) {
				return $value;
			}
			// The raw value without the units
			$raw_value = self::filter_number( $value );
			$unit_used = '';
			// An array of all valid CSS units. Their order was carefully chosen for this evaluation, don't mix it up!!!
			$units = array( 'rem', 'em', 'ex', '%', 'px', 'cm', 'mm', 'in', 'pt', 'pc', 'ch', 'vh', 'vw', 'vmin', 'vmax' );
			foreach ( $units as $unit ) {
				if ( false !== strpos( $value, $unit ) ) {
					$unit_used = $unit;
				}
			}
			return $raw_value . $unit_used;
		}

		/**
		 * @param string $value
		 */
		public static function filter_number( $value ) {
			return filter_var( $value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
		}

		/**
		 * Sanitize sortable controls
		 *
		 * @since 0.8.3
		 *
		 * @return mixed
		 */
		public static function sortable( $value ) {
			if ( is_serialized( $value ) ) {
				return $value;
			} else {
				return serialize( $value );
			}
		}

		/**
		 * Sanitize RGBA colors
		 *
		 * @since 0.8.5
		 *
		 * @return string
		 */
		public static function rgba( $value ) {
			$color = ariColor::newColor( $value );
			return $color->toCSS( 'rgba' );
		}

		/**
		 * Sanitize colors.
		 *
		 * @since 0.8.5
		 * @return string
		 */
		public static function color( $value ) {
			// If empty, return empty
			if ( '' == $value ) {
				return '';
			}
			// If transparent, return 'transparent'
			if ( is_string( $value ) && 'transparent' == trim( $value ) ) {
				return 'transparent';
			}
			// Instantiate the object
			$color = ariColor::newColor( $value );
			// Return a CSS value, using the auto-detected mode
			return $color->toCSS( $color->mode );
		}

		/**
		 * DOES NOT SANITIZE ANYTHING.
		 *
		 * @since 0.5
		 *
		 * @return mixed
		 */
		public static function unfiltered( $value ) {
			return $value;
		}

	}

}
