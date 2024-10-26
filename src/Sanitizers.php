<?php

namespace Bojaghi\Continy\Field;

class Sanitizers
{
    public static function sanitizeAccept(mixed $value): string
    {
        $output = '';

        if (is_string($value)) {
            $value = array_filter(array_map('trim', explode(',', $value)));
        }

        if (is_array($value)) {
            $output = implode(', ', array_unique(array_map('sanitize_mime_type', $value)));
        }

        return $output;
    }

    public static function sanitizeClass(mixed $value): string
    {
        $output = '';

        if (is_string($value)) {
            $value = preg_split('/\s+/', $value);
        }

        if (is_array($value)) {
            $output = implode(' ', array_map('sanitize_html_class', $value));
        }

        return $output;
    }

    public static function sanitizeUrl(mixed $value): string
    {
        $output = '';

        if (is_string($value)) {
            $value = preg_split('/\s+/', $value);
        }

        if (is_array($value)) {
            $output = implode(' ', array_unique(array_filter((array_map('esc_url', $value)))));
        }

        return $output;
    }

    public static function sanitizeBool(mixed $value, string $key): string
    {
        $key = sanitize_key($key);

        if (is_bool($value)) {
            return $value ? $key : '';
        } else {
            return $key ?: '';
        }
    }

    public static function sanitize(mixed $value): string
    {
        $output = '';

        if (is_string($value)) {
            $value = preg_split('/\s+/', $value);
        }

        if (is_array($value)) {
            $output = implode(' ', array_unique(array_filter((array_map('esc_attr', $value)))));
        }

        return $output;
    }
}
