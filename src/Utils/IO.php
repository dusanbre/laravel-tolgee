<?php

namespace LaravelTolgee\Utils;

use Illuminate\Filesystem\Filesystem;

readonly class IO
{
    public function __construct(private Filesystem $filesystem)
    {
    }

}
