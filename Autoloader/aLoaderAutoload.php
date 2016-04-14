<?php
namespace Poirot\Loader\Autoloader;

!class_exists('Poirot/Loader/aLoader', false)
    and require_once __DIR__.'/../aLoader.php';
!interface_exists('Poirot\Loader\Interfaces\iLoaderAutoload', false)
    and require_once __DIR__ . '/../Interfaces/iLoaderAutoload.php';

use Poirot\Loader\aLoader;
use Poirot\Loader\Interfaces\iLoaderAutoload;

abstract class aLoaderAutoload
    extends aLoader
    implements iLoaderAutoload
{
    // Implement iLoaderAutoload:

    /**
     * Autoload Class Callable
     *
     * - must not throw exception
     *
     * @param string $class Class Name
     *
     * @return mixed
     */
    abstract function resolve($class);

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
    function register($prepend = false)
    {
        spl_autoload_register(array($this, 'resolve'), true, $prepend);
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
        spl_autoload_unregister(array($this, 'resolve'));
    }
}
