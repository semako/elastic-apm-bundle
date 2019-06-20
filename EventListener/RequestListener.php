<?php

namespace Goksagun\ElasticApmBundle\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

class RequestListener implements ElasticApmInterface
{
    use ElasticApmTrait;

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
            $this->getTransactionName($event->getRequest())
        );

        $this->apm->send();
    }

    private function getTransactionName(Request $request): string
    {
        $routeName = $request->get('_route');
        $controllerName = $request->get('_controller');

        return sprintf('%s (%s)', $controllerName, $routeName);
    }
}