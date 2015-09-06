<?php
namespace Poirot\Loader;

use Poirot\Loader\Interfaces\iLoader;

class PathStackLoader implements iLoader
{
    use PathStackTrait;

    /**
     * Construct
     *
     * @param array $namespaces
     */
    function __construct(array $namespaces = [])
    {
        if ($namespaces)
            $this->setStackArray($namespaces);
    }
}
 