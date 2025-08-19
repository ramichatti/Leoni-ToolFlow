<?php

namespace App\Security\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class UserBlockedException extends AuthenticationException
{
    public function getMessageKey(): string
    {
        return 'Votre compte est bloqué. Veuillez contacter l\'administrateur.';
    }
} 