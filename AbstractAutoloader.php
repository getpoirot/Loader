<?php
namespace Poirot\Autoloader;

use Poirot\Autoloader\Interfaces\iSplAutoloader;

if (class_exists('Poirot\\Autoloader\\AbstractAutoloader'))
    return;

require_once __DIR__.'/Interfaces/iSplAutoloader.php';

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
    abstract function attainClass($class);

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
        spl_autoload_register([$this, 'attainClass']);
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
        spl_autoload_unregister([$this, 'attainClass']);
    }
}
 