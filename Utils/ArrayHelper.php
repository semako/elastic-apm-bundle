<?php

namespace Goksagun\ElasticApmBundle\Utils;

class ArrayHelper
{
    public static function replaceKey(&$array, $search, $replace)
    {
        $keys = array_keys($array);
        $index = array_search($search, $keys);

        if (false !== $index) {
            $keys[$index] = $replace;

            $array = array_combine($keys, $array);
        }
    }
}