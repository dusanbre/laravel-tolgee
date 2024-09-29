<?php

namespace LaravelTolgee\Utils;

class IO
{
    /**
     * Write a string to a file.
     */
    public static function write(string $content, string $path): void
    {
        file_put_contents($path, $content . PHP_EOL);
    }

    /**
     * Read json file and convert it into an array of strings.
     */
    public static function read(string $path): false|string
    {
        if (!file_exists($path)) {
            return false;
        }

        return file_get_contents($path);
    }
}
