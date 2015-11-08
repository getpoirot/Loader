<?php
namespace Poirot\Loader;

use Poirot\Loader\Interfaces\iLoader;

class AggregateLoader implements iLoader
{
    use AggregateTrait;

    /**
     * Construct
     *
     * @param array $options
     */
    function __construct(array $options = null)
    {
        if ($options !== null)
            $this->from($options);
    }
}
