<?php

declare(strict_types=1);

namespace App\Service\Faq;

use App\Entity\QuestionUser;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Persiste une question utilisateur (FAQ).
 *
 * Sort l'accès direct à l'EntityManager du contrôleur (SRP / DIP).
 */
final readonly class QuestionUserManager
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function submit(QuestionUser $question, ?User $author): void
    {
        if (null !== $author) {
            $question->setUser($author);
        }

        $this->em->persist($question);
        $this->em->flush();
    }
}
