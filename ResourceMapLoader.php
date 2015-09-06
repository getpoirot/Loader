<?php
namespace Poirot\Loader;

use Poirot\Loader\Interfaces\iLoader;

class ResourceMapLoader implements iLoader
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
            $this->setMapResource($options);
    }
}
 