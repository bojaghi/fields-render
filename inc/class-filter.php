<?php
/**
 * Bojaghi Fields Render
 *
 * @package Bojaghi\FieldsRender
 */
declare( strict_types=1 );

namespace Bojaghi\FieldsRender;

/**
 * Filter Class
 *
 * Filter HTML attributes
 */
class Filter {
	/**
	 * Canonicalize attributes
	 *
	 * By wp_parse_args(), $attrs can be like URL parameter string.
	 * However, attribute in an array form is better than a text form.
	 * Read $attrs and ensure that is an array form, with default values.
	 *
	 * @param string|array $attrs    Key-value pairs of HTML attributes to canonicalize.
	 * @param string|array $defaults Key-value pairs of default values when key does not exist in $attrs.
	 *
	 * @return array
	 */
	public static function canon_attrs( string|array $attrs, string|array $defaults = '' ): array {
		$output   = array();
		$attrs    = wp_parse_args( $attrs );
		$defaults = wp_parse_args( $defaults );

		foreach ( $attrs as $key => $value ) {
			if ( is_array( $value ) ) {
				$output[ $key ] = implode( ' ', array_filter( $value ) );
			} elseif ( is_string( $value ) ) {
				$output[ $key ] = implode( ' ', preg_split( '/\s+/', $value ) );
			}
		}

		foreach ( $defaults as $key => $value ) {
			if ( ! isset( $output[ $key ] ) ) {
				$output[ $key ] = $value;
			}
		}

		return $output;
	}

	/**
	 * Generic attribute filter
	 *
	 * @param string $key   Attribute name.
	 * @param mixed  $value Attribute value to filter.
	 *
	 * @return array
	 */
	public static function filter_generic( string $key, mixed $value ): array {
		$key    = sanitize_key( $key );
		$output = self::map_filter( $value, 'esc_attr' );

		return array( $key, $output );
	}

	/**
	 * Bool-like attribute filter
	 *
	 * @param string $key   Attribute name.
	 * @param mixed  $value Attribute value to filter.
	 *
	 * @return array
	 */
	public static function filter_bool( string $key, mixed $value ): array {
		$key = sanitize_key( $key );

		if ( is_bool( $value ) ) {
			if ( $value ) {
				$value = $key;
			} else {
				$key   = '';
				$value = '';
			}
		} elseif ( $key ) {
			if ( ! empty( $value ) ) {
				$value = $key;
			} else {
				$key   = '';
				$value = '';
			}
		}

		return array( $key, $value );
	}

	/**
	 * Sanitize CSS class
	 *
	 * @param string $key   Attribute name.
	 * @param mixed  $value Attribute value to filter.
	 *
	 * @return array
	 */
	public static function filter_html_class( string $key, mixed $value ): array {
		$key    = sanitize_key( $key );
		$output = self::map_filter( $value, 'sanitize_html_class' );

		return array( $key, $output );
	}

	/**
	 * Attributes filter helper
	 *
	 * For example, esc_attr is okay with spaces, whereas sanitize_html_class does not.
	 * Split $input by whitespaces, apply filter function, and merge with a single space.
	 *
	 * It ensures that all attributes are separated by a single whitespace without duplication,
	 * and it allows each token to be filtered properly.
	 *
	 * @param mixed                 $input Input to filter.
	 * @param array|string|callable $func  Filter function to apply.
	 *
	 * @return string
	 */
	public static function map_filter( mixed $input, array|string|callable $func ): string {
		$output = '';

		if ( is_string( $input ) ) {
			$input = preg_split( '/\s+/', $input );
		} else {
			$input = (array) $input;
		}

		if ( is_array( $input ) && is_callable( $func ) ) {
			$output = implode( ' ', array_unique( array_filter( array_map( $func, $input ) ) ) );
		}

		return $output;
	}

	/**
	 * Sanitize URL
	 *
	 * @param string $key   Attribute name.
	 * @param mixed  $value Attribute value.
	 *
	 * @return array
	 */
	public static function filter_url( string $key, mixed $value ): array {
		$key    = sanitize_key( $key );
		$output = self::map_filter( $value, 'esc_url' );

		return array( $key, $output );
	}

	/**
	 * Get default 2nd argument for wp_kses()
	 *
	 * @param string $objective Which context.
	 *
	 * @return array|array[]
	 */
	public static function get_kses_allowed_html( string $objective = '' ): array {
		return match ( $objective ) {
			'label__inner'   => array(
				'span' => array(
					'id'    => true,
					'class' => true,
					'style' => true,
				),
			),
			'option__text'   => array(
				'a'    => array(
					'id'     => true,
					'class'  => true,
					'href'   => true,
					'style'  => true,
					'target' => true,
				),
				'span' => array(
					'id'    => true,
					'class' => true,
					'style' => true,
				),
			),
			'p__description' => array(
				'a'    => array(
					'id'     => true,
					'class'  => true,
					'href'   => true,
					'style'  => true,
					'target' => true,
				),
				'code' => array(
					'id'    => true,
					'class' => true,
					'style' => true,
				),
				'br'   => array(),
				'hr'   => array(
					'id'    => true,
					'class' => true,
					'style' => true,
				),
				'pre'  => array(
					'id'    => true,
					'class' => true,
					'style' => true,
				),
				'span' => array(
					'id'    => true,
					'class' => true,
					'style' => true,
				),
			),
			default          => array(),
		};
	}
}
