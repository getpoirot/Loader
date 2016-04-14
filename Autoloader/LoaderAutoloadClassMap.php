<?php
namespace Poirot\Loader\Autoloader;

if (class_exists('Poirot\Loader\Autoloader\LoaderAutoloadClassMap', false))
    return;

!class_exists('Poirot\Loader\LoaderMapResource', false)
    and require_once __DIR__.'/../LoaderMapResource.php';
!interface_exists('Poirot\Loader\Interfaces\iLoaderAutoload', false)
    and require_once __DIR__ . '/../Interfaces/iLoaderAutoload.php';

use Poirot\Loader\Interfaces\iLoaderAutoload;
use Poirot\Loader\LoaderMapResource;

class LoaderAutoloadClassMap
    extends LoaderMapResource
    implements iLoaderAutoload
{
    /**
     * Autoload Class Callable
     *
     * - must not throw exception
     *
     * @param string $class Class Name
     *
     * @return mixed
     */
    function resolve($class)
    {
        $resolved = parent::resolve($class);
        if ($resolved)
            require_once $resolved;

        return $resolved;
    }

    // Implement iLoaderAutoload:

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
