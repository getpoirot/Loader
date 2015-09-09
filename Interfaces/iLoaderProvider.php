<?php
namespace Poirot\Loader\Interfaces;

interface iLoaderProvider 
{
    /**
     * Loader Resolver
     *
     * @return iLoader
     */
    function resolver();
}
