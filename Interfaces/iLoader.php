<?php
namespace Poirot\Loader\Interfaces;

interface iLoader
{
    /**
     * Resolve To Resource
     *
     * @param string $name
     *
     * @return mixed|null
     */
    function resolve($name);
}
