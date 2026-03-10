<?php

namespace App\Support;

class MenuHelper
{
    public static function isActive(string|array $patterns): string
    {
        foreach ((array) $patterns as $pattern) {
            if (request()->routeIs($pattern)) {
                return 'active';
            }
        }

        return '';
    }
}
