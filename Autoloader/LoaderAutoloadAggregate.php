<?php
namespace Poirot\Loader\Autoloader;

use Poirot\Loader\Interfaces\iLoader;
use Poirot\Loader\Traits\tLoaderAggregate;

if (class_exists('Poirot\\Loader\\Autoloader\\LoaderAutoloadAggregate' , false))
    return;

require_once __DIR__ . '/aLoaderAutoload.php';
require_once __DIR__ . '/LoaderAutoloadNamespace.php';
require_once __DIR__ . '/../Traits\tLoaderAggregate.php';

/*
 * TODO lazy loading for attached loaders
 */
class LoaderAutoloadAggregate
    extends aLoaderAutoload
{
    use tLoaderAggregate {
        tLoaderAggregate::listAttached as protected _t__listAttached;
        tLoaderAggregate::by           as protected _t__by;
    }

    protected $_aliases = [
        'Namespace'               => 'LoaderAutoloadNamespace',
        'LoaderAutoloadNamespace' => \Poirot\Loader\Autoloader\LoaderAutoloadNamespace::class,

        'ClassMap'                => 'LoaderAutoloadClassMap',
        'LoaderAutoloadClassMap'  => \Poirot\Loader\Autoloader\LoaderAutoloadClassMap::class,
    ];

    /**
     * Construct
     *
     *
     *
     * @param array $options
     */
    function __construct(array $options = [])
    {
        ## register, so we can access related autoloader classes
        $autoloader = new LoaderAutoloadNamespace(['Poirot\\Loader' => [dirname(__DIR__)]]);
        $autoloader->register(true);

        if ($options !== null)
            $this->with($options);

        ## unregister default autoloader after attaching
        $autoloader->unregister();
    }

    /**
     * @override get by aliased
     *
     * @inheritdoc
     * @return iLoader
     */
    function by($name)
    {
        while(isset($this->_aliases[$name]))
            $name = $this->_aliases[$name];

        ## resolve to loader class name by registered autoloaders
        return $this->_t__by($name);
    }

    /**
     * @override list aliased
     *
     * @inheritdoc
     * @return array Associate Array Of Name
     */
    function listAttached()
    {
        return array_merge(array_keys($this->_aliases), $this->_t__listAttached());
    }
}
