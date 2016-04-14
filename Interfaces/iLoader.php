<?php
namespace Poirot\Loader\Interfaces;

interface iLoader
{
    /**
     * Resolve To Resource
     *
     * @param string $resourceName
     *
     * @return mixed Resolved Resource Or Anything
     */
    function resolve($resourceName);
}
