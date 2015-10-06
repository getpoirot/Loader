<?php
namespace Poirot\Loader\Interfaces;

if (interface_exists('Poirot\\Loader\\Interfaces\\iSplAutoloader',false))
    return;

require_once 'iLoader.php';

interface iSplAutoloader extends iLoader
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
