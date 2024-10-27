<?php

namespace Bojaghi\Fields;

class Render
{
    private static array $stack = [];

    public static function checkbox(string $text, bool $checked, array|string $inputAttrs = ''): string
    {
        $attrs            = $inputAttrs;
        $attrs['checked'] = $checked;
        $attrs['type']    = 'checkbox';
        $attrs['value']   = 'yes';

        return self::label(['for' => $inputAttrs['id'] ?? ''], $text) . self::input($attrs);
    }

    public static function label(array|string $attrs = '', string $text = ''): string
    {
        return self::open('label', $attrs) . wp_kses($text, Filter::ksesAttrs('label')) . self::close();
    }

    /**
     * Create simple input
     *
     * @param array|string $attrs
     *
     * @return string
     */
    public static function input(array|string $attrs = ''): string
    {
        return self::open('input', $attrs, true);
    }

    /**
     * Create p.description and text
     *
     * @param string $text
     *
     * @return string
     */
    public static function description(string $text): string
    {
        return self::open('p', 'class=description') . wp_kses($text, Filter::ksesAttrs('description')) . self::close();
    }

    /**
     * Open a tag
     *
     * @param string       $tag
     * @param array|string $attrs
     * @param bool         $enclosed
     *
     * @return string
     */
    public static function open(string $tag, array|string $attrs = '', bool $enclosed = false): string
    {
        $tag   = sanitize_key($tag);
        $attrs = self::attrs($attrs);

        if ($enclosed) {
            $e = '/';
        } else {
            $e             = '';
            self::$stack[] = $tag;
        }

        return $tag ? "<$tag$attrs$e>" : '';
    }

    /**
     * Format attributes
     *
     * @param array|string $attrs
     *
     * @return string
     */
    public static function attrs(array|string $attrs = ''): string
    {
        $buffer = [];
        $attrs  = wp_parse_args($attrs);

        foreach ($attrs as $key => $value) {
            /** @link https://html.spec.whatwg.org/multipage/indices.html#attributes-3 */
            $key = sanitize_key($key);
            [$key, $value] = match ($key) {
                'class'                                 => Filter::filterClass($key, $value),
                //
                // URLS
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
                'src'                                   => Filter::filterUrl($key, $value),
                //
                // Boolean-like
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
                'shadowrootserializableallowfullscreen' => Filter::filterBool($key, $value),
                //
                // default
                default                                 => Filter::filterGeneric($key, $value),
            };

            if ($key) {
                $buffer[] = "$key=\"$value\"";
            }
        }

        return $buffer ? (' ' . implode(' ', $buffer)) : '';
    }

    public static function close(): string
    {
        $tag = array_pop(self::$stack);

        return $tag ? "</$tag>" : '';
    }

    public static function select(array $options, string $selected = '', array|string $selectAttrs = ''): string
    {
        $output = self::open('select', $selectAttrs);

        foreach ($options as $value => $text) {
            if (is_array($text)) {
                $output .= self::open('optgroup', "label=$value");
                foreach ($text as $inValue => $inText) {
                    $output .= self::open('option', ['value' => $inValue, 'selected' => $inValue == $selected]);
                    $output .= esc_html($inText);
                    $output .= self::close();
                }
                $output .= self::close();
            } else {
                $output .= self::open('option', ['value' => $value, 'selected' => $value == $selected]);
                $output .= esc_html($text);
                $output .= self::close();
            }
        }

        return $output . self::close();
    }
}
