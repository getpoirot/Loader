<?php
namespace Poirot\Loader\Interfaces;

interface iLoader
{
    /**
     * Resolve To Resource
     *
     * @param string $name
     *
     * @return mixed Resolved Resource Or Anything
     */
    function resolve($name);
}
