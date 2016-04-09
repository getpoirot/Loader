<?php
namespace Poirot\Loader;

use Poirot\Loader\Interfaces\iLoader;
use Poirot\Loader\Traits\tLoaderPathStack;
use Poirot\Std\Interfaces\Pact\ipConfigurable;

class LoaderPathStack
    implements iLoader
    , ipConfigurable
{
    use tLoaderPathStack;

    /**
     * Construct
     *
     * @param array $namespaces
     */
    function __construct(array $namespaces = [])
    {
        if ($namespaces)
            $this->with($namespaces);
    }
}
