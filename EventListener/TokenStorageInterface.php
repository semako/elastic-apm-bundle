<?php

namespace Goksagun\ElasticApmBundle\EventListener;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

interface TokenStorageInterface
{
    public function setTokenStorage(TokenStorage $tokenStorage);
}