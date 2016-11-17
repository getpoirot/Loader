<?php
namespace Poirot\Loader\Traits;

use SplPriorityQueue;

use Poirot\Loader\Interfaces\iLoader;

trait tLoaderAggregate
{
    ## @see fixes/LoaderAggregate;
    ## Code Clone <begin> =================================================================
    /** @var SplPriorityQueue */
    protected $_t_loader_aggregate_Queue;

    protected $_t_loader_aggregate_Names = array(
        # Used to get loader instances
        ## 'LoaderName_Or_ClassName' => iLoader
    );
    protected $_c__normalized = array();


    /**
     * Resolve To Resource
     *
     * @param string $name
     *
     * @return mixed
     */
    function resolve($name)
    {
        $resolve = false;
        /** @var iLoader $loader */
        foreach(clone $this->_t_loader_aggregate_getQueue() as $loader) {
            $resolve = call_user_func_array(array($loader, 'resolve'), func_get_args());
            if ($resolve)
                break;
        }

        return $resolve;
    }

    /**
     * Attach (insert) Loader
     *
     * - it will store loader can retrieved by ClassName
     *
     * @param iLoader     $loader
     * @param int         $priority
     *
     * @return $this
     */
    function attach(iLoader $loader, $priority = 0)
    {
        $this->_t_loader_aggregate_getQueue()->insert($loader, $priority);

        $loaderClass = get_class($loader);
        $loaderClass = $this->_normalizeLoaderName($loaderClass);
        $this->_t_loader_aggregate_Names[$loaderClass] = $loader;

        return $this;
    }

    /**
     * Get Loader By Name
     *
     * [code:]
     *  $aggregateLoader->loader(\Poirot\Loader\Autoloader\LoaderAutoloadNamespace::class)
     *     ->with([..options])
     * [code]
     *
     * @param string $loaderName Loader Name, default is class name
     *
     * @throws \Exception Loader class not found
     * @return iLoader
     */
    function loader($loaderName)
    {
        $loaderName = $this->_normalizeLoaderName($loaderName);

        if (!$this->hasAttached($loaderName))
            throw new \Exception(sprintf(
                'Loader with name (%s) has not attached.'
                , $loaderName
            ));

        return $this->_t_loader_aggregate_Names[$loaderName];
    }

    /**
     * Has Loader With This Name Attached?
     *
     * [code:]
     *  $aggregateLoader->hasAttached(\Poirot\Loader\Autoloader\LoaderAutoloadNamespace::class)
     * [code]
     *
     * @param string $loaderName Loader Name, default is class name
     *
     * @return bool
     */
    function hasAttached($loaderName)
    {
        $loaderName = $this->_normalizeLoaderName($loaderName);
        return in_array($loaderName, $this->listAttached());
    }

    /**
     * Get Attached loader List
     *
     * @return array Array Of Names
     */
    function listAttached()
    {
        return array_keys($this->_t_loader_aggregate_Names);
    }


    // ...

    protected function _t_loader_aggregate_getQueue()
    {
        if (!$this->_t_loader_aggregate_Queue)
            ## standard spl queue to avoid using extra libraries
            $this->_t_loader_aggregate_Queue = new SplPriorityQueue();

        return $this->_t_loader_aggregate_Queue;
    }

    protected function _normalizeLoaderName($loaderName)
    {
        $loaderName = (string) $loaderName;
        if (isset($this->_c__normalized[$loaderName]))
            return $this->_c__normalized[$loaderName];

        $normalized = ltrim($loaderName, '\\');
        return $this->_c__normalized[$loaderName] = $normalized;
    }
    ## Code Clone <end> ===================================================================
}
