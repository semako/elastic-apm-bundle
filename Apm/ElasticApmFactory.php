<?php

namespace Goksagun\ElasticApmBundle\Apm;

use Goksagun\ElasticApmBundle\Utils\ArrayHelper;
use PhilKra\Agent;

class ElasticApmFactory
{
    public static function createAgent(array $config = [])
    {
        // Check php sapi is cli disable apm agent
        if (PHP_SAPI === 'cli') {
            $config['enabled'] = false;
        }

        return new Agent($config);
    }
}