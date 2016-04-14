<?php
namespace Poirot\Loader\Interfaces;

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
