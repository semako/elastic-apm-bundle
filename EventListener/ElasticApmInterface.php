<?php

namespace Goksagun\ElasticApmBundle\EventListener;

use PhilKra\Agent;

interface ElasticApmInterface
{
    public function createApmAgent(): Agent;
}