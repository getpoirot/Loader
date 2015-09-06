<?php
namespace Poirot\Loader;

use Poirot\Loader\Interfaces\iLoader;

trait AggregateTrait
{
    protected $_attachedLoader = [
        'queue' => [/* ..iLoader */],
        'names' => [
            # Used to get loader instances
            ## 'LoaderName_Or_ClassName' => iLoader
        ],
    ];

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
        foreach($this->_attachedLoader['queue'] as $loader) {
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
     *
     * @return $this
     */
    function attach(iLoader $loader)
    {
        $this->_attachedLoader['queue'][]  = $loader;

        $loaderClass = get_class($loader);
        $this->_attachedLoader['names'][$loaderClass] = $loader;

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
        $list = $this->listAttached();
        if (!$this->hasAttached($name))
            throw new \Exception(sprintf(
                'Loader with name (%s) has not attached.'
                , $name
            ));

        return $list[$name];
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
        $list = $this->listAttached();

        return array_key_exists($name, $list);
    }

    /**
     * Get Attached loader List
     *
     * @return array Associate Array Of Name=>iLoader
     */
    function listAttached()
    {
        return $this->_attachedLoader['names'];
    }
}
