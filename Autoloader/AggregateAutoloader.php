<?php
namespace Poirot\Loader\Autoloader;

use Poirot\Loader\AggregateTrait;
use Poirot\Loader\Interfaces\iLoader;

if (class_exists('Poirot\\Loader\\AggregateAutoloader'))
    return;

require_once __DIR__ . '/AbstractAutoloader.php';
require_once __DIR__ . '/NamespaceAutoloader.php';
require_once __DIR__ . '/../AggregateTrait.php';

class AggregateAutoloader extends AbstractAutoloader
{
    use AggregateTrait;
    /*use AggregateTrait {
        -        AggregateTrait::loader as protected _t__loader;
        -        AggregateTrait::listAttached as protected _t__listAttached;
        -    }*/

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
        $autoloader = new NamespaceAutoloader(['Poirot\\Loader' => [dirname(__DIR__)]]);
        $autoloader->register(true);

        if (!empty($options))
            $this->__setupFromArray($options);

        ## unregister default autoloader after attaching
        $autoloader->unregister();
    }

    /**
     * @override Using lazy loading
     *
     * @inheritdoc
     * @return iLoader
     */
    function _loader($name)
    {
        if (!$this->hasAttached($name))
            throw new \Exception(sprintf(
                'Loader with name (%s) has not attached.'
                , $name
            ));

        return $this->_t_loader_names[$name];
    }

    /**
     * @override Lazy Loading
     *
     * @inheritdoc
     * @return array Associate Array Of Name
     */
    function _listAttached()
    {
        $_t_attached = $this->_t__listAttached();

        return array_merge($this->_c__setup, $_t_attached);
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
