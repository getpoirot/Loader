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
    function __construct(array $options = [])
    {
        if (isset($options['attach'])) {
            $attach = $options['attach'];
            if(!is_array($attach))
                $attach = [$attach];

            foreach($attach as $loader)
                $this->attach($loader);
        }
    }
}
