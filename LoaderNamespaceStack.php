<?php
namespace Poirot\Loader;

if (version_compare(phpversion(), '5.4.0') < 0) {
    ## php version not support traits
    require_once __DIR__.'/fixes/LoaderNamespaceStack.php';
    return;
}

!class_exists('Poirot/Loader/aLoader', false)
    and require_once __DIR__.'/aLoader.php';
!trait_exists('Poirot\Loader\Traits\tLoaderNamespaceStack', false)
    and require_once __DIR__.'/Traits/tLoaderNamespaceStack.php';

use Poirot\Loader\Traits\tLoaderNamespaceStack;

class LoaderNamespaceStack
    extends aLoader
{
    use tLoaderNamespaceStack;

    ## @see fixes/LoaderNamespaceStack;
    ## Code Clone <begin> =================================================================
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
    ## Code Clone <end> ===================================================================
}
