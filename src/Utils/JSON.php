<?php

namespace LaravelTolgee\Utils;

class JSON
{
    /**
     * Convert an array to a JSON string.
     */
    public static function jsonEncode(array $strings): false|string
    {
        return json_encode($strings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Convert a JSON string to an array.
     */
    public static function jsonDecode(string $string): array
    {
        return (array)json_decode($string);
    }
}
