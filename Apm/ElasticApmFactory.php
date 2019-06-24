<?php

namespace Goksagun\ElasticApmBundle\Apm;

use Goksagun\ElasticApmBundle\Utils\ArrayHelper;
use PhilKra\Agent;

class ElasticApmFactory
{
    public static function createAgent(array $config = [])
    {
        // Replace "enabled" config key with active from config array
        ArrayHelper::replaceKey($config, 'enabled', 'active');

        // Check php sapi is cli disable apm agent
        if (PHP_SAPI === 'cli') {
            $config['active'] = false;
        }

        return new Agent($config);
    }
}