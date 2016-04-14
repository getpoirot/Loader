<?php
namespace Poirot\Loader\Autoloader;

if (class_exists('Poirot\Loader\Autoloader\LoaderAutoloadAggregate', false))
    return;

use Poirot\Loader\LoaderAggregate;
use Poirot\Loader\Interfaces\iLoader;
use Poirot\Loader\Interfaces\iLoaderAutoload;

require_once __DIR__ . '/../LoaderAggregate.php';
require_once __DIR__ . '/../Interfaces/iLoaderAutoload.php';

/*
 * TODO lazy loading for attached loaders
 */
class LoaderAutoloadAggregate
    extends LoaderAggregate
    implements iLoaderAutoload
{
    protected $_aliases = array(
        'Namespace'               => 'LoaderAutoloadNamespace',
        'LoaderAutoloadNamespace' => '\Poirot\Loader\Autoloader\LoaderAutoloadNamespace',
//        'LoaderAutoloadNamespace' => \Poirot\Loader\Autoloader\LoaderAutoloadNamespace::class,

        'ClassMap'                => 'LoaderAutoloadClassMap',
        'LoaderAutoloadClassMap'  => '\Poirot\Loader\Autoloader\LoaderAutoloadClassMap',
//        'LoaderAutoloadClassMap'  => \Poirot\Loader\Autoloader\LoaderAutoloadClassMap::class,
    );

    /**
     * Construct
     *
     * @param array $options
     */
    function __construct(array $options = null)
    {
        ## register, so we can access related autoloader classes
        $autoloader = new LoaderAutoloadNamespace( array('Poirot\Loader' => array(dirname(__DIR__))) );
        $autoloader->register(true);

        parent::__construct($options);

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
        return parent::by($name);
    }

    /**
     * @override list aliased
     *
     * @inheritdoc
     * @return array Associate Array Of Name
     */
    function listAttached()
    {
        return array_merge(array_keys($this->_aliases), parent::listAttached());
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
