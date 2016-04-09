<?php
namespace Poirot\Loader;

use Poirot\Loader\Interfaces\iLoader;
use Poirot\Loader\Traits\tLoaderPathStack;
// use Poirot\Std\Interfaces\Pact\ipConfigurable;

class LoaderPathStack
    implements iLoader
    // , ipConfigurable
{
    use tLoaderPathStack;

    /**
     * Construct
     *
     * @param array|string $namespaces
     */
    function __construct($namespaces = null)
    {
        if ($namespaces !== null)
            $this->with(self::withOf($namespaces));
    }
}
