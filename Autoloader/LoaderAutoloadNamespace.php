<?php
namespace Poirot\Loader\Autoloader;

if (class_exists('Poirot\Loader\Autoloader\LoaderAutoloadNamespace', false))
    return;

use Poirot\Loader\Interfaces\iLoaderAutoload;
use Poirot\Loader\LoaderNamespaceStack;

require_once __DIR__ . '/../Interfaces/iLoaderAutoload.php';
require_once __DIR__ . '/../LoaderNamespaceStack.php';

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
        return parent::resolve($class
            , function($resolvedFile)
            {
                $file = $resolvedFile.'.php';
                if (!file_exists($file))
                    return false;

                require_once $file;

                ## stop propagation
                return true;
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
