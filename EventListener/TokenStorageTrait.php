<?php

namespace Goksagun\ElasticApmBundle\EventListener;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

trait TokenStorageTrait
{
    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    public function setTokenStorage(TokenStorage $tokenStorage) {
        $this->tokenStorage = $tokenStorage;
    }
}