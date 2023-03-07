<?php

namespace App\Helpers;

class GetYoutubeId
{
    public static function getYoutubeId($url)
    {
        if (!$url) {
            return null;
        }
        preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
        return $match[1] ?? '';
    }
}
