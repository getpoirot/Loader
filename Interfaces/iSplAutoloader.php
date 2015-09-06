<?php
namespace Poirot\Loader\Interfaces;

interface iSplAutoloader extends iLoader
{
    /**
     * Register to spl autoloader
     *
     * <code>
     * spl_autoload_register(callable);
     * </code>
     *
     * @return void
     */
    function register();

    /**
     * Unregister from spl autoloader
     *
     * ! using same callable on register
     *
     * @return void
     */
    function unregister();
}
