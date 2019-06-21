<?php

namespace Goksagun\ElasticApmBundle\Apm;

use PhilKra\Agent;

class ElasticApmFactory
{
    public static function createAgent(array $config = [])
    {
        // Shift "enabled" config key from config array
        $config['active'] = array_shift($config);

        // Check php sapi is cli disable apm agent
        if (PHP_SAPI === 'cli') {
            $config['active'] = false;
        }

        return new Agent($config);
    }
}