<?php
namespace Poirot\Loader\Autoloader;

if (class_exists('Poirot\Loader\Autoloader\LoaderAutoloadAggregate', false))
    return;

!class_exists('Poirot\Loader\LoaderAggregate', false)
    and require_once __DIR__.'/../LoaderAggregate.php';
!interface_exists('Poirot\Loader\Interfaces\iLoaderAutoload', false)
    and require_once __DIR__ . '/../Interfaces/iLoaderAutoload.php';

use Poirot\Loader\LoaderAggregate;
use Poirot\Loader\Interfaces\iLoaderAutoload;

/*
require_once __DIR__.'/Loader/Autoloader/LoaderAutoloadAggregate.php';
$loader = new P\Loader\Autoloader\LoaderAutoloadAggregate([
    // examine we have not autoloading yet!!
    // so we register default autoloader classmap and namespace
    // and can configured with class name
    P\Loader\Autoloader\LoaderAutoloadClassMap::class => [
        'Poirot\Std\ErrorStack' => __DIR__.'/Std/ErrorStack.php'
    ]
]);
$loader->register();

new P\Std\ErrorStack(); // autoload class
*/
class LoaderAutoloadAggregate
    extends LoaderAggregate
    implements iLoaderAutoload
{
    /**
     * Construct
     *
     * @param array $options
     */
    function __construct(array $options = null)
    {
        ## register, so we can access related autoloader classes
        require_once __DIR__.'/LoaderAutoloadNamespace.php';
        $autoloader = new LoaderAutoloadNamespace( array('Poirot\Loader' => array(dirname(__DIR__))) );
        $autoloader->register(true);

        // examine we have not autoloading yet!!
        // so we register default autoloader classmap and namespace
        // and can configured with class name as option key member
        $this->attach(new LoaderAutoloadNamespace(), 90); // at priority 100
        $this->attach(new LoaderAutoloadClassMap() , 100);

        parent::__construct($options);

        ## unregister default autoloader after attaching
        $autoloader->unregister();
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
