<?php

namespace Goksagun\ElasticApmBundle\Apm;

use PhilKra\Agent;

trait ElasticApmAwareTrait
{
    /**
     * @var Agent
     */
    protected $apm;

    public function __construct(Agent $apm)
    {
        $this->apm = $apm;
    }
}