<?php
namespace Poirot\Loader\Autoloader;

use Poirot\Loader\Interfaces\iSplAutoloader;

if (class_exists('Poirot\\Loader\\Autoloader\\AbstractAutoloader'))
    return;

require_once __DIR__ . '/../Interfaces/iSplAutoloader.php';

abstract class AbstractAutoloader implements iSplAutoloader
{
    // Implement iSplAutoloader:

    /**
     * Autoload Class Callable
     *
     * - must not throw exception
     *
     * @param string $class Class Name
     *
     * @return void
     */
    abstract function resolve($class);

    /**
     * Register to spl autoloader
     *
     * <code>
     * spl_autoload_register(callable);
     * </code>
     *
     * @return void
     */
    function register()
    {
        spl_autoload_register([$this, 'resolve']);
    }

    /**
     * Unregister from spl autoloader
     *
     * ! using same callable on register
     *
     * @return void
     */
    function unregister()
    {
        spl_autoload_unregister([$this, 'resolve']);
    }
}
 