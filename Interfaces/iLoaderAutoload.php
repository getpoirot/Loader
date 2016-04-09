<?php
namespace Poirot\Loader\Interfaces;

if (interface_exists('Poirot\\Loader\\Interfaces\\iLoaderAutoload',false))
    return;

require_once __DIR__.'/iLoader.php';

interface   iLoaderAutoload
    extends iLoader
{
    /**
     * Register to spl autoloader
     *
     * <code>
     * spl_autoload_register(callable);
     * </code>
     *
     * @param bool $prepend
     *
     * @return void
     */
    function register($prepend = false);

    /**
     * Unregister from spl autoloader
     *
     * ! using same callable on register
     *
     * @return void
     */
    function unregister();
}
