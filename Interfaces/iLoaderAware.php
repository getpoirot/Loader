<?php
namespace Poirot\Loader\Interfaces;

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
