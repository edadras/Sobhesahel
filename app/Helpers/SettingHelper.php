<?php
namespace App\Helpers;
/**
 * GitHub: RoyalHaze
 * Date: 2/27/25
 * Time: 8:00 PM
 **/

class SettingHelper
{
    public static function getWebsiteSocial()
    {
        $data = setting('social');

        return $data;
    }
}
