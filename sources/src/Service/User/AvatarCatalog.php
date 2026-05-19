<?php

declare(strict_types=1);

namespace App\Service\User;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Catalogue des avatars disponibles (public/img/avatar/<id>.png).
 *
 * Centralise la validation pour empêcher tout path traversal sur la valeur
 * `pathAvatar` fournie par l'utilisateur.
 */
final class AvatarCatalog
{
    private const RELATIVE_DIR = '/public/img/avatar';

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    public function isValid(string $path): bool
    {
        // Un identifiant d'avatar est strictement numérique.
        if (1 !== preg_match('/^\d+$/', $path)) {
            return false;
        }

        $file = $this->projectDir.self::RELATIVE_DIR.'/'.$path.'.png';
        $real = realpath($file);
        $base = realpath($this->projectDir.self::RELATIVE_DIR);

        return false !== $real
            && false !== $base
            && str_starts_with($real, $base.\DIRECTORY_SEPARATOR);
    }
}
