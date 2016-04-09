<?php
namespace Poirot\Loader\Interfaces\Respec;

use Poirot\Loader\Interfaces\iLoader;

interface iLoaderAware
{
    /**
     * Set Loader Resolver
     *
     * @param iLoader $resolver
     *
     * @return $this
     */
    function setResolver(iLoader $resolver);
}
