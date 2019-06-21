<?php

namespace Goksagun\ElasticApmBundle\EventListener;

use PhilKra\Agent;

trait ElasticApmTrait
{
    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var array
     */
    private $config;

    /**
     * @var Agent
     */
    private $apm;

    public function __construct(array $config)
    {
        $this->enabled = array_shift($config);
        $this->config = $config;
        // TODO: create a factory
        $this->apm = $this->createApmAgent();
    }

    public function createApmAgent(): Agent
    {
        $config = array_merge($this->config, ['active' => PHP_SAPI !== 'cli']);

        return new Agent($config);
    }
}