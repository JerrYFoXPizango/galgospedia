<?php
declare(strict_types=1);
namespace Helpers;

class Slugify
{
    public static function make(string $text): string
    {
        // Transliterate Spanish chars
        $map = [
            'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u',
            'Á'=>'a','É'=>'e','Í'=>'i','Ó'=>'o','Ú'=>'u',
            'ñ'=>'n','Ñ'=>'n','ü'=>'u','Ü'=>'u',
            'à'=>'a','è'=>'e','ì'=>'i','ò'=>'o','ù'=>'u',
        ];
        $text = strtr($text, $map);
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', trim($text));
        return trim($text, '-');
    }
}
