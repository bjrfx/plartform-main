<?php

namespace App\Helpers\General;

use Illuminate\Support\Str;

class ReferenceKeyHelper
{
    public static function generate(int $length = 6): string
    {
        $date = now()->format('ymd');
        $random = Str::random($length);

        return $date . strtoupper($random);
    }
}
