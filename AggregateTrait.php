<?php
namespace Poirot\Loader;

use Poirot\Loader\Interfaces\iLoader;
use SplPriorityQueue;

trait AggregateTrait
{
    /**
     * @var \SplPriorityQueue
     */
    protected $_prioQuee;

    protected $_t_loader_names = [
        # Used to get loader instances
        ## 'LoaderName_Or_ClassName' => iLoader
    ];

    /**
     * Setup Aggregate Loader
     *
     * @param array $resource
     *
     * @return $this
     */
    function from($resource)
    {
        if (is_array($resource))
            $this->fromArray($resource);

        return $this;
    }

    /**
     * Setup Aggregate Loader
     *
     * @param array $options
     *
     * @return $this
     */
    function fromArray(array $options)
    {
        if (isset($options['attach'])) {
            $attach = $options['attach'];
            if(!is_array($attach))
                $attach = [$attach];

            foreach($attach as $pr => $loader)
                $this->attach($loader, $pr);
        }

        return $this;
    }

    /**
     * Resolve To Resource
     *
     * @param mixed $resource
     *
     * @return mixed
     */
    function resolve($resource)
    {
        $resolve = false;
        /** @var iLoader $loader */
        foreach(clone $this->_getPrioQuee() as $loader) {
            $resolve = call_user_func_array([$loader, 'resolve'], func_get_args());
            if ($resolve)
                break;
        }

        return $resolve;
    }

    /**
     * Attach (insert) Loader
     *
     * @param iLoader $loader
     * @param int     $priority
     *
     * @return $this
     */
    function attach(iLoader $loader, $priority = 0)
    {
        $this->_getPrioQuee()->insert($loader, $priority);

        $loaderClass = get_class($loader);
        $this->_t_loader_names[$loaderClass] = $loader;

        return $this;
    }

    /**
     * Get Loader By Name
     *
     * @param string $name Loader Name, default is class name
     *
     * @throws \Exception Loader class not found
     * @return iLoader
     */
    function loader($name)
    {
        if (!$this->hasAttached($name))
            throw new \Exception(sprintf(
                'Loader with name (%s) has not attached.'
                , $name
            ));

        return $this->_t_loader_names[$name];
    }

    /**
     * Has Loader With This Name Attached?
     *
     * @param string $name Loader Name, default is class name
     *
     * @return bool
     */
    function hasAttached($name)
    {
        return in_array($name, $this->listAttached());
    }

    /**
     * Get Attached loader List
     *
     * @return array Associate Array Of Name
     */
    function listAttached()
    {
        return array_keys($this->_t_loader_names);
    }

    protected function _getPrioQuee()
    {
        if (!$this->_prioQuee)
            ## standard spl queue to avoid using extra libraries
            $this->_prioQuee = new SplPriorityQueue();

        return $this->_prioQuee;
    }
}
