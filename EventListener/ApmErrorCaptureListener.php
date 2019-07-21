<?php

namespace Goksagun\ElasticApmBundle\EventListener;

use Goksagun\ElasticApmBundle\Apm\ElasticApmAwareInterface;
use Goksagun\ElasticApmBundle\Apm\ElasticApmAwareTrait;
use Goksagun\ElasticApmBundle\Utils\StringHelper;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ApmErrorCaptureListener implements ElasticApmAwareInterface, LoggerAwareInterface
{
    use ElasticApmAwareTrait, LoggerAwareTrait;

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $config = $this->apm->getConfig();

        $errors = $config->get('errors');

        if (!$config->get('enabled') || !$errors['enabled']) {
            return;
        }

        $exception = $event->getException();

        if ($errors) {
            if ($excludedStatusCodes = $errors['exclude']['status_codes'] ?? []) {
                if (!$exception instanceof HttpExceptionInterface) {
                    return;
                }

                foreach ($excludedStatusCodes as $excludedStatusCode) {
                    if (StringHelper::match($excludedStatusCode, $exception->getStatusCode())) {
                        return;
                    }
                }
            }

            if ($excludedExceptions = $errors['exclude']['exceptions'] ?? []) {
                foreach ($excludedExceptions as $excludedException) {
                    if ($exception instanceof $excludedException) {
                        return;
                    }
                }
            }
        }

        $this->apm->captureThrowable($exception);

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