<?php
namespace Poirot\Loader\Autoloader;

if (class_exists('Poirot\Loader\Autoloader\LoaderAutoloadNamespace', false))
    return;

!class_exists('Poirot\Loader\LoaderNamespaceStack', false)
    and require_once __DIR__.'/../LoaderNamespaceStack.php';
!interface_exists('Poirot\Loader\Interfaces\iLoaderAutoload', false)
    and require_once __DIR__ . '/../Interfaces/iLoaderAutoload.php';

require_once __DIR__.'/../_functions.php';

use Poirot\Loader\Interfaces\iLoaderAutoload;
use Poirot\Loader\LoaderNamespaceStack;

/*
$loader = new P\Loader\Autoloader\LoaderAutoloadNamespace([
    'Poirot' => __DIR__,
    'Poirot\\Loader' => __DIR__.'/Loader',
    'Poirot\\Loader\\LoaderNamespaceStack' => __DIR__.'/Loader/LoaderNamespaceStack.php',

    # 'Poirot\\Std' => __DIR__.'/Std',
]);

$resolved = $loader->resolve('Poirot\Std\ErrorStack');
$errorStack = new P\Std\ErrorStack(); // class will resolved with 'Poirot' =>
*/

class LoaderAutoloadNamespace
    extends LoaderNamespaceStack
    implements iLoaderAutoload
{
    protected $_cache_Normalized  = array();

    /**
     * Autoload Class
     *
     * - must not throw exception
     *
     * @param string $class Class Name
     *
     * @return mixed
     */
    function resolve($class, \Closure $__resolve_compatible = null)
    {
        return parent::resolve($class, function($class, $resource, $match) {
            if (false === $fileIncludeClass = \Poirot\Loader\watchFileExists($class, $resource, $match, '.php'))
                return false;

            ## require file so class can be accessible (AutoLoading Goes Work)
            require_once $fileIncludeClass;
            return $fileIncludeClass;
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
