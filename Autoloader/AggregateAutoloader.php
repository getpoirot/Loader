<?php
namespace Poirot\Loader\Autoloader;

use Poirot\Loader\Interfaces\iSplAutoloader;

if (class_exists('Poirot\\Loader\\AggregateAutoloader'))
    return;

require_once __DIR__ . '/NamespaceAutoloader.php';
require_once __DIR__ . '/../Interfaces/iSplAutoloader.php';

class AggregateAutoloader implements iSplAutoloader
{
    protected $_attachedAutoloader = [
        'queue' => [/* ..iSplAutoloader */],
        'names' => [
            # Used to get autoloader instances
            ## 'AutoloadName_Or_ClassName' => iSplAutoloader
        ],
    ];

    /**
     * Tmp cache used to ignore recursion call for registered
     * autoloader objects
     *
     * @var array
     */
    protected $_registeredTmp = [];

    /**
     * Construct
     *
     */
    function __construct()
    {
        // Attach Default Autoloaders:
        ## register, so we can access related autoloader classes
        $autoloader = new NamespaceAutoloader(['Poirot\\Loader' => __DIR__]);
        $autoloader->register();

        $this->attach($autoloader);
        $this->attach(new ClassMapAutoloader);
    }

    // Implement Specific AggregateAutoloader Methods:

    /**
     * Resolve To Resource
     *
     * @param mixed $resource
     *
     * @return mixed
     */
    function resolve($resource)
    {
        // TODO: Implement resolve() method.
    }

    /**
     * Attach (insert) Autoloader
     *
     * @param iSplAutoloader $autoloader
     *
     * @return $this
     */
    function attach(iSplAutoloader $autoloader)
    {
        $this->_attachedAutoloader['queue'][]  = $autoloader;

        $autoloaderClass = get_class($autoloader);
        $this->_attachedAutoloader['names'][$autoloaderClass] = $autoloader;

        return $this;
    }

    /**
     * Get Attached Autoloader List
     *
     * @return array Associate Array Of Name=>iSplAutoloader
     */
    function listAttached()
    {
        return $this->_attachedAutoloader['names'];
    }

    /**
     * Get Autoloader By Name
     *
     * @param string $name Autoloader Name, default is class name
     *
     * @throws \Exception Autoloader class not found
     * @return iSplAutoloader
     */
    function autoloader($name)
    {
        $list = $this->listAttached();
        if (!$this->hasAttached($name))
            throw new \Exception(sprintf(
                'Autoloader by name "%s" not attached.'
                , $name
            ));

        return $list[$name];
    }

    /**
     * Has Autoloader With This Name Attached?
     *
     * @param string $name Autoloader Name, default is class name
     *
     * @return bool
     */
    function hasAttached($name)
    {
        $list = $this->listAttached();

        return array_key_exists($name, $list);
    }

    // Implement iSplAutoloader:

    /**
     * Register to spl autoloader
     *
     * <code>
     * spl_autoload_register(callable);
     * </code>
     *
     * @return void
     */
    function register()
    {
        foreach($this->_attachedAutoloader['queue'] as $i => $sa) {
            $objectHash = spl_object_hash($sa);

            if (in_array($objectHash, $this->_registeredTmp))
                // registered before
                return;

            /** @var iSplAutoloader $sa */
            $sa->register();
            $this->_registeredTmp[] = $objectHash;
        }
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
        foreach($this->_registeredTmp as $sa)
            /** @var iSplAutoloader $sa */
            $sa->unregister();
    }
}
