<?php

namespace Goksagun\ElasticApmBundle\EventListener;

use Goksagun\ElasticApmBundle\Apm\ElasticApmAwareInterface;
use Goksagun\ElasticApmBundle\Apm\ElasticApmAwareTrait;
use Goksagun\ElasticApmBundle\Security\TokenStorageAwareInterface;
use Goksagun\ElasticApmBundle\Security\TokenStorageAwareTrait;
use Goksagun\ElasticApmBundle\Utils\RequestProcessor;
use PhilKra\Exception\Transaction\UnknownTransactionException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\Security\Core\User\User;

class ApmTransactionSenderListener implements ElasticApmAwareInterface, TokenStorageAwareInterface, LoggerAwareInterface
{
    use ElasticApmAwareTrait, TokenStorageAwareTrait, LoggerAwareTrait;

    public function onKernelTerminate(PostResponseEvent $event)
    {
        if (!$event->isMasterRequest() || !$this->apm->getConfig()->get('active')) {
            return;
        }

        try {
            $transaction = $this->apm->getTransaction(
                $name = RequestProcessor::getTransactionName($event->getRequest())
            );
        } catch (UnknownTransactionException $e) {
            return;
        }

        $transaction->stop();

        $meta = $this->getMeta($event->getResponse());

        $transaction->setMeta($meta);

        if (null !== $this->logger) {
            $this->logger->info(sprintf('Transaction stopped for "%s"', $name));
        }

        $userContext = $this->getUserContext();

        $transaction->setUserContext($userContext);

        try {
            $sent = $this->apm->send();
        } catch (\Exception $e) {
            $sent = false;
        }

        if (null !== $this->logger) {
            $this->logger->info(sprintf('Transaction %s for "%s"', $sent ? 'sent' : 'not sent', $name));
        }
    }

    private function getUserContext(): array
    {
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

        return $userContext;
    }

    private function getMeta(Response $response): array
    {
        $meta = [
            'result' => $response->getStatusCode(),
        ];

        return $meta;
    }
}