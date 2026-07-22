<?php
/**
 * GitHub: RoyalHaze
 * Date: 3/11/25
 * Time: 2:37 PM
 **/

namespace App\Traits;

trait FilamentContent
{
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'create',
            'update',
            'delete',
        ];
    }
}
