<?php
namespace Poirot\Loader;

use Poirot\Loader\Interfaces\iLoader;
use Poirot\Loader\Traits\tLoaderMapResource;
use Poirot\Std\Interfaces\Pact\ipConfigurable;

class LoaderMapResource
    implements iLoader
    , ipConfigurable
{
    use tLoaderMapResource;

    /**
     * Construct
     *
     * @param array|string $options
     */
    function __construct($options = null)
    {
        if ($options !== null)
            $this->with($options);
    }
}
