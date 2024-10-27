<?php

namespace Bojaghi\Fields;

class Filter
{
    /**
     * Generic attribute filter
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return array
     */
    public static function filterGeneric(string $key, mixed $value): array
    {
        $key    = sanitize_key($key);
        $output = self::deepAttrFilter($value, 'esc_attr');

        return [$key, $output];
    }

    /**
     * Bool-like attribute filter
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return array
     */
    public static function filterBool(string $key, mixed $value): array
    {
        $key   = sanitize_key($key);
        $value = (is_bool($value) && $value) || $key ? $key : '';

        return [$key, $value];
    }

    /**
     * Sanitize CSS class
     * @param string $key
     * @param mixed  $value
     *
     * @return array
     */
    public static function filterClass(string $key, mixed $value): array
    {
        $key    = sanitize_key($key);
        $output = self::deepAttrFilter($value, 'sanitize_html_class');

        return [$key, $output];
    }

    /**
     * Deep attributes filter
     *
     * @param mixed                 $input
     * @param array|string|callable $func
     *
     * @return string
     */
    public static function deepAttrFilter(mixed $input, array|string|callable $func): string
    {
        $output = '';

        if (is_string($input)) {
            $input = preg_split('/\s+/', $input);
        }

        if (is_array($input) && is_callable($func)) {
            $output = implode(' ', array_unique(array_filter(array_map($func, $input))));
        }

        return $output;
    }

    /**
     * Sanitize URL
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return array
     */
    public static function filterUrl(string $key, mixed $value): array
    {
        $key    = sanitize_key($key);
        $output = self::deepAttrFilter($value, 'esc_url');

        return [$key, $output];
    }

    public static function ksesAttrs(string $objective = ''): array
    {
        return match ($objective) {
            'label'       => [
                'span' => [
                    'id'    => true,
                    'class' => true,
                    'style' => true,
                ],
            ],
            'description' => [
                'a'    => [
                    'id'     => true,
                    'class'  => true,
                    'href'   => true,
                    'style'  => true,
                    'target' => true,
                ],
                'br'   => [],
                'span' => [
                    'id'    => true,
                    'class' => true,
                    'style' => true,
                ],
            ],
            default       => [],
        };
    }
}