<?php

namespace App\Tools;

use App\Entity\Event;

class TagService
{
    public function makeTagCode(Event $event): string
    {
        return self::code().'-'.self::year($event->getStartedAt()).self::department($event->getLocalisation()->getCityCp());
    }


    public static function code($size = 5): string
    {
        $characters = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];
        $code = '';
        for ($i = 0; $i < $size; ++$i) {
            $code .= strtoupper($characters[array_rand($characters)]);
        }
        return $code;
    }

    public static function department($cp): string
    {
        return substr($cp, 0, 2);
    }

    public static function year(\DateTimeInterface $date): string
    {
        return $date->format('y');
    }

    public static function createHtmlTag(string $tagCode, string $eventId, string $eventTitle): string
    {
        return "<div style='border: 3px solid #054550; width: 120px;'>
                    <a style='text-decoration:none;color:black;' title='$eventTitle' href='https://herewego.aureliengirard.fr/event/show/$eventId'>
                        <img style='width: 90%; margin: 0 auto;' src='https://herewego.aureliengirard.fr/img/hwg_header.png' alt='tag-event'>
                        <div style='text-align: center;font-weight: bold;color: #054550'>$tagCode</div>
                    </a>
                </div>";
    }
}
