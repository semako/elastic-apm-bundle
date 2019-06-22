<?php

namespace Goksagun\ElasticApmBundle\EventListener;

use Goksagun\ElasticApmBundle\Apm\ElasticApmAwareInterface;
use Goksagun\ElasticApmBundle\Apm\ElasticApmAwareTrait;
use Goksagun\ElasticApmBundle\Security\TokenStorageAwareInterface;
use Goksagun\ElasticApmBundle\Security\TokenStorageAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\Security\Core\User\User;

class RequestListener implements ElasticApmAwareInterface, TokenStorageAwareInterface, LoggerAwareInterface
{
    use ElasticApmAwareTrait, TokenStorageAwareTrait, LoggerAwareTrait;

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $this->apm->startTransaction(
            $name = $this->getTransactionName($event->getRequest())
        );

        if (null !== $this->logger) {
            $this->logger->info(sprintf('Transaction started for "%s"', $name));
        }
    }

    public function onKernelTerminate(PostResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $this->apm->stopTransaction(
            $name = $this->getTransactionName($event->getRequest()),
            [
                'result' => $event->getResponse()->getStatusCode(),
                'status' => $event->getResponse()->getStatusCode(),
            ]
        );

        if (null !== $this->logger) {
            $this->logger->info(sprintf('Transaction stopped for "%s"', $name));
        }

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

        $sent = $this->apm->send();

        if (null !== $this->logger) {
            $this->logger->info(sprintf('Transaction %s for "%s"', $sent ? 'sent' : 'not sent', $name));
        }
    }

    public function getTransactionName(Request $request): string
    {
        $routeName = $request->get('_route');
        $controllerName = $request->get('_controller');

        return sprintf('%s (%s)', $controllerName, $routeName);
    }
}