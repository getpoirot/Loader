<?php
namespace Poirot\Loader\Autoloader;

use Poirot\Loader\AggregateTrait;

if (class_exists('Poirot\\Loader\\AggregateAutoloader'))
    return;

require_once __DIR__ . '/AbstractAutoloader.php';
require_once __DIR__ . '/NamespaceAutoloader.php';
require_once __DIR__ . '/../AggregateTrait.php';


/**
 * TODO lazy loading of autoload classes on loader()
 */

class AggregateAutoloader extends AbstractAutoloader
{
    use AggregateTrait;

    protected $_aliases = [
        'NamespaceAutoloader' => 'Poirot\Loader\Autoloader\NamespaceAutoloader',
        'ClassMapAutoloader'  => 'Poirot\Loader\Autoloader\ClassMapAutoloader',
    ];

    /**
     * Tmp cache used to ignore recursion call for registered
     * autoloader objects
     *
     * @var array[hash=>iSplAutoloader]
     */
    protected $__tmp_registered_hash = [];

    /**
     * Construct
     *
     * Options:
     * ['NamespaceAutoloader' => [
     *    'Poirot\AaResponder'  => [APP_DIR_VENDOR.'/poirot/action-responder/Poirot/AaResponder'],
     *    'Poirot\Application'  => [APP_DIR_VENDOR.'/poirot/application/Poirot/Application'],
     * ]]
     *
     * @param array $options
     */
    function __construct(array $options = [])
    {
        ## register, so we can access related autoloader classes
        $autoloader = new NamespaceAutoloader(['Poirot\\Loader' => [dirname(__DIR__)]]);
        $autoloader->register(true);

        if (!empty($options))
            $this->__setupFromArray($options);

        ## unregister default autoloader after attaching
        $autoloader->unregister();
    }


    // ...

    /**
     * Setup Class With Options
     * @param array $options
     */
    protected function __setupFromArray(array $options)
    {
        foreach($options as $loader => $loaderOptions)
        {
            if (!is_string($loader) && is_string($loaderOptions)) {
                ## ['loaderClass', ..]
                $loader = $loaderOptions;
                $loaderOptions = null;
            }

            if ($loaderOptions)
                $loader = new $loader($loaderOptions);
            else
                $loader = new $loader;

            $this->attach($loader);
        }
    }
}
