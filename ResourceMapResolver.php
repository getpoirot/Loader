<?php
namespace Poirot\Loader;

use Poirot\Loader\Interfaces\iLoader;

class ResourceMapResolver implements iLoader
{
    use ResourceMapTrait;

    /**
     * Construct
     *
     * @param array|string $options
     */
    function __construct($options = null)
    {
        if ($options !== null)
            $this->from($options);
    }
}
 