<?php
/**
 * Bojaghi Fields Render
 *
 * @package Bojaghi\FieldsRender
 */

declare( strict_types=1 );

namespace Bojaghi\FieldsRender;

/**
 * Render Class
 *
 * Render Some HTML tags easily.
 */
class Render {
	/**
	 * Tags stack
	 *
	 * When calling close(), we can get the tag from here.
	 *
	 * @var array
	 */
	private static array $tag_stack = array();

	/**
	 * Output input[type="checkbox"]
	 *
	 * @param string       $label   Checkbox's label. To omit <label> tag, enter an empty string.
	 * @param bool         $checked If it is checked or not.
	 * @param array|string $attrs   Any other input attributes.
	 *
	 * @return string
	 */
	public static function checkbox( string $label, bool $checked, array|string $attrs = '' ): string {
		$attrs            = wp_parse_args( $attrs );
		$attrs['checked'] = $checked;
		$attrs['type']    = 'checkbox';

		return self::input( $attrs ) . ( $label ? self::label( $label, array( 'for' => $attrs['id'] ?? '' ) ) : '' );
	}

	/**
	 * Create simple input
	 *
	 * @param array|string $attrs <input> attributes.
	 *
	 * @return string
	 */
	public static function input( array|string $attrs = '' ): string {
		return self::open( tag: 'input', attrs: $attrs, enclosed: true );
	}

	/**
	 * Output label
	 *
	 * @param string       $text  Text inside <label>.
	 * @param array|string $attrs <label> attributes.
	 *
	 * @return string
	 */
	public static function label( string $text = '', array|string $attrs = '' ): string {
		return self::open( 'label', $attrs ) .
			wp_kses( $text, Filter::get_kses_allowed_html( 'label__inner' ) ) .
			self::close();
	}

	/**
	 * Open a tag
	 *
	 * @param string       $tag      Tag name to open.
	 * @param array|string $attrs    Attributes to append.
	 * @param bool         $enclosed It is enclosed form, or not.
	 *
	 * @return string
	 */
	public static function open( string $tag, array|string $attrs = '', bool $enclosed = false ): string {
		$tag   = sanitize_key( $tag );
		$attrs = self::attrs( $attrs );

		if ( $enclosed ) {
			$e = '/';
		} else {
			$e                 = '';
			self::$tag_stack[] = $tag;
		}

		return $tag ? "<$tag$attrs$e>" : '';
	}

	/**
	 * Close opened tag.
	 *
	 * @return string
	 */
	public static function close(): string {
		$tag = array_pop( self::$tag_stack );

		return $tag ? "</$tag>" : '';
	}

	/**
	 * Format and sanitize attributes
	 *
	 * @param array|string $attrs Attributes to sanitize.
	 *
	 * @return string
	 */
	public static function attrs( array|string $attrs = '' ): string {
		$buffer = array();
		$attrs  = wp_parse_args( $attrs );

		foreach ( $attrs as $key => $value ) {
			/**
			 * Attributes reference
			 *
			 * @link https://html.spec.whatwg.org/multipage/indices.html#attributes-3
			 */
			$key = sanitize_key( $key );

			[ $key, $value ] = match ( $key ) {
				'class'                                 => Filter::filter_html_class( $key, $value ),
				//
				// URLS.
				'action',
				'cite',
				'data',
				'formaction',
				'href',
				'itemid',
				'itemprop',
				'itemtype',
				'manifest',
				'ping',
				'poster',
				'src'                                   => Filter::filter_url( $key, $value ),
				//
				// Boolean-like.
				'allowfullscreen',
				'alpha',
				'async',
				'autofocus',
				'autoplay',
				'checked',
				'controls',
				'default',
				'defer',
				'disabled',
				'formnovalidate',
				'inert',
				'ismap',
				'itemscope',
				'loop',
				'multiple',
				'muted',
				'nomodule',
				'novalidate',
				'open',
				'playsinline',
				'readonly',
				'required',
				'reversed',
				'selected',
				'shadowrootclonable',
				'shadowrootdelegatesfocus',
				'shadowrootserializableallowfullscreen' => Filter::filter_bool( $key, $value ),
				//
				// default.
				default                                 => Filter::filter_generic( $key, $value ),
			};

			if ( $key ) {
				$buffer[] = "$key=\"$value\"";
			}
		}

		return $buffer ? ( ' ' . implode( ' ', $buffer ) ) : '';
	}

	/**
	 * Flush tag stack
	 *
	 * @return void
	 */
	public static function flush_tag_stack(): void {
		self::$tag_stack = array();
	}

	/**
	 * Get tag stack.
	 *
	 * @return array
	 */
	public static function get_tag_stack(): array {
		return self::$tag_stack;
	}

	/**
	 * Output input[type="radio"]
	 *
	 * @param string       $label   Lable, to display. To omit, enter an empty string.
	 * @param bool         $checked Checked or not.
	 * @param array|string $attrs   Ant other <input> tag attributes.
	 *
	 * @return string
	 */
	public static function radio( string $label, bool $checked, array|string $attrs = '' ): string {
		$attrs            = wp_parse_args( $attrs );
		$attrs['checked'] = $checked;
		$attrs['type']    = 'radio';

		return self::input( $attrs ) . ( $label ? self::label( $label, array( 'for' => $attrs['id'] ?? '' ) ) : '' );
	}

	/**
	 * Output select - option tags
	 *
	 * @param array        $options      <option> tag information.
	 * @param string       $selected     Selected item.
	 * @param array|string $select_attrs Any other <select> attributes.
	 *
	 * @return string
	 *
	 * @example select(
	 *     array(
	 *         'value'          => 'Label',
	 *         'OptGroup Label' => array(
	 *             'value2' => 'Label2',
	 *         ),
	 *     ),
	 *     ....
	 * )
	 */
	public static function select( array $options, string $selected = '', array|string $select_attrs = '' ): string {
		$output = self::open( 'select', $select_attrs );

		foreach ( $options as $value => $text ) {
			if ( is_array( $text ) ) {
				$output .= self::open( 'optgroup', "label=$value" );
				foreach ( $text as $in_value => $in_text ) {
					$output .= self::open(
						'option',
						array(
							'value'    => $in_value,
							'selected' => $in_value === $selected,
						),
					);
					$output .= wp_kses( $in_text, Filter::get_kses_allowed_html( 'option__text' ) );
					$output .= self::close();
				}
			} else {
				$output .= self::open(
					'option',
					array(
						'value'    => $value,
						'selected' => $value === $selected,
					),
				);
				$output .= wp_kses( $text, Filter::get_kses_allowed_html( 'option__text' ) );
			}
			$output .= self::close();
		}

		return $output . self::close();
	}

	/**
	 * Output textarea
	 *
	 * @param string       $text  <textarea> content.
	 * @param string|array $attrs <textarea> attributes.
	 *
	 * @return string
	 */
	public static function textarea( string $text = '', string|array $attrs = '' ): string {
		return self::open( 'textarea', $attrs ) . esc_textarea( $text ) . self::close();
	}
}
