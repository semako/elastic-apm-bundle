<?php

namespace Goksagun\ElasticApmBundle\EventListener;

use Goksagun\ElasticApmBundle\Apm\ElasticApmAwareInterface;
use Goksagun\ElasticApmBundle\Apm\ElasticApmAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class ApmErrorCaptureListener implements ElasticApmAwareInterface, LoggerAwareInterface
{
    use ElasticApmAwareTrait, LoggerAwareTrait;

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (!$this->apm->getConfig()->get('active')) {
            return;
        }

        $this->apm->captureThrowable($exception = $event->getException());

        if (null !== $this->logger) {
            $this->logger->info(sprintf('Errors captured for "%s"', $exception->getTraceAsString()));
        }

        try {
            $sent = $this->apm->send();
        } catch (\Exception $e) {
            $sent = false;
        }

        if (null !== $this->logger) {
            $this->logger->info(
                sprintf('Errors %s for "%s"', $sent ? 'sent' : 'not sent', $exception->getTraceAsString())
            );
        }
    }
}