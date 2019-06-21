<?php

namespace Goksagun\ElasticApmBundle\EventListener;

use PhilKra\Agent;

trait ElasticApmTrait
{
    /**
     * @var Agent
     */
    private $apm;

    public function __construct(Agent $apm)
    {
        $this->apm = $apm;
    }
}