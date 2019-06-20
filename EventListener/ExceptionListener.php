<?php

namespace Goksagun\ElasticApmBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class ExceptionListener implements ElasticApmInterface
{
    use ElasticApmTrait;

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $this->apm->captureThrowable($event->getException());

        $this->apm->send();
    }
}