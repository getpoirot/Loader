<?php
namespace Poirot\Loader\Autoloader;

use Poirot\Loader\AggregateTrait;

if (class_exists('Poirot\\Loader\\AggregateAutoloader'))
    return;

require_once __DIR__ . '/AbstractAutoloader.php';
require_once __DIR__ . '/NamespaceAutoloader.php';
require_once __DIR__ . '/../AggregateTrait.php';

class AggregateAutoloader extends AbstractAutoloader
{
    use AggregateTrait;

    protected $_default_loaders = [
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
     * @param array $options
     */
    function __construct(array $options = [])
    {
        ## register, so we can access related autoloader classes
        $autoloader = new NamespaceAutoloader(['Poirot\\Loader' => dirname(__DIR__)]);
        $autoloader->register(true);

        if (!empty($options))
            $this->__conOptions($options);

        ## unregister default autoloader after attaching
        $autoloader->unregister();
    }


    // ...

    /**
     * Setup Class With Options
     * @param array $options
     */
    protected function __conOptions(array $options)
    {
        foreach($options as $loader => $loaderOptions) {
            if (isset($this->_default_loaders[$loader]))
                $loader = $this->_default_loaders[$loader];

            $loader = new $loader($loaderOptions);
            $this->attach($loader);
        }
    }
}
