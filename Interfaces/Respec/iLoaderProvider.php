<?php
namespace Poirot\Loader\Interfaces\Respec;

use Poirot\Loader\Interfaces\iLoader;

interface iLoaderProvider
{
    /**
     * Loader Resolver
     *
     * @return iLoader
     */
    function resolver();
}
