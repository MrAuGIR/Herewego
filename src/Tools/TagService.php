<?php

namespace App\Tools;

use DateTime;

class TagService
{
    /**
     * genere un code
     */
    public static function code($size = 5) : string
    {
        // Initialisation des caractères utilisables
        $characters = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"];
        $code = "";
        for($i = 0; $i < $size; $i++)
        {
            $code .= strtoupper($characters[array_rand($characters)]);
        }		
        return $code;
    }

    public static function department($cp) : string {

        return substr($cp, 0, 2);
    }
        
    public static function year(DateTime $date) {
        return $date->format('y');
    }

    public static function createTag(string $tagCode, string $eventId, string $eventTitle) : string {
        $html = "<div style='tag-block'>";
        $html .= "<a style='tag-link' title='$eventTitle' href='https://herewego.aureliengirard.fr/event/show/$eventId'>";
        $html .= "<img style='tag-img' src='public/img/hwg.png' alt='tag-event'>";
        $html .= "<div style='tag-code'>$tagCode</div>";
        $html .= "</a></div>";
 
        return $html;
    }
}