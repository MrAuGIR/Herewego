<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\Ticket;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;


class TicketVoter extends Voter{

    const CREATE = 'create';
    const DELETE = 'delete';
    const EDIT = 'edit';

    public function __construct(Security $security){

        $this->security = $security;
    }

    public function supports(string $attributes, $subject)
    {
        
        return \in_array($attributes, [self::EDIT, self::CREATE, self::DELETE]) && ($subject instanceof Ticket);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            //si l'utilisateur n'est pas logger, deny access
            return false;
        }

        //Si je suis l'administrateur j'ai tous les droit
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        //si on est là, c'est que $subject est un objet ticket
        /** @var Ticket $ticket */
        $ticket = $subject;

        switch ($attribute) {
            case self::CREATE:
                return true;
            case self::EDIT:
                return $ticket->getUser() == $user;
            case self::DELETE:
                return $ticket->getUser() == $user;
        }

        return false;
    }

}



?>