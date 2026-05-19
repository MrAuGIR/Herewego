<?php

declare(strict_types=1);

namespace App\Tools;

use App\Entity\Event;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TagService
{
    public function __construct(
        private readonly ?UrlGeneratorInterface $urlGenerator = null,
    ) {
    }

    public function makeTagCode(Event $event): string
    {
        return self::code().'-'.self::year($event->getStartedAt()).self::department($event->getLocalisation()->getCityCp());
    }

    public static function code(int $size = 5): string
    {
        $characters = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];
        $code = '';
        for ($i = 0; $i < $size; ++$i) {
            $code .= strtoupper((string) $characters[array_rand($characters)]);
        }

        return $code;
    }

    public static function department(string $cp): string
    {
        return substr($cp, 0, 2);
    }

    public static function year(\DateTimeInterface $date): string
    {
        return $date->format('y');
    }

    /**
     * Génère le code HTML embarquable du tag d'un évènement.
     *
     * Les données dynamiques sont échappées (anti-XSS) et l'URL est générée
     * dynamiquement (plus d'URL de production codée en dur).
     */
    public function createHtmlTag(string $tagCode, int|string $eventId, string $eventTitle): string
    {
        $title = htmlspecialchars($eventTitle, ENT_QUOTES, 'UTF-8');
        $code = htmlspecialchars($tagCode, ENT_QUOTES, 'UTF-8');

        if (null !== $this->urlGenerator) {
            $url = $this->urlGenerator->generate('event_show', ['id' => $eventId], UrlGeneratorInterface::ABSOLUTE_URL);
            $context = $this->urlGenerator->getContext();
            $baseUrl = $context->getScheme().'://'.$context->getHost().$context->getBaseUrl();
        } else {
            $url = '/event/show/'.rawurlencode((string) $eventId);
            $baseUrl = '';
        }

        $url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        $img = htmlspecialchars($baseUrl.'/img/hwg_header.png', ENT_QUOTES, 'UTF-8');

        return "<div style='border: 3px solid #054550; width: 120px;'>
                    <a style='text-decoration:none;color:black;' title='{$title}' href='{$url}'>
                        <img style='width: 90%; margin: 0 auto;' src='{$img}' alt='tag-event'>
                        <div style='text-align: center;font-weight: bold;color: #054550'>{$code}</div>
                    </a>
                </div>";
    }
}
