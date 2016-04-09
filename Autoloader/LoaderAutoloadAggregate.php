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
        tLoaderAggregate::loader       as protected _t__loader;
    }

    protected $_aliases = [
        'NamespaceAutoloader' => 'Poirot\Loader\Autoloader\NamespaceAutoloader',
        'ClassMapAutoloader'  => 'Poirot\Loader\Autoloader\ClassMapAutoloader',
    ];

    /**
     * Construct
     *
     * Options:
     * ## alias or class name
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
        $autoloader = new LoaderAutoloadNamespace(['Poirot\\Loader' => [dirname(__DIR__)]]);
        $autoloader->register(true);

        if (!empty($options))
            $this->__setupFromArray($options);

        ## unregister default autoloader after attaching
        $autoloader->unregister();
    }

    /**
     * @override get by aliased
     *
     * @inheritdoc
     * @return iLoader
     */
    function loader($name)
    {
        if (isset($this->_aliases[$name]))
            $name = $this->_aliases[$name];

        return $this->_t__loader($name);
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

    // ...

    /**
     * Setup Class With Options
     * @param array $options
     */
    protected function __setupFromArray(array $options)
    {
        foreach($options as $loader => $loaderOptions) {
            if (!is_string($loader) && is_string($loaderOptions)) {
                ## ['loaderClass', ..]
                $loader = $loaderOptions;
                $loaderOptions = null;
            }

            if (isset($this->_aliases[$loader]))
                ## register alias name class
                $loader = $this->_aliases[$loader];

            if ($loaderOptions)
                $loader = new $loader($loaderOptions);
            else
                $loader = new $loader;

            $this->attach($loader);
        }
    }
}
