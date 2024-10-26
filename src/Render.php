<?php

namespace Bojaghi\Continy\Field;

class Render
{
    public static function tagClose(string $tag): string
    {
        $tag = sanitize_key($tag);

        return $tag ? "</tag>" : '';
    }

    public static function tagOpen(string $tag, array|string $attrs = "", bool $enclosed = false): string
    {
        $tag   = sanitize_key($tag);
        $attrs = self::attrs($attrs);
        $encl  = $enclosed ? ' /' : '';

        return $tag ? "<$tag$attrs$encl>" : '';
    }

    public static function attrs(array|string $attrs = ""): string
    {
        $buffer = [];
        $attrs  = wp_parse_args($attrs);

        foreach ($attrs as $key => $value) {
            /** @link https://html.spec.whatwg.org/multipage/indices.html#attributes-3 */
            $key   = sanitize_key($key);
            $value = match ($key) {
                'accept' => Sanitizers::sanitizeAccept($value),
                'class' => Sanitizers::sanitizeClass($value),
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
                'src' => Sanitizers::sanitizeUrl($value),
                //
                // Boolean-like
                'allowfullscreen',
                'allowpaymentrequest',
                'async',
                'autofocus',
                'autoplay',
                'checked',
                'controls',
                'default',
                'defer',
                'disabled',
                'formnovalidate',
                'hidden',
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
                'selected' => Sanitizers::sanitizeBool($value, $key),
                //
                // default
                default => Sanitizers::sanitize($value),
            };

            if ($key) {
                $buffer[] = "$key=\"$value\"";
            }
        }

        return $buffer ? (' ' . implode(' ', $buffer)) : '';
    }
}
