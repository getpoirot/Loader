<?php
namespace Poirot\Loader\Autoloader;

if (class_exists('Poirot\Loader\Autoloader\LoaderAutoloadNamespace', false))
    return;

!class_exists('Poirot\Loader\LoaderNamespaceStack', false)
    and require_once __DIR__.'/../LoaderNamespaceStack.php';
!interface_exists('Poirot\Loader\Interfaces\iLoaderAutoload', false)
    and require_once __DIR__ . '/../Interfaces/iLoaderAutoload.php';

use Poirot\Loader\Interfaces\iLoaderAutoload;
use Poirot\Loader\LoaderNamespaceStack;

class LoaderAutoloadNamespace
    extends LoaderNamespaceStack
    implements iLoaderAutoload
{
    /**
     * Autoload Class
     *
     * - must not throw exception
     *
     * @param string $class Class Name
     *
     * @return mixed
     */
    function resolve($class)
    {
        return parent::resolve($class, function($resolvedFile) {
            $file = $resolvedFile.'.php';
            if (!file_exists($file))
                return false;

            require_once $file;

            ## stop propagation
            return $file;
        });
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
