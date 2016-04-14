<?php
namespace Poirot\Loader;

require_once __DIR__.'/aLoader.php';

if (version_compare(phpversion(), '5.4.0') < 0) {
    ## php version not support traits
    require_once __DIR__.'/fixes/LoaderMapResource.php';
    return;
}


require_once __DIR__.'/Traits/tLoaderMapResource.php';
use Poirot\Loader\Traits\tLoaderMapResource;

class LoaderMapResource
    extends aLoader
{
    use tLoaderMapResource;


    /**
     * Build Object With Provided Options
     * > Setup Aggregate Loader
     *
     * @param array $options Associated Array
     * @param bool $throwException Throw Exception On Wrong Option
     *
     * @throws \Exception
     * @return $this
     */
    function with(array $options, $throwException = false)
    {
        $this->setResources($options);
        return $this;
    }
}
