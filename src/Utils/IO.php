<?php

namespace LaravelTolgee\Utils;

class IO
{
    /**
     * Write a string to a file.
     */
    public static function write(string $content, string $path): void
    {
        $directory_path = dirname($path);
        
        if(!is_dir($directory_path)){
            mkdir($directory_path);
        }
        
        $file = fopen($path, 'w');
        fwrite($file, $content . PHP_EOL);
        fclose($file);
    }

    /**
     * Read json file and convert it into an array of strings.
     */
    public static function read(string $path): false|string
    {
        if (!file_exists($path)) {
            return false;
        }

        $file = fopen($path, 'r');
        $content = fread($file, filesize($path));
        fclose($file);
        
        return $content;
    }
}
