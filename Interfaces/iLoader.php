<?php
namespace Poirot\Loader\Interfaces;

if (interface_exists('Poirot\Loader\Interfaces\iLoader', false))
    return;

interface iLoader 
{
    /**
     * Resolve To Resource
     *
     * @param mixed $resource
     *
     * @return mixed
     */
    function resolve($resource);
}
