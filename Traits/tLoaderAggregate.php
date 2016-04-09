<?php
namespace Poirot\Loader\Traits;

use SplPriorityQueue;

use Poirot\Loader\Interfaces\iLoader;

trait tLoaderAggregate
{
    /**
     * @var SplPriorityQueue
     */
    protected $_t_loader_aggregate_Queue;

    protected $_t_loader_aggregate_Names = [
        # Used to get loader instances
        ## 'LoaderName_Or_ClassName' => iLoader
    ];

    /**
     * Build Object With Provided Options
     * > Setup Aggregate Loader
     *   Options:
     *  [
     *    'attach' => [0 => iLoader, $priority => iLoader],
     *    'Registered\ClassLoader' => [
             // Options
     *       'Poirot\AaResponder'  => [APP_DIR_VENDOR.'/poirot/action-responder/Poirot/AaResponder'],
     *       'Poirot\Application'  => [APP_DIR_VENDOR.'/poirot/application/Poirot/Application'],
     *    ]
     *  ]
     *
     * @param array $options       Associated Array
     * @param bool $throwException Throw Exception On Wrong Option
     *
     * @throws \Exception
     * @return $this
     */
    function with(array $options, $throwException = false)
    {
        # Attach Loader:
        if (isset($options['attach'])) {
            $attach = $options['attach'];
            if(!is_array($attach))
                $attach = [$attach];

            foreach($attach as $pr => $loader)
                $this->attach($loader, $pr);

            unset($options['attach']);
        }

        # Set Loader Specific Config:
        foreach($options as $loader => $loaderOptions) {

            try{
                $loader = $this->by($loader);
            } catch (\Exception $e) {
                if ($throwException)
                    throw new \InvalidArgumentException(sprintf(
                        'Loader (%s) not attached.'
                        , $loader
                    ));
            }

            if (method_exists($loader, 'with'))
                /** @var \Poirot\Std\Interfaces\Pact\ipConfigurable $loader */
                $loader->with($loaderOptions);
        }

        return $this;
    }

    /**
     * Load Build Options From Given Resource
     *
     * - usually it used in cases that we have to support
     *   more than once configure situation
     *   [code:]
     *     Configurable->with(Configurable::withOf(path\to\file.conf))
     *   [code]
     *
     *
     * @param array|mixed $resource
     *
     * @throws \InvalidArgumentException if resource not supported
     * @return array
     */
    static function withOf($resource)
    {
        if (!is_array($resource))
            throw new \InvalidArgumentException(sprintf(
                'Resource must be an array, given: (%s).'
                , \Poirot\Std\flatten($resource)
            ));

        return $resource;
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
        foreach(clone $this->_t_loader_aggregate_getQueue() as $loader) {
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
        $this->_t_loader_aggregate_getQueue()->insert($loader, $priority);

        $loaderClass = get_class($loader);
        $this->_t_loader_aggregate_Names[$loaderClass] = $loader;

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
    function by($name)
    {
        if (!$this->hasAttached($name))
            throw new \Exception(sprintf(
                'Loader with name (%s) has not attached.'
                , $name
            ));

        return $this->_t_loader_aggregate_Names[$name];
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
}
