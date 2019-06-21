<?php

namespace Goksagun\ElasticApmBundle\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\Security\Core\User\User;

class RequestListener implements ElasticApmInterface, TokenStorageInterface
{
    use ElasticApmTrait, TokenStorageTrait;

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest() || !$this->enabled) {
            return;
        }

        $this->apm->startTransaction(
            $this->getTransactionName($event->getRequest())
        );
    }

    public function onKernelTerminate(PostResponseEvent $event)
    {
        if (!$event->isMasterRequest() || !$this->enabled) {
            return;
        }

        $this->apm->stopTransaction(
            $name = $this->getTransactionName($event->getRequest()),
            [
                'result' => $event->getResponse()->getStatusCode(),
                'status' => $event->getResponse()->getStatusCode(),
            ]
        );

        $userContext = [];
        /** @var User $user */
        if ($user = $this->getUser()) {
            $userContext['username'] = $user->getUsername();

            if (method_exists($user, 'getId')) {
                $userContext['id'] = $user->getId();
            }

            if (method_exists($user, 'getEmail')) {
                $userContext['email'] = $user->getEmail();
            }

            if (method_exists($user, 'getRoles')) {
                $userContext['roles'] = $user->getRoles();
            }
        }

        $this->apm->getTransaction($name)->setUserContext($userContext);

        $this->apm->send();
    }

    public function getTransactionName(Request $request): string
    {
        $routeName = $request->get('_route');
        $controllerName = $request->get('_controller');

        return sprintf('%s (%s)', $controllerName, $routeName);
    }

    public function getUser()
    {
        if (null === $tokenStorage = $this->tokenStorage) {
            return null;
        }

        if (null === $token = $tokenStorage->getToken()) {
            return null;
        }

        if (!\is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return null;
        }

        return $user;
    }
}